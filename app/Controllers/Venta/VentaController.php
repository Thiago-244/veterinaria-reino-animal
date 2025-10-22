<?php
namespace App\Controllers\Venta;

use App\Core\BaseController;

class VentaController extends BaseController {

    private $ventaModel;

    public function __construct() {
        $this->ventaModel = $this->model('VentaModel');
    }

    public function index() {
        $ventas = $this->ventaModel->obtenerTodas();
        $data = [
            'titulo' => 'Gestión de Ventas',
            'ventas' => $ventas 
        ];
        $this->view('ventas/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva venta.
     */
    public function crear() {
        // Obtener datos necesarios para el formulario
        $clientes = $this->obtenerClientes();
        $usuarios = $this->obtenerUsuarios();
        $productosServicios = $this->obtenerProductosServicios();
        
        $data = [
            'titulo' => 'Crear Venta',
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'productosServicios' => $productosServicios
        ];
        $this->view('ventas/crear', $data);
    }

    /**
     * Guarda la nueva venta en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'id_usuario' => (int)$_POST['id_usuario'],
                'id_cliente' => (int)$_POST['id_cliente'],
                'total' => (float)$_POST['total'],
                'detalles' => []
            ];

            // 2. Validaciones básicas
            if (empty($datos['id_usuario'])) {
                die('El usuario es requerido.');
            }

            if (empty($datos['id_cliente'])) {
                die('El cliente es requerido.');
            }

            if ($datos['total'] <= 0) {
                die('El total debe ser mayor a 0.');
            }

            // 3. Verificar que el usuario y cliente existen
            if (!$this->ventaModel->usuarioExiste($datos['id_usuario'])) {
                die('El usuario seleccionado no existe.');
            }

            if (!$this->ventaModel->clienteExiste($datos['id_cliente'])) {
                die('El cliente seleccionado no existe.');
            }

            // 4. Procesar detalles de venta
            if (isset($_POST['detalles']) && is_array($_POST['detalles'])) {
                foreach ($_POST['detalles'] as $detalle) {
                    if (!empty($detalle['id_producto']) && !empty($detalle['cantidad']) && !empty($detalle['precio'])) {
                        $datos['detalles'][] = [
                            'id_producto' => (int)$detalle['id_producto'],
                            'cantidad' => (int)$detalle['cantidad'],
                            'precio' => (float)$detalle['precio']
                        ];
                    }
                }
            }

            if (empty($datos['detalles'])) {
                die('Debe agregar al menos un producto/servicio a la venta.');
            }

            // 5. Llamar al método del modelo para guardar
            $ventaId = $this->ventaModel->crear($datos);
            if ($ventaId) {
                // 6. Redirigir al listado de ventas
                header('Location: ' . APP_URL . '/venta');
            } else {
                die('Algo salió mal al guardar la venta.');
            }
        }
    }

    /**
     * Muestra los detalles de una venta.
     */
    public function ver($id) {
        $venta = $this->ventaModel->obtenerPorId((int)$id);
        if (!$venta) { 
            die('Venta no encontrada'); 
        }
        
        $detalles = $this->ventaModel->obtenerDetalles((int)$id);
        
        $data = [
            'titulo' => 'Detalles de Venta #' . $id,
            'venta' => $venta,
            'detalles' => $detalles
        ];
        $this->view('ventas/ver', $data);
    }

    /**
     * Elimina una venta.
     */
    public function eliminar($id) {
        if ($this->ventaModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/venta');
        } else {
            die('No se pudo eliminar la venta.');
        }
    }

    /**
     * Muestra ventas por cliente
     */
    public function porCliente($id_cliente) {
        $ventas = $this->ventaModel->obtenerPorCliente((int)$id_cliente);
        $data = [
            'titulo' => 'Ventas del Cliente',
            'ventas' => $ventas 
        ];
        $this->view('ventas/por-cliente', $data);
    }

    /**
     * Muestra ventas por usuario
     */
    public function porUsuario($id_usuario) {
        $ventas = $this->ventaModel->obtenerPorUsuario((int)$id_usuario);
        $data = [
            'titulo' => 'Ventas del Usuario',
            'ventas' => $ventas 
        ];
        $this->view('ventas/por-usuario', $data);
    }

    /**
     * Muestra ventas del día
     */
    public function ventasDelDia() {
        $ventas = $this->ventaModel->obtenerVentasDelDia();
        $data = [
            'titulo' => 'Ventas del Día',
            'ventas' => $ventas 
        ];
        $this->view('ventas/ventas-del-dia', $data);
    }

    /**
     * Muestra reportes de ventas
     */
    public function reportes() {
        $estadisticas = $this->ventaModel->obtenerEstadisticas();
        $productosMasVendidos = $this->ventaModel->obtenerProductosMasVendidos();
        $ventasPorCliente = $this->ventaModel->obtenerPorClienteAgrupado();
        $ventasPorUsuario = $this->ventaModel->obtenerPorUsuarioAgrupado();
        
        $data = [
            'titulo' => 'Reportes de Ventas',
            'estadisticas' => $estadisticas,
            'productosMasVendidos' => $productosMasVendidos,
            'ventasPorCliente' => $ventasPorCliente,
            'ventasPorUsuario' => $ventasPorUsuario
        ];
        $this->view('ventas/reportes', $data);
    }

    /**
     * Métodos auxiliares para obtener datos
     */
    private function obtenerClientes() {
        $this->db = new \App\Core\Database();
        $this->db->query("SELECT id, nombre, apellido, dni FROM clientes ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    private function obtenerUsuarios() {
        $this->db = new \App\Core\Database();
        $this->db->query("SELECT id, nombre FROM usuarios WHERE estado = 1 ORDER BY nombre ASC");
        return $this->db->resultSet();
    }

    private function obtenerProductosServicios() {
        $this->db = new \App\Core\Database();
        $this->db->query("SELECT id, tipo, nombre, precio, stock FROM productoservicio ORDER BY tipo, nombre ASC");
        return $this->db->resultSet();
    }
}
