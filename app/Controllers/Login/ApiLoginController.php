<?php
namespace App\Controllers\Login;

use App\Core\BaseController;

class ApiLoginController extends BaseController {
    
    private $loginModel;

    public function __construct() {
        $this->loginModel = $this->model('LoginModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * POST /apilogin/autenticar
     * Autentica un usuario y devuelve información de sesión
     */
    public function autenticar() {
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

        $email = trim($payload['email'] ?? '');
        $password = $payload['password'] ?? '';

        // Validaciones
        if (empty($email) || empty($password)) {
            http_response_code(422);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            return;
        }

        if (!$this->loginModel->validarEmail($email)) {
            http_response_code(422);
            echo json_encode(['error' => 'Formato de email inválido']);
            return;
        }

        // Verificar credenciales
        $usuario = $this->loginModel->verificarCredenciales($email, $password);

        if ($usuario) {
            // Iniciar sesión
            session_start();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['login_time'] = time();

            // Actualizar última sesión
            $this->loginModel->actualizarUltimaSesion($usuario['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Autenticación exitosa',
                'data' => [
                    'usuario' => $usuario,
                    'session_id' => session_id(),
                    'login_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales incorrectas o usuario inactivo']);
        }
    }

    /**
     * POST /apilogin/cerrar-sesion
     * Cierra la sesión del usuario
     */
    public function cerrarSesion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        session_start();
        session_destroy();
        session_start();

        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * GET /apilogin/verificar-sesion
     * Verifica si hay una sesión activa
     */
    public function verificarSesion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        session_start();

        if (isset($_SESSION['usuario_id'])) {
            $usuario = $this->loginModel->obtenerUsuarioPorId($_SESSION['usuario_id']);
            
            if ($usuario && $this->loginModel->usuarioActivo($_SESSION['usuario_id'])) {
                echo json_encode([
                    'authenticated' => true,
                    'data' => [
                        'usuario' => $usuario,
                        'session_time' => isset($_SESSION['login_time']) ? 
                            date('Y-m-d H:i:s', $_SESSION['login_time']) : null
                    ]
                ]);
            } else {
                // Usuario inactivo, cerrar sesión
                session_destroy();
                echo json_encode(['authenticated' => false]);
            }
        } else {
            echo json_encode(['authenticated' => false]);
        }
    }

    /**
     * POST /apilogin/cambiar-password
     * Cambia la contraseña del usuario autenticado
     */
    public function cambiarPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        session_start();

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $passwordActual = $payload['password_actual'] ?? '';
        $nuevaPassword = $payload['nueva_password'] ?? '';

        // Validaciones
        if (empty($passwordActual) || empty($nuevaPassword)) {
            http_response_code(422);
            echo json_encode(['error' => 'Contraseña actual y nueva contraseña son requeridas']);
            return;
        }

        if (!$this->loginModel->validarPassword($nuevaPassword)) {
            http_response_code(422);
            echo json_encode(['error' => 'La nueva contraseña debe tener al menos 8 caracteres, una letra y un número']);
            return;
        }

        $usuarioId = $_SESSION['usuario_id'];

        if ($this->loginModel->cambiarPassword($usuarioId, $passwordActual, $nuevaPassword)) {
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña cambiada correctamente'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'La contraseña actual es incorrecta']);
        }
    }

    /**
     * GET /apilogin/perfil
     * Obtiene el perfil del usuario autenticado
     */
    public function perfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        session_start();

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        $usuario = $this->loginModel->obtenerUsuarioPorId($_SESSION['usuario_id']);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        echo json_encode(['data' => $usuario]);
    }

    /**
     * GET /apilogin/permisos
     * Obtiene los permisos del usuario autenticado
     */
    public function permisos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        session_start();

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $funcionalidades = ['dashboard', 'usuarios', 'clientes', 'mascotas', 'citas', 'ventas', 'productos', 'reportes', 'configuracion'];
        
        $permisos = [];
        foreach ($funcionalidades as $funcionalidad) {
            $permisos[$funcionalidad] = $this->loginModel->tienePermiso($usuarioId, $funcionalidad);
        }

        echo json_encode([
            'data' => [
                'permisos' => $permisos,
                'rol' => $_SESSION['usuario_rol'] ?? null,
                'es_administrador' => $this->loginModel->esAdministrador($usuarioId),
                'puede_editar' => $this->loginModel->puedeEditar($usuarioId)
            ]
        ]);
    }

    /**
     * POST /apilogin/validar-email
     * Valida si un email existe en el sistema
     */
    public function validarEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (!is_array($payload) || !isset($payload['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email requerido']);
            return;
        }

        $email = trim($payload['email']);

        if (!$this->loginModel->validarEmail($email)) {
            http_response_code(422);
            echo json_encode(['error' => 'Formato de email inválido']);
            return;
        }

        $existe = $this->loginModel->emailExiste($email);

        echo json_encode([
            'data' => [
                'email' => $email,
                'existe' => $existe,
                'mensaje' => $existe ? 'Email registrado' : 'Email no registrado'
            ]
        ]);
    }

    /**
     * GET /apilogin/estadisticas
     * Obtiene estadísticas del sistema de login
     */
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        session_start();

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        // Solo administradores pueden ver estadísticas
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'Administrador') {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos para acceder a estadísticas']);
            return;
        }

        $estadisticas = $this->loginModel->obtenerEstadisticasLogin();
        $usuariosRecientes = $this->loginModel->obtenerUsuariosRecientes(5);

        echo json_encode([
            'data' => [
                'estadisticas' => $estadisticas,
                'usuarios_recientes' => $usuariosRecientes
            ]
        ]);
    }
}
