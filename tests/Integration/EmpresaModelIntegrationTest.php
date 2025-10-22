<?php
use PHPUnit\Framework\TestCase;
use App\Models\EmpresaModel;
use App\Core\Database;

class EmpresaModelIntegrationTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new EmpresaModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    public function testCrearEmpresa() {
        $datos = [
            'nombre' => 'Test Empresa',
            'ruc' => '12345678901',
            'direccion' => 'Test Dirección 123',
            'telefono' => '01-234-5678',
            'email' => 'test@empresa.com',
            'iva' => 18.00
        ];
        
        $resultado = $this->model->crear($datos);
        $this->assertTrue($resultado);
        
        // Verificar que se creó correctamente
        $empresas = $this->model->obtenerTodas();
        $this->assertNotEmpty($empresas);
        
        // Buscar la empresa creada
        $encontrada = false;
        foreach ($empresas as $empresa) {
            if ($empresa['nombre'] === 'Test Empresa') {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada);
    }

    public function testActualizarEmpresa() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Original',
            'ruc' => '11111111111',
            'direccion' => 'Dirección Original',
            'telefono' => '01-111-1111',
            'email' => 'original@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Obtener la empresa creada
        $empresas = $this->model->obtenerTodas();
        $empresaId = null;
        foreach ($empresas as $empresa) {
            if ($empresa['nombre'] === 'Empresa Original') {
                $empresaId = $empresa['id'];
                break;
            }
        }
        
        $this->assertNotNull($empresaId);
        
        // Actualizar la empresa
        $nuevosDatos = [
            'nombre' => 'Empresa Actualizada',
            'ruc' => '11111111111',
            'direccion' => 'Dirección Actualizada',
            'telefono' => '01-111-1111',
            'email' => 'actualizada@empresa.com',
            'iva' => 19.00
        ];
        $resultado = $this->model->actualizar($empresaId, $nuevosDatos);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $empresaActualizada = $this->model->obtenerPorId($empresaId);
        $this->assertEquals('Empresa Actualizada', $empresaActualizada['nombre']);
        $this->assertEquals(19.00, $empresaActualizada['iva']);
    }

    public function testEliminarEmpresa() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa para Eliminar',
            'ruc' => '22222222222',
            'direccion' => 'Dirección Eliminar',
            'telefono' => '01-222-2222',
            'email' => 'eliminar@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Obtener la empresa creada
        $empresas = $this->model->obtenerTodas();
        $empresaId = null;
        foreach ($empresas as $empresa) {
            if ($empresa['nombre'] === 'Empresa para Eliminar') {
                $empresaId = $empresa['id'];
                break;
            }
        }
        
        $this->assertNotNull($empresaId);
        
        // Eliminar la empresa
        $resultado = $this->model->eliminar($empresaId);
        $this->assertTrue($resultado);
        
        // Verificar que se eliminó
        $empresaEliminada = $this->model->obtenerPorId($empresaId);
        $this->assertNull($empresaEliminada);
    }

    public function testObtenerPorRuc() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Test RUC',
            'ruc' => '33333333333',
            'direccion' => 'Dirección Test',
            'telefono' => '01-333-3333',
            'email' => 'ruc@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Buscar por RUC
        $empresa = $this->model->obtenerPorRuc('33333333333');
        $this->assertNotNull($empresa);
        $this->assertEquals('Empresa Test RUC', $empresa['nombre']);
    }

    public function testObtenerPorEmail() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Test Email',
            'ruc' => '44444444444',
            'direccion' => 'Dirección Test',
            'telefono' => '01-444-4444',
            'email' => 'email@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Buscar por email
        $empresa = $this->model->obtenerPorEmail('email@empresa.com');
        $this->assertNotNull($empresa);
        $this->assertEquals('Empresa Test Email', $empresa['nombre']);
    }

    public function testRucExiste() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Test Existencia',
            'ruc' => '55555555555',
            'direccion' => 'Dirección Test',
            'telefono' => '01-555-5555',
            'email' => 'existencia@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Verificar que existe
        $this->assertTrue($this->model->rucExiste('55555555555'));
        $this->assertFalse($this->model->rucExiste('99999999999'));
    }

    public function testEmailExiste() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Test Email Existencia',
            'ruc' => '66666666666',
            'direccion' => 'Dirección Test',
            'telefono' => '01-666-6666',
            'email' => 'emailexistencia@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Verificar que existe
        $this->assertTrue($this->model->emailExiste('emailexistencia@empresa.com'));
        $this->assertFalse($this->model->emailExiste('inexistente@empresa.com'));
    }

    public function testActualizarLogo() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Test Logo',
            'ruc' => '77777777777',
            'direccion' => 'Dirección Test',
            'telefono' => '01-777-7777',
            'email' => 'logo@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Obtener la empresa creada
        $empresas = $this->model->obtenerTodas();
        $empresaId = null;
        foreach ($empresas as $empresa) {
            if ($empresa['nombre'] === 'Empresa Test Logo') {
                $empresaId = $empresa['id'];
                break;
            }
        }
        
        $this->assertNotNull($empresaId);
        
        // Actualizar logo
        $resultado = $this->model->actualizarLogo($empresaId, 'nuevo-logo.png');
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $empresaActualizada = $this->model->obtenerPorId($empresaId);
        $this->assertEquals('nuevo-logo.png', $empresaActualizada['logo']);
    }

    public function testActualizarIva() {
        // Crear una empresa primero
        $datos = [
            'nombre' => 'Empresa Test IVA',
            'ruc' => '88888888888',
            'direccion' => 'Dirección Test',
            'telefono' => '01-888-8888',
            'email' => 'iva@empresa.com',
            'iva' => 18.00
        ];
        $this->model->crear($datos);
        
        // Obtener la empresa creada
        $empresas = $this->model->obtenerTodas();
        $empresaId = null;
        foreach ($empresas as $empresa) {
            if ($empresa['nombre'] === 'Empresa Test IVA') {
                $empresaId = $empresa['id'];
                break;
            }
        }
        
        $this->assertNotNull($empresaId);
        
        // Actualizar IVA
        $resultado = $this->model->actualizarIva($empresaId, 19.00);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $empresaActualizada = $this->model->obtenerPorId($empresaId);
        $this->assertEquals(19.00, $empresaActualizada['iva']);
    }

    public function testObtenerEstadisticas() {
        // Crear algunas empresas
        $this->model->crear([
            'nombre' => 'Empresa 1',
            'ruc' => '11111111111',
            'direccion' => 'Dirección 1',
            'telefono' => '01-111-1111',
            'email' => 'empresa1@test.com',
            'iva' => 18.00
        ]);
        
        $this->model->crear([
            'nombre' => 'Empresa 2',
            'ruc' => '22222222222',
            'direccion' => 'Dirección 2',
            'telefono' => '01-222-2222',
            'email' => 'empresa2@test.com',
            'iva' => 19.00
        ]);
        
        // Obtener estadísticas
        $estadisticas = $this->model->obtenerEstadisticas();
        $this->assertNotEmpty($estadisticas);
        $this->assertArrayHasKey('total_empresas', $estadisticas[0]);
        $this->assertArrayHasKey('promedio_iva', $estadisticas[0]);
    }
}
