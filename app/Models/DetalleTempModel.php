<?php
namespace App\Models;

use App\Core\Database;

class DetalleTempModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodos() {
        $this->db->query("
            SELECT 
                dt.id, dt.id_producto, dt.cantidad, dt.token_usuario, dt.creado_en,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo, ps.precio as producto_precio
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            ORDER BY dt.creado_en DESC
        ");
        return $this->db->resultSet();
    }

    public function obtenerPorToken(string $token_usuario) {
        $this->db->query("
            SELECT 
                dt.id, dt.id_producto, dt.cantidad, dt.token_usuario, dt.creado_en,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo, ps.precio as producto_precio
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            WHERE dt.token_usuario = :token_usuario
            ORDER BY dt.creado_en ASC
        ");
        $this->db->bind(':token_usuario', $token_usuario);
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("
            SELECT 
                dt.*,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo, ps.precio as producto_precio
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            WHERE dt.id = :id LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorProductoYToken(int $id_producto, string $token_usuario) {
        $this->db->query("
            SELECT * FROM detalle_temp 
            WHERE id_producto = :id_producto AND token_usuario = :token_usuario 
            LIMIT 1
        ");
        $this->db->bind(':id_producto', $id_producto);
        $this->db->bind(':token_usuario', $token_usuario);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    /**
     * Inserta un nuevo detalle temporal en la base de datos.
     */
    public function crear($datos) {
        // Verificar si ya existe un detalle para este producto y token
        $existente = $this->obtenerPorProductoYToken($datos['id_producto'], $datos['token_usuario']);
        
        if ($existente) {
            // Si ya existe, actualizar la cantidad
            return $this->actualizarCantidad($existente['id'], $existente['cantidad'] + $datos['cantidad']);
        } else {
            // Si no existe, crear uno nuevo
            $this->db->query("
                INSERT INTO detalle_temp (id_producto, cantidad, token_usuario) 
                VALUES (:id_producto, :cantidad, :token_usuario)
            ");

            $this->db->bind(':id_producto', $datos['id_producto']);
            $this->db->bind(':cantidad', $datos['cantidad']);
            $this->db->bind(':token_usuario', $datos['token_usuario']);

            return $this->db->execute();
        }
    }

    public function actualizarCantidad(int $id, int $cantidad) {
        if ($cantidad <= 0) {
            return $this->eliminar($id);
        }

        $this->db->query("UPDATE detalle_temp SET cantidad = :cantidad WHERE id = :id");
        $this->db->bind(':cantidad', $cantidad);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM detalle_temp WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function eliminarPorToken(string $token_usuario) {
        $this->db->query("DELETE FROM detalle_temp WHERE token_usuario = :token_usuario");
        $this->db->bind(':token_usuario', $token_usuario);
        return $this->db->execute();
    }

    public function eliminarPorProductoYToken(int $id_producto, string $token_usuario) {
        $this->db->query("DELETE FROM detalle_temp WHERE id_producto = :id_producto AND token_usuario = :token_usuario");
        $this->db->bind(':id_producto', $id_producto);
        $this->db->bind(':token_usuario', $token_usuario);
        return $this->db->execute();
    }

    /**
     * Verifica si un detalle temporal existe
     */
    public function detalleExiste(int $id) {
        $this->db->query("SELECT id FROM detalle_temp WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si un producto existe
     */
    public function productoExiste(int $id_producto) {
        $this->db->query("SELECT id FROM productoservicio WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id_producto);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Obtiene el total de items en el carrito
     */
    public function obtenerTotalItems(string $token_usuario) {
        $this->db->query("
            SELECT SUM(cantidad) as total_items 
            FROM detalle_temp 
            WHERE token_usuario = :token_usuario
        ");
        $this->db->bind(':token_usuario', $token_usuario);
        $rows = $this->db->resultSet();
        return $rows ? (int)$rows[0]['total_items'] : 0;
    }

    /**
     * Obtiene el total del carrito
     */
    public function obtenerTotalCarrito(string $token_usuario) {
        $this->db->query("
            SELECT SUM(dt.cantidad * ps.precio) as total_carrito
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            WHERE dt.token_usuario = :token_usuario
        ");
        $this->db->bind(':token_usuario', $token_usuario);
        $rows = $this->db->resultSet();
        return $rows ? (float)$rows[0]['total_carrito'] : 0.0;
    }

    /**
     * Obtiene estadísticas del carrito temporal
     */
    public function obtenerEstadisticas(string $token_usuario) {
        $this->db->query("
            SELECT 
                COUNT(*) as total_productos,
                SUM(cantidad) as total_items,
                SUM(dt.cantidad * ps.precio) as total_carrito
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            WHERE dt.token_usuario = :token_usuario
        ");
        $this->db->bind(':token_usuario', $token_usuario);
        return $this->db->resultSet();
    }

    /**
     * Limpia detalles temporales antiguos (más de 24 horas)
     */
    public function limpiarAntiguos() {
        $this->db->query("
            DELETE FROM detalle_temp 
            WHERE creado_en < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        return $this->db->execute();
    }

    /**
     * Obtiene detalles temporales por rango de fechas
     */
    public function obtenerPorRangoFechas(string $fecha_inicio, string $fecha_fin) {
        $this->db->query("
            SELECT 
                dt.id, dt.id_producto, dt.cantidad, dt.token_usuario, dt.creado_en,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo, ps.precio as producto_precio
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            WHERE DATE(dt.creado_en) BETWEEN :fecha_inicio AND :fecha_fin
            ORDER BY dt.creado_en DESC
        ");
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin', $fecha_fin);
        return $this->db->resultSet();
    }

    /**
     * Obtiene productos más agregados al carrito
     */
    public function obtenerProductosMasAgregados(int $limite = 10) {
        $this->db->query("
            SELECT 
                ps.id,
                ps.nombre,
                ps.tipo,
                SUM(dt.cantidad) as total_agregado,
                COUNT(DISTINCT dt.token_usuario) as usuarios_unicos
            FROM detalle_temp dt
            LEFT JOIN productoservicio ps ON dt.id_producto = ps.id
            GROUP BY ps.id, ps.nombre, ps.tipo
            ORDER BY total_agregado DESC
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }
}
