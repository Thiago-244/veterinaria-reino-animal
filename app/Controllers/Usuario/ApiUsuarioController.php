<?php
namespace App\Controllers\Usuario;

use App\Core\BaseController;
use App\Models\UsuarioModel;

class ApiUsuarioController extends BaseController {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apiusuario/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $usuarios = $this->usuarioModel->obtenerTodos();
        echo json_encode(['data' => $usuarios]);
    }

    // POST /apiusuario/crear
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

        $required = ['nombre', 'email', 'password', 'rol'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $nombre = trim((string)$payload['nombre']);
        $email = trim((string)$payload['email']);
        $password = (string)$payload['password'];
        $rol = trim((string)$payload['rol']);
        $estado = isset($payload['estado']) ? (int)$payload['estado'] : 1;

        // Validaciones de formato y longitud
        if (strlen($nombre) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 100 caracteres']);
            return;
        }

        if (strlen($email) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no debe superar 100 caracteres']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no tiene un formato válido']);
            return;
        }

        if (strlen($password) < 6) {
            http_response_code(422);
            echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }

        if (!in_array($rol, ['Administrador', 'Editor', 'Consultor'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El rol debe ser Administrador, Editor o Consultor']);
            return;
        }

        if (!in_array($estado, [0, 1])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado debe ser 0 (inactivo) o 1 (activo)']);
            return;
        }

        // Validaciones de unicidad
        if ($this->usuarioModel->emailExiste($email)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un usuario con ese email']);
            return;
        }

        $ok = $this->usuarioModel->crear([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'rol' => $rol,
            'estado' => $estado
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Usuario creado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el usuario']);
        }
    }

    // PUT /apiusuario/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Usuario no encontrado']); 
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
        $email = isset($payload['email']) ? trim((string)$payload['email']) : $existente['email'];
        $rol = isset($payload['rol']) ? trim((string)$payload['rol']) : $existente['rol'];
        $estado = isset($payload['estado']) ? (int)$payload['estado'] : $existente['estado'];

        // Validaciones
        if (strlen($nombre) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 100 caracteres']);
            return;
        }

        if (strlen($email) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no debe superar 100 caracteres']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no tiene un formato válido']);
            return;
        }

        if (!in_array($rol, ['Administrador', 'Editor', 'Consultor'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El rol debe ser Administrador, Editor o Consultor']);
            return;
        }

        if (!in_array($estado, [0, 1])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado debe ser 0 (inactivo) o 1 (activo)']);
            return;
        }

        // Verificar si ya existe otro con el mismo email
        if ($this->usuarioModel->emailExiste($email, (int)$id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otro usuario con ese email']);
            return;
        }

        $datos = [
            'nombre' => $nombre,
            'email' => $email,
            'rol' => $rol,
            'estado' => $estado
        ];

        // Si se proporciona una nueva contraseña
        if (isset($payload['password']) && !empty($payload['password'])) {
            $password = (string)$payload['password'];
            if (strlen($password) < 6) {
                http_response_code(422);
                echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
                return;
            }
            $datos['password'] = $password;
        }

        $ok = $this->usuarioModel->actualizar((int)$id, $datos);
        
        if ($ok) { 
            echo json_encode(['message' => 'Usuario actualizado']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apiusuario/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Usuario no encontrado']); 
            return; 
        }
        
        $ok = $this->usuarioModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Usuario eliminado']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apiusuario/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $usuario = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }
        
        // No devolver la contraseña
        unset($usuario['password']);
        echo json_encode(['data' => $usuario]);
    }

    // GET /apiusuario/por-rol/{rol}
    public function porRol($rol) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        if (!in_array($rol, ['Administrador', 'Editor', 'Consultor'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Rol no válido']);
            return;
        }
        
        $usuarios = $this->usuarioModel->obtenerPorRol($rol);
        echo json_encode(['data' => $usuarios]);
    }

    // GET /apiusuario/activos
    public function activos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $usuarios = $this->usuarioModel->obtenerActivos();
        echo json_encode(['data' => $usuarios]);
    }

    // PUT /apiusuario/cambiar-estado/{id}
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Usuario no encontrado']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['estado'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: estado']);
            return;
        }

        $estado = (int)$payload['estado'];
        if (!in_array($estado, [0, 1])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado debe ser 0 (inactivo) o 1 (activo)']);
            return;
        }
        
        $ok = $this->usuarioModel->cambiarEstado((int)$id, $estado);
        if ($ok) {
            echo json_encode(['message' => 'Estado del usuario actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo cambiar el estado']);
        }
    }

    // PUT /apiusuario/cambiar-password/{id}
    public function cambiarPassword($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Usuario no encontrado']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['password'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: password']);
            return;
        }

        $password = (string)$payload['password'];
        if (strlen($password) < 6) {
            http_response_code(422);
            echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }
        
        $ok = $this->usuarioModel->cambiarPassword((int)$id, $password);
        if ($ok) {
            echo json_encode(['message' => 'Contraseña actualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo cambiar la contraseña']);
        }
    }

    // POST /apiusuario/login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['email']) || !isset($payload['password'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            return;
        }

        $email = trim((string)$payload['email']);
        $password = (string)$payload['password'];

        $usuario = $this->usuarioModel->verificarLogin($email, $password);
        if ($usuario) {
            // No devolver la contraseña
            unset($usuario['password']);
            echo json_encode(['message' => 'Login exitoso', 'data' => $usuario]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
        }
    }

    // GET /apiusuario/estadisticas
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->usuarioModel->obtenerEstadisticas();
        echo json_encode(['data' => $estadisticas]);
    }

    // GET /apiusuario/buscar/{termino}
    public function buscar($termino) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $usuarios = $this->usuarioModel->buscar($termino);
        echo json_encode(['data' => $usuarios]);
    }
}
