<?php
namespace App\Controllers\ProductoServicio;

use App\Core\BaseController;

class ProductoServicioController extends BaseController {

    private $productoServicioModel;

    public function __construct() {
        $this->productoServicioModel = $this->model('ProductoServicioModel');
    }

    public function index() {
        $productosServicios = $this->productoServicioModel->obtenerTodos();
        $data = [
            'titulo' => 'Gestión de Productos y Servicios',
            'productosServicios' => $productosServicios 
        ];
        $this->view('productosservicios/index', $data);
    }

    /**
     * Muestra el formulario para crear un nuevo producto/servicio.
     */
    public function crear() {
        $data = [
            'titulo' => 'Crear Producto/Servicio',
            'tipos' => ['Producto', 'Servicio']
        ];
        $this->view('productosservicios/crear', $data);
    }

    /**
     * Guarda el nuevo producto/servicio en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'tipo' => trim($_POST['tipo']),
                'nombre' => trim($_POST['nombre']),
                'precio' => (float)$_POST['precio'],
                'stock' => (int)$_POST['stock']
            ];

            // 2. Validaciones básicas
            if (empty($datos['nombre'])) {
                die('El nombre es requerido.');
            }

            if (empty($datos['tipo'])) {
                die('El tipo es requerido.');
            }

            if ($datos['precio'] <= 0) {
                die('El precio debe ser mayor a 0.');
            }

            if (!in_array($datos['tipo'], ['Producto', 'Servicio'])) {
                die('El tipo debe ser Producto o Servicio.');
            }

            // Para servicios, el stock se establece en 9999
            if ($datos['tipo'] === 'Servicio') {
                $datos['stock'] = 9999;
            } else {
                if ($datos['stock'] < 0) {
                    die('El stock no puede ser negativo.');
                }
            }

            // 3. Verificar si ya existe
            if ($this->productoServicioModel->nombreExiste($datos['nombre'])) {
                die('Ya existe un producto/servicio con ese nombre.');
            }

            // 4. Llamar al método del modelo para guardar
            if ($this->productoServicioModel->crear($datos)) {
                // 5. Redirigir al listado
                header('Location: ' . APP_URL . '/productoservicio');
            } else {
                die('Algo salió mal al guardar el producto/servicio.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $productoServicio = $this->productoServicioModel->obtenerPorId((int)$id);
        if (!$productoServicio) { 
            die('Producto/Servicio no encontrado'); 
        }
        
        $data = [
            'titulo' => 'Editar Producto/Servicio',
            'productoServicio' => $productoServicio,
            'tipos' => ['Producto', 'Servicio']
        ];
        $this->view('productosservicios/editar', $data);
    }

    /**
     * Procesa la actualización del producto/servicio.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'tipo' => trim($_POST['tipo']),
                'nombre' => trim($_POST['nombre']),
                'precio' => (float)$_POST['precio'],
                'stock' => (int)$_POST['stock']
            ];

            // Validaciones
            if (empty($datos['nombre'])) {
                die('El nombre es requerido.');
            }

            if (empty($datos['tipo'])) {
                die('El tipo es requerido.');
            }

            if ($datos['precio'] <= 0) {
                die('El precio debe ser mayor a 0.');
            }

            if (!in_array($datos['tipo'], ['Producto', 'Servicio'])) {
                die('El tipo debe ser Producto o Servicio.');
            }

            // Para servicios, el stock se establece en 9999
            if ($datos['tipo'] === 'Servicio') {
                $datos['stock'] = 9999;
            } else {
                if ($datos['stock'] < 0) {
                    die('El stock no puede ser negativo.');
                }
            }

            // Verificar si ya existe otro con el mismo nombre
            if ($this->productoServicioModel->nombreExiste($datos['nombre'], (int)$id)) {
                die('Ya existe otro producto/servicio con ese nombre.');
            }
            
            if ($this->productoServicioModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/productoservicio');
            } else {
                die('Algo salió mal al actualizar el producto/servicio.');
            }
        }
    }

    /**
     * Elimina un producto/servicio.
     */
    public function eliminar($id) {
        if ($this->productoServicioModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/productoservicio');
        } else {
            die('No se pudo eliminar el producto/servicio.');
        }
    }

    /**
     * Muestra solo productos
     */
    public function productos() {
        $productos = $this->productoServicioModel->obtenerProductos();
        $data = [
            'titulo' => 'Gestión de Productos',
            'productos' => $productos 
        ];
        $this->view('productosservicios/productos', $data);
    }

    /**
     * Muestra solo servicios
     */
    public function servicios() {
        $servicios = $this->productoServicioModel->obtenerServicios();
        $data = [
            'titulo' => 'Gestión de Servicios',
            'servicios' => $servicios 
        ];
        $this->view('productosservicios/servicios', $data);
    }

    /**
     * Muestra productos con stock bajo
     */
    public function stockBajo() {
        $productosStockBajo = $this->productoServicioModel->obtenerConStockBajo();
        $data = [
            'titulo' => 'Productos con Stock Bajo',
            'productosStockBajo' => $productosStockBajo 
        ];
        $this->view('productosservicios/stock-bajo', $data);
    }
}
