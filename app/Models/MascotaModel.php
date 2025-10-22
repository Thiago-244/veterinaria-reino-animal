<?php
namespace App\Models;

use App\Core\Database;

class MascotaModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodas() {
        $this->db->query("
            SELECT 
                m.id, m.codigo, m.nombre, m.fecha_nacimiento, m.sexo, m.color, m.peso, m.foto,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                r.nombre as raza_nombre, e.nombre as especie_nombre
            FROM mascotas m
            LEFT JOIN clientes c ON m.id_cliente = c.id
            LEFT JOIN razas r ON m.id_raza = r.id
            LEFT JOIN especies e ON r.id_especie = e.id
            ORDER BY m.nombre ASC
        ");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("
            SELECT 
                m.*,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                r.nombre as raza_nombre, e.nombre as especie_nombre
            FROM mascotas m
            LEFT JOIN clientes c ON m.id_cliente = c.id
            LEFT JOIN razas r ON m.id_raza = r.id
            LEFT JOIN especies e ON r.id_especie = e.id
            WHERE m.id = :id LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorCliente(int $id_cliente) {
        $this->db->query("
            SELECT 
                m.id, m.codigo, m.nombre, m.fecha_nacimiento, m.sexo, m.color, m.peso, m.foto,
                r.nombre as raza_nombre, e.nombre as especie_nombre
            FROM mascotas m
            LEFT JOIN razas r ON m.id_raza = r.id
            LEFT JOIN especies e ON r.id_especie = e.id
            WHERE m.id_cliente = :id_cliente
            ORDER BY m.nombre ASC
        ");
        $this->db->bind(':id_cliente', $id_cliente);
        return $this->db->resultSet();
    }

    public function obtenerPorCodigo(string $codigo) {
        $this->db->query("SELECT * FROM mascotas WHERE codigo = :codigo LIMIT 1");
        $this->db->bind(':codigo', $codigo);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Inserta una nueva mascota en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("
            INSERT INTO mascotas (codigo, nombre, id_cliente, id_raza, fecha_nacimiento, sexo, color, peso, foto) 
            VALUES (:codigo, :nombre, :id_cliente, :id_raza, :fecha_nacimiento, :sexo, :color, :peso, :foto)
        ");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':codigo', $datos['codigo']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':id_cliente', $datos['id_cliente']);
        $this->db->bind(':id_raza', $datos['id_raza']);
        $this->db->bind(':fecha_nacimiento', $datos['fecha_nacimiento']);
        $this->db->bind(':sexo', $datos['sexo']);
        $this->db->bind(':color', $datos['color'] ?? '');
        $this->db->bind(':peso', $datos['peso'] ?? null);
        $this->db->bind(':foto', $datos['foto'] ?? 'default_pet.png');

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("
            UPDATE mascotas 
            SET nombre = :nombre, id_cliente = :id_cliente, id_raza = :id_raza, 
                fecha_nacimiento = :fecha_nacimiento, sexo = :sexo, color = :color, peso = :peso
            WHERE id = :id
        ");
        
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':id_cliente', $datos['id_cliente']);
        $this->db->bind(':id_raza', $datos['id_raza']);
        $this->db->bind(':fecha_nacimiento', $datos['fecha_nacimiento']);
        $this->db->bind(':sexo', $datos['sexo']);
        $this->db->bind(':color', $datos['color'] ?? '');
        $this->db->bind(':peso', $datos['peso'] ?? null);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM mascotas WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Genera un código único para la mascota
     */
    public function generarCodigo() {
        $this->db->query("SELECT COUNT(*) as total FROM mascotas");
        $result = $this->db->resultSet();
        $numero = ($result[0]['total'] ?? 0) + 1;
        return 'CM-' . str_pad($numero, 5, '0', STR_PAD_LEFT) . '-1';
    }

    /**
     * Obtiene todas las especies
     */
    public function obtenerEspecies() {
        $this->db->query("SELECT * FROM especies ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Obtiene todas las razas
     */
    public function obtenerRazas() {
        $this->db->query("
            SELECT r.*, e.nombre as especie_nombre 
            FROM razas r 
            LEFT JOIN especies e ON r.id_especie = e.id 
            ORDER BY e.nombre, r.nombre ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene razas por especie
     */
    public function obtenerRazasPorEspecie(int $id_especie) {
        $this->db->query("SELECT * FROM razas WHERE id_especie = :id_especie ORDER BY nombre ASC");
        $this->db->bind(':id_especie', $id_especie);
        return $this->db->resultSet();
    }

    /**
     * Obtiene todos los clientes
     */
    public function obtenerClientes() {
        $this->db->query("SELECT id, nombre, apellido FROM clientes ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Verifica si un cliente existe
     */
    public function clienteExiste(int $id_cliente) {
        $this->db->query("SELECT id FROM clientes WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id_cliente);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si una raza existe
     */
    public function razaExiste(int $id_raza) {
        $this->db->query("SELECT id FROM razas WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id_raza);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Calcula la edad de la mascota en años
     */
    public function calcularEdad(string $fecha_nacimiento) {
        $nacimiento = new \DateTime($fecha_nacimiento);
        $hoy = new \DateTime();
        $edad = $hoy->diff($nacimiento);
        return $edad->y;
    }

    /**
     * Busca mascotas por término
     */
    public function buscarMascotas(string $termino) {
        $this->db->query("
            SELECT 
                m.id, m.codigo, m.nombre, m.fecha_nacimiento, m.sexo,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                r.nombre as raza_nombre, e.nombre as especie_nombre
            FROM mascotas m
            JOIN clientes c ON m.id_cliente = c.id
            JOIN razas r ON m.id_raza = r.id
            JOIN especies e ON r.id_especie = e.id
            WHERE m.nombre LIKE :termino 
            OR m.codigo LIKE :termino
            OR c.nombre LIKE :termino 
            OR c.apellido LIKE :termino
            OR c.dni LIKE :termino
            OR r.nombre LIKE :termino
            OR e.nombre LIKE :termino
            ORDER BY m.nombre ASC
        ");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }

    /**
     * Obtiene mascotas por especie
     */
    public function obtenerMascotasPorEspecie(int $id_especie) {
        $this->db->query("
            SELECT 
                m.id, m.codigo, m.nombre, m.fecha_nacimiento, m.sexo,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                r.nombre as raza_nombre
            FROM mascotas m
            JOIN clientes c ON m.id_cliente = c.id
            JOIN razas r ON m.id_raza = r.id
            WHERE r.id_especie = :id_especie
            ORDER BY m.nombre ASC
        ");
        $this->db->bind(':id_especie', $id_especie);
        return $this->db->resultSet();
    }

    /**
     * Obtiene estadísticas de mascotas
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_mascotas,
                COUNT(CASE WHEN sexo = 'Macho' THEN 1 END) as machos,
                COUNT(CASE WHEN sexo = 'Hembra' THEN 1 END) as hembras
            FROM mascotas
        ");
        return $this->db->single();
    }
}
