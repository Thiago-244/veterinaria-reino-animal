<?php
use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\CitaModel;

/**
 * Requiere que la BD real exista y tenga las tablas necesarias.
 * Cada test se ejecuta dentro de una transacción y realiza rollback para no persistir cambios.
 */
class CitaModelIntegrationTest extends TestCase {
    private Database $db;

    protected function setUp(): void {
        $this->db = new Database();
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        $this->db->rollBack();
    }

    public function testCrearInsertaEnBDRealConRollback() {
        $model = new CitaModel($this->db);
        
        // Primero necesitamos un cliente y una mascota existentes
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
        
        $this->db->query("INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo) VALUES ('CM-TEST-1', 'TestMascota', :cliente_id, :raza_id, '2020-01-01', 'Macho')");
        $this->db->bind(':cliente_id', $clienteId);
        $this->db->bind(':raza_id', $razaId);
        $this->db->execute();
        $mascotaId = $this->db->lastInsertId();
        
        $ok = $model->crear([
            'codigo' => 'CT-TEST-1',
            'id_mascota' => $mascotaId,
            'id_cliente' => $clienteId,
            'fecha_cita' => '2025-10-25 10:00:00',
            'motivo' => 'Consulta de prueba',
            'estado' => 'Pendiente'
        ]);
        $this->assertTrue($ok);

        // Verificar que el registro está visible dentro de la transacción
        $this->db->query("SELECT COUNT(*) as c FROM citas WHERE codigo = :codigo");
        $this->db->bind(':codigo', 'CT-TEST-1');
        $count = $this->db->resultSet();
        $this->assertSame(1, (int)$count[0]['c']);
    }

    public function testActualizarYEliminarConRollback() {
        $model = new CitaModel($this->db);
        
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
        
        $this->db->query("INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo) VALUES ('CM-TEMP-1', 'TempMascota', :cliente_id, :raza_id, '2020-01-01', 'Hembra')");
        $this->db->bind(':cliente_id', $clienteId);
        $this->db->bind(':raza_id', $razaId);
        $this->db->execute();
        $mascotaId = $this->db->lastInsertId();
        
        // Crear cita temporal
        $this->assertTrue($model->crear([
            'codigo' => 'CT-TEMP-1',
            'id_mascota' => $mascotaId,
            'id_cliente' => $clienteId,
            'fecha_cita' => '2025-10-26 14:00:00',
            'motivo' => 'Consulta temporal',
            'estado' => 'Pendiente'
        ]));

        // Obtener su id
        $this->db->query("SELECT id FROM citas WHERE codigo = :codigo");
        $this->db->bind(':codigo', 'CT-TEMP-1');
        $row = $this->db->resultSet();
        $id = (int)$row[0]['id'];

        // Actualizar
        $this->assertTrue($model->actualizar($id, [
            'id_mascota' => $mascotaId,
            'id_cliente' => $clienteId,
            'fecha_cita' => '2025-10-26 15:00:00',
            'motivo' => 'Consulta temporal actualizada',
            'estado' => 'Procesada'
        ]));

        // Verificar cambio visible en transacción
        $found = $model->obtenerPorId($id);
        $this->assertSame('Consulta temporal actualizada', $found['motivo']);
        $this->assertSame('Procesada', $found['estado']);

        // Eliminar
        $this->assertTrue($model->eliminar($id));
    }

    public function testObtenerTodasConJoins() {
        $model = new CitaModel($this->db);
        
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
        
        $this->db->query("INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo) VALUES ('CM-JOIN-1', 'JoinMascota', :cliente_id, :raza_id, '2021-01-01', 'Macho')");
        $this->db->bind(':cliente_id', $clienteId);
        $this->db->bind(':raza_id', $razaId);
        $this->db->execute();
        $mascotaId = $this->db->lastInsertId();
        
        $this->assertTrue($model->crear([
            'codigo' => 'CT-JOIN-1',
            'id_mascota' => $mascotaId,
            'id_cliente' => $clienteId,
            'fecha_cita' => '2025-10-27 09:00:00',
            'motivo' => 'Consulta con joins',
            'estado' => 'Pendiente'
        ]));

        $citas = $model->obtenerTodas();
        $this->assertNotEmpty($citas);
        
        // Verificar que los joins funcionan
        $cita = $citas[0];
        $this->assertArrayHasKey('mascota_nombre', $cita);
        $this->assertArrayHasKey('cliente_nombre', $cita);
        $this->assertArrayHasKey('cliente_apellido', $cita);
    }

