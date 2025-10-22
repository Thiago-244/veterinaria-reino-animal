<?php

use PHPUnit\Framework\TestCase;
use App\Models\DetalleVentaModel;

class FakeDatabase {
    private $queries = [];
    private $binds = [];
    private $results = [];
    private $executeResults = [];
    private $lastInsertId = 1;

    public function query($sql) {
        $this->queries[] = $sql;
        return $this;
    }

    public function bind($param, $value, $type = null) {
        $this->binds[] = ['param' => $param, 'value' => $value, 'type' => $type];
        return $this;
    }

    public function execute() {
        $this->executeResults[] = true;
        return true;
    }

    public function resultSet() {
        return $this->results['resultSet'] ?? [];
    }

    public function single() {
        return $this->results['single'] ?? null;
    }

    public function lastInsertId() {
        return $this->lastInsertId++;
    }

    public function setResultSet($results) {
        $this->results['resultSet'] = $results;
    }

    public function setSingle($result) {
        $this->results['single'] = $result;
    }

    public function getQueries() {
        return $this->queries;
    }

    public function getBinds() {
        return $this->binds;
    }

    public function getExecuteResults() {
        return $this->executeResults;
    }
}

class DetalleVentaModelTest extends TestCase {
    private $fakeDb;
    private $model;

    protected function setUp(): void {
        $this->fakeDb = new FakeDatabase();
        $this->model = new DetalleVentaModel($this->fakeDb);
    }

