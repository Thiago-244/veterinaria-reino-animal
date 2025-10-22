<?php
namespace App\Models;

use App\Core\Database;

class VentaModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodas() {
        $this->db->query("
            SELECT 
                v.id, v.id_usuario, v.id_cliente, v.total, v.creado_en,
                u.nombre as usuario_nombre,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni as cliente_dni
            FROM venta v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            ORDER BY v.creado_en DESC
        ");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("
            SELECT 
                v.*,
                u.nombre as usuario_nombre,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni as cliente_dni
            FROM venta v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE v.id = :id LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorCliente(int $id_cliente) {
        $this->db->query("
            SELECT 
                v.id, v.id_usuario, v.id_cliente, v.total, v.creado_en,
                u.nombre as usuario_nombre
            FROM venta v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            WHERE v.id_cliente = :id_cliente
            ORDER BY v.creado_en DESC
        ");
        $this->db->bind(':id_cliente', $id_cliente);
        return $this->db->resultSet();
    }

    public function obtenerPorUsuario(int $id_usuario) {
        $this->db->query("
            SELECT 
                v.id, v.id_usuario, v.id_cliente, v.total, v.creado_en,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni as cliente_dni
            FROM venta v
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE v.id_usuario = :id_usuario
            ORDER BY v.creado_en DESC
        ");
        $this->db->bind(':id_usuario', $id_usuario);
        return $this->db->resultSet();
    }

    public function obtenerPorRangoFechas(string $fecha_inicio, string $fecha_fin) {
        $this->db->query("
            SELECT 
                v.id, v.id_usuario, v.id_cliente, v.total, v.creado_en,
                u.nombre as usuario_nombre,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni as cliente_dni
            FROM venta v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE DATE(v.creado_en) BETWEEN :fecha_inicio AND :fecha_fin
            ORDER BY v.creado_en DESC
        ");
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin', $fecha_fin);
        return $this->db->resultSet();
    }

    /**
     * Inserta una nueva venta en la base de datos.
     */
    public function crear($datos) {
        $this->db->beginTransaction();
        
        try {
            // 1. Insertar la venta
            $this->db->query("
                INSERT INTO venta (id_usuario, id_cliente, total) 
                VALUES (:id_usuario, :id_cliente, :total)
            ");
            $this->db->bind(':id_usuario', $datos['id_usuario']);
            $this->db->bind(':id_cliente', $datos['id_cliente']);
            $this->db->bind(':total', $datos['total']);
            
            if (!$this->db->execute()) {
                throw new \Exception('Error al crear la venta');
            }
            
            $ventaId = $this->db->lastInsertId();
            
            // 2. Insertar los detalles de venta
            if (isset($datos['detalles']) && is_array($datos['detalles'])) {
                foreach ($datos['detalles'] as $detalle) {
                    $this->db->query("
                        INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio) 
                        VALUES (:id_venta, :id_producto, :cantidad, :precio)
                    ");
                    $this->db->bind(':id_venta', $ventaId);
                    $this->db->bind(':id_producto', $detalle['id_producto']);
                    $this->db->bind(':cantidad', $detalle['cantidad']);
                    $this->db->bind(':precio', $detalle['precio']);
                    
                    if (!$this->db->execute()) {
                        throw new \Exception('Error al crear el detalle de venta');
                    }
                    
                    // 3. Reducir el stock del producto (solo si es producto)
                    $this->db->query("
                        UPDATE productoservicio 
                        SET stock = stock - :cantidad 
                        WHERE id = :id_producto AND tipo = 'Producto' AND stock >= :cantidad
                    ");
                    $this->db->bind(':cantidad', $detalle['cantidad']);
                    $this->db->bind(':id_producto', $detalle['id_producto']);
                    
                    if (!$this->db->execute()) {
                        throw new \Exception('Error al actualizar el stock del producto');
                    }
                }
            }
            
            $this->db->commit();
            return $ventaId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function eliminar(int $id) {
        $this->db->beginTransaction();
        
        try {
            // 1. Obtener los detalles de venta para restaurar stock
            $this->db->query("
                SELECT dv.id_producto, dv.cantidad, ps.tipo
                FROM detalle_venta dv
                LEFT JOIN productoservicio ps ON dv.id_producto = ps.id
                WHERE dv.id_venta = :id_venta
            ");
            $this->db->bind(':id_venta', $id);
            $detalles = $this->db->resultSet();
            
            // 2. Restaurar el stock de los productos
            foreach ($detalles as $detalle) {
                if ($detalle['tipo'] === 'Producto') {
                    $this->db->query("
                        UPDATE productoservicio 
                        SET stock = stock + :cantidad 
                        WHERE id = :id_producto
                    ");
                    $this->db->bind(':cantidad', $detalle['cantidad']);
                    $this->db->bind(':id_producto', $detalle['id_producto']);
                    
                    if (!$this->db->execute()) {
                        throw new \Exception('Error al restaurar el stock del producto');
                    }
                }
            }
            
            // 3. Eliminar los detalles de venta
            $this->db->query("DELETE FROM detalle_venta WHERE id_venta = :id_venta");
            $this->db->bind(':id_venta', $id);
            
            if (!$this->db->execute()) {
                throw new \Exception('Error al eliminar los detalles de venta');
            }
            
            // 4. Eliminar la venta
            $this->db->query("DELETE FROM venta WHERE id = :id");
            $this->db->bind(':id', $id);
            
            if (!$this->db->execute()) {
                throw new \Exception('Error al eliminar la venta');
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Verifica si una venta existe
     */
    public function ventaExiste(int $id) {
        $this->db->query("SELECT id FROM venta WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si un usuario existe
     */
    public function usuarioExiste(int $id_usuario) {
        $this->db->query("SELECT id FROM usuarios WHERE id = :id_usuario LIMIT 1");
        $this->db->bind(':id_usuario', $id_usuario);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si un cliente existe
     */
    public function clienteExiste(int $id_cliente) {
        $this->db->query("SELECT id FROM clientes WHERE id = :id_cliente LIMIT 1");
        $this->db->bind(':id_cliente', $id_cliente);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Obtiene los detalles de una venta
     */
    public function obtenerDetalles(int $id_venta) {
        $this->db->query("
            SELECT 
                dv.*,
                ps.nombre as producto_nombre, ps.tipo as producto_tipo
            FROM detalle_venta dv
            LEFT JOIN productoservicio ps ON dv.id_producto = ps.id
            WHERE dv.id_venta = :id_venta
            ORDER BY dv.id ASC
        ");
        $this->db->bind(':id_venta', $id_venta);
        return $this->db->resultSet();
    }

    /**
     * Obtiene estadísticas de ventas
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                COUNT(*) as total_ventas,
                SUM(total) as total_ingresos,
                AVG(total) as promedio_venta,
                COUNT(DISTINCT id_cliente) as clientes_unicos,
                COUNT(DISTINCT id_usuario) as usuarios_activos
            FROM venta
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene estadísticas de ventas por período
     */
    public function obtenerEstadisticasPorPeriodo(string $fecha_inicio, string $fecha_fin) {
        $this->db->query("
            SELECT 
                COUNT(*) as total_ventas,
                SUM(total) as total_ingresos,
                AVG(total) as promedio_venta,
                COUNT(DISTINCT id_cliente) as clientes_unicos,
                COUNT(DISTINCT id_usuario) as usuarios_activos
            FROM venta
            WHERE DATE(creado_en) BETWEEN :fecha_inicio AND :fecha_fin
        ");
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin', $fecha_fin);
        return $this->db->resultSet();
    }

    /**
     * Obtiene ventas agrupadas por cliente
     */
    public function obtenerPorClienteAgrupado() {
        $this->db->query("
            SELECT 
                c.id as cliente_id,
                c.nombre as cliente_nombre,
                c.apellido as cliente_apellido,
                c.dni as cliente_dni,
                COUNT(v.id) as total_ventas,
                SUM(v.total) as total_gastado,
                MAX(v.creado_en) as ultima_venta
            FROM clientes c
            LEFT JOIN venta v ON c.id = v.id_cliente
            GROUP BY c.id, c.nombre, c.apellido, c.dni
            HAVING total_ventas > 0
            ORDER BY total_gastado DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene ventas agrupadas por usuario
     */
    public function obtenerPorUsuarioAgrupado() {
        $this->db->query("
            SELECT 
                u.id as usuario_id,
                u.nombre as usuario_nombre,
                COUNT(v.id) as total_ventas,
                SUM(v.total) as total_vendido
            FROM usuarios u
            LEFT JOIN venta v ON u.id = v.id_usuario
            GROUP BY u.id, u.nombre
            HAVING total_ventas > 0
            ORDER BY total_vendido DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene productos más vendidos
     */
    public function obtenerProductosMasVendidos(int $limite = 10) {
        $this->db->query("
            SELECT 
                ps.id,
                ps.nombre,
                ps.tipo,
                SUM(dv.cantidad) as total_vendido,
                SUM(dv.cantidad * dv.precio) as total_ingresos
            FROM detalle_venta dv
            LEFT JOIN productoservicio ps ON dv.id_producto = ps.id
            GROUP BY ps.id, ps.nombre, ps.tipo
            ORDER BY total_vendido DESC
            LIMIT :limite
        ");
        $this->db->bind(':limite', $limite);
        return $this->db->resultSet();
    }

    /**
     * Obtiene ventas del día actual
     */
    public function obtenerVentasDelDia() {
        $this->db->query("
            SELECT 
                v.id, v.id_usuario, v.id_cliente, v.total, v.creado_en,
                u.nombre as usuario_nombre,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido
            FROM venta v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE DATE(v.creado_en) = CURDATE()
            ORDER BY v.creado_en DESC
        ");
        return $this->db->resultSet();
    }
}
