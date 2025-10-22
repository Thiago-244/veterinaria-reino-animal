<?php
namespace App\Controllers\Raza;

use App\Core\BaseController;
use App\Models\RazaModel;

class ApiRazaController extends BaseController {

    private $razaModel;

    public function __construct() {
        $this->razaModel = $this->model('RazaModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apiraza/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $razas = $this->razaModel->obtenerTodas();
        echo json_encode(['data' => $razas]);
    }

    // POST /apiraza/crear
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $required = ['nombre', 'id_especie'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $nombre = trim((string)$payload['nombre']);
        $id_especie = (int)$payload['id_especie'];

        // Validaciones de formato y longitud
        if (strlen($nombre) > 50) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 50 caracteres']);
            return;
        }

        if (strlen($nombre) < 2) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre debe tener al menos 2 caracteres']);
            return;
        }

        // Validar que la especie existe
        if (!$this->razaModel->especieExiste($id_especie)) {
            http_response_code(404);
            echo json_encode(['error' => 'Especie no encontrada']);
            return;
        }

        // Validaciones de unicidad
        if ($this->razaModel->nombreExisteEnEspecie($nombre, $id_especie)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe una raza con ese nombre en la especie seleccionada']);
            return;
        }

        $ok = $this->razaModel->crear([
            'nombre' => $nombre,
            'id_especie' => $id_especie
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Raza creada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la raza']);
        }
    }

    // PUT /apiraza/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->razaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Raza no encontrada']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) { 
            http_response_code(400); 
            echo json_encode(['error' => 'JSON inválido']); 
            return; 
        }

        $nombre = isset($payload['nombre']) ? trim((string)$payload['nombre']) : $existente['nombre'];
        $id_especie = isset($payload['id_especie']) ? (int)$payload['id_especie'] : $existente['id_especie'];

        // Validaciones
        if (strlen($nombre) > 50) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 50 caracteres']);
            return;
        }

        if (strlen($nombre) < 2) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre debe tener al menos 2 caracteres']);
            return;
        }

        // Validar que la especie existe
        if (!$this->razaModel->especieExiste($id_especie)) {
            http_response_code(404);
            echo json_encode(['error' => 'Especie no encontrada']);
            return;
        }

        // Verificar si ya existe otra raza con el mismo nombre en esa especie
        if ($this->razaModel->nombreExisteEnEspecie($nombre, $id_especie, (int)$id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otra raza con ese nombre en la especie seleccionada']);
            return;
        }

        $ok = $this->razaModel->actualizar((int)$id, [
            'nombre' => $nombre,
            'id_especie' => $id_especie
        ]);
        
        if ($ok) { 
            echo json_encode(['message' => 'Raza actualizada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apiraza/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->razaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Raza no encontrada']); 
            return; 
        }
        
        $ok = $this->razaModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Raza eliminada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apiraza/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $raza = $this->razaModel->obtenerPorId((int)$id);
        if (!$raza) {
            http_response_code(404);
            echo json_encode(['error' => 'Raza no encontrada']);
            return;
        }
        
        echo json_encode(['data' => $raza]);
    }

    // GET /apiraza/por-especie/{id_especie}
    public function porEspecie($id_especie) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $razas = $this->razaModel->obtenerPorEspecie((int)$id_especie);
        echo json_encode(['data' => $razas]);
    }

    // GET /apiraza/agrupadas-por-especie
    public function agrupadasPorEspecie() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $razas = $this->razaModel->obtenerAgrupadasPorEspecie();
        echo json_encode(['data' => $razas]);
    }

    // GET /apiraza/estadisticas
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->razaModel->obtenerEstadisticas();
        echo json_encode(['data' => $estadisticas]);
    }
}
