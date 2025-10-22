<?php
namespace App\Controllers\Mascota;

use App\Core\BaseController;

class MascotaController extends BaseController {

    private $mascotaModel;

    public function __construct() {
        $this->mascotaModel = $this->model('MascotaModel');
    }

    public function index() {
        $mascotas = $this->mascotaModel->obtenerTodas();
        $data = [
            'titulo' => 'Gestión de Mascotas',
            'mascotas' => $mascotas 
        ];
        $this->view('mascotas/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva mascota.
     */
    public function crear() {
        // Obtener datos necesarios para el formulario
        $especies = $this->mascotaModel->obtenerEspecies();
        $razas = $this->mascotaModel->obtenerRazas();
        $clientes = $this->mascotaModel->obtenerClientes();
        
        $data = [
            'titulo' => 'Crear Mascota',
            'especies' => $especies,
            'razas' => $razas,
            'clientes' => $clientes
        ];
        $this->view('mascotas/crear', $data);
    }

    /**
     * Guarda la nueva mascota en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'codigo' => $this->mascotaModel->generarCodigo(),
                'nombre' => trim($_POST['nombre']),
                'id_cliente' => (int)$_POST['id_cliente'],
                'id_raza' => (int)$_POST['id_raza'],
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento']),
                'sexo' => trim($_POST['sexo']),
                'color' => trim($_POST['color'] ?? ''),
                'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
                'foto' => 'default_pet.png'
            ];

            // 2. Llamar al método del modelo para guardar
            if ($this->mascotaModel->crear($datos)) {
                // 3. Redirigir al listado de mascotas
                header('Location: ' . APP_URL . '/mascota');
            } else {
                die('Algo salió mal al guardar la mascota.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $mascota = $this->mascotaModel->obtenerPorId((int)$id);
        if (!$mascota) { 
            die('Mascota no encontrada'); 
        }
        
        // Obtener datos necesarios para el formulario
        $especies = $this->mascotaModel->obtenerEspecies();
        $razas = $this->mascotaModel->obtenerRazas();
        $clientes = $this->mascotaModel->obtenerClientes();
        
        $data = [
            'titulo' => 'Editar Mascota',
            'mascota' => $mascota,
            'especies' => $especies,
            'razas' => $razas,
            'clientes' => $clientes
        ];
        $this->view('mascotas/editar', $data);
    }

    /**
     * Procesa la actualización de la mascota.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'id_cliente' => (int)$_POST['id_cliente'],
                'id_raza' => (int)$_POST['id_raza'],
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento']),
                'sexo' => trim($_POST['sexo']),
                'color' => trim($_POST['color'] ?? ''),
                'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
            ];
            
            if ($this->mascotaModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/mascota');
            } else {
                die('Algo salió mal al actualizar la mascota.');
            }
        }
    }

    /**
     * Elimina una mascota.
     */
    public function eliminar($id) {
        if ($this->mascotaModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/mascota');
        } else {
            die('No se pudo eliminar la mascota.');
        }
    }

    /**
     * Obtiene las razas por especie (AJAX)
     */
    public function obtenerRazasPorEspecie() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_especie'])) {
            $id_especie = (int)$_POST['id_especie'];
            $razas = $this->mascotaModel->obtenerRazasPorEspecie($id_especie);
            
            header('Content-Type: application/json');
            echo json_encode($razas);
        }
    }
}
