<?php
use PHPUnit\Framework\TestCase;
use App\Models\EspecieModel;

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

class EspecieModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'nombre' => 'Canino'
            ],
            [
                'id' => 2, 
                'nombre' => 'Felino'
            ]
        ];
        $model = new EspecieModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(2, $all);
        $this->assertSame('Canino', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new EspecieModel($fakeDb);
        $ok = $model->crear([
            'nombre' => 'Ave'
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':nombre', $fakeDb->bindings);
        $this->assertEquals('Ave', $fakeDb->bindings[':nombre']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new EspecieModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'nombre' => 'Canino Actualizado'
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testObtenerPorIdReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Canino']];
        $model = new EspecieModel($fakeDb);
        $especie = $model->obtenerPorId(1);
        $this->assertSame('Canino', $especie['nombre']);
    }

    public function testObtenerPorIdReturnsNullWhenNotFound() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EspecieModel($fakeDb);
        $especie = $model->obtenerPorId(999);
        $this->assertNull($especie);
    }

    public function testObtenerPorNombreReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Canino']];
        $model = new EspecieModel($fakeDb);
        $especie = $model->obtenerPorNombre('Canino');
        $this->assertSame('Canino', $especie['nombre']);
    }

    public function testEspecieExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new EspecieModel($fakeDb);
        $this->assertTrue($model->especieExiste(1));
    }

    public function testEspecieExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EspecieModel($fakeDb);
        $this->assertFalse($model->especieExiste(999));
    }

    public function testNombreExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new EspecieModel($fakeDb);
        $this->assertTrue($model->nombreExiste('Canino'));
    }

    public function testNombreExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EspecieModel($fakeDb);
        $this->assertFalse($model->nombreExiste('Inexistente'));
    }

    public function testNombreExisteWithExcludeIdReturnsFalseWhenExcludingSameId() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EspecieModel($fakeDb);
        $this->assertFalse($model->nombreExiste('Canino', 1));
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['total_especies' => 6, 'total_razas' => 12]
        ];
        $model = new EspecieModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(1, $estadisticas);
        $this->assertEquals(6, $estadisticas[0]['total_especies']);
    }

    public function testObtenerConRazasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'especie_nombre' => 'Canino', 'total_razas' => 5],
            ['id' => 2, 'especie_nombre' => 'Felino', 'total_razas' => 3]
        ];
        $model = new EspecieModel($fakeDb);
        $especies = $model->obtenerConRazas();
        $this->assertCount(2, $especies);
        $this->assertSame('Canino', $especies[0]['especie_nombre']);
        $this->assertEquals(5, $especies[0]['total_razas']);
    }
}
