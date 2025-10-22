<?php
use PHPUnit\Framework\TestCase;
use App\Models\RazaModel;
use App\Core\Database;

class RazaModelIntegrationTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new RazaModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    public function testCrearRaza() {
        $datos = [
            'id_especie' => 1, // Asumiendo que existe especie con ID 1
            'nombre' => 'Test Raza'
        ];
        
        $resultado = $this->model->crear($datos);
        $this->assertTrue($resultado);
        
        // Verificar que se creó correctamente
        $razas = $this->model->obtenerTodas();
        $this->assertNotEmpty($razas);
        
        // Buscar la raza creada
        $encontrada = false;
        foreach ($razas as $raza) {
            if ($raza['nombre'] === 'Test Raza') {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada);
    }

    public function testActualizarRaza() {
        // Crear una raza primero
        $datos = [
            'id_especie' => 1,
            'nombre' => 'Raza Original'
        ];
        $this->model->crear($datos);
        
        // Obtener la raza creada
        $razas = $this->model->obtenerTodas();
        $razaId = null;
        foreach ($razas as $raza) {
            if ($raza['nombre'] === 'Raza Original') {
                $razaId = $raza['id'];
                break;
            }
        }
        
        $this->assertNotNull($razaId);
        
        // Actualizar la raza
        $nuevosDatos = [
            'id_especie' => 1,
            'nombre' => 'Raza Actualizada'
        ];
        $resultado = $this->model->actualizar($razaId, $nuevosDatos);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $razaActualizada = $this->model->obtenerPorId($razaId);
        $this->assertEquals('Raza Actualizada', $razaActualizada['nombre']);
    }

    public function testEliminarRaza() {
        // Crear una raza primero
        $datos = [
            'id_especie' => 1,
            'nombre' => 'Raza para Eliminar'
        ];
        $this->model->crear($datos);
        
        // Obtener la raza creada
        $razas = $this->model->obtenerTodas();
        $razaId = null;
        foreach ($razas as $raza) {
            if ($raza['nombre'] === 'Raza para Eliminar') {
                $razaId = $raza['id'];
                break;
            }
        }
        
        $this->assertNotNull($razaId);
        
        // Eliminar la raza
        $resultado = $this->model->eliminar($razaId);
        $this->assertTrue($resultado);
        
        // Verificar que se eliminó
        $razaEliminada = $this->model->obtenerPorId($razaId);
        $this->assertNull($razaEliminada);
    }

    public function testObtenerPorEspecie() {
        // Crear algunas razas primero
        $this->model->crear(['id_especie' => 1, 'nombre' => 'Raza Test 1']);
        $this->model->crear(['id_especie' => 1, 'nombre' => 'Raza Test 2']);
        $this->model->crear(['id_especie' => 2, 'nombre' => 'Raza Test 3']);
        
        // Obtener razas por especie
        $razasEspecie1 = $this->model->obtenerPorEspecie(1);
        $razasEspecie2 = $this->model->obtenerPorEspecie(2);
        
        $this->assertGreaterThanOrEqual(2, count($razasEspecie1));
        $this->assertGreaterThanOrEqual(1, count($razasEspecie2));
        
        // Verificar que todas las razas de especie 1 pertenecen a esa especie
        foreach ($razasEspecie1 as $raza) {
            $this->assertEquals(1, $raza['id_especie']);
        }
    }

    public function testNombreExisteEnEspecie() {
        // Crear una raza primero
        $datos = [
            'id_especie' => 1,
            'nombre' => 'Raza Test Existencia'
        ];
        $this->model->crear($datos);
        
        // Verificar que existe
        $this->assertTrue($this->model->nombreExisteEnEspecie('Raza Test Existencia', 1));
        $this->assertFalse($this->model->nombreExisteEnEspecie('Raza Inexistente', 1));
        $this->assertFalse($this->model->nombreExisteEnEspecie('Raza Test Existencia', 2));
    }

    public function testObtenerAgrupadasPorEspecie() {
        // Crear algunas razas
        $this->model->crear(['id_especie' => 1, 'nombre' => 'Raza 1']);
        $this->model->crear(['id_especie' => 1, 'nombre' => 'Raza 2']);
        
        // Obtener agrupadas por especie
        $razasAgrupadas = $this->model->obtenerAgrupadasPorEspecie();
        $this->assertNotEmpty($razasAgrupadas);
        
        // Verificar estructura
        $especie = $razasAgrupadas[0];
        $this->assertArrayHasKey('especie_id', $especie);
        $this->assertArrayHasKey('especie_nombre', $especie);
        $this->assertArrayHasKey('total_razas', $especie);
        $this->assertArrayHasKey('razas', $especie);
    }

    public function testObtenerEstadisticas() {
        // Crear algunas razas
        $this->model->crear(['id_especie' => 1, 'nombre' => 'Raza 1']);
        $this->model->crear(['id_especie' => 1, 'nombre' => 'Raza 2']);
        $this->model->crear(['id_especie' => 2, 'nombre' => 'Raza 3']);
        
        // Obtener estadísticas
        $estadisticas = $this->model->obtenerEstadisticas();
        $this->assertNotEmpty($estadisticas);
        $this->assertArrayHasKey('total_razas', $estadisticas[0]);
        $this->assertArrayHasKey('especies_con_razas', $estadisticas[0]);
    }
}
