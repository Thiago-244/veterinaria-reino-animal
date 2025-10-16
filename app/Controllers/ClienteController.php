<?php
namespace App\Controllers;

use App\Core\BaseController;

class ClienteController extends BaseController {
    
    private $clienteModel;

    public function __construct() {
        // Creamos una instancia del modelo de cliente
        $this->clienteModel = $this->model('ClienteModel');
    }

    /**
     * Método principal, muestra la lista de todos los clientes.
     */
    public function index() {
        // 1. Obtenemos la lista de clientes desde el modelo
        $clientes = $this->clienteModel->obtenerTodos();

        // 2. Creamos el array de datos para pasar a la vista
        $data = [
            'titulo' => 'Gestión de Clientes',
            'clientes' => $clientes 
        ];

        // 3. Cargamos la vista y le pasamos los datos
        $this->view('clientes/index', $data);
    }
}