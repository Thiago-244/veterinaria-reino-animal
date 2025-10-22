<?php
use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\MascotaModel;

/**
 * Requiere que la BD real exista y tenga las tablas necesarias.
 * Cada test se ejecuta dentro de una transacción y realiza rollback para no persistir cambios.
 */
class MascotaModelIntegrationTest extends TestCase {
    private Database $db;

    protected function setUp(): void {
        $this->db = new Database();
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        $this->db->rollBack();
    }

    public function testCrearInsertaEnBDRealConRollback() {
        $model = new MascotaModel($this->db);
        
        // Primero necesitamos un cliente y una raza existentes
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono) VALUES ('99988877', 'TestCliente', 'TestApellido', '900000000')");
        $this->db->execute();
        $clienteId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO especies (nombre) VALUES ('TestEspecie')");
        $this->db->execute();
        $especieId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO razas (id_especie, nombre) VALUES (:especie_id, 'TestRaza')");
        $this->db->bind(':especie_id', $especieId);
        $this->db->execute();
        $razaId = $this->db->lastInsertId();
        
        $ok = $model->crear([
            'codigo' => 'CM-TEST-1',
            'nombre' => 'TestMascota',
            'id_cliente' => $clienteId,
            'id_raza' => $razaId,
            'fecha_nacimiento' => '2020-01-01',
            'sexo' => 'Macho',
            'color' => 'TestColor',
            'peso' => 10.5,
            'foto' => 'default_pet.png'
        ]);
        $this->assertTrue($ok);

        // Verificar que el registro está visible dentro de la transacción
        $this->db->query("SELECT COUNT(*) as c FROM mascotas WHERE codigo = :codigo");
        $this->db->bind(':codigo', 'CM-TEST-1');
        $count = $this->db->resultSet();
        $this->assertSame(1, (int)$count[0]['c']);
    }

    public function testActualizarYEliminarConRollback() {
        $model = new MascotaModel($this->db);
        
        // Crear datos de prueba
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono) VALUES ('88877766', 'TempCliente', 'TempApellido', '800000000')");
        $this->db->execute();
        $clienteId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO especies (nombre) VALUES ('TempEspecie')");
        $this->db->execute();
        $especieId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO razas (id_especie, nombre) VALUES (:especie_id, 'TempRaza')");
        $this->db->bind(':especie_id', $especieId);
        $this->db->execute();
        $razaId = $this->db->lastInsertId();
        
        // Crear mascota temporal
        $this->assertTrue($model->crear([
            'codigo' => 'CM-TEMP-1',
            'nombre' => 'TempMascota',
            'id_cliente' => $clienteId,
            'id_raza' => $razaId,
            'fecha_nacimiento' => '2019-01-01',
            'sexo' => 'Hembra',
            'color' => 'TempColor',
            'peso' => 8.0,
            'foto' => 'default_pet.png'
        ]));

        // Obtener su id
        $this->db->query("SELECT id FROM mascotas WHERE codigo = :codigo");
        $this->db->bind(':codigo', 'CM-TEMP-1');
        $row = $this->db->resultSet();
        $id = (int)$row[0]['id'];

        // Actualizar
        $this->assertTrue($model->actualizar($id, [
            'nombre' => 'TempMascota2',
            'id_cliente' => $clienteId,
            'id_raza' => $razaId,
            'fecha_nacimiento' => '2019-01-01',
            'sexo' => 'Hembra',
            'color' => 'TempColor2',
            'peso' => 9.0
        ]));

        // Verificar cambio visible en transacción
        $found = $model->obtenerPorId($id);
        $this->assertSame('TempMascota2', $found['nombre']);

        // Eliminar
        $this->assertTrue($model->eliminar($id));
    }

    public function testObtenerTodasConJoins() {
        $model = new MascotaModel($this->db);
        
        // Crear datos de prueba
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono) VALUES ('77766655', 'JoinCliente', 'JoinApellido', '700000000')");
        $this->db->execute();
        $clienteId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO especies (nombre) VALUES ('JoinEspecie')");
        $this->db->execute();
        $especieId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO razas (id_especie, nombre) VALUES (:especie_id, 'JoinRaza')");
        $this->db->bind(':especie_id', $especieId);
        $this->db->execute();
        $razaId = $this->db->lastInsertId();
        
        $this->assertTrue($model->crear([
            'codigo' => 'CM-JOIN-1',
            'nombre' => 'JoinMascota',
            'id_cliente' => $clienteId,
            'id_raza' => $razaId,
            'fecha_nacimiento' => '2021-01-01',
            'sexo' => 'Macho',
            'color' => 'JoinColor',
            'peso' => 12.0,
            'foto' => 'default_pet.png'
        ]));

        $mascotas = $model->obtenerTodas();
        $this->assertNotEmpty($mascotas);
        
        // Verificar que los joins funcionan
        $mascota = $mascotas[0];
        $this->assertArrayHasKey('cliente_nombre', $mascota);
        $this->assertArrayHasKey('raza_nombre', $mascota);
        $this->assertArrayHasKey('especie_nombre', $mascota);
    }

    public function testObtenerPorClienteFiltraCorrectamente() {
        $model = new MascotaModel($this->db);
        
        // Crear dos clientes
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono) VALUES ('66655544', 'Cliente1', 'Apellido1', '600000000')");
        $this->db->execute();
        $cliente1Id = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono) VALUES ('55544433', 'Cliente2', 'Apellido2', '500000000')");
        $this->db->execute();
        $cliente2Id = $this->db->lastInsertId();
        
        // Crear especie y raza
        $this->db->query("INSERT INTO especies (nombre) VALUES ('TestEspecie2')");
        $this->db->execute();
        $especieId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO razas (id_especie, nombre) VALUES (:especie_id, 'TestRaza2')");
        $this->db->bind(':especie_id', $especieId);
        $this->db->execute();
        $razaId = $this->db->lastInsertId();
        
        // Crear mascotas para ambos clientes
        $model->crear([
            'codigo' => 'CM-CLI1-1',
            'nombre' => 'MascotaCliente1',
            'id_cliente' => $cliente1Id,
            'id_raza' => $razaId,
            'fecha_nacimiento' => '2020-01-01',
            'sexo' => 'Macho',
            'color' => 'Color1',
            'peso' => 10.0,
            'foto' => 'default_pet.png'
        ]);
        
        $model->crear([
            'codigo' => 'CM-CLI2-1',
            'nombre' => 'MascotaCliente2',
            'id_cliente' => $cliente2Id,
            'id_raza' => $razaId,
            'fecha_nacimiento' => '2021-01-01',
            'sexo' => 'Hembra',
            'color' => 'Color2',
            'peso' => 8.0,
            'foto' => 'default_pet.png'
        ]);
        
        // Verificar que solo se obtienen las mascotas del cliente 1
        $mascotasCliente1 = $model->obtenerPorCliente($cliente1Id);
        $this->assertCount(1, $mascotasCliente1);
        $this->assertSame('MascotaCliente1', $mascotasCliente1[0]['nombre']);
        
        // Verificar que solo se obtienen las mascotas del cliente 2
        $mascotasCliente2 = $model->obtenerPorCliente($cliente2Id);
        $this->assertCount(1, $mascotasCliente2);
        $this->assertSame('MascotaCliente2', $mascotasCliente2[0]['nombre']);
    }
}
