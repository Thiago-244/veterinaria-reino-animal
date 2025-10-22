<?php
use PHPUnit\Framework\TestCase;
use App\Models\MascotaModel;

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

class MascotaModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'nombre' => 'Max', 
                'codigo' => 'CM-00001-1',
                'cliente_nombre' => 'Juan',
                'cliente_apellido' => 'Pérez'
            ]
        ];
        $model = new MascotaModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(1, $all);
        $this->assertSame('Max', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new MascotaModel($fakeDb);
        $ok = $model->crear([
            'codigo' => 'CM-00001-1',
            'nombre' => 'Max',
            'id_cliente' => 1,
            'id_raza' => 1,
            'fecha_nacimiento' => '2020-01-01',
            'sexo' => 'Macho',
            'color' => 'Marrón',
            'peso' => 15.5,
            'foto' => 'default_pet.png'
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':nombre', $fakeDb->bindings);
        $this->assertEquals('Max', $fakeDb->bindings[':nombre']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new MascotaModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'nombre' => 'Max',
            'id_cliente' => 1,
            'id_raza' => 1,
            'fecha_nacimiento' => '2020-01-01',
            'sexo' => 'Macho',
            'color' => 'Marrón',
            'peso' => 16.0
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testGenerarCodigoReturnsValidFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['total' => 5]];
        $model = new MascotaModel($fakeDb);
        $codigo = $model->generarCodigo();
        $this->assertMatchesRegularExpression('/^CM-\d{5}-1$/', $codigo);
        $this->assertEquals('CM-00006-1', $codigo);
    }

    public function testObtenerPorClienteReturnsFilteredResults() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Max', 'id_cliente' => 1],
            ['id' => 2, 'nombre' => 'Luna', 'id_cliente' => 1]
        ];
        $model = new MascotaModel($fakeDb);
        $mascotas = $model->obtenerPorCliente(1);
        $this->assertCount(2, $mascotas);
        $this->assertSame('Max', $mascotas[0]['nombre']);
    }

    public function testClienteExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new MascotaModel($fakeDb);
        $this->assertTrue($model->clienteExiste(1));
    }

    public function testClienteExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new MascotaModel($fakeDb);
        $this->assertFalse($model->clienteExiste(999));
    }

    public function testRazaExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new MascotaModel($fakeDb);
        $this->assertTrue($model->razaExiste(1));
    }

    public function testRazaExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new MascotaModel($fakeDb);
        $this->assertFalse($model->razaExiste(999));
    }

    public function testCalcularEdadReturnsCorrectAge() {
        $fakeDb = new FakeDatabase();
        $model = new MascotaModel($fakeDb);
        
        // Fecha de nacimiento hace 3 años
        $fechaNacimiento = date('Y-m-d', strtotime('-3 years'));
        $edad = $model->calcularEdad($fechaNacimiento);
        $this->assertEquals(3, $edad);
    }
}
