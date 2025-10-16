<?php
namespace App\Controllers;

use App\Core\BaseController;

class ClienteController extends BaseController {

    private $clienteModel;

    public function __construct() {
        $this->clienteModel = $this->model('ClienteModel');
    }

    public function index() {
        // ... tu método index sigue igual ...
        $clientes = $this->clienteModel->obtenerTodos();
        $data = [
            'titulo' => 'Gestión de Clientes',
            'clientes' => $clientes 
        ];
        $this->view('clientes/index', $data);
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function crear() {
        $data = [
            'titulo' => 'Crear Cliente'
        ];
        $this->view('clientes/crear', $data);
    }

    /**
     * Guarda el nuevo cliente en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'dni' => trim($_POST['dni']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
            ];

            // 2. Llamar al método del modelo para guardar
            if ($this->clienteModel->crear($datos)) {
                // 3. Redirigir al listado de clientes
                header('Location: ' . APP_URL . '/cliente');
            } else {
                die('Algo salió mal al guardar el cliente.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $cliente = $this->clienteModel->obtenerPorId((int)$id);
        if (!$cliente) { die('Cliente no encontrado'); }
        $data = [ 'titulo' => 'Editar Cliente', 'cliente' => $cliente ];
        $this->view('clientes/editar', $data);
    }

    /**
     * Procesa la actualización del cliente.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'dni' => trim($_POST['dni']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
            ];
            if ($this->clienteModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/cliente');
            } else {
                die('Algo salió mal al actualizar el cliente.');
            }
        }
    }

    /**
     * Elimina un cliente.
     */
    public function eliminar($id) {
        if ($this->clienteModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/cliente');
        } else {
            die('No se pudo eliminar el cliente.');
        }
    }
}