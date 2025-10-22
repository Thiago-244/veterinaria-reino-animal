<?php
namespace App\Controllers\DetalleTemp;

use App\Core\BaseController;

class DetalleTempController extends BaseController {

    private $detalleTempModel;

    public function __construct() {
        $this->detalleTempModel = $this->model('DetalleTempModel');
    }

    public function index() {
        $detallesTemp = $this->detalleTempModel->obtenerTodos();
        $data = [
            'titulo' => 'Gestión de Carrito Temporal',
            'detallesTemp' => $detallesTemp 
        ];
        $this->view('detalletemp/index', $data);
    }

    /**
     * Muestra el carrito de un usuario específico
     */
    public function carrito($token_usuario) {
        $detallesTemp = $this->detalleTempModel->obtenerPorToken($token_usuario);
        $estadisticas = $this->detalleTempModel->obtenerEstadisticas($token_usuario);
        
        $data = [
            'titulo' => 'Carrito de Compras',
            'token_usuario' => $token_usuario,
            'detallesTemp' => $detallesTemp,
            'estadisticas' => $estadisticas[0] ?? []
        ];
        $this->view('detalletemp/carrito', $data);
    }

    /**
     * Agrega un producto al carrito
     */
    public function agregar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'id_producto' => (int)$_POST['id_producto'],
                'cantidad' => (int)$_POST['cantidad'],
                'token_usuario' => $_POST['token_usuario'] ?? session_id()
            ];

            // Validaciones
            if (empty($datos['id_producto'])) {
                die('El producto es requerido.');
            }

            if ($datos['cantidad'] <= 0) {
                die('La cantidad debe ser mayor a 0.');
            }

            if (empty($datos['token_usuario'])) {
                die('El token de usuario es requerido.');
            }

            // Verificar que el producto existe
            if (!$this->detalleTempModel->productoExiste($datos['id_producto'])) {
                die('El producto seleccionado no existe.');
            }

            // Agregar al carrito
            if ($this->detalleTempModel->crear($datos)) {
                header('Location: ' . APP_URL . '/detalletemp/carrito/' . urlencode($datos['token_usuario']));
            } else {
                die('Algo salió mal al agregar el producto al carrito.');
            }
        }
    }

    /**
     * Actualiza la cantidad de un producto en el carrito
     */
    public function actualizarCantidad($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cantidad = (int)$_POST['cantidad'];
            
            if ($cantidad < 0) {
                die('La cantidad no puede ser negativa.');
            }

            if ($this->detalleTempModel->actualizarCantidad((int)$id, $cantidad)) {
                $detalle = $this->detalleTempModel->obtenerPorId((int)$id);
                if ($detalle) {
                    header('Location: ' . APP_URL . '/detalletemp/carrito/' . urlencode($detalle['token_usuario']));
                } else {
                    header('Location: ' . APP_URL . '/detalletemp');
                }
            } else {
                die('No se pudo actualizar la cantidad.');
            }
        }
    }

    /**
     * Elimina un producto del carrito
     */
    public function eliminar($id) {
        if ($this->detalleTempModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/detalletemp');
        } else {
            die('No se pudo eliminar el producto del carrito.');
        }
    }

    /**
     * Vacía el carrito de un usuario
     */
    public function vaciarCarrito($token_usuario) {
        if ($this->detalleTempModel->eliminarPorToken($token_usuario)) {
            header('Location: ' . APP_URL . '/detalletemp/carrito/' . urlencode($token_usuario));
        } else {
            die('No se pudo vaciar el carrito.');
        }
    }

    /**
     * Procesa el carrito y crea una venta
     */
    public function procesarVenta($token_usuario) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $detallesTemp = $this->detalleTempModel->obtenerPorToken($token_usuario);
            
            if (empty($detallesTemp)) {
                die('El carrito está vacío.');
            }

            // Crear la venta
            $datosVenta = [
                'id_usuario' => (int)$_POST['id_usuario'],
                'id_cliente' => (int)$_POST['id_cliente'],
                'total' => $this->detalleTempModel->obtenerTotalCarrito($token_usuario),
                'detalles' => []
            ];

            // Convertir detalles temporales a detalles de venta
            foreach ($detallesTemp as $detalle) {
                $datosVenta['detalles'][] = [
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['producto_precio']
                ];
            }

            // Crear la venta usando el modelo de venta
            $ventaModel = $this->model('VentaModel');
            $ventaId = $ventaModel->crear($datosVenta);

            if ($ventaId) {
                // Vaciar el carrito después de crear la venta
                $this->detalleTempModel->eliminarPorToken($token_usuario);
                
                // Redirigir a la venta creada
                header('Location: ' . APP_URL . '/venta/ver/' . $ventaId);
            } else {
                die('No se pudo procesar la venta.');
            }
        }
    }

    /**
     * Muestra estadísticas del carrito temporal
     */
    public function estadisticas() {
        $productosMasAgregados = $this->detalleTempModel->obtenerProductosMasAgregados();
        
        $data = [
            'titulo' => 'Estadísticas del Carrito Temporal',
            'productosMasAgregados' => $productosMasAgregados
        ];
        $this->view('detalletemp/estadisticas', $data);
    }

    /**
     * Limpia detalles temporales antiguos
     */
    public function limpiarAntiguos() {
        if ($this->detalleTempModel->limpiarAntiguos()) {
            header('Location: ' . APP_URL . '/detalletemp');
        } else {
            die('No se pudieron limpiar los detalles antiguos.');
        }
    }
}
