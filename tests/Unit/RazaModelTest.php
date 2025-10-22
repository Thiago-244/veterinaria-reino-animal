<?php
use PHPUnit\Framework\TestCase;
use App\Models\RazaModel;

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

class RazaModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'nombre' => 'Labrador',
                'id_especie' => 1,
                'especie_nombre' => 'Canino'
            ],
            [
                'id' => 2, 
                'nombre' => 'Persa',
                'id_especie' => 2,
                'especie_nombre' => 'Felino'
            ]
        ];
        $model = new RazaModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(2, $all);
        $this->assertSame('Labrador', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new RazaModel($fakeDb);
        $ok = $model->crear([
            'id_especie' => 1,
            'nombre' => 'Golden Retriever'
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':id_especie', $fakeDb->bindings);
        $this->assertArrayHasKey(':nombre', $fakeDb->bindings);
        $this->assertEquals(1, $fakeDb->bindings[':id_especie']);
        $this->assertEquals('Golden Retriever', $fakeDb->bindings[':nombre']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new RazaModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'id_especie' => 1,
            'nombre' => 'Labrador Actualizado'
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testObtenerPorIdReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Labrador', 'id_especie' => 1, 'especie_nombre' => 'Canino']];
        $model = new RazaModel($fakeDb);
        $raza = $model->obtenerPorId(1);
        $this->assertSame('Labrador', $raza['nombre']);
    }

    public function testObtenerPorIdReturnsNullWhenNotFound() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new RazaModel($fakeDb);
        $raza = $model->obtenerPorId(999);
        $this->assertNull($raza);
    }

    public function testObtenerPorEspecieReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Labrador', 'id_especie' => 1],
            ['id' => 2, 'nombre' => 'Golden Retriever', 'id_especie' => 1]
        ];
        $model = new RazaModel($fakeDb);
        $razas = $model->obtenerPorEspecie(1);
        $this->assertCount(2, $razas);
        $this->assertSame('Labrador', $razas[0]['nombre']);
    }

    public function testRazaExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new RazaModel($fakeDb);
        $this->assertTrue($model->razaExiste(1));
    }

    public function testRazaExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new RazaModel($fakeDb);
        $this->assertFalse($model->razaExiste(999));
    }

    public function testEspecieExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new RazaModel($fakeDb);
        $this->assertTrue($model->especieExiste(1));
    }

    public function testEspecieExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new RazaModel($fakeDb);
        $this->assertFalse($model->especieExiste(999));
    }

    public function testNombreExisteEnEspecieReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new RazaModel($fakeDb);
        $this->assertTrue($model->nombreExisteEnEspecie('Labrador', 1));
    }

    public function testNombreExisteEnEspecieReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new RazaModel($fakeDb);
        $this->assertFalse($model->nombreExisteEnEspecie('Inexistente', 1));
    }

    public function testNombreExisteEnEspecieWithExcludeIdReturnsFalseWhenExcludingSameId() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new RazaModel($fakeDb);
        $this->assertFalse($model->nombreExisteEnEspecie('Labrador', 1, 1));
    }

    public function testObtenerEspeciesReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Canino'],
            ['id' => 2, 'nombre' => 'Felino']
        ];
        $model = new RazaModel($fakeDb);
        $especies = $model->obtenerEspecies();
        $this->assertCount(2, $especies);
        $this->assertSame('Canino', $especies[0]['nombre']);
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['total_razas' => 12, 'especies_con_razas' => 6]
        ];
        $model = new RazaModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(1, $estadisticas);
        $this->assertEquals(12, $estadisticas[0]['total_razas']);
    }

    public function testObtenerAgrupadasPorEspecieReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['especie_id' => 1, 'especie_nombre' => 'Canino', 'total_razas' => 5, 'razas' => 'Labrador, Golden Retriever, Bulldog, Chihuahua, Pastor Alemán'],
            ['especie_id' => 2, 'especie_nombre' => 'Felino', 'total_razas' => 3, 'razas' => 'Persa, Siamés, Maine Coon']
        ];
        $model = new RazaModel($fakeDb);
        $razas = $model->obtenerAgrupadasPorEspecie();
        $this->assertCount(2, $razas);
        $this->assertSame('Canino', $razas[0]['especie_nombre']);
        $this->assertEquals(5, $razas[0]['total_razas']);
    }
}
