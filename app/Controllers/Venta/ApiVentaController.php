<?php
namespace App\Controllers\Venta;

use App\Core\BaseController;
use App\Models\VentaModel;

class ApiVentaController extends BaseController {

    private $ventaModel;

    public function __construct() {
        $this->ventaModel = $this->model('VentaModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apiventa/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $ventas = $this->ventaModel->obtenerTodas();
        echo json_encode(['data' => $ventas]);
    }

    // POST /apiventa/crear
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $required = ['id_usuario', 'id_cliente', 'total', 'detalles'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $id_usuario = (int)$payload['id_usuario'];
        $id_cliente = (int)$payload['id_cliente'];
        $total = (float)$payload['total'];
        $detalles = $payload['detalles'];

        // Validaciones
        if ($id_usuario <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'ID de usuario inválido']);
            return;
        }

        if ($id_cliente <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'ID de cliente inválido']);
            return;
        }

        if ($total <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'El total debe ser mayor a 0']);
            return;
        }

        if (!is_array($detalles) || empty($detalles)) {
            http_response_code(422);
            echo json_encode(['error' => 'Debe agregar al menos un detalle de venta']);
            return;
        }

        // Verificar que el usuario y cliente existen
        if (!$this->ventaModel->usuarioExiste($id_usuario)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        if (!$this->ventaModel->clienteExiste($id_cliente)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }

        // Validar detalles
        foreach ($detalles as $detalle) {
            if (!isset($detalle['id_producto']) || !isset($detalle['cantidad']) || !isset($detalle['precio'])) {
                http_response_code(422);
                echo json_encode(['error' => 'Cada detalle debe tener id_producto, cantidad y precio']);
                return;
            }

            if ((int)$detalle['cantidad'] <= 0) {
                http_response_code(422);
                echo json_encode(['error' => 'La cantidad debe ser mayor a 0']);
                return;
            }

            if ((float)$detalle['precio'] <= 0) {
                http_response_code(422);
                echo json_encode(['error' => 'El precio debe ser mayor a 0']);
                return;
            }
        }

        $datos = [
            'id_usuario' => $id_usuario,
            'id_cliente' => $id_cliente,
            'total' => $total,
            'detalles' => $detalles
        ];

        $ventaId = $this->ventaModel->crear($datos);
        if ($ventaId) {
            http_response_code(201);
            echo json_encode(['message' => 'Venta creada correctamente', 'venta_id' => $ventaId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la venta']);
        }
    }

    // DELETE /apiventa/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->ventaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Venta no encontrada']); 
            return; 
        }
        
        $ok = $this->ventaModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Venta eliminada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar la venta']); 
        }
    }

    // GET /apiventa/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $venta = $this->ventaModel->obtenerPorId((int)$id);
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            return;
        }
        
        $detalles = $this->ventaModel->obtenerDetalles((int)$id);
        $venta['detalles'] = $detalles;
        
        echo json_encode(['data' => $venta]);
    }

    // GET /apiventa/por-cliente/{id_cliente}
    public function porCliente($id_cliente) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ventas = $this->ventaModel->obtenerPorCliente((int)$id_cliente);
        echo json_encode(['data' => $ventas]);
    }

    // GET /apiventa/por-usuario/{id_usuario}
    public function porUsuario($id_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ventas = $this->ventaModel->obtenerPorUsuario((int)$id_usuario);
        echo json_encode(['data' => $ventas]);
    }

    // GET /apiventa/por-rango-fechas
    public function porRangoFechas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Se requieren fecha_inicio y fecha_fin']);
            return;
        }
        
        $fecha_inicio = $_GET['fecha_inicio'];
        $fecha_fin = $_GET['fecha_fin'];
        
        $ventas = $this->ventaModel->obtenerPorRangoFechas($fecha_inicio, $fecha_fin);
        echo json_encode(['data' => $ventas]);
    }

    // GET /apiventa/ventas-del-dia
    public function ventasDelDia() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ventas = $this->ventaModel->obtenerVentasDelDia();
        echo json_encode(['data' => $ventas]);
    }

    // GET /apiventa/estadisticas
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->ventaModel->obtenerEstadisticas();
        echo json_encode(['data' => $estadisticas]);
    }

    // GET /apiventa/estadisticas-por-periodo
    public function estadisticasPorPeriodo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Se requieren fecha_inicio y fecha_fin']);
            return;
        }
        
        $fecha_inicio = $_GET['fecha_inicio'];
        $fecha_fin = $_GET['fecha_fin'];
        
        $estadisticas = $this->ventaModel->obtenerEstadisticasPorPeriodo($fecha_inicio, $fecha_fin);
        echo json_encode(['data' => $estadisticas]);
    }

    // GET /apiventa/productos-mas-vendidos
    public function productosMasVendidos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
        $productos = $this->ventaModel->obtenerProductosMasVendidos($limite);
        echo json_encode(['data' => $productos]);
    }

    // GET /apiventa/por-cliente-agrupado
    public function porClienteAgrupado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ventas = $this->ventaModel->obtenerPorClienteAgrupado();
        echo json_encode(['data' => $ventas]);
    }

    // GET /apiventa/por-usuario-agrupado
    public function porUsuarioAgrupado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ventas = $this->ventaModel->obtenerPorUsuarioAgrupado();
        echo json_encode(['data' => $ventas]);
    }
}
