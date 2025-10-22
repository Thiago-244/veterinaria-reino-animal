<?php

use PHPUnit\Framework\TestCase;
use App\Models\DetalleVentaModel;
use App\Models\VentaModel;
use App\Models\ProductoServicioModel;
use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use App\Core\Database;

class DetalleVentaModelIntegrationTest extends TestCase {
    private $db;
    private $model;
    private $ventaModel;
    private $productoServicioModel;
    private $clienteModel;
    private $usuarioModel;
    private $testVentaId;
    private $testProductoId;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new DetalleVentaModel($this->db);
        $this->ventaModel = new VentaModel($this->db);
        $this->productoServicioModel = new ProductoServicioModel($this->db);
        $this->clienteModel = new ClienteModel($this->db);
        $this->usuarioModel = new UsuarioModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
        
        // Insertar datos de prueba
        $this->insertTestData();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    private function insertTestData() {
        // Insertar cliente de prueba
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono, email) VALUES ('12345678', 'Cliente', 'Test', '123456789', 'cliente@test.com')");
        $this->db->execute();
        
        // Insertar usuario de prueba
        $this->db->query("INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES ('Usuario Test', 'usuario@test.com', 'password123', 'admin', 'activo')");
        $this->db->execute();
        
        // Insertar producto de prueba
        $this->db->query("INSERT INTO productoservicio (tipo, nombre, precio, stock) VALUES ('Producto', 'Producto Test', 10.00, 100)");
        $this->db->execute();
        
        // Insertar venta de prueba
        $this->db->query("INSERT INTO venta (id_usuario, id_cliente, total) VALUES (1, 1, 0.00)");
        $this->db->execute();
        
