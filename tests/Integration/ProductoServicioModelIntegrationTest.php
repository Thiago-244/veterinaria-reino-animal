<?php
use PHPUnit\Framework\TestCase;
use App\Models\ProductoServicioModel;
use App\Core\Database;

class ProductoServicioModelIntegrationTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new ProductoServicioModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    public function testCrearProducto() {
        $datos = [
            'tipo' => 'Producto',
            'nombre' => 'Test Producto',
            'precio' => 25.50,
            'stock' => 30
        ];
        
        $resultado = $this->model->crear($datos);
        $this->assertTrue($resultado);
        
        // Verificar que se creó correctamente
        $productosServicios = $this->model->obtenerTodas();
        $this->assertNotEmpty($productosServicios);
        
        // Buscar el producto creado
        $encontrado = false;
        foreach ($productosServicios as $item) {
            if ($item['nombre'] === 'Test Producto') {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado);
    }

    public function testCrearServicio() {
        $datos = [
            'tipo' => 'Servicio',
            'nombre' => 'Test Servicio',
            'precio' => 50.00,
            'stock' => 0 // Para servicios, el stock se establece en 9999 automáticamente
        ];
        
        $resultado = $this->model->crear($datos);
        $this->assertTrue($resultado);
        
        // Verificar que se creó correctamente
        $productosServicios = $this->model->obtenerTodas();
        $this->assertNotEmpty($productosServicios);
        
        // Buscar el servicio creado
        $encontrado = false;
        foreach ($productosServicios as $item) {
            if ($item['nombre'] === 'Test Servicio') {
                $encontrado = true;
                $this->assertEquals('Servicio', $item['tipo']);
                break;
            }
        }
        $this->assertTrue($encontrado);
    }

    public function testActualizarProducto() {
        // Crear un producto primero
        $datos = [
            'tipo' => 'Producto',
            'nombre' => 'Producto Original',
            'precio' => 20.00,
            'stock' => 25
        ];
        $this->model->crear($datos);
        
        // Obtener el producto creado
        $productosServicios = $this->model->obtenerTodas();
        $productoId = null;
        foreach ($productosServicios as $item) {
            if ($item['nombre'] === 'Producto Original') {
                $productoId = $item['id'];
                break;
            }
        }
        
        $this->assertNotNull($productoId);
        
        // Actualizar el producto
        $nuevosDatos = [
            'tipo' => 'Producto',
            'nombre' => 'Producto Actualizado',
            'precio' => 30.00,
            'stock' => 35
        ];
        $resultado = $this->model->actualizar($productoId, $nuevosDatos);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $productoActualizado = $this->model->obtenerPorId($productoId);
        $this->assertEquals('Producto Actualizado', $productoActualizado['nombre']);
        $this->assertEquals(30.00, $productoActualizado['precio']);
    }

    public function testEliminarProducto() {
        // Crear un producto primero
        $datos = [
            'tipo' => 'Producto',
            'nombre' => 'Producto para Eliminar',
            'precio' => 15.00,
            'stock' => 20
        ];
        $this->model->crear($datos);
        
        // Obtener el producto creado
        $productosServicios = $this->model->obtenerTodas();
        $productoId = null;
        foreach ($productosServicios as $item) {
            if ($item['nombre'] === 'Producto para Eliminar') {
                $productoId = $item['id'];
                break;
            }
        }
        
        $this->assertNotNull($productoId);
        
        // Eliminar el producto
        $resultado = $this->model->eliminar($productoId);
        $this->assertTrue($resultado);
        
        // Verificar que se eliminó
        $productoEliminado = $this->model->obtenerPorId($productoId);
        $this->assertNull($productoEliminado);
    }