    public function testObtenerPorVentaReturnsResultSet() {
        $expectedResults = [
            ['id' => 1, 'id_venta' => 1, 'id_producto' => 1, 'cantidad' => 2, 'precio_unitario' => 10.00, 'subtotal' => 20.00],
            ['id' => 2, 'id_venta' => 1, 'id_producto' => 2, 'cantidad' => 1, 'precio_unitario' => 15.00, 'subtotal' => 15.00]
        ];
        
        $this->fakeDb->setResultSet($expectedResults);
        
        $result = $this->model->obtenerPorVenta(1);
        
        $this->assertEquals($expectedResults, $result);
        $this->assertStringContainsString('SELECT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerPorIdReturnsSingle() {
        $expectedResult = ['id' => 1, 'id_venta' => 1, 'id_producto' => 1, 'cantidad' => 2, 'precio_unitario' => 10.00, 'subtotal' => 20.00];
        
        $this->fakeDb->setSingle($expectedResult);
        
        $result = $this->model->obtenerPorId(1);
        
        $this->assertEquals($expectedResult, $result);
        $this->assertStringContainsString('SELECT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
    }

    public function testCrearBindsAndExecutes() {
        $datos = [
            'id_venta' => 1,
            'id_producto' => 1,
            'cantidad' => 2,
            'precio_unitario' => 10.00,
            'subtotal' => 20.00
        ];
        
        $result = $this->model->crear($datos);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('INSERT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':id_venta', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[0]['value']);
        $this->assertEquals(':id_producto', $this->fakeDb->getBinds()[1]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[1]['value']);
        $this->assertEquals(':cantidad', $this->fakeDb->getBinds()[2]['param']);
        $this->assertEquals(2, $this->fakeDb->getBinds()[2]['value']);
        $this->assertEquals(':precio_unitario', $this->fakeDb->getBinds()[3]['param']);
        $this->assertEquals(10.00, $this->fakeDb->getBinds()[3]['value']);
        $this->assertEquals(':subtotal', $this->fakeDb->getBinds()[4]['param']);
        $this->assertEquals(20.00, $this->fakeDb->getBinds()[4]['value']);
    }

    public function testActualizarBindsAndExecutes() {
        $datos = [
            'id_producto' => 2,
            'cantidad' => 3,
            'precio_unitario' => 15.00,
            'subtotal' => 45.00
        ];
        
        $result = $this->model->actualizar(1, $datos);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('UPDATE', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':id_producto', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(2, $this->fakeDb->getBinds()[0]['value']);
        $this->assertEquals(':cantidad', $this->fakeDb->getBinds()[1]['param']);
        $this->assertEquals(3, $this->fakeDb->getBinds()[1]['value']);
        $this->assertEquals(':precio_unitario', $this->fakeDb->getBinds()[2]['param']);
        $this->assertEquals(15.00, $this->fakeDb->getBinds()[2]['value']);
        $this->assertEquals(':subtotal', $this->fakeDb->getBinds()[3]['param']);
        $this->assertEquals(45.00, $this->fakeDb->getBinds()[3]['value']);
        $this->assertEquals(':id', $this->fakeDb->getBinds()[4]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[4]['value']);
    }

    public function testEliminarBindsAndExecutes() {
        $result = $this->model->eliminar(1);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('DELETE', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':id', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[0]['value']);
    }

    public function testEliminarPorVentaBindsAndExecutes() {
        $result = $this->model->eliminarPorVenta(1);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('DELETE', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':id_venta', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[0]['value']);
    }

    public function testVentaExisteReturnsCount() {
        $this->fakeDb->setSingle(['COUNT(*)' => 1]);
        
        $result = $this->model->ventaExiste(1);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('SELECT COUNT(*)', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('venta', $this->fakeDb->getQueries()[0]);
    }

    public function testProductoExisteReturnsCount() {
        $this->fakeDb->setSingle(['COUNT(*)' => 1]);
        
        $result = $this->model->productoExiste(1);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('SELECT COUNT(*)', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerPrecioProductoReturnsPrice() {
        $this->fakeDb->setSingle(['precio' => 25.50]);
        
        $result = $this->model->obtenerPrecioProducto(1);
        
        $this->assertEquals(25.50, $result);
        $this->assertStringContainsString('SELECT precio', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerPrecioProductoReturnsZeroWhenNotFound() {
        $this->fakeDb->setSingle(null);
        
        $result = $this->model->obtenerPrecioProducto(999);
        
        $this->assertEquals(0, $result);
    }

    public function testCalcularSubtotal() {
        $result = $this->model->calcularSubtotal(10.50, 3);
        
        $this->assertEquals(31.50, $result);
    }

    public function testObtenerTotalVentaReturnsTotal() {
        $this->fakeDb->setSingle(['total' => 150.75]);
        
        $result = $this->model->obtenerTotalVenta(1);
        
        $this->assertEquals(150.75, $result);
        $this->assertStringContainsString('SELECT SUM(subtotal)', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerTotalVentaReturnsZeroWhenNoResults() {
        $this->fakeDb->setSingle(null);
        
        $result = $this->model->obtenerTotalVenta(999);
        
        $this->assertEquals(0.0, $result);
    }

    public function testObtenerEstadisticasVentasReturnsResultSet() {
        $expectedResults = [
            ['producto_nombre' => 'Producto A', 'total_vendido' => 10, 'total_ingresos' => 100.00, 'veces_vendido' => 5]
        ];
        
        $this->fakeDb->setResultSet($expectedResults);
        
        $result = $this->model->obtenerEstadisticasVentas();
        
        $this->assertEquals($expectedResults, $result);
        $this->assertStringContainsString('SELECT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('GROUP BY', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerVentasPorProductoReturnsResultSet() {
        $expectedResults = [
            ['id' => 1, 'id_venta' => 1, 'cantidad' => 2, 'precio_unitario' => 10.00, 'subtotal' => 20.00]
        ];
        
        $this->fakeDb->setResultSet($expectedResults);
        
        $result = $this->model->obtenerVentasPorProducto(1);
        
        $this->assertEquals($expectedResults, $result);
        $this->assertStringContainsString('SELECT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_venta', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('venta', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('clientes', $this->fakeDb->getQueries()[0]);
    }

    public function testVentaExisteReturnsFalseWhenCountIsZero() {
        $this->fakeDb->setSingle(['COUNT(*)' => 0]);
        
        $result = $this->model->ventaExiste(999);
        
        $this->assertFalse($result);
    }

    public function testProductoExisteReturnsFalseWhenCountIsZero() {
        $this->fakeDb->setSingle(['COUNT(*)' => 0]);
        
        $result = $this->model->productoExiste(999);
        
        $this->assertFalse($result);
    }
}
