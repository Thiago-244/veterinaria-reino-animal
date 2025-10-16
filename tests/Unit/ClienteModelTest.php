<?php
use PHPUnit\Framework\TestCase;
use App\Models\ClienteModel;

class FakeDatabase {
    public array $queries = [];
    public array $bindings = [];
    public bool $executeReturn = true;
    public array $result = [];

    public function query($sql) { $this->queries[] = $sql; }
    public function bind($param, $value, $type = null) { $this->bindings[$param] = $value; }
    public function execute() { return $this->executeReturn; }
    public function resultSet() { return $this->result; }
}

class ClienteModelTest extends TestCase {
    public function testObtenerTodosReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Ana']];
        $model = new ClienteModel($fakeDb);
        $all = $model->obtenerTodos();
        $this->assertCount(1, $all);
        $this->assertSame('Ana', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new ClienteModel($fakeDb);
        $ok = $model->crear([
            'dni' => '12345678',
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'telefono' => '999',
            'email' => 'ana@example.com'
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':dni', $fakeDb->bindings);
        $this->assertEquals('12345678', $fakeDb->bindings[':dni']);
        $this->assertNotEmpty($fakeDb->queries);
    }
}


