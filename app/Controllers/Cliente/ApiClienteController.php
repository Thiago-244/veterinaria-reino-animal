<?php
namespace App\Controllers\Cliente;

use App\Core\BaseController;
use App\Models\ClienteModel;

class ApiClienteController extends BaseController {

    private $clienteModel;

    public function __construct() {
        $this->clienteModel = $this->model('ClienteModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // PUT /apicliente/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->clienteModel->obtenerPorId((int)$id);
        if (!$existente) { http_response_code(404); echo json_encode(['error' => 'Cliente no encontrado']); return; }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) { http_response_code(400); echo json_encode(['error' => 'JSON inválido']); return; }

        $dni = isset($payload['dni']) ? trim((string)$payload['dni']) : $existente['dni'];
        $nombre = isset($payload['nombre']) ? trim((string)$payload['nombre']) : $existente['nombre'];
        $apellido = isset($payload['apellido']) ? trim((string)$payload['apellido']) : $existente['apellido'];
        $telefono = isset($payload['telefono']) ? trim((string)$payload['telefono']) : $existente['telefono'];
        $direccion = isset($payload['direccion']) ? trim((string)$payload['direccion']) : $existente['direccion'];
        $email = isset($payload['email']) ? trim((string)$payload['email']) : $existente['email'];

        if (!preg_match('/^\d{8,15}$/', $dni)) { http_response_code(422); echo json_encode(['error' => 'El DNI debe tener entre 8 y 15 dígitos numéricos']); return; }
        if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(422); echo json_encode(['error' => 'El email no tiene un formato válido']); return; }
        if (strlen($nombre) > 50 || strlen($apellido) > 50) { http_response_code(422); echo json_encode(['error' => 'Nombre y Apellido no deben superar 50 caracteres']); return; }
        if (strlen($telefono) > 15) { http_response_code(422); echo json_encode(['error' => 'Teléfono no debe superar 15 caracteres']); return; }

        $otroConMismoDni = $this->clienteModel->obtenerPorDni($dni);
        if ($otroConMismoDni && (int)$otroConMismoDni['id'] !== (int)$id) { http_response_code(409); echo json_encode(['error' => 'El DNI ya existe']); return; }
        $otroConMismoEmail = $email !== null && $email !== '' ? $this->clienteModel->obtenerPorEmail($email) : null;
        if ($otroConMismoEmail && (int)$otroConMismoEmail['id'] !== (int)$id) { http_response_code(409); echo json_encode(['error' => 'El email ya existe']); return; }

        $ok = $this->clienteModel->actualizar((int)$id, [
            'dni' => $dni,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'email' => $email,
        ]);
        if ($ok) { echo json_encode(['message' => 'Cliente actualizado']); } else { http_response_code(500); echo json_encode(['error' => 'No se pudo actualizar']); }
    }

    // DELETE /apicliente/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') { // permitir GET para pruebas rápidas
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $existente = $this->clienteModel->obtenerPorId((int)$id);
        if (!$existente) { http_response_code(404); echo json_encode(['error' => 'Cliente no encontrado']); return; }
        $ok = $this->clienteModel->eliminar((int)$id);
        if ($ok) { echo json_encode(['message' => 'Cliente eliminado']); } else { http_response_code(500); echo json_encode(['error' => 'No se pudo eliminar']); }
    }
    // GET /apicliente/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $clientes = $this->clienteModel->obtenerTodos();
        echo json_encode(['data' => $clientes]);
    }

    // POST /apicliente/crear
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

        $required = ['dni','nombre','apellido','telefono'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $dni = trim((string)$payload['dni']);
        $nombre = trim((string)$payload['nombre']);
        $apellido = trim((string)$payload['apellido']);
        $telefono = trim((string)$payload['telefono']);
        $direccion = isset($payload['direccion']) ? trim((string)$payload['direccion']) : null;
        $email = isset($payload['email']) ? trim((string)$payload['email']) : null;

        // Validaciones de formato y longitud
        if (!preg_match('/^\d{8,15}$/', $dni)) {
            http_response_code(422);
            echo json_encode(['error' => 'El DNI debe tener entre 8 y 15 dígitos numéricos']);
            return;
        }
        if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no tiene un formato válido']);
            return;
        }
        if (strlen($nombre) > 50 || strlen($apellido) > 50) {
            http_response_code(422);
            echo json_encode(['error' => 'Nombre y Apellido no deben superar 50 caracteres']);
            return;
        }
        if (strlen($telefono) > 15) {
            http_response_code(422);
            echo json_encode(['error' => 'Teléfono no debe superar 15 caracteres']);
            return;
        }

        // Validaciones de unicidad
        if ($this->clienteModel->obtenerPorDni($dni)) {
            http_response_code(409);
            echo json_encode(['error' => 'El DNI ya existe']);
            return;
        }
        if ($email !== null && $email !== '' && $this->clienteModel->obtenerPorEmail($email)) {
            http_response_code(409);
            echo json_encode(['error' => 'El email ya existe']);
            return;
        }

        $ok = $this->clienteModel->crear([
            'dni' => $dni,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'email' => $email,
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Cliente creado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el cliente']);
        }
    }
}


