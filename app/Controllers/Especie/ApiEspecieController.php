<?php
namespace App\Controllers\Especie;

use App\Core\BaseController;
use App\Models\EspecieModel;

class ApiEspecieController extends BaseController {

    private $especieModel;

    public function __construct() {
        $this->especieModel = $this->model('EspecieModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apiespecie/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $especies = $this->especieModel->obtenerTodas();
        echo json_encode(['data' => $especies]);
    }

    // POST /apiespecie/crear
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

        $required = ['nombre'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $nombre = trim((string)$payload['nombre']);

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

        // Validaciones de unicidad
        if ($this->especieModel->nombreExiste($nombre)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe una especie con ese nombre']);
            return;
        }

        $ok = $this->especieModel->crear([
            'nombre' => $nombre
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Especie creada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la especie']);
        }
    }

    // PUT /apiespecie/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->especieModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Especie no encontrada']); 
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

        // Verificar si ya existe otro con el mismo nombre
        if ($this->especieModel->nombreExiste($nombre, (int)$id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otra especie con ese nombre']);
            return;
        }

        $ok = $this->especieModel->actualizar((int)$id, [
            'nombre' => $nombre
        ]);
        
        if ($ok) { 
            echo json_encode(['message' => 'Especie actualizada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apiespecie/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->especieModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Especie no encontrada']); 
            return; 
        }

        // Verificar si tiene razas asociadas
        $especiesConRazas = $this->especieModel->obtenerConRazas();
        foreach ($especiesConRazas as $e) {
            if ($e['id'] == $id && $e['total_razas'] > 0) {
                http_response_code(422);
                echo json_encode(['error' => 'No se puede eliminar la especie porque tiene razas asociadas']);
                return;
            }
        }
        
        $ok = $this->especieModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Especie eliminada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apiespecie/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $especie = $this->especieModel->obtenerPorId((int)$id);
        if (!$especie) {
            http_response_code(404);
            echo json_encode(['error' => 'Especie no encontrada']);
            return;
        }
        
        echo json_encode(['data' => $especie]);
    }

    // GET /apiespecie/con-razas
    public function conRazas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $especies = $this->especieModel->obtenerConRazas();
        echo json_encode(['data' => $especies]);
    }

    // GET /apiespecie/estadisticas
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->especieModel->obtenerEstadisticas();
        echo json_encode(['data' => $estadisticas]);
    }
}
