<?php
use PHPUnit\Framework\TestCase;
use App\Models\EmpresaModel;

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

class EmpresaModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'nombre' => 'Veterinaria Reino Animal',
                'ruc' => '20123456789',
                'direccion' => 'Av. Principal 123',
                'telefono' => '01-234-5678',
                'email' => 'info@veterinaria.com',
                'iva' => 18.00
            ]
        ];
        $model = new EmpresaModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(1, $all);
        $this->assertSame('Veterinaria Reino Animal', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new EmpresaModel($fakeDb);
        $ok = $model->crear([
            'nombre' => 'Test Empresa',
            'ruc' => '12345678901',
            'direccion' => 'Test Dirección',
            'telefono' => '01-234-5678',
            'email' => 'test@empresa.com',
            'iva' => 18.00
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':nombre', $fakeDb->bindings);
        $this->assertArrayHasKey(':ruc', $fakeDb->bindings);
        $this->assertArrayHasKey(':direccion', $fakeDb->bindings);
        $this->assertArrayHasKey(':telefono', $fakeDb->bindings);
        $this->assertArrayHasKey(':email', $fakeDb->bindings);
        $this->assertArrayHasKey(':iva', $fakeDb->bindings);
        $this->assertEquals('Test Empresa', $fakeDb->bindings[':nombre']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new EmpresaModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'nombre' => 'Empresa Actualizada',
            'ruc' => '12345678901',
            'direccion' => 'Dirección Actualizada',
            'telefono' => '01-234-5678',
            'email' => 'test@empresa.com',
            'iva' => 18.00
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testObtenerPorIdReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Empresa', 'ruc' => '12345678901']];
        $model = new EmpresaModel($fakeDb);
        $empresa = $model->obtenerPorId(1);
        $this->assertSame('Test Empresa', $empresa['nombre']);
    }

    public function testObtenerPorIdReturnsNullWhenNotFound() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EmpresaModel($fakeDb);
        $empresa = $model->obtenerPorId(999);
        $this->assertNull($empresa);
    }

    public function testObtenerPorRucReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Empresa', 'ruc' => '12345678901']];
        $model = new EmpresaModel($fakeDb);
        $empresa = $model->obtenerPorRuc('12345678901');
        $this->assertSame('Test Empresa', $empresa['nombre']);
    }

    public function testObtenerPorEmailReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Empresa', 'email' => 'test@empresa.com']];
        $model = new EmpresaModel($fakeDb);
        $empresa = $model->obtenerPorEmail('test@empresa.com');
        $this->assertSame('Test Empresa', $empresa['nombre']);
    }

    public function testEmpresaExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new EmpresaModel($fakeDb);
        $this->assertTrue($model->empresaExiste(1));
    }

    public function testEmpresaExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EmpresaModel($fakeDb);
        $this->assertFalse($model->empresaExiste(999));
    }

    public function testRucExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new EmpresaModel($fakeDb);
        $this->assertTrue($model->rucExiste('12345678901'));
    }

    public function testRucExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EmpresaModel($fakeDb);
        $this->assertFalse($model->rucExiste('99999999999'));
    }

    public function testEmailExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new EmpresaModel($fakeDb);
        $this->assertTrue($model->emailExiste('test@empresa.com'));
    }

    public function testEmailExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new EmpresaModel($fakeDb);
        $this->assertFalse($model->emailExiste('inexistente@empresa.com'));
    }

    public function testObtenerPrincipalReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Empresa Principal']];
        $model = new EmpresaModel($fakeDb);
        $empresa = $model->obtenerPrincipal();
        $this->assertSame('Empresa Principal', $empresa['nombre']);
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['total_empresas' => 1, 'promedio_iva' => 18.00]
        ];
        $model = new EmpresaModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(1, $estadisticas);
        $this->assertEquals(1, $estadisticas[0]['total_empresas']);
    }

    public function testActualizarLogoReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new EmpresaModel($fakeDb);
        $this->assertTrue($model->actualizarLogo(1, 'nuevo-logo.png'));
        $this->assertArrayHasKey(':logo', $fakeDb->bindings);
        $this->assertEquals('nuevo-logo.png', $fakeDb->bindings[':logo']);
    }

    public function testActualizarIvaReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new EmpresaModel($fakeDb);
        $this->assertTrue($model->actualizarIva(1, 19.00));
        $this->assertArrayHasKey(':iva', $fakeDb->bindings);
        $this->assertEquals(19.00, $fakeDb->bindings[':iva']);
    }
}
