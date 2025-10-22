<?php
use PHPUnit\Framework\TestCase;
use App\Models\EspecieModel;
use App\Core\Database;

class EspecieModelIntegrationTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new EspecieModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    public function testCrearEspecie() {
        $datos = ['nombre' => 'Test Especie'];
        
        $resultado = $this->model->crear($datos);
        $this->assertTrue($resultado);
        
        // Verificar que se creó correctamente
        $especies = $this->model->obtenerTodas();
        $this->assertNotEmpty($especies);
        
        // Buscar la especie creada
        $encontrada = false;
        foreach ($especies as $especie) {
            if ($especie['nombre'] === 'Test Especie') {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada);
    }

    public function testActualizarEspecie() {
        // Crear una especie primero
        $datos = ['nombre' => 'Especie Original'];
        $this->model->crear($datos);
        
        // Obtener la especie creada
        $especies = $this->model->obtenerTodas();
        $especieId = null;
        foreach ($especies as $especie) {
            if ($especie['nombre'] === 'Especie Original') {
                $especieId = $especie['id'];
                break;
            }
        }
        
        $this->assertNotNull($especieId);
        
        // Actualizar la especie
        $nuevosDatos = ['nombre' => 'Especie Actualizada'];
        $resultado = $this->model->actualizar($especieId, $nuevosDatos);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $especieActualizada = $this->model->obtenerPorId($especieId);
        $this->assertEquals('Especie Actualizada', $especieActualizada['nombre']);
    }

    public function testEliminarEspecie() {
        // Crear una especie primero
        $datos = ['nombre' => 'Especie para Eliminar'];
        $this->model->crear($datos);
        
        // Obtener la especie creada
        $especies = $this->model->obtenerTodas();
        $especieId = null;
        foreach ($especies as $especie) {
            if ($especie['nombre'] === 'Especie para Eliminar') {
                $especieId = $especie['id'];
                break;
            }
        }
        
        $this->assertNotNull($especieId);
        
        // Eliminar la especie
        $resultado = $this->model->eliminar($especieId);
        $this->assertTrue($resultado);
        
        // Verificar que se eliminó
        $especieEliminada = $this->model->obtenerPorId($especieId);
        $this->assertNull($especieEliminada);
    }

    public function testObtenerPorNombre() {
        // Crear una especie primero
        $datos = ['nombre' => 'Especie Test Nombre'];
        $this->model->crear($datos);
        
        // Buscar por nombre
        $especie = $this->model->obtenerPorNombre('Especie Test Nombre');
        $this->assertNotNull($especie);
        $this->assertEquals('Especie Test Nombre', $especie['nombre']);
    }

    public function testNombreExiste() {
        // Crear una especie primero
        $datos = ['nombre' => 'Especie Test Existencia'];
        $this->model->crear($datos);
        
        // Verificar que existe
        $this->assertTrue($this->model->nombreExiste('Especie Test Existencia'));
        $this->assertFalse($this->model->nombreExiste('Especie Inexistente'));
    }

    public function testObtenerConRazas() {
        // Crear una especie
        $datos = ['nombre' => 'Especie Test Razas'];
        $this->model->crear($datos);
        
        // Obtener con razas
        $especiesConRazas = $this->model->obtenerConRazas();
        $this->assertNotEmpty($especiesConRazas);
        
        // Verificar estructura
        $especie = $especiesConRazas[0];
        $this->assertArrayHasKey('id', $especie);
        $this->assertArrayHasKey('especie_nombre', $especie);
        $this->assertArrayHasKey('total_razas', $especie);
    }

    public function testObtenerEstadisticas() {
        // Crear algunas especies
        $this->model->crear(['nombre' => 'Especie 1']);
        $this->model->crear(['nombre' => 'Especie 2']);
        
        // Obtener estadísticas
        $estadisticas = $this->model->obtenerEstadisticas();
        $this->assertNotEmpty($estadisticas);
        $this->assertArrayHasKey('total_especies', $estadisticas[0]);
        $this->assertArrayHasKey('total_razas', $estadisticas[0]);
    }
}
