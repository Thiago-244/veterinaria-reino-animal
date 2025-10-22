<?php
namespace App\Models;

use App\Core\Database;

class EmpresaModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodas() {
        $this->db->query("SELECT * FROM empresa ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("SELECT * FROM empresa WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorRuc(string $ruc) {
        $this->db->query("SELECT * FROM empresa WHERE ruc = :ruc LIMIT 1");
        $this->db->bind(':ruc', $ruc);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorEmail(string $email) {
        $this->db->query("SELECT * FROM empresa WHERE email = :email LIMIT 1");
        $this->db->bind(':email', $email);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Inserta una nueva empresa en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("
            INSERT INTO empresa (nombre, ruc, direccion, telefono, email, logo, iva) 
            VALUES (:nombre, :ruc, :direccion, :telefono, :email, :logo, :iva)
        ");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':ruc', $datos['ruc']);
        $this->db->bind(':direccion', $datos['direccion']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':email', $datos['email']);
        $this->db->bind(':logo', $datos['logo'] ?? null);
        $this->db->bind(':iva', $datos['iva'] ?? 18.00);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("
            UPDATE empresa 
            SET nombre = :nombre, ruc = :ruc, direccion = :direccion, 
                telefono = :telefono, email = :email, logo = :logo, iva = :iva
            WHERE id = :id
        ");
        
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':ruc', $datos['ruc']);
        $this->db->bind(':direccion', $datos['direccion']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':email', $datos['email']);
        $this->db->bind(':logo', $datos['logo'] ?? null);
        $this->db->bind(':iva', $datos['iva'] ?? 18.00);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM empresa WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Verifica si una empresa existe
     */
    public function empresaExiste(int $id) {
        $this->db->query("SELECT id FROM empresa WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si el RUC ya existe
     */
    public function rucExiste(string $ruc, ?int $excludeId = null) {
        $sql = "SELECT id FROM empresa WHERE ruc = :ruc";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $sql .= " LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':ruc', $ruc);
        if ($excludeId) {
            $this->db->bind(':excludeId', $excludeId);
        }
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si el email ya existe
     */
    public function emailExiste(string $email, ?int $excludeId = null) {
        $sql = "SELECT id FROM empresa WHERE email = :email";
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
     * Obtiene la empresa principal (primera empresa)
     */
    public function obtenerPrincipal() {
        $this->db->query("SELECT * FROM empresa ORDER BY id ASC LIMIT 1");
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Obtiene estadísticas de la empresa
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_empresas,
                AVG(iva) as promedio_iva
            FROM empresa
        ");
        return $this->db->resultSet();
    }

    /**
     * Actualiza solo el logo de la empresa
     */
    public function actualizarLogo(int $id, string $logo) {
        $this->db->query("UPDATE empresa SET logo = :logo WHERE id = :id");
        $this->db->bind(':logo', $logo);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Actualiza solo el IVA de la empresa
     */
    public function actualizarIva(int $id, float $iva) {
        $this->db->query("UPDATE empresa SET iva = :iva WHERE id = :id");
        $this->db->bind(':iva', $iva);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
