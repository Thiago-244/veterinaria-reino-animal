<?php
namespace App\Controllers\Empresa;

use App\Core\BaseController;

class EmpresaController extends BaseController {

    private $empresaModel;

    public function __construct() {
        $this->empresaModel = $this->model('EmpresaModel');
    }

    public function index() {
        $empresas = $this->empresaModel->obtenerTodas();
        $data = [
            'titulo' => 'Gestión de Empresas',
            'empresas' => $empresas 
        ];
        $this->view('empresas/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva empresa.
     */
    public function crear() {
        $data = [
            'titulo' => 'Crear Empresa'
        ];
        $this->view('empresas/crear', $data);
    }

    /**
     * Guarda la nueva empresa en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'ruc' => trim($_POST['ruc']),
                'direccion' => trim($_POST['direccion']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
                'logo' => trim($_POST['logo'] ?? ''),
                'iva' => !empty($_POST['iva']) ? (float)$_POST['iva'] : 18.00
            ];

            // 2. Validaciones básicas
            if (empty($datos['nombre'])) {
                die('El nombre de la empresa es requerido.');
            }

            if (empty($datos['ruc'])) {
                die('El RUC es requerido.');
            }

            if (empty($datos['direccion'])) {
                die('La dirección es requerida.');
            }

            if (empty($datos['telefono'])) {
                die('El teléfono es requerido.');
            }

            if (empty($datos['email'])) {
                die('El email es requerido.');
            }

            // 3. Validaciones de formato
            if (!preg_match('/^\d{11}$/', $datos['ruc'])) {
                die('El RUC debe tener exactamente 11 dígitos.');
            }

            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                die('El email no tiene un formato válido.');
            }

            // 4. Verificar si ya existe
            if ($this->empresaModel->rucExiste($datos['ruc'])) {
                die('Ya existe una empresa con ese RUC.');
            }

            if ($this->empresaModel->emailExiste($datos['email'])) {
                die('Ya existe una empresa con ese email.');
            }

            // 5. Llamar al método del modelo para guardar
            if ($this->empresaModel->crear($datos)) {
                // 6. Redirigir al listado de empresas
                header('Location: ' . APP_URL . '/empresa');
            } else {
                die('Algo salió mal al guardar la empresa.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $empresa = $this->empresaModel->obtenerPorId((int)$id);
        if (!$empresa) { 
            die('Empresa no encontrada'); 
        }
        
        $data = [
            'titulo' => 'Editar Empresa',
            'empresa' => $empresa
        ];
        $this->view('empresas/editar', $data);
    }

    /**
     * Procesa la actualización de la empresa.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'ruc' => trim($_POST['ruc']),
                'direccion' => trim($_POST['direccion']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
                'logo' => trim($_POST['logo'] ?? ''),
                'iva' => !empty($_POST['iva']) ? (float)$_POST['iva'] : 18.00
            ];

            // Validaciones
            if (empty($datos['nombre'])) {
                die('El nombre de la empresa es requerido.');
            }

            if (empty($datos['ruc'])) {
                die('El RUC es requerido.');
            }

            if (empty($datos['direccion'])) {
                die('La dirección es requerida.');
            }

            if (empty($datos['telefono'])) {
                die('El teléfono es requerido.');
            }

            if (empty($datos['email'])) {
                die('El email es requerido.');
            }

            // Validaciones de formato
            if (!preg_match('/^\d{11}$/', $datos['ruc'])) {
                die('El RUC debe tener exactamente 11 dígitos.');
            }

            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                die('El email no tiene un formato válido.');
            }

            // Verificar si ya existe otro con el mismo RUC
            if ($this->empresaModel->rucExiste($datos['ruc'], (int)$id)) {
                die('Ya existe otra empresa con ese RUC.');
            }

            // Verificar si ya existe otro con el mismo email
            if ($this->empresaModel->emailExiste($datos['email'], (int)$id)) {
                die('Ya existe otra empresa con ese email.');
            }
            
            if ($this->empresaModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/empresa');
            } else {
                die('Algo salió mal al actualizar la empresa.');
            }
        }
    }

    /**
     * Elimina una empresa.
     */
    public function eliminar($id) {
        if ($this->empresaModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/empresa');
        } else {
            die('No se pudo eliminar la empresa.');
        }
    }
}