    public function testObtenerPorClienteFiltraCorrectamente() {
        $model = new CitaModel($this->db);
        
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
        $this->db->query("INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo) VALUES ('CM-CLI1-1', 'MascotaCliente1', :cliente_id, :raza_id, '2020-01-01', 'Macho')");
        $this->db->bind(':cliente_id', $cliente1Id);
        $this->db->bind(':raza_id', $razaId);
        $this->db->execute();
        $mascota1Id = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo) VALUES ('CM-CLI2-1', 'MascotaCliente2', :cliente_id, :raza_id, '2021-01-01', 'Hembra')");
        $this->db->bind(':cliente_id', $cliente2Id);
        $this->db->bind(':raza_id', $razaId);
        $this->db->execute();
        $mascota2Id = $this->db->lastInsertId();
        
        // Crear citas para ambos clientes
        $model->crear([
            'codigo' => 'CT-CLI1-1',
            'id_mascota' => $mascota1Id,
            'id_cliente' => $cliente1Id,
            'fecha_cita' => '2025-10-28 10:00:00',
            'motivo' => 'Cita cliente 1',
            'estado' => 'Pendiente'
        ]);
        
        $model->crear([
            'codigo' => 'CT-CLI2-1',
            'id_mascota' => $mascota2Id,
            'id_cliente' => $cliente2Id,
            'fecha_cita' => '2025-10-29 11:00:00',
            'motivo' => 'Cita cliente 2',
            'estado' => 'Pendiente'
        ]);
        
        // Verificar que solo se obtienen las citas del cliente 1
        $citasCliente1 = $model->obtenerPorCliente($cliente1Id);
        $this->assertCount(1, $citasCliente1);
        $this->assertSame('CT-CLI1-1', $citasCliente1[0]['codigo']);
        
        // Verificar que solo se obtienen las citas del cliente 2
        $citasCliente2 = $model->obtenerPorCliente($cliente2Id);
        $this->assertCount(1, $citasCliente2);
        $this->assertSame('CT-CLI2-1', $citasCliente2[0]['codigo']);
    }

    public function testCambiarEstadoFuncionaCorrectamente() {
        $model = new CitaModel($this->db);
        
        // Crear datos de prueba
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono) VALUES ('44433322', 'EstadoCliente', 'EstadoApellido', '400000000')");
        $this->db->execute();
        $clienteId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO especies (nombre) VALUES ('EstadoEspecie')");
        $this->db->execute();
        $especieId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO razas (id_especie, nombre) VALUES (:especie_id, 'EstadoRaza')");
        $this->db->bind(':especie_id', $especieId);
        $this->db->execute();
        $razaId = $this->db->lastInsertId();
        
        $this->db->query("INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo) VALUES ('CM-EST-1', 'EstadoMascota', :cliente_id, :raza_id, '2020-01-01', 'Macho')");
        $this->db->bind(':cliente_id', $clienteId);
        $this->db->bind(':raza_id', $razaId);
        $this->db->execute();
        $mascotaId = $this->db->lastInsertId();
        
        $this->assertTrue($model->crear([
            'codigo' => 'CT-EST-1',
            'id_mascota' => $mascotaId,
            'id_cliente' => $clienteId,
            'fecha_cita' => '2025-10-30 10:00:00',
            'motivo' => 'Prueba de estado',
            'estado' => 'Pendiente'
        ]));

        // Obtener ID de la cita
        $this->db->query("SELECT id FROM citas WHERE codigo = :codigo");
        $this->db->bind(':codigo', 'CT-EST-1');
        $row = $this->db->resultSet();
        $citaId = (int)$row[0]['id'];

        // Cambiar estado a Procesada
        $this->assertTrue($model->cambiarEstado($citaId, 'Procesada'));
        
        // Verificar el cambio
        $cita = $model->obtenerPorId($citaId);
        $this->assertSame('Procesada', $cita['estado']);

        // Cambiar estado a Cancelada
        $this->assertTrue($model->cambiarEstado($citaId, 'Cancelada'));
        
        // Verificar el cambio
        $cita = $model->obtenerPorId($citaId);
        $this->assertSame('Cancelada', $cita['estado']);
    }
}
