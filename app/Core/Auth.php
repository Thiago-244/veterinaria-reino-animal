<?php
namespace App\Core;

use App\Models\LoginModel;

class Auth {
    private static $loginModel;

    /**
     * Inicializa el modelo de login
     */
    private static function init() {
        if (!self::$loginModel) {
            self::$loginModel = new LoginModel();
        }
    }

    /**
     * Verifica si el usuario está autenticado
     */
    public static function check() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']) && 
               isset($_SESSION['usuario_email']) && 
               self::usuarioActivo();
    }

    /**
     * Verifica si el usuario está activo en la base de datos
     */
    public static function usuarioActivo() {
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }

        self::init();
        return self::$loginModel->usuarioActivo($_SESSION['usuario_id']);
    }

    /**
     * Obtiene el ID del usuario autenticado
     */
    public static function id() {
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Obtiene el email del usuario autenticado
     */
    public static function email() {
        return $_SESSION['usuario_email'] ?? null;
    }

    /**
     * Obtiene el nombre del usuario autenticado
     */
    public static function nombre() {
        return $_SESSION['usuario_nombre'] ?? null;
    }

    /**
     * Obtiene el rol del usuario autenticado
     */
    public static function rol() {
        return $_SESSION['usuario_rol'] ?? null;
    }

    /**
     * Obtiene toda la información del usuario autenticado
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        self::init();
        return self::$loginModel->obtenerUsuarioPorId(self::id());
    }

    /**
     * Verifica si el usuario es administrador
     */
    public static function isAdmin() {
        if (!self::check()) {
            return false;
        }

        self::init();
        return self::$loginModel->esAdministrador(self::id());
    }

    /**
     * Verifica si el usuario puede editar
     */
    public static function canEdit() {
        if (!self::check()) {
            return false;
        }

        self::init();
        return self::$loginModel->puedeEditar(self::id());
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public static function hasPermission($funcionalidad) {
        if (!self::check()) {
            return false;
        }

        self::init();
        return self::$loginModel->tienePermiso(self::id(), $funcionalidad);
    }

    /**
     * Middleware para verificar autenticación
     */
    public static function middleware() {
        if (!self::check()) {
            self::redirectToLogin();
        }
    }

    /**
     * Middleware para verificar permisos
     */
    public static function middlewarePermission($funcionalidad) {
        if (!self::check()) {
            self::redirectToLogin();
        }

        if (!self::hasPermission($funcionalidad)) {
            self::redirectToDashboard();
        }
    }

    /**
     * Middleware para verificar rol de administrador
     */
    public static function middlewareAdmin() {
        if (!self::check()) {
            self::redirectToLogin();
        }

        if (!self::isAdmin()) {
            self::redirectToDashboard();
        }
    }

    /**
     * Middleware para verificar permisos de edición
     */
    public static function middlewareEdit() {
        if (!self::check()) {
            self::redirectToLogin();
        }

        if (!self::canEdit()) {
            self::redirectToDashboard();
        }
    }

    /**
     * Redirige al login
     */
    private static function redirectToLogin() {
        header('Location: ' . APP_URL . '/login');
        exit;
    }

    /**
     * Redirige al dashboard
     */
    private static function redirectToDashboard() {
        header('Location: ' . APP_URL . '/dashboard');
        exit;
    }

    /**
     * Cierra la sesión del usuario
     */
    public static function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_destroy();
        session_start();
    }

    /**
     * Obtiene el tiempo de login
     */
    public static function loginTime() {
        return $_SESSION['login_time'] ?? null;
    }

    /**
     * Verifica si la sesión ha expirado
     */
    public static function sessionExpired($timeout = 3600) { // 1 hora por defecto
        if (!self::check()) {
            return true;
        }

        $loginTime = self::loginTime();
        if (!$loginTime) {
            return true;
        }

        return (time() - $loginTime) > $timeout;
    }

    /**
     * Refresca la sesión si está cerca de expirar
     */
    public static function refreshSession() {
        if (self::check() && !self::sessionExpired()) {
            $_SESSION['login_time'] = time();
            self::init();
            self::$loginModel->actualizarUltimaSesion(self::id());
        }
    }

    /**
     * Obtiene información de la sesión
     */
    public static function sessionInfo() {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => self::id(),
            'nombre' => self::nombre(),
            'email' => self::email(),
            'rol' => self::rol(),
            'login_time' => self::loginTime(),
            'session_id' => session_id(),
            'is_admin' => self::isAdmin(),
            'can_edit' => self::canEdit()
        ];
    }

    /**
     * Verifica múltiples permisos (OR)
     */
    public static function hasAnyPermission($funcionalidades) {
        if (!self::check()) {
            return false;
        }

        foreach ($funcionalidades as $funcionalidad) {
            if (self::hasPermission($funcionalidad)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica múltiples permisos (AND)
     */
    public static function hasAllPermissions($funcionalidades) {
        if (!self::check()) {
            return false;
        }

        foreach ($funcionalidades as $funcionalidad) {
            if (!self::hasPermission($funcionalidad)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene todos los permisos del usuario
     */
    public static function getAllPermissions() {
        if (!self::check()) {
            return [];
        }

        $funcionalidades = [
            'dashboard', 'usuarios', 'clientes', 'mascotas', 
            'citas', 'ventas', 'productos', 'reportes', 'configuracion'
        ];

        $permisos = [];
        foreach ($funcionalidades as $funcionalidad) {
            $permisos[$funcionalidad] = self::hasPermission($funcionalidad);
        }

        return $permisos;
    }
}