    public function testObtenerPorTipo() {
        // Crear algunos productos y servicios
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto Test 1',
            'precio' => 20.00,
            'stock' => 10
        ]);
        
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto Test 2',
            'precio' => 25.00,
            'stock' => 15
        ]);
        
        $this->model->crear([
            'tipo' => 'Servicio',
            'nombre' => 'Servicio Test 1',
            'precio' => 50.00,
            'stock' => 0
        ]);
        
        // Obtener por tipo
        $productos = $this->model->obtenerPorTipo('Producto');
        $servicios = $this->model->obtenerPorTipo('Servicio');
        
        $this->assertGreaterThanOrEqual(2, count($productos));
        $this->assertGreaterThanOrEqual(1, count($servicios));
        
        // Verificar que todos los productos tienen el tipo correcto
        foreach ($productos as $producto) {
            $this->assertEquals('Producto', $producto['tipo']);
        }
        
        foreach ($servicios as $servicio) {
            $this->assertEquals('Servicio', $servicio['tipo']);
        }
    }

    public function testNombreExiste() {
        // Crear un producto primero
        $datos = [
            'tipo' => 'Producto',
            'nombre' => 'Producto Test Existencia',
            'precio' => 20.00,
            'stock' => 10
        ];
        $this->model->crear($datos);
        
        // Verificar que existe
        $this->assertTrue($this->model->nombreExiste('Producto Test Existencia'));
        $this->assertFalse($this->model->nombreExiste('Producto Inexistente'));
    }

    public function testActualizarStock() {
        // Crear un producto primero
        $datos = [
            'tipo' => 'Producto',
            'nombre' => 'Producto Test Stock',
            'precio' => 20.00,
            'stock' => 10
        ];
        $this->model->crear($datos);
        
        // Obtener el producto creado
        $productosServicios = $this->model->obtenerTodas();
        $productoId = null;
        foreach ($productosServicios as $item) {
            if ($item['nombre'] === 'Producto Test Stock') {
                $productoId = $item['id'];
                break;
            }
        }
        
        $this->assertNotNull($productoId);
        
        // Actualizar stock
        $resultado = $this->model->actualizarStock($productoId, 25);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $productoActualizado = $this->model->obtenerPorId($productoId);
        $this->assertEquals(25, $productoActualizado['stock']);
    }

    public function testObtenerConStockBajo() {
        // Crear productos con stock bajo
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto Stock Bajo 1',
            'precio' => 20.00,
            'stock' => 5
        ]);
        
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto Stock Bajo 2',
            'precio' => 25.00,
            'stock' => 8
        ]);
        
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto Stock Normal',
            'precio' => 30.00,
            'stock' => 50
        ]);
        
        // Obtener productos con stock bajo
        $productosStockBajo = $this->model->obtenerConStockBajo(10);
        $this->assertGreaterThanOrEqual(2, count($productosStockBajo));
        
        // Verificar que todos tienen stock bajo
        foreach ($productosStockBajo as $producto) {
            $this->assertLessThanOrEqual(10, $producto['stock']);
            $this->assertEquals('Producto', $producto['tipo']);
        }
    }

    public function testBuscar() {
        // Crear algunos productos/servicios
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Alimento Test',
            'precio' => 20.00,
            'stock' => 10
        ]);
        
        $this->model->crear([
            'tipo' => 'Servicio',
            'nombre' => 'Consulta Test',
            'precio' => 50.00,
            'stock' => 0
        ]);
        
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Juguete Normal',
            'precio' => 15.00,
            'stock' => 20
        ]);
        
        // Buscar productos/servicios
        $resultados = $this->model->buscar('test');
        $this->assertGreaterThanOrEqual(2, count($resultados));
        
        // Verificar que los resultados contienen el término de búsqueda
        foreach ($resultados as $item) {
            $this->assertTrue(stripos($item['nombre'], 'test') !== false);
        }
    }

    public function testObtenerEstadisticas() {
        // Crear algunos productos y servicios
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto 1',
            'precio' => 20.00,
            'stock' => 10
        ]);
        
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto 2',
            'precio' => 30.00,
            'stock' => 5
        ]);
        
        $this->model->crear([
            'tipo' => 'Servicio',
            'nombre' => 'Servicio 1',
            'precio' => 50.00,
            'stock' => 0
        ]);
        
        // Obtener estadísticas
        $estadisticas = $this->model->obtenerEstadisticas();
        $this->assertNotEmpty($estadisticas);
        $this->assertArrayHasKey('total', $estadisticas[0]);
        $this->assertArrayHasKey('total_productos', $estadisticas[0]);
        $this->assertArrayHasKey('total_servicios', $estadisticas[0]);
        $this->assertArrayHasKey('precio_promedio', $estadisticas[0]);
        $this->assertArrayHasKey('stock_total', $estadisticas[0]);
        $this->assertArrayHasKey('productos_stock_bajo', $estadisticas[0]);
    }

    public function testObtenerAgrupadosPorTipo() {
        // Crear algunos productos y servicios
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto 1',
            'precio' => 20.00,
            'stock' => 10
        ]);
        
        $this->model->crear([
            'tipo' => 'Producto',
            'nombre' => 'Producto 2',
            'precio' => 30.00,
            'stock' => 15
        ]);
        
        $this->model->crear([
            'tipo' => 'Servicio',
            'nombre' => 'Servicio 1',
            'precio' => 50.00,
            'stock' => 0
        ]);
        
        // Obtener agrupados por tipo
        $agrupados = $this->model->obtenerAgrupadosPorTipo();
        $this->assertNotEmpty($agrupados);
        
        // Verificar estructura
        $item = $agrupados[0];
        $this->assertArrayHasKey('tipo', $item);
        $this->assertArrayHasKey('total', $item);
        $this->assertArrayHasKey('precio_promedio', $item);
        $this->assertArrayHasKey('stock_total', $item);
    }
}
