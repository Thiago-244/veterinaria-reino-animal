<?php

namespace App\Models;

use App\Core\Database;

class DetalleVentaModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodos() {
        $this->db->query("
            SELECT 
                dv.id, dv.id_venta, dv.id_producto, dv.cantidad, dv.precio as precio_unitario, (dv.cantidad * dv.precio) as subtotal,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo
            FROM detalle_venta dv
            JOIN productoservicio ps ON dv.id_producto = ps.id
            ORDER BY dv.id DESC
        ");
        return $this->db->resultSet();
    }

    public function obtenerPorVenta(int $id_venta) {
        $this->db->query("
            SELECT 
                dv.id, dv.id_venta, dv.id_producto, dv.cantidad, dv.precio as precio_unitario, (dv.cantidad * dv.precio) as subtotal,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo
            FROM detalle_venta dv
            JOIN productoservicio ps ON dv.id_producto = ps.id
            WHERE dv.id_venta = :id_venta
            ORDER BY dv.id ASC
        ");
        $this->db->bind(':id_venta', $id_venta);
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("
            SELECT 
                dv.id, dv.id_venta, dv.id_producto, dv.cantidad, dv.precio as precio_unitario, (dv.cantidad * dv.precio) as subtotal,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo
            FROM detalle_venta dv
            JOIN productoservicio ps ON dv.id_producto = ps.id
            WHERE dv.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function crear(array $datos) {
        $this->db->query("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio) VALUES (:id_venta, :id_producto, :cantidad, :precio)");
        $this->db->bind(':id_venta', $datos['id_venta']);
        $this->db->bind(':id_producto', $datos['id_producto']);
        $this->db->bind(':cantidad', $datos['cantidad']);
        $this->db->bind(':precio', $datos['precio_unitario']);
        return $this->db->execute();
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("UPDATE detalle_venta SET id_producto = :id_producto, cantidad = :cantidad, precio = :precio WHERE id = :id");
        $this->db->bind(':id_producto', $datos['id_producto']);
        $this->db->bind(':cantidad', $datos['cantidad']);
        $this->db->bind(':precio', $datos['precio_unitario']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM detalle_venta WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminarPorVenta(int $id_venta) {
        $this->db->query("DELETE FROM detalle_venta WHERE id_venta = :id_venta");
        $this->db->bind(':id_venta', $id_venta);
        return $this->db->execute();
    }

    public function ventaExiste(int $id_venta) {
        $this->db->query("SELECT COUNT(*) FROM venta WHERE id = :id_venta");
        $this->db->bind(':id_venta', $id_venta);
        return $this->db->single()['COUNT(*)'] > 0;
    }

    public function productoExiste(int $id_producto) {
        $this->db->query("SELECT COUNT(*) FROM productoservicio WHERE id = :id_producto");
        $this->db->bind(':id_producto', $id_producto);
        return $this->db->single()['COUNT(*)'] > 0;
    }

    public function obtenerPrecioProducto(int $id_producto) {
        $this->db->query("SELECT precio FROM productoservicio WHERE id = :id_producto");
        $this->db->bind(':id_producto', $id_producto);
        $result = $this->db->single();
        return $result ? $result['precio'] : 0;
    }

    public function calcularSubtotal(float $precio_unitario, int $cantidad) {
        return $precio_unitario * $cantidad;
    }

    public function obtenerTotalVenta(int $id_venta) {
        $this->db->query("SELECT SUM(cantidad * precio) as total FROM detalle_venta WHERE id_venta = :id_venta");
        $this->db->bind(':id_venta', $id_venta);
        $result = $this->db->single();
        return $result ? (float)$result['total'] : 0.0;
    }

    public function obtenerEstadisticasVentas() {
        $this->db->query("
            SELECT 
                ps.nombre as producto_nombre,
                ps.tipo as producto_tipo,
                SUM(dv.cantidad) as total_vendido,
                SUM(dv.cantidad * dv.precio) as total_ingresos,
                COUNT(DISTINCT dv.id_venta) as veces_vendido
            FROM detalle_venta dv
            JOIN productoservicio ps ON dv.id_producto = ps.id
            GROUP BY dv.id_producto, ps.nombre, ps.tipo
            ORDER BY total_vendido DESC
        ");
        return $this->db->resultSet();
    }

    public function obtenerVentasPorProducto(int $id_producto) {
        $this->db->query("
            SELECT 
                dv.id, dv.id_venta, dv.cantidad, dv.precio as precio_unitario, (dv.cantidad * dv.precio) as subtotal,
                v.creado_en as fecha_venta,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido
            FROM detalle_venta dv
            JOIN venta v ON dv.id_venta = v.id
            JOIN clientes c ON v.id_cliente = c.id
            WHERE dv.id_producto = :id_producto
            ORDER BY v.creado_en DESC
        ");
        $this->db->bind(':id_producto', $id_producto);
        return $this->db->resultSet();
    }
}
