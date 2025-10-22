<?php
namespace App\Models;

use App\Core\Database;

class ProductoServicioModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodos() {
        $this->db->query("SELECT * FROM productoservicio ORDER BY tipo, nombre ASC");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("SELECT * FROM productoservicio WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorTipo(string $tipo) {
        $this->db->query("SELECT * FROM productoservicio WHERE tipo = :tipo ORDER BY nombre ASC");
        $this->db->bind(':tipo', $tipo);
        return $this->db->resultSet();
    }

    public function obtenerProductos() {
        $this->db->query("SELECT * FROM productoservicio WHERE tipo = 'Producto' ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    public function obtenerServicios() {
        $this->db->query("SELECT * FROM productoservicio WHERE tipo = 'Servicio' ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    public function buscar(string $termino) {
        $this->db->query("
            SELECT * FROM productoservicio 
            WHERE nombre LIKE :termino 
            ORDER BY tipo, nombre ASC
        ");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }

    /**
     * Inserta un nuevo producto/servicio en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("
            INSERT INTO productoservicio (tipo, nombre, precio, stock) 
            VALUES (:tipo, :nombre, :precio, :stock)
        ");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':tipo', $datos['tipo']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':precio', $datos['precio']);
        $this->db->bind(':stock', $datos['stock'] ?? 0);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("
            UPDATE productoservicio 
            SET tipo = :tipo, nombre = :nombre, precio = :precio, stock = :stock
            WHERE id = :id
        ");
        
        $this->db->bind(':tipo', $datos['tipo']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':precio', $datos['precio']);
        $this->db->bind(':stock', $datos['stock'] ?? 0);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM productoservicio WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Verifica si un producto/servicio existe
     */
    public function productoExiste(int $id) {
        $this->db->query("SELECT id FROM productoservicio WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si el nombre ya existe
     */
    public function nombreExiste(string $nombre, ?int $excludeId = null) {
        $sql = "SELECT id FROM productoservicio WHERE nombre = :nombre";
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
     * Actualiza el stock de un producto
     */
    public function actualizarStock(int $id, int $nuevoStock) {
        $this->db->query("UPDATE productoservicio SET stock = :stock WHERE id = :id AND tipo = 'Producto'");
        $this->db->bind(':stock', $nuevoStock);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Reduce el stock de un producto
     */
    public function reducirStock(int $id, int $cantidad) {
        $this->db->query("UPDATE productoservicio SET stock = stock - :cantidad WHERE id = :id AND tipo = 'Producto' AND stock >= :cantidad");
        $this->db->bind(':cantidad', $cantidad);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Aumenta el stock de un producto
     */
    public function aumentarStock(int $id, int $cantidad) {
        $this->db->query("UPDATE productoservicio SET stock = stock + :cantidad WHERE id = :id AND tipo = 'Producto'");
        $this->db->bind(':cantidad', $cantidad);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Obtiene productos con stock bajo
     */
    public function obtenerProductosConStockBajo(int $limite = 10) {
        $this->db->query("SELECT * FROM productoservicio WHERE stock <= :limite AND tipo = 'Producto' ORDER BY stock ASC");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }

    /**
     * Obtiene los productos más vendidos
     */
    public function obtenerMasVendidos($limite = 10) {
        $this->db->query("
            SELECT 
                ps.id, ps.nombre, ps.tipo, ps.precio, ps.stock,
                COALESCE(SUM(dv.cantidad), 0) as total_vendido
            FROM productoservicio ps
            LEFT JOIN detalle_venta dv ON ps.id = dv.id_producto
            GROUP BY ps.id, ps.nombre, ps.tipo, ps.precio, ps.stock
            ORDER BY total_vendido DESC
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }

    /**
     * Busca productos por término
     */
    public function buscarProductos(string $termino) {
        $this->db->query("SELECT * FROM productoservicio WHERE nombre LIKE :termino OR tipo LIKE :termino ORDER BY nombre ASC");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }


    /**
     * Obtiene productos con stock bajo
     */
    public function obtenerConStockBajo(int $limiteStock = 10) {
        $this->db->query("
            SELECT * FROM productoservicio 
            WHERE tipo = 'Producto' AND stock <= :limiteStock 
            ORDER BY stock ASC, nombre ASC
        ");
        $this->db->bind(':limiteStock', $limiteStock);
        return $this->db->resultSet();
    }

    /**
     * Obtiene estadísticas de productos/servicios
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_productos,
                COUNT(CASE WHEN tipo = 'Producto' THEN 1 END) as total_productos_fisicos,
                COUNT(CASE WHEN tipo = 'Servicio' THEN 1 END) as total_servicios,
                AVG(precio) as precio_promedio,
                SUM(CASE WHEN tipo = 'Producto' THEN stock ELSE 0 END) as stock_total,
                COUNT(CASE WHEN tipo = 'Producto' AND stock <= 5 THEN 1 END) as productos_stock_bajo
            FROM productoservicio
        ");
        return $this->db->single();
    }

    /**
     * Obtiene productos/servicios agrupados por tipo
     */
    public function obtenerAgrupadosPorTipo() {
        $this->db->query("
            SELECT 
                tipo,
                COUNT(*) as total,
                AVG(precio) as precio_promedio,
                SUM(CASE WHEN tipo = 'Producto' THEN stock ELSE 0 END) as stock_total
            FROM productoservicio 
            GROUP BY tipo
            ORDER BY tipo ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene los productos/servicios más caros
     */
    public function obtenerMasCaros(int $limite = 5) {
        $this->db->query("
            SELECT * FROM productoservicio 
            ORDER BY precio DESC 
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }

    /**
     * Obtiene los productos/servicios más baratos
     */
    public function obtenerMasBaratos(int $limite = 5) {
        $this->db->query("
            SELECT * FROM productoservicio 
            ORDER BY precio ASC 
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }
}
