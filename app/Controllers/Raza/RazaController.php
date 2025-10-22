<?php
namespace App\Controllers\Raza;

use App\Core\BaseController;

class RazaController extends BaseController {

    private $razaModel;

    public function __construct() {
        $this->razaModel = $this->model('RazaModel');
    }

    public function index() {
        $razas = $this->razaModel->obtenerTodas();
        $data = [
            'titulo' => 'Gestión de Razas',
            'razas' => $razas 
        ];
        $this->view('razas/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva raza.
     */
    public function crear() {
        $especies = $this->razaModel->obtenerEspecies();
        $data = [
            'titulo' => 'Crear Raza',
            'especies' => $especies
        ];
        $this->view('razas/crear', $data);
    }

    /**
     * Guarda la nueva raza en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'id_especie' => (int)$_POST['id_especie'],
                'nombre' => trim($_POST['nombre'])
            ];

            // 2. Validaciones básicas
            if (empty($datos['nombre'])) {
                die('El nombre de la raza es requerido.');
            }

            if (empty($datos['id_especie'])) {
                die('La especie es requerida.');
            }

            // 3. Verificar si la especie existe
            if (!$this->razaModel->especieExiste($datos['id_especie'])) {
                die('La especie seleccionada no existe.');
            }

            // 4. Verificar si ya existe la raza en esa especie
            if ($this->razaModel->nombreExisteEnEspecie($datos['nombre'], $datos['id_especie'])) {
                die('Ya existe una raza con ese nombre en la especie seleccionada.');
            }

            // 5. Llamar al método del modelo para guardar
            if ($this->razaModel->crear($datos)) {
                // 6. Redirigir al listado de razas
                header('Location: ' . APP_URL . '/raza');
            } else {
                die('Algo salió mal al guardar la raza.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $raza = $this->razaModel->obtenerPorId((int)$id);
        if (!$raza) { 
            die('Raza no encontrada'); 
        }
        
        $especies = $this->razaModel->obtenerEspecies();
        $data = [
            'titulo' => 'Editar Raza',
            'raza' => $raza,
            'especies' => $especies
        ];
        $this->view('razas/editar', $data);
    }

    /**
     * Procesa la actualización de la raza.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'id_especie' => (int)$_POST['id_especie'],
                'nombre' => trim($_POST['nombre'])
            ];

            // Validaciones
            if (empty($datos['nombre'])) {
                die('El nombre de la raza es requerido.');
            }

            if (empty($datos['id_especie'])) {
                die('La especie es requerida.');
            }

            // Verificar si la especie existe
            if (!$this->razaModel->especieExiste($datos['id_especie'])) {
                die('La especie seleccionada no existe.');
            }

            // Verificar si ya existe otra raza con el mismo nombre en esa especie
            if ($this->razaModel->nombreExisteEnEspecie($datos['nombre'], $datos['id_especie'], (int)$id)) {
                die('Ya existe otra raza con ese nombre en la especie seleccionada.');
            }
            
            if ($this->razaModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/raza');
            } else {
                die('Algo salió mal al actualizar la raza.');
            }
        }
    }

    /**
     * Elimina una raza.
     */
    public function eliminar($id) {
        if ($this->razaModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/raza');
        } else {
            die('No se pudo eliminar la raza.');
        }
    }

    /**
     * Obtiene las razas por especie (AJAX)
     */
    public function obtenerRazasPorEspecie() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_especie'])) {
            $id_especie = (int)$_POST['id_especie'];
            $razas = $this->razaModel->obtenerPorEspecie($id_especie);
            
            header('Content-Type: application/json');
            echo json_encode($razas);
        }
    }
}
