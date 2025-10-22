<?php

use PHPUnit\Framework\TestCase;
use App\Models\DetalleTempModel;

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

class DetalleTempModelTest extends TestCase {
    private $fakeDb;
    private $model;

    protected function setUp(): void {
        $this->fakeDb = new FakeDatabase();
        $this->model = new DetalleTempModel($this->fakeDb);
    }

    public function testObtenerPorTokenUsuarioReturnsResultSet() {
        $expectedResults = [
            ['id' => 1, 'id_producto' => 1, 'cantidad' => 2, 'token_usuario' => 'token123'],
            ['id' => 2, 'id_producto' => 2, 'cantidad' => 1, 'token_usuario' => 'token123']
        ];
        
        $this->fakeDb->setResultSet($expectedResults);
        
        $result = $this->model->obtenerPorTokenUsuario('token123');
        
        $this->assertEquals($expectedResults, $result);
        $this->assertStringContainsString('SELECT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_temp', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerItemPorProductoYTokenReturnsSingle() {
        $expectedResult = ['id' => 1, 'id_producto' => 1, 'cantidad' => 2, 'token_usuario' => 'token123'];
        
        $this->fakeDb->setSingle($expectedResult);
        
        $result = $this->model->obtenerItemPorProductoYToken(1, 'token123');
        
        $this->assertEquals($expectedResult, $result);
        $this->assertStringContainsString('SELECT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_temp', $this->fakeDb->getQueries()[0]);
    }

    public function testAgregarItemBindsAndExecutes() {
        $datos = [
            'id_producto' => 1,
            'cantidad' => 2,
            'token_usuario' => 'token123'
        ];
        
        $result = $this->model->agregarItem($datos);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('INSERT', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_temp', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':id_producto', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[0]['value']);
        $this->assertEquals(':cantidad', $this->fakeDb->getBinds()[1]['param']);
        $this->assertEquals(2, $this->fakeDb->getBinds()[1]['value']);
        $this->assertEquals(':token_usuario', $this->fakeDb->getBinds()[2]['param']);
        $this->assertEquals('token123', $this->fakeDb->getBinds()[2]['value']);
    }

    public function testActualizarCantidadBindsAndExecutes() {
        $result = $this->model->actualizarCantidad(1, 5);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('UPDATE', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_temp', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':cantidad', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(5, $this->fakeDb->getBinds()[0]['value']);
        $this->assertEquals(':id', $this->fakeDb->getBinds()[1]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[1]['value']);
    }

    public function testEliminarItemBindsAndExecutes() {
        $result = $this->model->eliminarItem(1);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('DELETE', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_temp', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':id', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals(1, $this->fakeDb->getBinds()[0]['value']);
    }

    public function testLimpiarCarritoBindsAndExecutes() {
        $result = $this->model->limpiarCarrito('token123');
        
        $this->assertTrue($result);
        $this->assertStringContainsString('DELETE', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('detalle_temp', $this->fakeDb->getQueries()[0]);
        $this->assertEquals(':token_usuario', $this->fakeDb->getBinds()[0]['param']);
        $this->assertEquals('token123', $this->fakeDb->getBinds()[0]['value']);
    }

    public function testProductoExisteReturnsCount() {
        $this->fakeDb->setSingle(['COUNT(*)' => 1]);
        
        $result = $this->model->productoExiste(1);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('SELECT COUNT(*)', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerStockProductoReturnsStock() {
        $this->fakeDb->setSingle(['stock' => 10]);
        
        $result = $this->model->obtenerStockProducto(1);
        
        $this->assertEquals(10, $result);
        $this->assertStringContainsString('SELECT stock', $this->fakeDb->getQueries()[0]);
        $this->assertStringContainsString('productoservicio', $this->fakeDb->getQueries()[0]);
    }

    public function testObtenerStockProductoReturnsZeroWhenNotFound() {
        $this->fakeDb->setSingle(null);
        
        $result = $this->model->obtenerStockProducto(999);
        
        $this->assertEquals(0, $result);
    }

    public function testProductoExisteReturnsFalseWhenCountIsZero() {
        $this->fakeDb->setSingle(['COUNT(*)' => 0]);
        
        $result = $this->model->productoExiste(999);
        
        $this->assertFalse($result);
    }
}