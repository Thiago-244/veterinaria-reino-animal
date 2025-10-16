<?php
namespace App\Models;

use App\Core\Database;

class ClienteModel {
    private $db;

    public function __construct() {
        // Creamos una nueva instancia de nuestra clase Database
        $this->db = new Database();
    }

    /**
     * Obtiene todos los clientes de la base de datos.
     */
    public function obtenerTodos() {
        // 1. Preparamos la consulta SQL
        $this->db->query("SELECT id, dni, nombre, apellido, telefono FROM clientes ORDER BY nombre ASC");
        
        // 2. Ejecutamos la consulta y devolvemos los resultados
        return $this->db->resultSet();
    }
}