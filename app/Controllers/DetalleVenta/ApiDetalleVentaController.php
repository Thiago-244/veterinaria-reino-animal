<?php

namespace App\Controllers\DetalleVenta;

use App\Core\BaseController;
use App\Models\DetalleVentaModel;
use App\Models\VentaModel;
use App\Models\ProductoServicioModel;

class ApiDetalleVentaController extends BaseController {
    private $detalleVentaModel;
    private $ventaModel;
    private $productoServicioModel;

    public function __construct() {
        $this->detalleVentaModel = $this->model('DetalleVentaModel');
        $this->ventaModel = $this->model('VentaModel');
        $this->productoServicioModel = $this->model('ProductoServicioModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    public function listar() {
        $detallesVenta = $this->detalleVentaModel->obtenerTodos();
        echo json_encode($detallesVenta);
    }

    public function porVenta($id_venta) {
        if (!is_numeric($id_venta)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de venta inválido.']);
            return;
        }

        $venta = $this->ventaModel->obtenerPorId($id_venta);
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['message' => 'Venta no encontrada.']);
            return;
        }

        $detallesVenta = $this->detalleVentaModel->obtenerPorVenta($id_venta);
        echo json_encode([
            'venta' => $venta,
            'detalles' => $detallesVenta
        ]);
    }

    public function obtener($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID inválido.']);
            return;
        }

        $detalleVenta = $this->detalleVentaModel->obtenerPorId($id);
        if ($detalleVenta) {
            echo json_encode($detalleVenta);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Detalle de venta no encontrado.']);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_venta']) || !isset($data['id_producto']) || !isset($data['cantidad']) || !isset($data['precio_unitario'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Los campos id_venta, id_producto, cantidad y precio_unitario son requeridos.']);
            return;
        }

        $id_venta = (int)$data['id_venta'];
        $id_producto = (int)$data['id_producto'];
        $cantidad = (int)$data['cantidad'];
        $precio_unitario = (float)$data['precio_unitario'];

        if ($cantidad <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'La cantidad debe ser un número positivo.']);
            return;
        }

        if ($precio_unitario <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'El precio unitario debe ser un número positivo.']);
            return;
        }

        if (!$this->detalleVentaModel->ventaExiste($id_venta)) {
            http_response_code(404);
            echo json_encode(['message' => 'La venta especificada no existe.']);
            return;
        }

        if (!$this->detalleVentaModel->productoExiste($id_producto)) {
            http_response_code(404);
            echo json_encode(['message' => 'El producto especificado no existe.']);
            return;
        }

        $subtotal = $this->detalleVentaModel->calcularSubtotal($precio_unitario, $cantidad);

        $datos = [
            'id_venta' => $id_venta,
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario,
            'subtotal' => $subtotal
        ];

        if ($this->detalleVentaModel->crear($datos)) {
            http_response_code(201);
            echo json_encode(['message' => 'Detalle de venta creado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al crear el detalle de venta.']);
        }
    }

    public function actualizar($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID inválido.']);
            return;
        }

        $detalleVenta = $this->detalleVentaModel->obtenerPorId($id);
        if (!$detalleVenta) {
            http_response_code(404);
            echo json_encode(['message' => 'Detalle de venta no encontrado.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_producto']) || !isset($data['cantidad']) || !isset($data['precio_unitario'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Los campos id_producto, cantidad y precio_unitario son requeridos.']);
            return;
        }

        $id_producto = (int)$data['id_producto'];
        $cantidad = (int)$data['cantidad'];
        $precio_unitario = (float)$data['precio_unitario'];

        if ($cantidad <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'La cantidad debe ser un número positivo.']);
            return;
        }

        if ($precio_unitario <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'El precio unitario debe ser un número positivo.']);
            return;
        }

        if (!$this->detalleVentaModel->productoExiste($id_producto)) {
            http_response_code(404);
            echo json_encode(['message' => 'El producto especificado no existe.']);
            return;
        }

        $subtotal = $this->detalleVentaModel->calcularSubtotal($precio_unitario, $cantidad);

        $datos = [
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario,
            'subtotal' => $subtotal
        ];

        if ($this->detalleVentaModel->actualizar($id, $datos)) {
            http_response_code(200);
            echo json_encode(['message' => 'Detalle de venta actualizado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al actualizar el detalle de venta.']);
        }
    }

    public function eliminar($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID inválido.']);
            return;
        }

        $detalleVenta = $this->detalleVentaModel->obtenerPorId($id);
        if (!$detalleVenta) {
            http_response_code(404);
            echo json_encode(['message' => 'Detalle de venta no encontrado.']);
            return;
        }

        if ($this->detalleVentaModel->eliminar($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Detalle de venta eliminado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al eliminar el detalle de venta.']);
        }
    }

    public function eliminarPorVenta($id_venta) {
        if (!is_numeric($id_venta)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de venta inválido.']);
            return;
        }

        $venta = $this->ventaModel->obtenerPorId($id_venta);
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['message' => 'Venta no encontrada.']);
            return;
        }

        if ($this->detalleVentaModel->eliminarPorVenta($id_venta)) {
            http_response_code(200);
            echo json_encode(['message' => 'Todos los detalles de venta eliminados exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al eliminar los detalles de venta.']);
        }
    }

    public function estadisticas() {
        $estadisticas = $this->detalleVentaModel->obtenerEstadisticasVentas();
        echo json_encode($estadisticas);
    }

    public function porProducto($id_producto) {
        if (!is_numeric($id_producto)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de producto inválido.']);
            return;
        }

        if (!$this->detalleVentaModel->productoExiste($id_producto)) {
            http_response_code(404);
            echo json_encode(['message' => 'Producto no encontrado.']);
            return;
        }

        $ventas = $this->detalleVentaModel->obtenerVentasPorProducto($id_producto);
        echo json_encode($ventas);
    }

    public function calcularSubtotal() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['precio_unitario']) || !isset($data['cantidad'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Los campos precio_unitario y cantidad son requeridos.']);
            return;
        }

        $precio_unitario = (float)$data['precio_unitario'];
        $cantidad = (int)$data['cantidad'];

        if ($precio_unitario <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'El precio unitario debe ser un número positivo.']);
            return;
        }

        if ($cantidad <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'La cantidad debe ser un número positivo.']);
            return;
        }

        $subtotal = $this->detalleVentaModel->calcularSubtotal($precio_unitario, $cantidad);
        echo json_encode(['subtotal' => $subtotal]);
    }

    public function obtenerPrecioProducto($id_producto) {
        if (!is_numeric($id_producto)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de producto inválido.']);
            return;
        }

        if (!$this->detalleVentaModel->productoExiste($id_producto)) {
            http_response_code(404);
            echo json_encode(['message' => 'Producto no encontrado.']);
            return;
        }

        $precio = $this->detalleVentaModel->obtenerPrecioProducto($id_producto);
        echo json_encode(['precio' => $precio]);
    }

    public function obtenerTotalVenta($id_venta) {
        if (!is_numeric($id_venta)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de venta inválido.']);
            return;
        }

        $venta = $this->ventaModel->obtenerPorId($id_venta);
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['message' => 'Venta no encontrada.']);
            return;
        }

        $total = $this->detalleVentaModel->obtenerTotalVenta($id_venta);
        echo json_encode(['total' => $total]);
    }
}
