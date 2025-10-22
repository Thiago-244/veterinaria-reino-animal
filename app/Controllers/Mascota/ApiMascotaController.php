<?php
namespace App\Controllers\Mascota;

use App\Core\BaseController;
use App\Models\MascotaModel;

class ApiMascotaController extends BaseController {

    private $mascotaModel;

    public function __construct() {
        $this->mascotaModel = $this->model('MascotaModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apimascota/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $mascotas = $this->mascotaModel->obtenerTodas();
        echo json_encode(['data' => $mascotas]);
    }

    // POST /apimascota/crear
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

        $required = ['nombre', 'id_cliente', 'id_raza', 'fecha_nacimiento', 'sexo'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $nombre = trim((string)$payload['nombre']);
        $id_cliente = (int)$payload['id_cliente'];
        $id_raza = (int)$payload['id_raza'];
        $fecha_nacimiento = trim((string)$payload['fecha_nacimiento']);
        $sexo = trim((string)$payload['sexo']);
        $color = isset($payload['color']) ? trim((string)$payload['color']) : '';
        $peso = isset($payload['peso']) && $payload['peso'] !== '' ? (float)$payload['peso'] : null;

        // Validaciones de formato y longitud
        if (strlen($nombre) > 50) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 50 caracteres']);
            return;
        }
        
        if (!in_array($sexo, ['Macho', 'Hembra'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El sexo debe ser "Macho" o "Hembra"']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
            http_response_code(422);
            echo json_encode(['error' => 'La fecha de nacimiento debe tener formato YYYY-MM-DD']);
            return;
        }

        // Validar que el cliente existe
        if (!$this->mascotaModel->clienteExiste($id_cliente)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }

        // Validar que la raza existe
        if (!$this->mascotaModel->razaExiste($id_raza)) {
            http_response_code(404);
            echo json_encode(['error' => 'Raza no encontrada']);
            return;
        }

        $ok = $this->mascotaModel->crear([
            'codigo' => $this->mascotaModel->generarCodigo(),
            'nombre' => $nombre,
            'id_cliente' => $id_cliente,
            'id_raza' => $id_raza,
            'fecha_nacimiento' => $fecha_nacimiento,
            'sexo' => $sexo,
            'color' => $color,
            'peso' => $peso,
            'foto' => 'default_pet.png'
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Mascota creada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la mascota']);
        }
    }

    // PUT /apimascota/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->mascotaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Mascota no encontrada']); 
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
        $id_cliente = isset($payload['id_cliente']) ? (int)$payload['id_cliente'] : $existente['id_cliente'];
        $id_raza = isset($payload['id_raza']) ? (int)$payload['id_raza'] : $existente['id_raza'];
        $fecha_nacimiento = isset($payload['fecha_nacimiento']) ? trim((string)$payload['fecha_nacimiento']) : $existente['fecha_nacimiento'];
        $sexo = isset($payload['sexo']) ? trim((string)$payload['sexo']) : $existente['sexo'];
        $color = isset($payload['color']) ? trim((string)$payload['color']) : $existente['color'];
        $peso = isset($payload['peso']) && $payload['peso'] !== '' ? (float)$payload['peso'] : $existente['peso'];

        // Validaciones
        if (strlen($nombre) > 50) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 50 caracteres']);
            return;
        }
        
        if (!in_array($sexo, ['Macho', 'Hembra'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El sexo debe ser "Macho" o "Hembra"']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
            http_response_code(422);
            echo json_encode(['error' => 'La fecha de nacimiento debe tener formato YYYY-MM-DD']);
            return;
        }

        // Validar que el cliente existe
        if (!$this->mascotaModel->clienteExiste($id_cliente)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }

        // Validar que la raza existe
        if (!$this->mascotaModel->razaExiste($id_raza)) {
            http_response_code(404);
            echo json_encode(['error' => 'Raza no encontrada']);
            return;
        }

        $ok = $this->mascotaModel->actualizar((int)$id, [
            'nombre' => $nombre,
            'id_cliente' => $id_cliente,
            'id_raza' => $id_raza,
            'fecha_nacimiento' => $fecha_nacimiento,
            'sexo' => $sexo,
            'color' => $color,
            'peso' => $peso,
        ]);
        
        if ($ok) { 
            echo json_encode(['message' => 'Mascota actualizada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apimascota/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->mascotaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Mascota no encontrada']); 
            return; 
        }
        
        $ok = $this->mascotaModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Mascota eliminada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apimascota/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $mascota = $this->mascotaModel->obtenerPorId((int)$id);
        if (!$mascota) {
            http_response_code(404);
            echo json_encode(['error' => 'Mascota no encontrada']);
            return;
        }
        
        echo json_encode(['data' => $mascota]);
    }

    // GET /apimascota/por-cliente/{id_cliente}
    public function porCliente($id_cliente) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $mascotas = $this->mascotaModel->obtenerPorCliente((int)$id_cliente);
        echo json_encode(['data' => $mascotas]);
    }
}
