<?php
namespace App\Models;

use App\Core\Database;

class ClienteModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodos() {
        $this->db->query("SELECT id, dni, nombre, apellido, telefono FROM clientes ORDER BY nombre ASC");
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
        $this->db->query("INSERT INTO clientes (dni, nombre, apellido, telefono, email) VALUES (:dni, :nombre, :apellido, :telefono, :email)");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':dni', $datos['dni']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':apellido', $datos['apellido']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':email', $datos['email']);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("UPDATE clientes SET dni = :dni, nombre = :nombre, apellido = :apellido, telefono = :telefono, email = :email WHERE id = :id");
        $this->db->bind(':dni', $datos['dni']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':apellido', $datos['apellido']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':email', $datos['email']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM clientes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}