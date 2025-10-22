<?php

namespace App\Controllers\DetalleVenta;

use App\Core\BaseController;
use App\Models\DetalleVentaModel;
use App\Models\VentaModel;
use App\Models\ProductoServicioModel;

class DetalleVentaController extends BaseController {
    private $detalleVentaModel;
    private $ventaModel;
    private $productoServicioModel;

    public function __construct() {
        $this->detalleVentaModel = $this->model('DetalleVentaModel');
        $this->ventaModel = $this->model('VentaModel');
        $this->productoServicioModel = $this->model('ProductoServicioModel');
    }

    public function index() {
        $detallesVenta = $this->detalleVentaModel->obtenerTodos();
        $data = [
            'titulo' => 'Gestión de Detalles de Venta',
            'detallesVenta' => $detallesVenta
        ];
        $this->view('detalleventa/index', $data);
    }

    public function porVenta($id_venta) {
        $venta = $this->ventaModel->obtenerPorId($id_venta);
        if (!$venta) {
            $_SESSION['error_message'] = 'Venta no encontrada.';
            header('Location: ' . APP_URL . '/venta');
            exit();
        }

        $detallesVenta = $this->detalleVentaModel->obtenerPorVenta($id_venta);
        $data = [
            'titulo' => 'Detalles de Venta #' . $id_venta,
            'venta' => $venta,
            'detallesVenta' => $detallesVenta
        ];
        $this->view('detalleventa/por_venta', $data);
    }

