<?php
namespace App\Controllers\Especie;

use App\Core\BaseController;

class EspecieController extends BaseController {

    private $especieModel;

    public function __construct() {
        $this->especieModel = $this->model('EspecieModel');
    }

    public function index() {
        $especies = $this->especieModel->obtenerConRazas();
        $data = [
            'titulo' => 'Gestión de Especies',
            'especies' => $especies 
        ];
        $this->view('especies/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva especie.
     */
    public function crear() {
        $data = [
            'titulo' => 'Crear Especie'
        ];
        $this->view('especies/crear', $data);
    }

    /**
     * Guarda la nueva especie en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'nombre' => trim($_POST['nombre'])
            ];

            // 2. Validaciones básicas
            if (empty($datos['nombre'])) {
                die('El nombre de la especie es requerido.');
            }

            // 3. Verificar si ya existe
            if ($this->especieModel->nombreExiste($datos['nombre'])) {
                die('Ya existe una especie con ese nombre.');
            }

            // 4. Llamar al método del modelo para guardar
            if ($this->especieModel->crear($datos)) {
                // 5. Redirigir al listado de especies
                header('Location: ' . APP_URL . '/especie');
            } else {
                die('Algo salió mal al guardar la especie.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $especie = $this->especieModel->obtenerPorId((int)$id);
        if (!$especie) { 
            die('Especie no encontrada'); 
        }
        
        $data = [
            'titulo' => 'Editar Especie',
            'especie' => $especie
        ];
        $this->view('especies/editar', $data);
    }

    /**
     * Procesa la actualización de la especie.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'nombre' => trim($_POST['nombre'])
            ];

            // Validaciones
            if (empty($datos['nombre'])) {
                die('El nombre de la especie es requerido.');
            }

            // Verificar si ya existe otro con el mismo nombre
            if ($this->especieModel->nombreExiste($datos['nombre'], (int)$id)) {
                die('Ya existe otra especie con ese nombre.');
            }
            
            if ($this->especieModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/especie');
            } else {
                die('Algo salió mal al actualizar la especie.');
            }
        }
    }

    /**
     * Elimina una especie.
     */
    public function eliminar($id) {
        // Verificar si la especie tiene razas asociadas
        $especie = $this->especieModel->obtenerConRazas();
        foreach ($especie as $e) {
            if ($e['id'] == $id && $e['total_razas'] > 0) {
                die('No se puede eliminar la especie porque tiene razas asociadas.');
            }
        }

        if ($this->especieModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/especie');
        } else {
            die('No se pudo eliminar la especie.');
        }
    }
}
