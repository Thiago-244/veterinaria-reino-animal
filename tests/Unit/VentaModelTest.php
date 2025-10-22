<?php
use PHPUnit\Framework\TestCase;
use App\Models\VentaModel;

class FakeDatabase {
    public array $queries = [];
    public array $bindings = [];
    public bool $executeReturn = true;
    public array $result = [];
    public int $lastInsertId = 1;
    public bool $inTransaction = false;

    public function query($sql) { $this->queries[] = $sql; }
    public function bind($param, $value, $type = null) { $this->bindings[$param] = $value; }
    public function execute() { return $this->executeReturn; }
    public function resultSet() { return $this->result; }
    public function lastInsertId() { return $this->lastInsertId; }
    public function beginTransaction() { $this->inTransaction = true; }
    public function commit() { $this->inTransaction = false; }
    public function rollBack() { $this->inTransaction = false; }
}

class VentaModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'id_usuario' => 1,
                'id_cliente' => 1,
                'total' => 100.00,
                'usuario_nombre' => 'Dr. Carlos',
                'cliente_nombre' => 'Juan',
                'cliente_apellido' => 'Pérez'
            ]
        ];
        $model = new VentaModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(1, $all);
        $this->assertSame('Dr. Carlos', $all[0]['usuario_nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new VentaModel($fakeDb);
        $ok = $model->crear([
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 150.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 2,
                    'precio' => 75.00
                ]
            ]
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':id_usuario', $fakeDb->bindings);
        $this->assertArrayHasKey(':id_cliente', $fakeDb->bindings);
        $this->assertArrayHasKey(':total', $fakeDb->bindings);
        $this->assertEquals(1, $fakeDb->bindings[':id_usuario']);
        $this->assertEquals(1, $fakeDb->bindings[':id_cliente']);
        $this->assertEquals(150.00, $fakeDb->bindings[':total']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id_producto' => 1, 'cantidad' => 2, 'tipo' => 'Producto']
        ];
        $model = new VentaModel($fakeDb);
        $this->assertTrue($model->eliminar(1));
    }

    public function testObtenerPorIdReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'total' => 100.00, 'usuario_nombre' => 'Dr. Carlos']];
        $model = new VentaModel($fakeDb);
        $venta = $model->obtenerPorId(1);
        $this->assertSame(100.00, $venta['total']);
    }

    public function testObtenerPorIdReturnsNullWhenNotFound() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new VentaModel($fakeDb);
        $venta = $model->obtenerPorId(999);
        $this->assertNull($venta);
    }

    public function testObtenerPorClienteReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'total' => 100.00, 'usuario_nombre' => 'Dr. Carlos'],
            ['id' => 2, 'total' => 150.00, 'usuario_nombre' => 'Dr. Carlos']
        ];
        $model = new VentaModel($fakeDb);
        $ventas = $model->obtenerPorCliente(1);
        $this->assertCount(2, $ventas);
        $this->assertSame(100.00, $ventas[0]['total']);
    }

    public function testObtenerPorUsuarioReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'total' => 100.00, 'cliente_nombre' => 'Juan', 'cliente_apellido' => 'Pérez']
        ];
        $model = new VentaModel($fakeDb);
        $ventas = $model->obtenerPorUsuario(1);
        $this->assertCount(1, $ventas);
        $this->assertSame('Juan', $ventas[0]['cliente_nombre']);
    }

    public function testVentaExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new VentaModel($fakeDb);
        $this->assertTrue($model->ventaExiste(1));
    }

    public function testVentaExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new VentaModel($fakeDb);
        $this->assertFalse($model->ventaExiste(999));
    }

    public function testUsuarioExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new VentaModel($fakeDb);
        $this->assertTrue($model->usuarioExiste(1));
    }

    public function testUsuarioExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new VentaModel($fakeDb);
        $this->assertFalse($model->usuarioExiste(999));
    }

    public function testClienteExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new VentaModel($fakeDb);
        $this->assertTrue($model->clienteExiste(1));
    }

    public function testClienteExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new VentaModel($fakeDb);
        $this->assertFalse($model->clienteExiste(999));
    }

    public function testObtenerDetallesReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'id_producto' => 1, 'cantidad' => 2, 'precio' => 50.00, 'producto_nombre' => 'Alimento']
        ];
        $model = new VentaModel($fakeDb);
        $detalles = $model->obtenerDetalles(1);
        $this->assertCount(1, $detalles);
        $this->assertSame('Alimento', $detalles[0]['producto_nombre']);
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'total_ventas' => 10,
                'total_ingresos' => 1500.00,
                'promedio_venta' => 150.00,
                'clientes_unicos' => 5,
                'usuarios_activos' => 2
            ]
        ];
        $model = new VentaModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(1, $estadisticas);
        $this->assertEquals(10, $estadisticas[0]['total_ventas']);
        $this->assertEquals(1500.00, $estadisticas[0]['total_ingresos']);
    }

    public function testObtenerPorClienteAgrupadoReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'cliente_id' => 1,
                'cliente_nombre' => 'Juan',
                'cliente_apellido' => 'Pérez',
                'total_ventas' => 3,
                'total_gastado' => 450.00
            ]
        ];
        $model = new VentaModel($fakeDb);
        $ventas = $model->obtenerPorClienteAgrupado();
        $this->assertCount(1, $ventas);
        $this->assertSame('Juan', $ventas[0]['cliente_nombre']);
        $this->assertEquals(3, $ventas[0]['total_ventas']);
    }

    public function testObtenerPorUsuarioAgrupadoReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'usuario_id' => 1,
                'usuario_nombre' => 'Dr. Carlos',
                'total_ventas' => 5,
                'total_vendido' => 750.00
            ]
        ];
        $model = new VentaModel($fakeDb);
        $ventas = $model->obtenerPorUsuarioAgrupado();
        $this->assertCount(1, $ventas);
        $this->assertSame('Dr. Carlos', $ventas[0]['usuario_nombre']);
        $this->assertEquals(5, $ventas[0]['total_ventas']);
    }

    public function testObtenerProductosMasVendidosReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1,
                'nombre' => 'Alimento Premium',
                'tipo' => 'Producto',
                'total_vendido' => 50,
                'total_ingresos' => 2250.00
            ]
        ];
        $model = new VentaModel($fakeDb);
        $productos = $model->obtenerProductosMasVendidos(5);
        $this->assertCount(1, $productos);
        $this->assertSame('Alimento Premium', $productos[0]['nombre']);
        $this->assertEquals(50, $productos[0]['total_vendido']);
    }

    public function testObtenerVentasDelDiaReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1,
                'total' => 100.00,
                'usuario_nombre' => 'Dr. Carlos',
                'cliente_nombre' => 'Juan',
                'cliente_apellido' => 'Pérez'
            ]
        ];
        $model = new VentaModel($fakeDb);
        $ventas = $model->obtenerVentasDelDia();
        $this->assertCount(1, $ventas);
        $this->assertSame('Dr. Carlos', $ventas[0]['usuario_nombre']);
    }

    public function testObtenerPorRangoFechasReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1,
                'total' => 100.00,
                'usuario_nombre' => 'Dr. Carlos',
                'cliente_nombre' => 'Juan'
            ]
        ];
        $model = new VentaModel($fakeDb);
        $ventas = $model->obtenerPorRangoFechas('2025-01-01', '2025-01-31');
        $this->assertCount(1, $ventas);
        $this->assertSame('Dr. Carlos', $ventas[0]['usuario_nombre']);
    }
}
