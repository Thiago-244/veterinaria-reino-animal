<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private $dbh;
    private $stmt;

    public function __construct() { /* ... el constructor sigue igual ... */ 
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Error de Conexión: ' . $e->getMessage());
        }
    }
    public function query($sql) { $this->stmt = $this->dbh->prepare($sql); }

    // NUEVO MÉTODO: Vincula valores a la consulta preparada
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() { return $this->stmt->execute(); }
    public function resultSet() { $this->execute(); return $this->stmt->fetchAll(); }
    public function single() { $this->execute(); return $this->stmt->fetch(); }

    // Transacciones
    public function beginTransaction() { return $this->dbh->beginTransaction(); }
    public function commit() { return $this->dbh->commit(); }
    public function rollBack() { return $this->dbh->rollBack(); }

    // Accesos útiles
    public function lastInsertId() { return $this->dbh->lastInsertId(); }
}