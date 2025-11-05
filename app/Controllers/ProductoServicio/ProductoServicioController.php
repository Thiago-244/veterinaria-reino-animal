<?php
namespace App\Controllers\ProductoServicio;

use App\Core\BaseController;

class ProductoServicioController extends BaseController {

    private $productoServicioModel;

    public function __construct() {
        $this->productoServicioModel = $this->model('ProductoServicioModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($termino !== '') {
            $productosServicios = $this->productoServicioModel->buscar($termino);
        } else {
            $productosServicios = $this->productoServicioModel->obtenerTodos();
        }
        $data = [
            'titulo' => 'Gestión de Productos y Servicios',
            'productosServicios' => $productosServicios,
            'buscar' => $termino
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
            $datos = [
                'tipo' => trim($_POST['tipo'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'precio' => isset($_POST['precio']) ? (float)$_POST['precio'] : 0,
                'stock' => isset($_POST['stock']) ? (int)$_POST['stock'] : 0
            ];
            $error = '';
            if ($datos['nombre'] === '') $error = 'Campo requerido: nombre';
            elseif ($datos['tipo'] === '') $error = 'Campo requerido: tipo';
            elseif (!in_array($datos['tipo'], ['Producto','Servicio'])) $error = 'El tipo debe ser Producto o Servicio';
            elseif ($datos['precio'] <= 0) $error = 'El precio debe ser mayor a 0';
            elseif ($datos['tipo']==='Producto' && $datos['stock'] < 0) $error = 'El stock no puede ser negativo';
            elseif ($this->productoServicioModel->nombreExiste($datos['nombre'])) $error = 'Ya existe un producto/servicio con ese nombre';
            if ($datos['tipo'] === 'Servicio') { $datos['stock'] = 9999; }
            if ($error) {
                $this->view('productosservicios/crear', [
                    'titulo' => 'Crear Producto/Servicio',
                    'error' => $error,
                    'tipos' => ['Producto','Servicio'],
                    'productoServicio' => $datos
                ]);
                return;
            }
            if ($this->productoServicioModel->crear($datos)) {
                $_SESSION['success_message'] = 'Producto/Servicio creado correctamente';
                header('Location: ' . APP_URL . '/productoservicio');
                exit;
            } else {
                $this->view('productosservicios/crear', [
                    'titulo' => 'Crear Producto/Servicio',
                    'error' => 'No se pudo crear el producto/servicio',
                    'tipos' => ['Producto','Servicio'],
                    'productoServicio' => $datos
                ]);
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
            $ps0 = $this->productoServicioModel->obtenerPorId((int)$id);
            $datos = [
                'tipo' => trim($_POST['tipo'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'precio' => isset($_POST['precio']) ? (float)$_POST['precio'] : 0,
                'stock' => isset($_POST['stock']) ? (int)$_POST['stock'] : 0
            ];
            $error = '';
            if ($datos['nombre'] === '') $error = 'Campo requerido: nombre';
            elseif ($datos['tipo'] === '') $error = 'Campo requerido: tipo';
            elseif (!in_array($datos['tipo'], ['Producto','Servicio'])) $error = 'El tipo debe ser Producto o Servicio';
            elseif ($datos['precio'] <= 0) $error = 'El precio debe ser mayor a 0';
            elseif ($datos['tipo']==='Producto' && $datos['stock'] < 0) $error = 'El stock no puede ser negativo';
            elseif ($this->productoServicioModel->nombreExiste($datos['nombre'], (int)$id)) $error = 'Ya existe otro producto/servicio con ese nombre';
            if ($datos['tipo'] === 'Servicio') { $datos['stock'] = 9999; }
            if ($error) {
                $this->view('productosservicios/editar', [
                    'titulo' => 'Editar Producto/Servicio',
                    'error' => $error,
                    'productoServicio' => array_merge($ps0, $datos),
                    'tipos' => ['Producto','Servicio']
                ]);
                return;
            }
            if ($this->productoServicioModel->actualizar((int)$id, $datos)) {
                $_SESSION['success_message'] = 'Producto/Servicio actualizado correctamente';
                header('Location: ' . APP_URL . '/productoservicio');
                exit;
            } else {
                $this->view('productosservicios/editar', [
                    'titulo' => 'Editar Producto/Servicio',
                    'error' => 'No se pudo actualizar el producto/servicio',
                    'productoServicio' => array_merge($ps0, $datos),
                    'tipos' => ['Producto','Servicio']
                ]);
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
