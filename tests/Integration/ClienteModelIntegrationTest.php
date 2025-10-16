<?php
use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\ClienteModel;

/**
 * Requiere que la BD real exista y tenga la tabla `clientes` con columnas
 * (dni, nombre, apellido, telefono, email). Cada test se ejecuta dentro de
 * una transacción y realiza rollback para no persistir cambios.
 */
class ClienteModelIntegrationTest extends TestCase {
    private Database $db;

    protected function setUp(): void {
        $this->db = new Database();
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        $this->db->rollBack();
    }

    public function testCrearInsertaEnBDRealConRollback() {
        $model = new ClienteModel($this->db);
        $ok = $model->crear([
            'dni' => '99988877',
            'nombre' => 'TestNombre',
            'apellido' => 'TestApellido',
            'telefono' => '900000000',
            'email' => 'integ@example.com'
        ]);
        $this->assertTrue($ok);

        // Verificar que el registro está visible dentro de la transacción
        $this->db->query("SELECT COUNT(*) as c FROM clientes WHERE dni = :dni");
        $this->db->bind(':dni', '99988877');
        $count = $this->db->resultSet();
        $this->assertSame(1, (int)$count[0]['c']);
    }

    public function testActualizarYEliminarConRollback() {
        $model = new ClienteModel($this->db);
        // Crear uno temporal
        $this->assertTrue($model->crear([
            'dni' => '11223344',
            'nombre' => 'Temp',
            'apellido' => 'User',
            'telefono' => '911111111',
            'email' => 'temp@example.com'
        ]));

        // Obtener su id
        $this->db->query("SELECT id FROM clientes WHERE dni = :dni");
        $this->db->bind(':dni', '11223344');
        $row = $this->db->resultSet();
        $id = (int)$row[0]['id'];

        // Actualizar
        $this->assertTrue($model->actualizar($id, [
            'dni' => '11223344',
            'nombre' => 'Temp2',
            'apellido' => 'User2',
            'telefono' => '922222222',
            'email' => 'temp2@example.com'
        ]));

        // Verificar cambio visible en transacción
        $found = $model->obtenerPorId($id);
        $this->assertSame('Temp2', $found['nombre']);

        // Eliminar
        $this->assertTrue($model->eliminar($id));
    }
}


