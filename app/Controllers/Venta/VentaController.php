<?php
namespace App\Controllers\Venta;

use App\Core\BaseController;

class VentaController extends BaseController {

    private $ventaModel;

    public function __construct() {
        $this->ventaModel = $this->model('VentaModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
        
        if ($fecha !== '') {
            // Buscar por fecha
            $ventas = $this->ventaModel->obtenerPorRangoFechas($fecha, $fecha);
        } elseif ($termino !== '') {
            // Buscar por cliente (nombre, apellido, DNI)
            $ventas = $this->buscarVentasPorCliente($termino);
        } else {
            $ventas = $this->ventaModel->obtenerTodas();
        }
        
        $data = [
            'titulo' => 'Gestión de Ventas',
            'ventas' => $ventas,
            'buscar' => $termino,
            'fecha' => $fecha
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
                'id_usuario' => isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0,
                'id_cliente' => isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0,
                'total' => isset($_POST['total']) ? (float)$_POST['total'] : 0,
                'detalles' => []
            ];

            // 2. Validaciones completas (iguales a ApiVentaController)
            $error = '';
            
            // Validar campos requeridos
            if ($datos['id_usuario'] <= 0) {
                $error = 'ID de usuario inválido';
            } elseif ($datos['id_cliente'] <= 0) {
                $error = 'ID de cliente inválido';
            } elseif ($datos['total'] <= 0) {
                $error = 'El total debe ser mayor a 0';
            } elseif (!$this->ventaModel->usuarioExiste($datos['id_usuario'])) {
                $error = 'Usuario no encontrado';
            } elseif (!$this->ventaModel->clienteExiste($datos['id_cliente'])) {
                $error = 'Cliente no encontrado';
            }

            // 3. Procesar detalles de venta
            if (!$error && isset($_POST['detalles']) && is_array($_POST['detalles'])) {
                foreach ($_POST['detalles'] as $detalle) {
                    if (isset($detalle['id_producto']) && isset($detalle['cantidad']) && isset($detalle['precio'])) {
                        $id_producto = (int)$detalle['id_producto'];
                        $cantidad = (int)$detalle['cantidad'];
                        $precio = (float)$detalle['precio'];
                        
                        // Validar cada detalle
                        if ($cantidad <= 0) {
                            $error = 'La cantidad debe ser mayor a 0';
                            break;
                        }
                        if ($precio <= 0) {
                            $error = 'El precio debe ser mayor a 0';
                            break;
                        }
                        
                        $datos['detalles'][] = [
                            'id_producto' => $id_producto,
                            'cantidad' => $cantidad,
                            'precio' => $precio
                        ];
                    }
                }
            }

            // Validar que haya al menos un detalle
            if (!$error && empty($datos['detalles'])) {
                $error = 'Debe agregar al menos un detalle de venta';
            }

            // Si hay error, mostrar el formulario con el error
            if ($error) {
                $clientes = $this->obtenerClientes();
                $usuarios = $this->obtenerUsuarios();
                $productosServicios = $this->obtenerProductosServicios();
                
                $this->view('ventas/crear', [
                    'titulo' => 'Crear Venta',
                    'error' => $error,
                    'clientes' => $clientes,
                    'usuarios' => $usuarios,
                    'productosServicios' => $productosServicios,
                    'venta' => $datos
                ]);
                return;
            }

            // 4. Llamar al método del modelo para guardar
            $ventaId = $this->ventaModel->crear($datos);
            if ($ventaId) {
                $_SESSION['success_message'] = 'Venta creada correctamente';
                header('Location: ' . APP_URL . '/venta');
                exit;
            } else {
                $clientes = $this->obtenerClientes();
                $usuarios = $this->obtenerUsuarios();
                $productosServicios = $this->obtenerProductosServicios();
                
                $this->view('ventas/crear', [
                    'titulo' => 'Crear Venta',
                    'error' => 'No se pudo crear la venta',
                    'clientes' => $clientes,
                    'usuarios' => $usuarios,
                    'productosServicios' => $productosServicios,
                    'venta' => $datos
                ]);
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
        $existente = $this->ventaModel->obtenerPorId((int)$id);
        if (!$existente) {
            $_SESSION['error_message'] = 'Venta no encontrada';
            header('Location: ' . APP_URL . '/venta');
            exit;
        }
        
        if ($this->ventaModel->eliminar((int)$id)) {
            $_SESSION['success_message'] = 'Venta eliminada correctamente';
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar la venta';
        }
        header('Location: ' . APP_URL . '/venta');
        exit;
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
        $db = new \App\Core\Database();
        $db->query("SELECT id, nombre, apellido, dni FROM clientes ORDER BY nombre ASC");
        return $db->resultSet();
    }

    private function obtenerUsuarios() {
        $db = new \App\Core\Database();
        $db->query("SELECT id, nombre FROM usuarios WHERE estado = 1 ORDER BY nombre ASC");
        return $db->resultSet();
    }

    private function obtenerProductosServicios() {
        $db = new \App\Core\Database();
        $db->query("SELECT id, tipo, nombre, precio, stock FROM productoservicio ORDER BY tipo, nombre ASC");
        return $db->resultSet();
    }

    /**
     * Busca ventas por cliente (nombre, apellido o DNI)
     */
    private function buscarVentasPorCliente($termino) {
        $db = new \App\Core\Database();
        $db->query("
            SELECT 
                v.id, v.id_usuario, v.id_cliente, v.total, v.creado_en,
                u.nombre as usuario_nombre,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni as cliente_dni
            FROM venta v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE c.nombre LIKE :termino 
            OR c.apellido LIKE :termino 
            OR c.dni LIKE :termino
            ORDER BY v.creado_en DESC
        ");
        $db->bind(':termino', '%' . $termino . '%');
        return $db->resultSet();
    }
}
