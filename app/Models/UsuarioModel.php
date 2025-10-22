<?php
namespace App\Models;

use App\Core\Database;

class UsuarioModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodos() {
        $this->db->query("SELECT id, nombre, email, rol, estado FROM usuarios ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorEmail(string $email) {
        $this->db->query("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $this->db->bind(':email', $email);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorRol(string $rol) {
        $this->db->query("SELECT * FROM usuarios WHERE rol = :rol ORDER BY nombre ASC");
        $this->db->bind(':rol', $rol);
        return $this->db->resultSet();
    }

    public function obtenerActivos() {
        $this->db->query("SELECT id, nombre, email, rol FROM usuarios WHERE estado = 1 ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Inserta un nuevo usuario en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES (:nombre, :email, :password, :rol, :estado)
        ");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':email', $datos['email']);
        $this->db->bind(':password', password_hash($datos['password'], PASSWORD_DEFAULT));
        $this->db->bind(':rol', $datos['rol'] ?? 'Consultor');
        $this->db->bind(':estado', $datos['estado'] ?? 1);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol, estado = :estado";
        $params = [
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':rol' => $datos['rol'] ?? 'Consultor',
            ':estado' => $datos['estado'] ?? 1,
            ':id' => $id
        ];

        // Si se proporciona una nueva contraseña, actualizarla
        if (isset($datos['password']) && !empty($datos['password'])) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM usuarios WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Verifica si un usuario existe
     */
    public function usuarioExiste(int $id) {
        $this->db->query("SELECT id FROM usuarios WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si el email ya existe
     */
    public function emailExiste(string $email, ?int $excludeId = null) {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $sql .= " LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':email', $email);
        if ($excludeId) {
            $this->db->bind(':excludeId', $excludeId);
        }
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica las credenciales de login
     */
    public function verificarLogin(string $email, string $password) {
        $usuario = $this->obtenerPorEmail($email);
        if ($usuario && $usuario['estado'] == 1) {
            if (password_verify($password, $usuario['password'])) {
                return $usuario;
            }
        }
        return null;
    }

    /**
     * Cambia el estado del usuario (activar/desactivar)
     */
    public function cambiarEstado(int $id, int $estado) {
        $this->db->query("UPDATE usuarios SET estado = :estado WHERE id = :id");
        $this->db->bind(':estado', $estado);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Cambia la contraseña del usuario
     */
    public function cambiarPassword(int $id, string $nuevaPassword) {
        $this->db->query("UPDATE usuarios SET password = :password WHERE id = :id");
        $this->db->bind(':password', password_hash($nuevaPassword, PASSWORD_DEFAULT));
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Obtiene estadísticas de usuarios
     */
    public function obtenerEstadisticas() {
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
        return $this->db->resultSet();
    }

    /**
     * Obtiene usuarios agrupados por rol
     */
    public function obtenerPorRolAgrupado() {
        $this->db->query("
            SELECT 
                rol,
                COUNT(*) as total,
                COUNT(CASE WHEN estado = 1 THEN 1 END) as activos
            FROM usuarios 
            GROUP BY rol
            ORDER BY rol ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Busca usuarios por nombre o email
     */
    public function buscar(string $termino) {
        $this->db->query("
            SELECT id, nombre, email, rol, estado 
            FROM usuarios 
            WHERE nombre LIKE :termino OR email LIKE :termino
            ORDER BY nombre ASC
        ");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }
}
