<?php
use PHPUnit\Framework\TestCase;
use App\Models\CitaModel;

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

class CitaModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'codigo' => 'CT-00001-1',
                'fecha_cita' => '2025-10-25 10:00:00',
                'motivo' => 'Consulta de rutina',
                'estado' => 'Pendiente',
                'mascota_nombre' => 'Max',
                'cliente_nombre' => 'Juan'
            ]
        ];
        $model = new CitaModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(1, $all);
        $this->assertSame('CT-00001-1', $all[0]['codigo']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new CitaModel($fakeDb);
        $ok = $model->crear([
            'codigo' => 'CT-00001-1',
            'id_mascota' => 1,
            'id_cliente' => 1,
            'fecha_cita' => '2025-10-25 10:00:00',
            'motivo' => 'Consulta de rutina',
            'estado' => 'Pendiente'
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':codigo', $fakeDb->bindings);
        $this->assertEquals('CT-00001-1', $fakeDb->bindings[':codigo']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new CitaModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'id_mascota' => 1,
            'id_cliente' => 1,
            'fecha_cita' => '2025-10-25 11:00:00',
            'motivo' => 'Consulta de rutina actualizada',
            'estado' => 'Procesada'
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testGenerarCodigoReturnsValidFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['total' => 3]];
        $model = new CitaModel($fakeDb);
        $codigo = $model->generarCodigo();
        $this->assertMatchesRegularExpression('/^CT-\d{5}-1$/', $codigo);
        $this->assertEquals('CT-00004-1', $codigo);
    }

    public function testObtenerPorClienteReturnsFilteredResults() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'codigo' => 'CT-00001-1', 'id_cliente' => 1],
            ['id' => 2, 'codigo' => 'CT-00002-1', 'id_cliente' => 1]
        ];
        $model = new CitaModel($fakeDb);
        $citas = $model->obtenerPorCliente(1);
        $this->assertCount(2, $citas);
        $this->assertSame('CT-00001-1', $citas[0]['codigo']);
    }

    public function testObtenerPorMascotaReturnsFilteredResults() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'codigo' => 'CT-00001-1', 'id_mascota' => 1],
            ['id' => 2, 'codigo' => 'CT-00002-1', 'id_mascota' => 1]
        ];
        $model = new CitaModel($fakeDb);
        $citas = $model->obtenerPorMascota(1);
        $this->assertCount(2, $citas);
        $this->assertSame('CT-00001-1', $citas[0]['codigo']);
    }

    public function testCambiarEstadoUpdatesCorrectly() {
        $fakeDb = new FakeDatabase();
        $model = new CitaModel($fakeDb);
        $this->assertTrue($model->cambiarEstado(1, 'Procesada'));
        $this->assertArrayHasKey(':estado', $fakeDb->bindings);
        $this->assertEquals('Procesada', $fakeDb->bindings[':estado']);
    }

    public function testMascotaExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new CitaModel($fakeDb);
        $this->assertTrue($model->mascotaExiste(1));
    }

    public function testMascotaExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new CitaModel($fakeDb);
        $this->assertFalse($model->mascotaExiste(999));
    }

    public function testClienteExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new CitaModel($fakeDb);
        $this->assertTrue($model->clienteExiste(1));
    }

    public function testClienteExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new CitaModel($fakeDb);
        $this->assertFalse($model->clienteExiste(999));
    }

    public function testMascotaPerteneceAClienteReturnsTrueWhenBelongs() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new CitaModel($fakeDb);
        $this->assertTrue($model->mascotaPerteneceACliente(1, 1));
    }

    public function testMascotaPerteneceAClienteReturnsFalseWhenNotBelongs() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new CitaModel($fakeDb);
        $this->assertFalse($model->mascotaPerteneceACliente(1, 999));
    }

    public function testObtenerCitasPorMesReturnsCorrectResults() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'codigo' => 'CT-00001-1', 'fecha_cita' => '2025-10-15 10:00:00'],
            ['id' => 2, 'codigo' => 'CT-00002-1', 'fecha_cita' => '2025-10-20 14:00:00']
        ];
        $model = new CitaModel($fakeDb);
        $citas = $model->obtenerCitasPorMes(10, 2025);
        $this->assertCount(2, $citas);
        $this->assertSame('CT-00001-1', $citas[0]['codigo']);
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['estado' => 'Pendiente', 'total' => 5],
            ['estado' => 'Procesada', 'total' => 3],
            ['estado' => 'Cancelada', 'total' => 1]
        ];
        $model = new CitaModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(3, $estadisticas);
        $this->assertSame('Pendiente', $estadisticas[0]['estado']);
        $this->assertEquals(5, $estadisticas[0]['total']);
    }

    public function testObtenerCitasProximasReturnsCorrectResults() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'codigo' => 'CT-00001-1', 'fecha_cita' => date('Y-m-d H:i:s', strtotime('+1 day'))],
            ['id' => 2, 'codigo' => 'CT-00002-1', 'fecha_cita' => date('Y-m-d H:i:s', strtotime('+3 days'))]
        ];
        $model = new CitaModel($fakeDb);
        $citas = $model->obtenerCitasProximas();
        $this->assertCount(2, $citas);
        $this->assertSame('CT-00001-1', $citas[0]['codigo']);
    }
}
