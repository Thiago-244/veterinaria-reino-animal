<?php
namespace App\Controllers\ProductoServicio;

use App\Core\BaseController;
use App\Models\ProductoServicioModel;

class ApiProductoServicioController extends BaseController {

    private $productoServicioModel;

    public function __construct() {
        $this->productoServicioModel = $this->model('ProductoServicioModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apiproductoservicio/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $productosServicios = $this->productoServicioModel->obtenerTodos();
        echo json_encode(['data' => $productosServicios]);
    }

    // POST /apiproductoservicio/crear
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

        $required = ['tipo', 'nombre', 'precio'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $tipo = trim((string)$payload['tipo']);
        $nombre = trim((string)$payload['nombre']);
        $precio = (float)$payload['precio'];
        $stock = isset($payload['stock']) ? (int)$payload['stock'] : 0;

        // Validaciones de formato y longitud
        if (!in_array($tipo, ['Producto', 'Servicio'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El tipo debe ser "Producto" o "Servicio"']);
            return;
        }

        if (strlen($nombre) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 100 caracteres']);
            return;
        }

        if (strlen($nombre) < 2) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre debe tener al menos 2 caracteres']);
            return;
        }

        if ($precio <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'El precio debe ser mayor a 0']);
            return;
        }

        // Para servicios, el stock se establece en 9999
        if ($tipo === 'Servicio') {
            $stock = 9999;
        } else {
            if ($stock < 0) {
                http_response_code(422);
                echo json_encode(['error' => 'El stock no puede ser negativo']);
                return;
            }
        }

        // Validaciones de unicidad
        if ($this->productoServicioModel->nombreExiste($nombre)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un producto/servicio con ese nombre']);
            return;
        }

        $ok = $this->productoServicioModel->crear([
            'tipo' => $tipo,
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Producto/Servicio creado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el producto/servicio']);
        }
    }

    // PUT /apiproductoservicio/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->productoServicioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Producto/Servicio no encontrado']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) { 
            http_response_code(400); 
            echo json_encode(['error' => 'JSON inválido']); 
            return; 
        }

        $tipo = isset($payload['tipo']) ? trim((string)$payload['tipo']) : $existente['tipo'];
        $nombre = isset($payload['nombre']) ? trim((string)$payload['nombre']) : $existente['nombre'];
        $precio = isset($payload['precio']) ? (float)$payload['precio'] : $existente['precio'];
        $stock = isset($payload['stock']) ? (int)$payload['stock'] : $existente['stock'];

        // Validaciones
        if (!in_array($tipo, ['Producto', 'Servicio'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El tipo debe ser "Producto" o "Servicio"']);
            return;
        }

        if (strlen($nombre) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 100 caracteres']);
            return;
        }

        if (strlen($nombre) < 2) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre debe tener al menos 2 caracteres']);
            return;
        }

        if ($precio <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'El precio debe ser mayor a 0']);
            return;
        }

        // Para servicios, el stock se establece en 9999
        if ($tipo === 'Servicio') {
            $stock = 9999;
        } else {
            if ($stock < 0) {
                http_response_code(422);
                echo json_encode(['error' => 'El stock no puede ser negativo']);
                return;
            }
        }

        // Verificar si ya existe otro con el mismo nombre
        if ($this->productoServicioModel->nombreExiste($nombre, (int)$id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otro producto/servicio con ese nombre']);
            return;
        }

        $ok = $this->productoServicioModel->actualizar((int)$id, [
            'tipo' => $tipo,
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock
        ]);
        
        if ($ok) { 
            echo json_encode(['message' => 'Producto/Servicio actualizado']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apiproductoservicio/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->productoServicioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Producto/Servicio no encontrado']); 
            return; 
        }
        
        $ok = $this->productoServicioModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Producto/Servicio eliminado']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apiproductoservicio/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $productoServicio = $this->productoServicioModel->obtenerPorId((int)$id);
        if (!$productoServicio) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto/Servicio no encontrado']);
            return;
        }
        
        echo json_encode(['data' => $productoServicio]);
    }

    // GET /apiproductoservicio/productos
    public function productos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $productos = $this->productoServicioModel->obtenerProductos();
        echo json_encode(['data' => $productos]);
    }

    // GET /apiproductoservicio/servicios
    public function servicios() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $servicios = $this->productoServicioModel->obtenerServicios();
        echo json_encode(['data' => $servicios]);
    }

    // GET /apiproductoservicio/por-tipo/{tipo}
    public function porTipo($tipo) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        if (!in_array($tipo, ['Producto', 'Servicio'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Tipo no válido. Debe ser "Producto" o "Servicio"']);
            return;
        }
        
        $productosServicios = $this->productoServicioModel->obtenerPorTipo($tipo);
        echo json_encode(['data' => $productosServicios]);
    }

    // GET /apiproductoservicio/buscar/{termino}
    public function buscar($termino) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $productosServicios = $this->productoServicioModel->buscar($termino);
        echo json_encode(['data' => $productosServicios]);
    }

    // PUT /apiproductoservicio/actualizar-stock/{id}
    public function actualizarStock($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->productoServicioModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Producto/Servicio no encontrado']); 
            return; 
        }

        if ($existente['tipo'] !== 'Producto') {
            http_response_code(422);
            echo json_encode(['error' => 'Solo se puede actualizar el stock de productos']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['stock'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: stock']);
            return;
        }

        $stock = (int)$payload['stock'];
        if ($stock < 0) {
            http_response_code(422);
            echo json_encode(['error' => 'El stock no puede ser negativo']);
            return;
        }
        
        $ok = $this->productoServicioModel->actualizarStock((int)$id, $stock);
        if ($ok) {
            echo json_encode(['message' => 'Stock actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo actualizar el stock']);
        }
    }

    // GET /apiproductoservicio/stock-bajo
    public function stockBajo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
        $productosStockBajo = $this->productoServicioModel->obtenerConStockBajo($limite);
        echo json_encode(['data' => $productosStockBajo]);
    }

    // GET /apiproductoservicio/estadisticas
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->productoServicioModel->obtenerEstadisticas();
        echo json_encode(['data' => $estadisticas]);
    }

    // GET /apiproductoservicio/mas-caros
    public function masCaros() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 5;
        $productosServicios = $this->productoServicioModel->obtenerMasCaros($limite);
        echo json_encode(['data' => $productosServicios]);
    }

    // GET /apiproductoservicio/mas-baratos
    public function masBaratos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 5;
        $productosServicios = $this->productoServicioModel->obtenerMasBaratos($limite);
        echo json_encode(['data' => $productosServicios]);
    }
}
