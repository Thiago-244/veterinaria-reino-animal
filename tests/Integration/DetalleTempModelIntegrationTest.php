<?php

use PHPUnit\Framework\TestCase;
use App\Models\DetalleTempModel;
use App\Core\Database;

class DetalleTempModelIntegrationTest extends TestCase {
    private $db;
    private $model;
    private $testToken = 'test_token_' . uniqid();

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new DetalleTempModel($this->db);
        
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
        // Insertar producto de prueba
        $this->db->query("INSERT INTO productoservicio (tipo, nombre, precio, stock) VALUES ('Producto', 'Producto Test', 10.00, 100)");
        $this->db->execute();
        
        $this->db->query("INSERT INTO productoservicio (tipo, nombre, precio, stock) VALUES ('Servicio', 'Servicio Test', 25.00, 0)");
        $this->db->execute();
    }

    public function testObtenerPorTokenUsuarioReturnsEmptyArrayWhenNoItems() {
        $result = $this->model->obtenerPorTokenUsuario('nonexistent_token');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testAgregarItemAndObtenerPorTokenUsuario() {
        $datos = [
            'id_producto' => 1,
            'cantidad' => 3,
            'token_usuario' => $this->testToken
        ];
        
        $result = $this->model->agregarItem($datos);
        $this->assertTrue($result);
        
        $items = $this->model->obtenerPorTokenUsuario($this->testToken);
        $this->assertCount(1, $items);
        $this->assertEquals(1, $items[0]['id_producto']);
        $this->assertEquals(3, $items[0]['cantidad']);
        $this->assertEquals($this->testToken, $items[0]['token_usuario']);
        $this->assertEquals('Producto Test', $items[0]['producto_nombre']);
        $this->assertEquals(10.00, $items[0]['producto_precio']);
        $this->assertEquals(100, $items[0]['producto_stock']);
    }

    public function testObtenerItemPorProductoYToken() {
        $datos = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => $this->testToken
        ];
        
        $this->model->agregarItem($datos);
        
        $item = $this->model->obtenerItemPorProductoYToken(1, $this->testToken);
        $this->assertNotNull($item);
        $this->assertEquals(1, $item['id_producto']);
        $this->assertEquals(2, $item['cantidad']);
        $this->assertEquals($this->testToken, $item['token_usuario']);
    }

    public function testActualizarCantidad() {
        $datos = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => $this->testToken
        ];
        
        $this->model->agregarItem($datos);
        
        $items = $this->model->obtenerPorTokenUsuario($this->testToken);
        $itemId = $items[0]['id'];
        
        $result = $this->model->actualizarCantidad($itemId, 5);
        $this->assertTrue($result);
        
        $updatedItems = $this->model->obtenerPorTokenUsuario($this->testToken);
        $this->assertEquals(5, $updatedItems[0]['cantidad']);
    }

    public function testEliminarItem() {
        $datos = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => $this->testToken
        ];
        
        $this->model->agregarItem($datos);
        
        $items = $this->model->obtenerPorTokenUsuario($this->testToken);
        $itemId = $items[0]['id'];
        
        $result = $this->model->eliminarItem($itemId);
        $this->assertTrue($result);
        
        $remainingItems = $this->model->obtenerPorTokenUsuario($this->testToken);
        $this->assertEmpty($remainingItems);
    }

    public function testLimpiarCarrito() {
        $datos1 = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => $this->testToken
        ];
        
        $datos2 = [
            'id_producto' => 2,
            'cantidad' => 1,
            'token_usuario' => $this->testToken
        ];
        
        $this->model->agregarItem($datos1);
        $this->model->agregarItem($datos2);
        
        $items = $this->model->obtenerPorTokenUsuario($this->testToken);
        $this->assertCount(2, $items);
        
        $result = $this->model->limpiarCarrito($this->testToken);
        $this->assertTrue($result);
        
        $remainingItems = $this->model->obtenerPorTokenUsuario($this->testToken);
        $this->assertEmpty($remainingItems);
    }

    public function testProductoExiste() {
        $this->assertTrue($this->model->productoExiste(1));
        $this->assertTrue($this->model->productoExiste(2));
        $this->assertFalse($this->model->productoExiste(999));
    }

    public function testObtenerStockProducto() {
        $stock = $this->model->obtenerStockProducto(1);
        $this->assertEquals(100, $stock);
        
        $stock = $this->model->obtenerStockProducto(2);
        $this->assertEquals(0, $stock);
        
        $stock = $this->model->obtenerStockProducto(999);
        $this->assertEquals(0, $stock);
    }

    public function testMultipleItemsWithSameProduct() {
        $datos1 = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => $this->testToken
        ];
        
        $datos2 = [
            'id_producto' => 1,
            'cantidad' => 3,
            'token_usuario' => $this->testToken
        ];
        
        $this->model->agregarItem($datos1);
        $this->model->agregarItem($datos2);
        
        $items = $this->model->obtenerPorTokenUsuario($this->testToken);
        $this->assertCount(2, $items);
        
        // Verificar que ambos items tienen el mismo producto pero diferentes cantidades
        $this->assertEquals(1, $items[0]['id_producto']);
        $this->assertEquals(1, $items[1]['id_producto']);
        $this->assertNotEquals($items[0]['cantidad'], $items[1]['cantidad']);
    }

    public function testItemsWithDifferentTokens() {
        $token1 = 'token1_' . uniqid();
        $token2 = 'token2_' . uniqid();
        
        $datos1 = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => $token1
        ];
        
        $datos2 = [
            'id_producto' => 2,
            'cantidad' => 1,
            'token_usuario' => $token2
        ];
        
        $this->model->agregarItem($datos1);
        $this->model->agregarItem($datos2);
        
        $items1 = $this->model->obtenerPorTokenUsuario($token1);
        $items2 = $this->model->obtenerPorTokenUsuario($token2);
        
        $this->assertCount(1, $items1);
        $this->assertCount(1, $items2);
        $this->assertEquals($token1, $items1[0]['token_usuario']);
        $this->assertEquals($token2, $items2[0]['token_usuario']);
    }
}
