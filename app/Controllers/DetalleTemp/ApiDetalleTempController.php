<?php
namespace App\Controllers\DetalleTemp;

use App\Core\BaseController;
use App\Models\DetalleTempModel;

class ApiDetalleTempController extends BaseController {

    private $detalleTempModel;

    public function __construct() {
        $this->detalleTempModel = $this->model('DetalleTempModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apidetalletemp/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $detallesTemp = $this->detalleTempModel->obtenerTodos();
        echo json_encode(['data' => $detallesTemp]);
    }

    // POST /apidetalletemp/agregar
    public function agregar() {
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

        $required = ['id_producto', 'cantidad', 'token_usuario'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $id_producto = (int)$payload['id_producto'];
        $cantidad = (int)$payload['cantidad'];
        $token_usuario = trim((string)$payload['token_usuario']);

        // Validaciones
        if ($id_producto <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'ID de producto inválido']);
            return;
        }

        if ($cantidad <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'La cantidad debe ser mayor a 0']);
            return;
        }

        if (empty($token_usuario)) {
            http_response_code(422);
            echo json_encode(['error' => 'El token de usuario es requerido']);
            return;
        }

        // Verificar que el producto existe
        if (!$this->detalleTempModel->productoExiste($id_producto)) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }

        $datos = [
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'token_usuario' => $token_usuario
        ];

        $ok = $this->detalleTempModel->crear($datos);
        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Producto agregado al carrito correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo agregar el producto al carrito']);
        }
    }

    // PUT /apidetalletemp/actualizar-cantidad/{id}
    public function actualizarCantidad($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->detalleTempModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Detalle temporal no encontrado']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['cantidad'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: cantidad']);
            return;
        }

        $cantidad = (int)$payload['cantidad'];
        if ($cantidad < 0) {
            http_response_code(422);
            echo json_encode(['error' => 'La cantidad no puede ser negativa']);
            return;
        }
        
        $ok = $this->detalleTempModel->actualizarCantidad((int)$id, $cantidad);
        if ($ok) {
            echo json_encode(['message' => 'Cantidad actualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo actualizar la cantidad']);
        }
    }

    // DELETE /apidetalletemp/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->detalleTempModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Detalle temporal no encontrado']); 
            return; 
        }
        
        $ok = $this->detalleTempModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Producto eliminado del carrito']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar el producto del carrito']); 
        }
    }

    // GET /apidetalletemp/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $detalleTemp = $this->detalleTempModel->obtenerPorId((int)$id);
        if (!$detalleTemp) {
            http_response_code(404);
            echo json_encode(['error' => 'Detalle temporal no encontrado']);
            return;
        }
        
        echo json_encode(['data' => $detalleTemp]);
    }

    // GET /apidetalletemp/carrito/{token_usuario}
    public function carrito($token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $detallesTemp = $this->detalleTempModel->obtenerPorToken($token_usuario);
        $estadisticas = $this->detalleTempModel->obtenerEstadisticas($token_usuario);
        
        echo json_encode([
            'data' => $detallesTemp,
            'estadisticas' => $estadisticas[0] ?? []
        ]);
    }

    // DELETE /apidetalletemp/vaciar-carrito/{token_usuario}
    public function vaciarCarrito($token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ok = $this->detalleTempModel->eliminarPorToken($token_usuario);
        if ($ok) { 
            echo json_encode(['message' => 'Carrito vaciado correctamente']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo vaciar el carrito']); 
        }
    }

    // DELETE /apidetalletemp/eliminar-producto/{id_producto}/{token_usuario}
    public function eliminarProducto($id_producto, $token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ok = $this->detalleTempModel->eliminarPorProductoYToken((int)$id_producto, $token_usuario);
        if ($ok) { 
            echo json_encode(['message' => 'Producto eliminado del carrito']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar el producto del carrito']); 
        }
    }

    // GET /apidetalletemp/total-items/{token_usuario}
    public function totalItems($token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $totalItems = $this->detalleTempModel->obtenerTotalItems($token_usuario);
        echo json_encode(['total_items' => $totalItems]);
    }

    // GET /apidetalletemp/total-carrito/{token_usuario}
    public function totalCarrito($token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $totalCarrito = $this->detalleTempModel->obtenerTotalCarrito($token_usuario);
        echo json_encode(['total_carrito' => $totalCarrito]);
    }

    // GET /apidetalletemp/estadisticas/{token_usuario}
    public function estadisticas($token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->detalleTempModel->obtenerEstadisticas($token_usuario);
        echo json_encode(['data' => $estadisticas[0] ?? []]);
    }

    // GET /apidetalletemp/productos-mas-agregados
    public function productosMasAgregados() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
        $productos = $this->detalleTempModel->obtenerProductosMasAgregados($limite);
        echo json_encode(['data' => $productos]);
    }

    // POST /apidetalletemp/limpiar-antiguos
    public function limpiarAntiguos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $ok = $this->detalleTempModel->limpiarAntiguos();
        if ($ok) {
            echo json_encode(['message' => 'Detalles antiguos limpiados correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudieron limpiar los detalles antiguos']);
        }
    }
}
