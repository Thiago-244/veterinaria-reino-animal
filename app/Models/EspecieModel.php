<?php
namespace App\Models;

use App\Core\Database;

class EspecieModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodas() {
        $this->db->query("SELECT id, nombre FROM especies ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("SELECT * FROM especies WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorNombre(string $nombre) {
        $this->db->query("SELECT * FROM especies WHERE nombre = :nombre LIMIT 1");
        $this->db->bind(':nombre', $nombre);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Inserta una nueva especie en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("INSERT INTO especies (nombre) VALUES (:nombre)");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':nombre', $datos['nombre']);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("UPDATE especies SET nombre = :nombre WHERE id = :id");
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM especies WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Verifica si una especie existe
     */
    public function especieExiste(int $id) {
        $this->db->query("SELECT id FROM especies WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si el nombre de especie ya existe
     */
    public function nombreExiste(string $nombre, ?int $excludeId = null) {
        $sql = "SELECT id FROM especies WHERE nombre = :nombre";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $sql .= " LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':nombre', $nombre);
        if ($excludeId) {
            $this->db->bind(':excludeId', $excludeId);
        }
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Obtiene estadísticas de especies
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_especies,
                (SELECT COUNT(*) FROM razas r WHERE r.id_especie = especies.id) as total_razas
            FROM especies
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene especies con sus razas
     */
    public function obtenerConRazas() {
        $this->db->query("
            SELECT 
                e.id, e.nombre as especie_nombre,
                COUNT(r.id) as total_razas
            FROM especies e
            LEFT JOIN razas r ON e.id = r.id_especie
            GROUP BY e.id, e.nombre
            ORDER BY e.nombre ASC
        ");
        return $this->db->resultSet();
    }
}
