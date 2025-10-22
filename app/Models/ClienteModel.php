<?php
namespace App\Models;

use App\Core\Database;

class ClienteModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodos() {
        $this->db->query("SELECT id, dni, nombre, apellido, telefono, direccion, email, foto, created_at FROM clientes ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("SELECT * FROM clientes WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorDni(string $dni) {
        $this->db->query("SELECT * FROM clientes WHERE dni = :dni LIMIT 1");
        $this->db->bind(':dni', $dni);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorEmail(?string $email) {
        if ($email === null || $email === '') { return null; }
        $this->db->query("SELECT * FROM clientes WHERE email = :email LIMIT 1");
        $this->db->bind(':email', $email);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Inserta un nuevo cliente en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono, direccion, email, foto) VALUES (:dni, :nombre, :apellido, :telefono, :direccion, :email, :foto)");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':dni', $datos['dni']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':apellido', $datos['apellido']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':direccion', $datos['direccion'] ?? null);
        $this->db->bind(':email', $datos['email'] ?? null);
        $this->db->bind(':foto', $datos['foto'] ?? 'default_avatar.png');

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("UPDATE clientes SET dni = :dni, nombre = :nombre, apellido = :apellido, telefono = :telefono, direccion = :direccion, email = :email, foto = :foto WHERE id = :id");
        $this->db->bind(':dni', $datos['dni']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':apellido', $datos['apellido']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':direccion', $datos['direccion'] ?? null);
        $this->db->bind(':email', $datos['email'] ?? null);
        $this->db->bind(':foto', $datos['foto'] ?? 'default_avatar.png');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM clientes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function buscarClientes(string $termino) {
        $this->db->query("
            SELECT * FROM clientes 
            WHERE nombre LIKE :termino 
            OR apellido LIKE :termino 
            OR dni LIKE :termino 
            OR email LIKE :termino
            ORDER BY nombre, apellido ASC
        ");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }

    public function obtenerClientesConMascotas() {
        $this->db->query("
            SELECT 
                c.id, c.dni, c.nombre, c.apellido, c.telefono, c.email,
                COUNT(m.id) as total_mascotas
            FROM clientes c
            LEFT JOIN mascotas m ON c.id = m.id_cliente
            GROUP BY c.id, c.dni, c.nombre, c.apellido, c.telefono, c.email
            HAVING total_mascotas > 0
            ORDER BY total_mascotas DESC, c.nombre ASC
        ");
        return $this->db->resultSet();
    }

    public function obtenerClientesConVentas() {
        $this->db->query("
            SELECT 
                c.id, c.dni, c.nombre, c.apellido, c.telefono, c.email,
                COUNT(v.id) as total_ventas,
                SUM(v.total) as total_gastado,
                MAX(v.creado_en) as ultima_compra
            FROM clientes c
            LEFT JOIN venta v ON c.id = v.id_cliente
            GROUP BY c.id, c.dni, c.nombre, c.apellido, c.telefono, c.email
            HAVING total_ventas > 0
            ORDER BY total_gastado DESC
        ");
        return $this->db->resultSet();
    }

    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_clientes,
                COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as clientes_con_email,
                COUNT(CASE WHEN direccion IS NOT NULL AND direccion != '' THEN 1 END) as clientes_con_direccion,
                (SELECT COUNT(*) FROM mascotas) as total_mascotas,
                (SELECT COUNT(DISTINCT id_cliente) FROM venta) as clientes_con_ventas
            FROM clientes
        ");
        return $this->db->single();
    }

    public function obtenerClientesRecientes($limite = 10) {
        $this->db->query("
            SELECT * FROM clientes 
            ORDER BY created_at DESC 
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }
}