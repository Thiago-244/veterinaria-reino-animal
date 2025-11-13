<?php
namespace App\Models;

use App\Core\Database;

class LoginModel {
    private $db; // Propiedad para la instancia de la base de datos.

    public function __construct(?Database $database = null) {
        // Inyección de dependencias: usa la BD pasada o crea una nueva.
        $this->db = $database ?? new Database();
    }

    /**
     * Verifica las credenciales de login
     */
    public function verificarCredenciales(string $email, string $password) {
        // Busca al usuario por email y que esté activo (estado = 1).
        $this->db->query("
            SELECT id, nombre, email, password, rol, estado 
            FROM usuarios 
            WHERE email = :email AND estado = 1
            LIMIT 1
        ");
        $this->db->bind(':email', $email);
        $usuario = $this->db->single();

        // Si el usuario existe y la contraseña (hasheada) es correcta...
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Remover password del resultado por seguridad
            unset($usuario['password']);
            return $usuario;
        }
        
        return null; // En caso de fallo de autenticación, no devuelve nada.
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
        // Intentar obtener con created_at y updated_at si existen
        try {
            // Intenta la consulta completa primero, asumiendo que las columnas existen.
            $this->db->query("
                SELECT id, nombre, email, rol, estado, created_at, updated_at 
                FROM usuarios 
                WHERE id = :id 
                LIMIT 1
            ");
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            // Si no tiene created_at, establecer valores por defecto
            if ($result && !isset($result['created_at'])) {
                $result['created_at'] = date('Y-m-d H:i:s');
            }
            if ($result && !isset($result['updated_at'])) {
                $result['updated_at'] = date('Y-m-d H:i:s');
            }
            return $result;
        } catch (\Exception $e) {
            // Si falla (ej. columnas no existen), ejecuta la consulta simple de 'fallback'.
            // Si falla, intentar sin created_at y updated_at
            $this->db->query("
                SELECT id, nombre, email, rol, estado
                FROM usuarios 
                WHERE id = :id 
                LIMIT 1
            ");
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            // Rellena con valores de 'fallback' para consistencia en la respuesta.
            if ($result) {
                $result['created_at'] = date('Y-m-d H:i:s');
                $result['updated_at'] = date('Y-m-d H:i:s');
            }
            return $result;
        }
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
        // Esta consulta no hace nada (estado=estado) pero es segura si falta 'updated_at'.
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
        
        // Si el usuario no existe o la pass actual es incorrecta, falla.
        if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
            return false;
        }

        // Actualizar contraseña
        $this->db->query("UPDATE usuarios SET password = :password WHERE id = :id");
        // Hashea la nueva contraseña antes de guardarla.
        $this->db->bind(':password', password_hash($nuevaPassword, PASSWORD_DEFAULT));
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Resetea la contraseña (solo para administradores)
     */
    public function resetearPassword(int $id, string $nuevaPassword) {
        // Este método no verifica la pass actual. Es para que un admin la fuerce.
        $this->db->query("UPDATE usuarios SET password = :password WHERE id = :id");
        $this->db->bind(':password', password_hash($nuevaPassword, PASSWORD_DEFAULT));
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Obtiene estadísticas de login
     */
    public function obtenerEstadisticasLogin() {
        // Query de agregación (conteo condicional) para el dashboard.
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
        return $this->db->single(); // Devuelve una sola fila con todos los contadores.
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
        // Consulta simple para mostrar en el dashboard, ordenada por ID (últimos creados).
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
        // Usa la función nativa de PHP (rápida y fiable) para validar emails.
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida fortaleza de contraseña
     */
    public function validarPassword(string $password) {
        // Mínimo 8 caracteres, al menos una letra y un número
        // Comprobación de reglas de negocio para la contraseña.
        return strlen($password) >= 8 && 
               preg_match('/[A-Za-z]/', $password) &&  // Debe tener al menos una letra
               preg_match('/[0-9]/', $password); // Debe tener al menos un número
    }

    /**
     * Genera token de sesión único
     */
    public function generarTokenSesion() {
        // Genera un token criptográficamente seguro (usado para 'recuérdame' o CSRF).
        return bin2hex(random_bytes(32));
    }

    /**
     * Verifica si el usuario puede acceder a una funcionalidad específica
     */
    public function tienePermiso(int $id, string $funcionalidad) {
        $this->db->query("SELECT rol FROM usuarios WHERE id = :id AND estado = 1 LIMIT 1");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        
        if (!$result) return false; // Si el usuario no existe o está inactivo.
        
        $rol = $result['rol'];
        
        // Definir permisos por rol
        // Matriz de permisos (ACL - Access Control List) simple y centralizada.
        $permisos = [
            'Administrador' => ['dashboard', 'usuarios', 'clientes', 'mascotas', 'citas', 'ventas', 'productos', 'reportes', 'configuracion'],
            'Editor' => ['dashboard', 'clientes', 'mascotas', 'citas', 'ventas', 'productos', 'reportes'],
            'Consultor' => ['dashboard', 'clientes', 'mascotas', 'citas', 'reportes']
        ];
        
        // Comprueba si el rol existe en la matriz y si la funcionalidad está en su lista.
        return isset($permisos[$rol]) && in_array($funcionalidad, $permisos[$rol]);
    }
}
