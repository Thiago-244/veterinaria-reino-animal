<?php
use PHPUnit\Framework\TestCase;
use App\Models\ProductoServicioModel;

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

class ProductoServicioModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'tipo' => 'Producto',
                'nombre' => 'Alimento Premium',
                'precio' => 45.00,
                'stock' => 50
            ],
            [
                'id' => 2, 
                'tipo' => 'Servicio',
                'nombre' => 'Consulta General',
                'precio' => 50.00,
                'stock' => 9999
            ]
        ];
        $model = new ProductoServicioModel($fakeDb);
        $all = $model->obtenerTodas();
        $this->assertCount(2, $all);
        $this->assertSame('Alimento Premium', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new ProductoServicioModel($fakeDb);
        $ok = $model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Test Producto',
            'precio' => 25.50,
            'stock' => 30
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':tipo', $fakeDb->bindings);
        $this->assertArrayHasKey(':nombre', $fakeDb->bindings);
        $this->assertArrayHasKey(':precio', $fakeDb->bindings);
        $this->assertArrayHasKey(':stock', $fakeDb->bindings);
        $this->assertEquals('Producto', $fakeDb->bindings[':tipo']);
        $this->assertEquals('Test Producto', $fakeDb->bindings[':nombre']);
        $this->assertEquals(25.50, $fakeDb->bindings[':precio']);
        $this->assertEquals(30, $fakeDb->bindings[':stock']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new ProductoServicioModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'tipo' => 'Producto',
            'nombre' => 'Producto Actualizado',
            'precio' => 30.00,
            'stock' => 40
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testObtenerPorIdReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'tipo' => 'Producto', 'nombre' => 'Test Producto', 'precio' => 25.50, 'stock' => 30]];
        $model = new ProductoServicioModel($fakeDb);
        $producto = $model->obtenerPorId(1);
        $this->assertSame('Test Producto', $producto['nombre']);
    }

    public function testObtenerPorIdReturnsNullWhenNotFound() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new ProductoServicioModel($fakeDb);
        $producto = $model->obtenerPorId(999);
        $this->assertNull($producto);
    }

    public function testObtenerPorTipoReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'tipo' => 'Producto', 'nombre' => 'Producto 1'],
            ['id' => 2, 'tipo' => 'Producto', 'nombre' => 'Producto 2']
        ];
        $model = new ProductoServicioModel($fakeDb);
        $productos = $model->obtenerPorTipo('Producto');
        $this->assertCount(2, $productos);
        $this->assertSame('Producto 1', $productos[0]['nombre']);
    }

    public function testObtenerProductosReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'tipo' => 'Producto', 'nombre' => 'Producto 1'],
            ['id' => 2, 'tipo' => 'Producto', 'nombre' => 'Producto 2']
        ];
        $model = new ProductoServicioModel($fakeDb);
        $productos = $model->obtenerProductos();
        $this->assertCount(2, $productos);
        $this->assertSame('Producto 1', $productos[0]['nombre']);
    }

    public function testObtenerServiciosReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'tipo' => 'Servicio', 'nombre' => 'Servicio 1'],
            ['id' => 2, 'tipo' => 'Servicio', 'nombre' => 'Servicio 2']
        ];
        $model = new ProductoServicioModel($fakeDb);
        $servicios = $model->obtenerServicios();
        $this->assertCount(2, $servicios);
        $this->assertSame('Servicio 1', $servicios[0]['nombre']);
    }

    public function testProductoExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new ProductoServicioModel($fakeDb);
        $this->assertTrue($model->productoExiste(1));
    }

    public function testProductoExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new ProductoServicioModel($fakeDb);
        $this->assertFalse($model->productoExiste(999));
    }

    public function testNombreExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new ProductoServicioModel($fakeDb);
        $this->assertTrue($model->nombreExiste('Test Producto'));
    }

    public function testNombreExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new ProductoServicioModel($fakeDb);
        $this->assertFalse($model->nombreExiste('Inexistente'));
    }

    public function testActualizarStockReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new ProductoServicioModel($fakeDb);
        $this->assertTrue($model->actualizarStock(1, 25));
        $this->assertArrayHasKey(':stock', $fakeDb->bindings);
        $this->assertEquals(25, $fakeDb->bindings[':stock']);
    }

    public function testReducirStockReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new ProductoServicioModel($fakeDb);
        $this->assertTrue($model->reducirStock(1, 5));
        $this->assertArrayHasKey(':cantidad', $fakeDb->bindings);
        $this->assertEquals(5, $fakeDb->bindings[':cantidad']);
    }

    public function testAumentarStockReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new ProductoServicioModel($fakeDb);
        $this->assertTrue($model->aumentarStock(1, 10));
        $this->assertArrayHasKey(':cantidad', $fakeDb->bindings);
        $this->assertEquals(10, $fakeDb->bindings[':cantidad']);
    }

    public function testObtenerConStockBajoReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'tipo' => 'Producto', 'nombre' => 'Producto Stock Bajo', 'stock' => 5],
            ['id' => 2, 'tipo' => 'Producto', 'nombre' => 'Producto Stock Bajo 2', 'stock' => 3]
        ];
        $model = new ProductoServicioModel($fakeDb);
        $productos = $model->obtenerConStockBajo(10);
        $this->assertCount(2, $productos);
        $this->assertSame('Producto Stock Bajo', $productos[0]['nombre']);
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'total' => 10,
                'total_productos' => 6,
                'total_servicios' => 4,
                'precio_promedio' => 35.50,
                'stock_total' => 150,
                'productos_stock_bajo' => 2
            ]
        ];
        $model = new ProductoServicioModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(1, $estadisticas);
        $this->assertEquals(10, $estadisticas[0]['total']);
        $this->assertEquals(6, $estadisticas[0]['total_productos']);
    }

    public function testObtenerAgrupadosPorTipoReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['tipo' => 'Producto', 'total' => 6, 'precio_promedio' => 40.00, 'stock_total' => 150],
            ['tipo' => 'Servicio', 'total' => 4, 'precio_promedio' => 30.00, 'stock_total' => 0]
        ];
        $model = new ProductoServicioModel($fakeDb);
        $agrupados = $model->obtenerAgrupadosPorTipo();
        $this->assertCount(2, $agrupados);
        $this->assertSame('Producto', $agrupados[0]['tipo']);
        $this->assertEquals(6, $agrupados[0]['total']);
    }

    public function testObtenerMasCarosReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Producto Caro 1', 'precio' => 100.00],
            ['id' => 2, 'nombre' => 'Producto Caro 2', 'precio' => 90.00]
        ];
        $model = new ProductoServicioModel($fakeDb);
        $caros = $model->obtenerMasCaros(5);
        $this->assertCount(2, $caros);
        $this->assertSame('Producto Caro 1', $caros[0]['nombre']);
    }

    public function testObtenerMasBaratosReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Producto Barato 1', 'precio' => 10.00],
            ['id' => 2, 'nombre' => 'Producto Barato 2', 'precio' => 15.00]
        ];
        $model = new ProductoServicioModel($fakeDb);
        $baratos = $model->obtenerMasBaratos(5);
        $this->assertCount(2, $baratos);
        $this->assertSame('Producto Barato 1', $baratos[0]['nombre']);
    }

    public function testBuscarReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Alimento Test', 'tipo' => 'Producto'],
            ['id' => 2, 'nombre' => 'Consulta Test', 'tipo' => 'Servicio']
        ];
        $model = new ProductoServicioModel($fakeDb);
        $resultados = $model->buscar('test');
        $this->assertCount(2, $resultados);
        $this->assertSame('Alimento Test', $resultados[0]['nombre']);
    }
}
