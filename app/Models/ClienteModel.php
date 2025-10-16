<?php
namespace App\Models;

use App\Core\Database;

class ClienteModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerTodos() {
        $this->db->query("SELECT id, dni, nombre, apellido, telefono FROM clientes ORDER BY nombre ASC");
        return $this->db->resultSet();
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
}