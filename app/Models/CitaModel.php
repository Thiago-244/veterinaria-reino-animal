<?php
namespace App\Models;

use App\Core\Database;

class CitaModel {
    private $db;

    public function __construct(?Database $database = null) {
        $this->db = $database ?? new Database();
    }

    public function obtenerTodas() {
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado, c.created_at,
                m.nombre as mascota_nombre, m.codigo as mascota_codigo,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido,
                u.nombre as veterinario_nombre
            FROM citas c
            LEFT JOIN mascotas m ON c.id_mascota = m.id
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            LEFT JOIN usuarios u ON c.id_usuario = u.id
            ORDER BY c.fecha_cita ASC
        ");
        return $this->db->resultSet();
    }

    public function obtenerPorId(int $id) {
        $this->db->query("
            SELECT 
                c.*,
                m.nombre as mascota_nombre, m.codigo as mascota_codigo,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido,
                u.nombre as veterinario_nombre
            FROM citas c
            LEFT JOIN mascotas m ON c.id_mascota = m.id
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            LEFT JOIN usuarios u ON c.id_usuario = u.id
            WHERE c.id = :id LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerPorCliente(int $id_cliente) {
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado,
                m.nombre as mascota_nombre, m.codigo as mascota_codigo
            FROM citas c
            LEFT JOIN mascotas m ON c.id_mascota = m.id
            WHERE c.id_cliente = :id_cliente
            ORDER BY c.fecha_cita ASC
        ");
        $this->db->bind(':id_cliente', $id_cliente);
        return $this->db->resultSet();
    }

    public function obtenerPorMascota(int $id_mascota) {
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido
            FROM citas c
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            WHERE c.id_mascota = :id_mascota
            ORDER BY c.fecha_cita ASC
        ");
        $this->db->bind(':id_mascota', $id_mascota);
        return $this->db->resultSet();
    }

    public function obtenerPorCodigo(string $codigo) {
        $this->db->query("SELECT * FROM citas WHERE codigo = :codigo LIMIT 1");
        $this->db->bind(':codigo', $codigo);
        $rows = $this->db->resultSet();
        return $rows ? $rows[0] : null;
    }

    public function obtenerCitasPorMes($mes = null, $año = null) {
        if (!$mes) $mes = (int)date('m');
        if (!$año) $año = (int)date('Y');
        
        // Asegurar que sean enteros
        $mes = (int)$mes;
        $año = (int)$año;
        
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado,
                m.nombre as mascota_nombre,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido
            FROM citas c
            LEFT JOIN mascotas m ON c.id_mascota = m.id
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            WHERE MONTH(c.fecha_cita) = :mes AND YEAR(c.fecha_cita) = :año
            ORDER BY c.fecha_cita ASC
        ");
        // El método bind detecta automáticamente el tipo cuando el valor es entero
        $this->db->bind(':mes', $mes);
        $this->db->bind(':año', $año);
        return $this->db->resultSet();
    }

    /**
     * Inserta una nueva cita en la base de datos.
     */
    public function crear($datos) {
        $this->db->query("
            INSERT INTO citas (codigo, id_mascota, id_cliente, fecha_cita, motivo, estado) 
            VALUES (:codigo, :id_mascota, :id_cliente, :fecha_cita, :motivo, :estado)
        ");

        // Vincular los valores para evitar inyección SQL
        $this->db->bind(':codigo', $datos['codigo']);
        $this->db->bind(':id_mascota', $datos['id_mascota']);
        $this->db->bind(':id_cliente', $datos['id_cliente']);
        $this->db->bind(':fecha_cita', $datos['fecha_cita']);
        $this->db->bind(':motivo', $datos['motivo']);
        $this->db->bind(':estado', $datos['estado']);

        // Ejecutar y devolver true si fue exitoso
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizar(int $id, array $datos) {
        $this->db->query("
            UPDATE citas 
            SET id_mascota = :id_mascota, id_cliente = :id_cliente, 
                fecha_cita = :fecha_cita, motivo = :motivo, estado = :estado
            WHERE id = :id
        ");
        
        $this->db->bind(':id_mascota', $datos['id_mascota']);
        $this->db->bind(':id_cliente', $datos['id_cliente']);
        $this->db->bind(':fecha_cita', $datos['fecha_cita']);
        $this->db->bind(':motivo', $datos['motivo']);
        $this->db->bind(':estado', $datos['estado']);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    public function eliminar(int $id) {
        $this->db->query("DELETE FROM citas WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function cambiarEstado(int $id, string $estado) {
        $this->db->query("UPDATE citas SET estado = :estado WHERE id = :id");
        $this->db->bind(':estado', $estado);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Genera un código único para la cita
     */
    public function generarCodigo() {
        $this->db->query("SELECT COUNT(*) as total FROM citas");
        $result = $this->db->resultSet();
        $numero = ($result[0]['total'] ?? 0) + 1;
        return 'CT-' . str_pad($numero, 5, '0', STR_PAD_LEFT) . '-1';
    }

    /**
     * Obtiene todas las mascotas
     */
    public function obtenerMascotas() {
        $this->db->query("
            SELECT m.id, m.nombre, m.codigo, c.nombre as cliente_nombre, c.apellido as cliente_apellido
            FROM mascotas m
            LEFT JOIN clientes c ON m.id_cliente = c.id
            ORDER BY c.nombre, m.nombre ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene mascotas por cliente
     */
    public function obtenerMascotasPorCliente(int $id_cliente) {
        $this->db->query("
            SELECT id, nombre, codigo 
            FROM mascotas 
            WHERE id_cliente = :id_cliente 
            ORDER BY nombre ASC
        ");
        $this->db->bind(':id_cliente', $id_cliente);
        return $this->db->resultSet();
    }

    /**
     * Obtiene todos los clientes
     */
    public function obtenerClientes() {
        $this->db->query("SELECT id, nombre, apellido FROM clientes ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Obtiene todos los usuarios (veterinarios)
     */
    public function obtenerUsuarios() {
        $this->db->query("SELECT id, nombre FROM usuarios WHERE rol IN ('Editor', 'Administrador') ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Verifica si una mascota existe
     */
    public function mascotaExiste(int $id_mascota) {
        $this->db->query("SELECT id FROM mascotas WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id_mascota);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si un cliente existe
     */
    public function clienteExiste(int $id_cliente) {
        $this->db->query("SELECT id FROM clientes WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id_cliente);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Verifica si una mascota pertenece a un cliente
     */
    public function mascotaPerteneceACliente(int $id_mascota, int $id_cliente) {
        $this->db->query("SELECT id FROM mascotas WHERE id = :id_mascota AND id_cliente = :id_cliente LIMIT 1");
        $this->db->bind(':id_mascota', $id_mascota);
        $this->db->bind(':id_cliente', $id_cliente);
        $rows = $this->db->resultSet();
        return !empty($rows);
    }

    /**
     * Obtiene estadísticas de citas
     */
    public function obtenerEstadisticas() {
        $this->db->query("
            SELECT 
                estado,
                COUNT(*) as total
            FROM citas 
            GROUP BY estado
        ");
        return $this->db->resultSet();
    }

    /**
     * Obtiene citas próximas (próximos 7 días)
     */
    public function obtenerCitasProximas() {
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado,
                m.nombre as mascota_nombre,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido
            FROM citas c
            LEFT JOIN mascotas m ON c.id_mascota = m.id
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            WHERE c.fecha_cita BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
            AND c.estado = 'Pendiente'
            ORDER BY c.fecha_cita ASC
        ");
        return $this->db->resultSet();
    }

    public function buscarCitas(string $termino) {
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado, c.created_at,
                m.nombre as mascota_nombre, m.codigo as mascota_codigo,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido,
                u.nombre as veterinario_nombre
            FROM citas c
            LEFT JOIN mascotas m ON c.id_mascota = m.id
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            LEFT JOIN usuarios u ON c.id_usuario = u.id
            WHERE c.codigo LIKE :t
               OR c.motivo LIKE :t
               OR m.nombre LIKE :t
               OR m.codigo LIKE :t
               OR cl.nombre LIKE :t
               OR cl.apellido LIKE :t
            ORDER BY c.fecha_cita ASC
        ");
        $this->db->bind(':t', '%' . $termino . '%');
        return $this->db->resultSet();
    }
}