    public function crear($id_venta) {
        $venta = $this->ventaModel->obtenerPorId($id_venta);
        if (!$venta) {
            $_SESSION['error_message'] = 'Venta no encontrada.';
            header('Location: ' . APP_URL . '/venta');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'id_venta' => $id_venta,
                'id_producto' => trim($_POST['id_producto']),
                'cantidad' => trim($_POST['cantidad']),
                'precio_unitario' => trim($_POST['precio_unitario']),
                'subtotal' => trim($_POST['subtotal'])
            ];

            // Validaciones
            if (empty($datos['id_producto']) || empty($datos['cantidad']) || empty($datos['precio_unitario'])) {
                $_SESSION['error_message'] = 'Todos los campos son requeridos.';
                $data = array_merge($datos, [
                    'titulo' => 'Agregar Detalle a Venta #' . $id_venta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos()
                ]);
                $this->view('detalleventa/crear', $data);
                return;
            }

            if (!is_numeric($datos['cantidad']) || $datos['cantidad'] <= 0) {
                $_SESSION['error_message'] = 'La cantidad debe ser un número positivo.';
                $data = array_merge($datos, [
                    'titulo' => 'Agregar Detalle a Venta #' . $id_venta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos()
                ]);
                $this->view('detalleventa/crear', $data);
                return;
            }

            if (!is_numeric($datos['precio_unitario']) || $datos['precio_unitario'] <= 0) {
                $_SESSION['error_message'] = 'El precio unitario debe ser un número positivo.';
                $data = array_merge($datos, [
                    'titulo' => 'Agregar Detalle a Venta #' . $id_venta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos()
                ]);
                $this->view('detalleventa/crear', $data);
                return;
            }

            // Verificar que el producto existe
            if (!$this->detalleVentaModel->productoExiste($datos['id_producto'])) {
                $_SESSION['error_message'] = 'El producto seleccionado no existe.';
                $data = array_merge($datos, [
                    'titulo' => 'Agregar Detalle a Venta #' . $id_venta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos()
                ]);
                $this->view('detalleventa/crear', $data);
                return;
            }

            // Calcular subtotal si no se proporciona
            if (empty($datos['subtotal'])) {
                $datos['subtotal'] = $this->detalleVentaModel->calcularSubtotal($datos['precio_unitario'], $datos['cantidad']);
            }

            if ($this->detalleVentaModel->crear($datos)) {
                $_SESSION['success_message'] = 'Detalle de venta agregado exitosamente.';
                header('Location: ' . APP_URL . '/detalleventa/por-venta/' . $id_venta);
                exit();
            } else {
                $_SESSION['error_message'] = 'Error al agregar el detalle de venta.';
                $data = array_merge($datos, [
                    'titulo' => 'Agregar Detalle a Venta #' . $id_venta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos()
                ]);
                $this->view('detalleventa/crear', $data);
            }
        } else {
            $data = [
                'titulo' => 'Agregar Detalle a Venta #' . $id_venta,
                'venta' => $venta,
                'productos' => $this->productoServicioModel->obtenerTodos(),
                'id_venta' => $id_venta,
                'id_producto' => '',
                'cantidad' => '',
                'precio_unitario' => '',
                'subtotal' => ''
            ];
            $this->view('detalleventa/crear', $data);
        }
    }

    public function editar($id) {
        $detalleVenta = $this->detalleVentaModel->obtenerPorId($id);
        if (!$detalleVenta) {
            $_SESSION['error_message'] = 'Detalle de venta no encontrado.';
            header('Location: ' . APP_URL . '/venta');
            exit();
        }

        $venta = $this->ventaModel->obtenerPorId($detalleVenta['id_venta']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'id_producto' => trim($_POST['id_producto']),
                'cantidad' => trim($_POST['cantidad']),
                'precio_unitario' => trim($_POST['precio_unitario']),
                'subtotal' => trim($_POST['subtotal'])
            ];

            // Validaciones
            if (empty($datos['id_producto']) || empty($datos['cantidad']) || empty($datos['precio_unitario'])) {
                $_SESSION['error_message'] = 'Todos los campos son requeridos.';
                $data = array_merge($datos, [
                    'titulo' => 'Editar Detalle de Venta',
                    'detalleVenta' => $detalleVenta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos(),
                    'id' => $id
                ]);
                $this->view('detalleventa/editar', $data);
                return;
            }

            if (!is_numeric($datos['cantidad']) || $datos['cantidad'] <= 0) {
                $_SESSION['error_message'] = 'La cantidad debe ser un número positivo.';
                $data = array_merge($datos, [
                    'titulo' => 'Editar Detalle de Venta',
                    'detalleVenta' => $detalleVenta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos(),
                    'id' => $id
                ]);
                $this->view('detalleventa/editar', $data);
                return;
            }

            if (!is_numeric($datos['precio_unitario']) || $datos['precio_unitario'] <= 0) {
                $_SESSION['error_message'] = 'El precio unitario debe ser un número positivo.';
                $data = array_merge($datos, [
                    'titulo' => 'Editar Detalle de Venta',
                    'detalleVenta' => $detalleVenta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos(),
                    'id' => $id
                ]);
                $this->view('detalleventa/editar', $data);
                return;
            }

            // Verificar que el producto existe
            if (!$this->detalleVentaModel->productoExiste($datos['id_producto'])) {
                $_SESSION['error_message'] = 'El producto seleccionado no existe.';
                $data = array_merge($datos, [
                    'titulo' => 'Editar Detalle de Venta',
                    'detalleVenta' => $detalleVenta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos(),
                    'id' => $id
                ]);
                $this->view('detalleventa/editar', $data);
                return;
            }

            // Calcular subtotal si no se proporciona
            if (empty($datos['subtotal'])) {
                $datos['subtotal'] = $this->detalleVentaModel->calcularSubtotal($datos['precio_unitario'], $datos['cantidad']);
            }

            if ($this->detalleVentaModel->actualizar($id, $datos)) {
                $_SESSION['success_message'] = 'Detalle de venta actualizado exitosamente.';
                header('Location: ' . APP_URL . '/detalleventa/por-venta/' . $detalleVenta['id_venta']);
                exit();
            } else {
                $_SESSION['error_message'] = 'Error al actualizar el detalle de venta.';
                $data = array_merge($datos, [
                    'titulo' => 'Editar Detalle de Venta',
                    'detalleVenta' => $detalleVenta,
                    'venta' => $venta,
                    'productos' => $this->productoServicioModel->obtenerTodos(),
                    'id' => $id
                ]);
                $this->view('detalleventa/editar', $data);
            }
        } else {
            $data = [
                'titulo' => 'Editar Detalle de Venta',
                'detalleVenta' => $detalleVenta,
                'venta' => $venta,
                'productos' => $this->productoServicioModel->obtenerTodos(),
                'id' => $detalleVenta['id'],
                'id_producto' => $detalleVenta['id_producto'],
                'cantidad' => $detalleVenta['cantidad'],
                'precio_unitario' => $detalleVenta['precio_unitario'],
                'subtotal' => $detalleVenta['subtotal']
            ];
            $this->view('detalleventa/editar', $data);
        }
    }

    public function eliminar($id) {
        $detalleVenta = $this->detalleVentaModel->obtenerPorId($id);
        if (!$detalleVenta) {
            $_SESSION['error_message'] = 'Detalle de venta no encontrado.';
            header('Location: ' . APP_URL . '/venta');
            exit();
        }

        $id_venta = $detalleVenta['id_venta'];

        if ($this->detalleVentaModel->eliminar($id)) {
            $_SESSION['success_message'] = 'Detalle de venta eliminado exitosamente.';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el detalle de venta.';
        }
        header('Location: ' . APP_URL . '/detalleventa/por-venta/' . $id_venta);
        exit();
    }

    public function estadisticas() {
        $estadisticas = $this->detalleVentaModel->obtenerEstadisticasVentas();
        $data = [
            'titulo' => 'Estadísticas de Ventas por Producto',
            'estadisticas' => $estadisticas
        ];
        $this->view('detalleventa/estadisticas', $data);
    }
}
