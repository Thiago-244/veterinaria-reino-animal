<?php
namespace App\Models;

use App\Core\Database;

class RazaModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodas() {
        $this->db->query("
            SELECT 
                r.id, r.nombre, r.id_especie,
                e.nombre as especie_nombre
            FROM razas r
            LEFT JOIN especies e ON r.id_especie = e.id
            ORDER BY e.nombre, r.nombre ASC
        ");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("
            SELECT 
                r.*,
                e.nombre as especie_nombre
            FROM razas r
            LEFT JOIN especies e ON r.id_especie = e.id
            WHERE r.id = :id LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorEspecie(int $id_especie) {
        $this->db->query("
            SELECT r.id, r.nombre, r.id_especie
            FROM razas r
            WHERE r.id_especie = :id_especie
            ORDER BY r.nombre ASC
        ");
        $this->db->bind(':id_especie', $id_especie);
        return $this->db->resultSet();
    }

    public function obtenerPorNombre(string $nombre, int $id_especie) {
        $this->db->query("SELECT * FROM razas WHERE nombre = :nombre AND id_especie = :id_especie LIMIT 1");
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':id_especie', $id_especie);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Inserta una nueva raza en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("INSERT INTO razas (id_especie, nombre) VALUES (:id_especie, :nombre)");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':id_especie', $datos['id_especie']);
        $this->db->bind(':nombre', $datos['nombre']);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("UPDATE razas SET id_especie = :id_especie, nombre = :nombre WHERE id = :id");
        $this->db->bind(':id_especie', $datos['id_especie']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM razas WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Obtiene todas las especies para los formularios
     */
    public function obtenerEspecies() {
        $this->db->query("SELECT id, nombre FROM especies ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Verifica si una raza existe
     */
    public function razaExiste(int $id) {
        $this->db->query("SELECT id FROM razas WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si una especie existe
     */
    public function especieExiste(int $id_especie) {
        $this->db->query("SELECT id FROM especies WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id_especie);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si el nombre de raza ya existe en la especie
     */
    public function nombreExisteEnEspecie(string $nombre, int $id_especie, ?int $excludeId = null) {
        $sql = "SELECT id FROM razas WHERE nombre = :nombre AND id_especie = :id_especie";
        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }
        $sql .= " LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':id_especie', $id_especie);
        if ($excludeId) {
            $this->db->bind(':excludeId', $excludeId);
        }
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Obtiene estadísticas de razas
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_razas,
                COUNT(DISTINCT id_especie) as especies_con_razas
            FROM razas
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene razas agrupadas por especie
     */
    public function obtenerAgrupadasPorEspecie() {
        $this->db->query("
            SELECT 
                e.id as especie_id,
                e.nombre as especie_nombre,
                COUNT(r.id) as total_razas,
                GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ', ') as razas
            FROM especies e
            LEFT JOIN razas r ON e.id = r.id_especie
            GROUP BY e.id, e.nombre
            ORDER BY e.nombre ASC
        ");
        return $this->db->resultSet();
    }
}
