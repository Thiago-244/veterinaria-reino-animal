<?php
namespace App\Models;

use App\Core\Database;

class LoginModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    /**
     * Verifica las credenciales de login
     */
    public function verificarCredenciales(string $email, string $password) {
        $this->db->query("
            SELECT id, nombre, email, password, rol, estado 
            FROM usuarios 
            WHERE email = :email AND estado = 1
            LIMIT 1
        ");
        $this->db->bind(':email', $email);
        $usuario = $this->db->single();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Remover password del resultado por seguridad
            unset($usuario['password']);
            return $usuario;
        }
        
        return null;
    }

    /**
     * Verifica si un usuario está activo
     */
    public function usuarioActivo(int $id) {
        $this->db->query("SELECT estado FROM usuarios WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result && $result['estado'] == 1;
    }

    /**
     * Obtiene información del usuario por ID (sin password)
     */
    public function obtenerUsuarioPorId(int $id) {
        $this->db->query("
            SELECT id, nombre, email, rol, estado 
            FROM usuarios 
            WHERE id = :id 
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Verifica si el email existe
     */
    public function emailExiste(string $email) {
        $this->db->query("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $this->db->bind(':email', $email);
        $result = $this->db->single();
        return !empty($result);
    }

    /**
     * Actualiza la última sesión del usuario
     */
    public function actualizarUltimaSesion(int $id) {
        // La tabla `usuarios` actual no tiene columna updated_at; mantener compatibilidad con una consulta no destructiva
        $this->db->query("UPDATE usuarios SET estado = estado WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Cambia la contraseña del usuario
     */
    public function cambiarPassword(int $id, string $passwordActual, string $nuevaPassword) {
        // Verificar contraseña actual
        $this->db->query("SELECT password FROM usuarios WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $usuario = $this->db->single();
        
        if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
            return false;
        }

        // Actualizar contraseña
        $this->db->query("UPDATE usuarios SET password = :password WHERE id = :id");
        $this->db->bind(':password', password_hash($nuevaPassword, PASSWORD_DEFAULT));
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Resetea la contraseña (solo para administradores)
     */
    public function resetearPassword(int $id, string $nuevaPassword) {
        $this->db->query("UPDATE usuarios SET password = :password WHERE id = :id");
        $this->db->bind(':password', password_hash($nuevaPassword, PASSWORD_DEFAULT));
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Obtiene estadísticas de login
     */
    public function obtenerEstadisticasLogin() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_usuarios,
                COUNT(CASE WHEN estado = 1 THEN 1 END) as usuarios_activos,
                COUNT(CASE WHEN estado = 0 THEN 1 END) as usuarios_inactivos,
                COUNT(CASE WHEN rol = 'Administrador' THEN 1 END) as administradores,
                COUNT(CASE WHEN rol = 'Editor' THEN 1 END) as editores,
                COUNT(CASE WHEN rol = 'Consultor' THEN 1 END) as consultores
            FROM usuarios
        ");
        return $this->db->single();
    }

    /**
     * Verifica si el usuario tiene permisos de administrador
     */
    public function esAdministrador(int $id) {
        $this->db->query("SELECT rol FROM usuarios WHERE id = :id AND estado = 1 LIMIT 1");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result && $result['rol'] === 'Administrador';
    }

    /**
     * Verifica si el usuario tiene permisos de editor o administrador
     */
    public function puedeEditar(int $id) {
        $this->db->query("SELECT rol FROM usuarios WHERE id = :id AND estado = 1 LIMIT 1");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result && in_array($result['rol'], ['Administrador', 'Editor']);
    }

    /**
     * Obtiene usuarios recientes (últimos 30 días)
     */
    public function obtenerUsuariosRecientes(int $limite = 10) {
        $this->db->query("
            SELECT id, nombre, email, rol 
            FROM usuarios 
            ORDER BY id DESC 
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }

    /**
     * Valida formato de email
     */
    public function validarEmail(string $email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida fortaleza de contraseña
     */
    public function validarPassword(string $password) {
        // Mínimo 8 caracteres, al menos una letra y un número
        return strlen($password) >= 8 && 
               preg_match('/[A-Za-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }

    /**
     * Genera token de sesión único
     */
    public function generarTokenSesion() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Verifica si el usuario puede acceder a una funcionalidad específica
     */
    public function tienePermiso(int $id, string $funcionalidad) {
        $this->db->query("SELECT rol FROM usuarios WHERE id = :id AND estado = 1 LIMIT 1");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        
        if (!$result) return false;
        
        $rol = $result['rol'];
        
        // Definir permisos por rol
        $permisos = [
            'Administrador' => ['dashboard', 'usuarios', 'clientes', 'mascotas', 'citas', 'ventas', 'productos', 'reportes', 'configuracion'],
            'Editor' => ['dashboard', 'clientes', 'mascotas', 'citas', 'ventas', 'productos', 'reportes'],
            'Consultor' => ['dashboard', 'clientes', 'mascotas', 'citas', 'reportes']
        ];
        
        return isset($permisos[$rol]) && in_array($funcionalidad, $permisos[$rol]);
    }
}
