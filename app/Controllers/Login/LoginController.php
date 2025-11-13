<?php
namespace App\Controllers\Login;

use App\Core\BaseController;

class LoginController extends BaseController {
    // Propiedades privadas para almacenar instancias de los modelos.
    private $loginModel;
    private $usuarioModel;

    /**
     * Constructor: Carga automáticamente los modelos necesarios.
     */
    public function __construct() {
        $this->loginModel = $this->model('LoginModel');
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    /**
     * Muestra el formulario de login
     */
    public function index() {
        // Si ya está logueado, redirigir al dashboard
        // Comprueba si el usuario ya tiene una sesión activa.
        if ($this->estaLogueado()) {
            // Si está logueado, no debe ver el login, sino el panel principal.
            header('Location: ' . APP_URL . '/dashboard');
            exit;// Detiene la ejecución del script tras la redirección.
        }

        // Prepara los datos para la vista.
        $data = [
            'titulo' => 'Iniciar Sesión - Reino Animal',
            // Recoge errores/éxitos de la sesión (ej. tras un logout o error).
            'error' => $_SESSION['login_error'] ?? null,
            'success' => $_SESSION['login_success'] ?? null
        ];

        // Limpiar mensajes de sesión
        // Limpia las variables de sesión para que no se muestren de nuevo (Flash messages).
        unset($_SESSION['login_error']);
        unset($_SESSION['login_success']);

        // Renderiza la vista del formulario de login.
        $this->view('login/index', $data);
    }

    /**
     * Procesa el login
     */
    public function procesar() {
        // Restringe el acceso a este método solo por peticiones POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Recoge y sanea las entradas del formulario.
        $email = trim($_POST['email'] ?? ''); // trim() elimina espacios en blanco.
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        // Primera capa de validación: campos no vacíos.
        if (empty($email) || empty($password)) {
            // Almacena el error en la sesión para mostrarlo en el formulario.
            $_SESSION['login_error'] = 'Email y contraseña son requeridos';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Segunda capa: validación de formato de email (delegada al modelo).
        if (!$this->loginModel->validarEmail($email)) {
            $_SESSION['login_error'] = 'El formato del email no es válido';
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Verificar credenciales
        // Lógica principal: se delega al modelo la verificación de credenciales.
        $usuario = $this->loginModel->verificarCredenciales($email, $password);

        if ($usuario) {
            // Iniciar sesión
            // Si las credenciales son correctas, crea la sesión.
            $this->iniciarSesion($usuario);
            
            // Actualizar última sesión
            // Registra la fecha/hora del último acceso.
            $this->loginModel->actualizarUltimaSesion($usuario['id']);
            
            // Redirigir según rol
            // Redirige al usuario a su panel correspondiente.
            $this->redirigirSegunRol($usuario['rol']);
        } else {
            // Si falla la autenticación, informa al usuario.
            $_SESSION['login_error'] = 'Credenciales incorrectas o usuario inactivo';
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Cierra la sesión
     */
    public function logout() {
        // Utiliza el método privado para destruir la sesión.
        $this->cerrarSesion();
        // Prepara un mensaje de éxito para la vista de login.
        $_SESSION['login_success'] = 'Sesión cerrada correctamente';
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /**
     * Muestra el formulario de cambio de contraseña
     */
    public function cambiarPassword() {
        // Protección de ruta: solo usuarios logueados pueden cambiar su password.
        if (!$this->estaLogueado()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Preparación de datos (título y mensajes flash).
        $data = [
            'titulo' => 'Cambiar Contraseña',
            'error' => $_SESSION['password_error'] ?? null,
            'success' => $_SESSION['password_success'] ?? null
        ];

        // Limpieza de mensajes flash.
        unset($_SESSION['password_error']);
        unset($_SESSION['password_success']);

        $this->view('login/cambiar-password', $data);
    }

    /**
     * Procesa el cambio de contraseña
     */
    public function procesarCambioPassword() {
        // Verificación de método HTTP.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        // Verificación de autenticación.
        if (!$this->estaLogueado()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Recolección de datos del formulario.
        $passwordActual = $_POST['password_actual'] ?? '';
        $nuevaPassword = $_POST['nueva_password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';

        // Validaciones
        // Validación de campos vacíos.
        if (empty($passwordActual) || empty($nuevaPassword) || empty($confirmarPassword)) {
            $_SESSION['password_error'] = 'Todos los campos son requeridos';
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        // Validación: Comprobar que las contraseñas nuevas coincidan.
        if ($nuevaPassword !== $confirmarPassword) {
            $_SESSION['password_error'] = 'Las contraseñas no coinciden';
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        // Validación: Comprobar la fortaleza de la nueva contraseña.
        if (!$this->loginModel->validarPassword($nuevaPassword)) {
            $_SESSION['password_error'] = 'La nueva contraseña debe tener al menos 8 caracteres, una letra y un número';
            header('Location: ' . APP_URL . '/login/cambiar-password');
            exit;
        }

        // Obtiene el ID del usuario de la sesión actual.
        $usuarioId = $_SESSION['usuario_id'];

        // Intenta el cambio de contraseña en la base de datos (lógica delegada al modelo).
        if ($this->loginModel->cambiarPassword($usuarioId, $passwordActual, $nuevaPassword)) {
            $_SESSION['password_success'] = 'Contraseña cambiada correctamente';
        } else {
            $_SESSION['password_error'] = 'La contraseña actual es incorrecta';
        }

        // Redirige de vuelta al formulario de cambio.
        header('Location: ' . APP_URL . '/login/cambiar-password');
        exit;
    }

    /**
     * Muestra el perfil del usuario
     */
    public function perfil() {
        // Protección de ruta.
        if (!$this->estaLogueado()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        // Obtiene la información completa del usuario desde el modelo.
        $usuario = $this->loginModel->obtenerUsuarioPorId($usuarioId);

        // Medida de seguridad: si el usuario de la sesión no existe en BD, forzar logout.
        if (!$usuario) {
            $this->cerrarSesion();
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Pasa los datos del usuario a la vista.
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
        // Define "estar logueado" como tener estas dos variables de sesión clave.
        return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_email']);
    }

    /**
     * Inicia la sesión del usuario
     */
    private function iniciarSesion($usuario) {
        session_start(); // Asegura que la sesión esté iniciada.
        // Almacena los datos esenciales del usuario en la sesión.
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
        // Este switch actualmente redirige a todos al dashboard.
        // Podría expandirse para redirigir a diferentes secciones si fuera necesario.
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
        exit; // Importante después de un header() de redirección.
    }

    /**
     * Middleware para verificar autenticación
     */
    // Los métodos estáticos (middleware) son útiles para llamar desde el router.
    public static function verificarAutenticacion() {
        // Primero, verifica que esté autenticado.
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Middleware para verificar permisos
     */
    public static function verificarPermisos($funcionalidad) {
        // Primero, verifica que esté autenticado.
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        // Instancia el modelo aquí porque es un método estático.
        $loginModel = new \App\Models\LoginModel();
        // Delega al modelo la lógica de comprobación de permisos.
        if (!$loginModel->tienePermiso($_SESSION['usuario_id'], $funcionalidad)) {
            // Redirige al dashboard (o podría ser a una página de "acceso denegado").
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
    }
}