        $this->testVentaId = 1;
        $this->testProductoId = 1;
    }

    public function testObtenerPorVentaReturnsEmptyArrayWhenNoItems() {
        $result = $this->model->obtenerPorVenta(999);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCrearAndObtenerPorVenta() {
        $datos = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 3,
            'precio_unitario' => 10.00,
            'subtotal' => 30.00
        ];
        
        $result = $this->model->crear($datos);
        $this->assertTrue($result);
        
        $detalles = $this->model->obtenerPorVenta($this->testVentaId);
        $this->assertCount(1, $detalles);
        $this->assertEquals($this->testVentaId, $detalles[0]['id_venta']);
        $this->assertEquals($this->testProductoId, $detalles[0]['id_producto']);
        $this->assertEquals(3, $detalles[0]['cantidad']);
        $this->assertEquals(10.00, $detalles[0]['precio_unitario']);
        $this->assertEquals(30.00, $detalles[0]['subtotal']);
        $this->assertEquals('Producto Test', $detalles[0]['producto_nombre']);
        $this->assertEquals('Producto', $detalles[0]['producto_tipo']);
    }

    public function testObtenerPorId() {
        $datos = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 15.00,
            'subtotal' => 30.00
        ];
        
        $this->model->crear($datos);
        
        $detalles = $this->model->obtenerPorVenta($this->testVentaId);
        $detalleId = $detalles[0]['id'];
        
        $detalle = $this->model->obtenerPorId($detalleId);
        $this->assertNotNull($detalle);
        $this->assertEquals($this->testVentaId, $detalle['id_venta']);
        $this->assertEquals($this->testProductoId, $detalle['id_producto']);
        $this->assertEquals(2, $detalle['cantidad']);
        $this->assertEquals(15.00, $detalle['precio_unitario']);
        $this->assertEquals(30.00, $detalle['subtotal']);
    }

    public function testActualizar() {
        $datos = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $this->model->crear($datos);
        
        $detalles = $this->model->obtenerPorVenta($this->testVentaId);
        $detalleId = $detalles[0]['id'];
        
        $datosActualizados = [
            'id_producto' => $this->testProductoId,
            'cantidad' => 5,
            'precio_unitario' => 12.00,
            'subtotal' => 60.00
        ];
        
        $result = $this->model->actualizar($detalleId, $datosActualizados);
        $this->assertTrue($result);
        
        $detalleActualizado = $this->model->obtenerPorId($detalleId);
        $this->assertEquals(5, $detalleActualizado['cantidad']);
        $this->assertEquals(12.00, $detalleActualizado['precio_unitario']);
        $this->assertEquals(60.00, $detalleActualizado['subtotal']);
    }

    public function testEliminar() {
        $datos = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $this->model->crear($datos);
        
        $detalles = $this->model->obtenerPorVenta($this->testVentaId);
        $detalleId = $detalles[0]['id'];
        
        $result = $this->model->eliminar($detalleId);
        $this->assertTrue($result);
        
        $detalleEliminado = $this->model->obtenerPorId($detalleId);
        $this->assertNull($detalleEliminado);
    }

    public function testEliminarPorVenta() {
        $datos1 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $datos2 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 1,
            'precio_unitario' => 15.00,
            'subtotal' => 15.00
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        $detalles = $this->model->obtenerPorVenta($this->testVentaId);
        $this->assertCount(2, $detalles);
        
        $result = $this->model->eliminarPorVenta($this->testVentaId);
        $this->assertTrue($result);
        
        $detallesRestantes = $this->model->obtenerPorVenta($this->testVentaId);
        $this->assertEmpty($detallesRestantes);
    }

    public function testVentaExiste() {
        $this->assertTrue($this->model->ventaExiste($this->testVentaId));
        $this->assertFalse($this->model->ventaExiste(999));
    }

    public function testProductoExiste() {
        $this->assertTrue($this->model->productoExiste($this->testProductoId));
        $this->assertFalse($this->model->productoExiste(999));
    }

    public function testObtenerPrecioProducto() {
        $precio = $this->model->obtenerPrecioProducto($this->testProductoId);
        $this->assertEquals(10.00, $precio);
        
        $precio = $this->model->obtenerPrecioProducto(999);
        $this->assertEquals(0, $precio);
    }

    public function testCalcularSubtotal() {
        $subtotal = $this->model->calcularSubtotal(10.50, 3);
        $this->assertEquals(31.50, $subtotal);
        
        $subtotal = $this->model->calcularSubtotal(0, 5);
        $this->assertEquals(0, $subtotal);
    }

    public function testObtenerTotalVenta() {
        $datos1 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $datos2 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 1,
            'precio_unitario' => 15.00,
            'subtotal' => 15.00
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        $total = $this->model->obtenerTotalVenta($this->testVentaId);
        $this->assertEquals(35.00, $total);
        
        $total = $this->model->obtenerTotalVenta(999);
        $this->assertEquals(0.0, $total);
    }

    public function testObtenerEstadisticasVentas() {
        $datos1 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $datos2 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 1,
            'precio_unitario' => 10.00,
            'subtotal' => 10.00
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        $estadisticas = $this->model->obtenerEstadisticasVentas();
        $this->assertIsArray($estadisticas);
        $this->assertCount(1, $estadisticas);
        $this->assertEquals('Producto Test', $estadisticas[0]['producto_nombre']);
        $this->assertEquals(3, $estadisticas[0]['total_vendido']);
        $this->assertEquals(30.00, $estadisticas[0]['total_ingresos']);
        $this->assertEquals(2, $estadisticas[0]['veces_vendido']);
    }

    public function testObtenerVentasPorProducto() {
        $datos = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $this->model->crear($datos);
        
        $ventas = $this->model->obtenerVentasPorProducto($this->testProductoId);
        $this->assertIsArray($ventas);
        $this->assertCount(1, $ventas);
        $this->assertEquals($this->testVentaId, $ventas[0]['id_venta']);
        $this->assertEquals(2, $ventas[0]['cantidad']);
        $this->assertEquals(10.00, $ventas[0]['precio_unitario']);
        $this->assertEquals(20.00, $ventas[0]['subtotal']);
        $this->assertEquals('Cliente Test', $ventas[0]['cliente_nombre']);
        $this->assertEquals('Test', $ventas[0]['cliente_apellido']);
    }

    public function testMultipleDetallesInSameVenta() {
        $datos1 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $datos2 = [
            'id_venta' => $this->testVentaId,
            'id_producto' => $this->testProductoId,
            'cantidad' => 1,
            'precio_unitario' => 15.00,
            'subtotal' => 15.00
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        $detalles = $this->model->obtenerPorVenta($this->testVentaId);
        $this->assertCount(2, $detalles);
        
        // Verificar que ambos detalles pertenecen a la misma venta
        $this->assertEquals($this->testVentaId, $detalles[0]['id_venta']);
        $this->assertEquals($this->testVentaId, $detalles[1]['id_venta']);
        
        // Verificar que tienen diferentes IDs
        $this->assertNotEquals($detalles[0]['id'], $detalles[1]['id']);
    }
}
