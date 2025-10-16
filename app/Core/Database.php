<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private $dbh;
    private $stmt;

    public function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve arrays asociativos
        ];

        try {
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Error de Conexión: ' . $e->getMessage());
        }
    }

    // Prepara la consulta
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Ejecuta la consulta
    public function execute() {
        return $this->stmt->execute();
    }

    // Obtener todos los resultados
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
}