<?php
namespace App\Controllers\Login;

use App\Core\BaseController;

class LoginController extends BaseController {
    
    private $loginModel;
    private $usuarioModel;

    public function __construct() {
        $this->loginModel = $this->model('LoginModel');
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    /**
     * Muestra el formulario de login
     */
    public function index() {
        // Si ya está logueado, redirigir al dashboard
        if ($this->estaLogueado()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        $data = [
            'titulo' => 'Iniciar Sesión - Reino Animal',
            'error' => $_SESSION['login_error'] ?? null,
            'success' => $_SESSION['login_success'] ?? null
        ];

        // Limpiar mensajes de sesión
        unset($_SESSION['login_error']);
        unset($_SESSION['login_success']);

        $this->view('login/index', $data);
    }

    /**
     * Procesa el login
     */
    public function procesar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Email y contraseña son requeridos';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if (!$this->loginModel->validarEmail($email)) {
            $_SESSION['login_error'] = 'El formato del email no es válido';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Verificar credenciales
        $usuario = $this->loginModel->verificarCredenciales($email, $password);

        if ($usuario) {
            // Iniciar sesión
            $this->iniciarSesion($usuario);
            
            // Actualizar última sesión
            $this->loginModel->actualizarUltimaSesion($usuario['id']);
            
            // Redirigir según rol
            $this->redirigirSegunRol($usuario['rol']);
        } else {
            $_SESSION['login_error'] = 'Credenciales incorrectas o usuario inactivo';
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Cierra la sesión
     */
    public function logout() {
        $this->cerrarSesion();
        $_SESSION['login_success'] = 'Sesión cerrada correctamente';
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /**
     * Muestra el formulario de cambio de contraseña
     */
    public function cambiarPassword() {
        if (!$this->estaLogueado()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $data = [
            'titulo' => 'Cambiar Contraseña',
            'error' => $_SESSION['password_error'] ?? null,
            'success' => $_SESSION['password_success'] ?? null
        ];

        unset($_SESSION['password_error']);
        unset($_SESSION['password_success']);

        $this->view('login/cambiar-password', $data);
    }

    /**
     * Procesa el cambio de contraseña
     */
    public function procesarCambioPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        if (!$this->estaLogueado()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $passwordActual = $_POST['password_actual'] ?? '';
        $nuevaPassword = $_POST['nueva_password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';

        // Validaciones
        if (empty($passwordActual) || empty($nuevaPassword) || empty($confirmarPassword)) {
            $_SESSION['password_error'] = 'Todos los campos son requeridos';
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        if ($nuevaPassword !== $confirmarPassword) {
            $_SESSION['password_error'] = 'Las contraseñas no coinciden';
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        if (!$this->loginModel->validarPassword($nuevaPassword)) {
            $_SESSION['password_error'] = 'La nueva contraseña debe tener al menos 8 caracteres, una letra y un número';
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];

        if ($this->loginModel->cambiarPassword($usuarioId, $passwordActual, $nuevaPassword)) {
            $_SESSION['password_success'] = 'Contraseña cambiada correctamente';
        } else {
            $_SESSION['password_error'] = 'La contraseña actual es incorrecta';
        }

        header('Location: ' . APP_URL . '/login/cambiar-password');
        exit;
    }

    /**
     * Muestra el perfil del usuario
     */
    public function perfil() {
        if (!$this->estaLogueado()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $usuario = $this->loginModel->obtenerUsuarioPorId($usuarioId);

        if (!$usuario) {
            $this->cerrarSesion();
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $data = [
            'titulo' => 'Mi Perfil',
            'usuario' => $usuario
        ];

        $this->view('login/perfil', $data);
    }

    /**
     * Verifica si el usuario está logueado
     */
    private function estaLogueado() {
        return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_email']);
    }

    /**
     * Inicia la sesión del usuario
     */
    private function iniciarSesion($usuario) {
        session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['login_time'] = time();
    }

    /**
     * Cierra la sesión del usuario
     */
    private function cerrarSesion() {
        session_start();
        session_destroy();
        session_start();
    }

    /**
     * Redirige según el rol del usuario
     */
    private function redirigirSegunRol($rol) {
        switch ($rol) {
            case 'Administrador':
                header('Location: ' . APP_URL . '/dashboard');
                break;
            case 'Editor':
                header('Location: ' . APP_URL . '/dashboard');
                break;
            case 'Consultor':
                header('Location: ' . APP_URL . '/dashboard');
                break;
            default:
                header('Location: ' . APP_URL . '/dashboard');
                break;
        }
        exit;
    }

    /**
     * Middleware para verificar autenticación
     */
    public static function verificarAutenticacion() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Middleware para verificar permisos
     */
    public static function verificarPermisos($funcionalidad) {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $loginModel = new \App\Models\LoginModel();
        if (!$loginModel->tienePermiso($_SESSION['usuario_id'], $funcionalidad)) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }
}
