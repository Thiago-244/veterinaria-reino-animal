<?php
namespace App\Controllers\Mascota;

use App\Core\BaseController;

class MascotaController extends BaseController {

    private $mascotaModel;

    public function __construct() {
        $this->mascotaModel = $this->model('MascotaModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($termino !== '') {
            $mascotas = $this->mascotaModel->buscarMascotas($termino);
        } else {
            $mascotas = $this->mascotaModel->obtenerTodas();
        }
        $data = [
            'titulo' => 'Gestión de Mascotas',
            'mascotas' => $mascotas,
            'buscar' => $termino
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
            $especies = $this->mascotaModel->obtenerEspecies();
            $razas = $this->mascotaModel->obtenerRazas();
            $clientes = $this->mascotaModel->obtenerClientes();
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
            $error = '';
            if ($datos['nombre'] === '') {
                $error = 'Campo requerido: nombre';
            } elseif (mb_strlen($datos['nombre']) > 50) {
                $error = 'El nombre no debe superar 50 caracteres';
            } elseif (!$this->mascotaModel->clienteExiste($datos['id_cliente'])) {
                $error = 'Debe seleccionar un cliente válido';
            } elseif (!$this->mascotaModel->razaExiste($datos['id_raza'])) {
                $error = 'Debe seleccionar una raza válida';
            } elseif ($datos['fecha_nacimiento'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_nacimiento'])) {
                $error = 'La fecha de nacimiento debe tener formato YYYY-MM-DD';
            } elseif (!in_array($datos['sexo'], ['Macho','Hembra'])) {
                $error = 'El sexo debe ser "Macho" o "Hembra"';
            }
            if ($error) {
                $this->view('mascotas/crear', [
                  'titulo' => 'Crear Mascota',
                  'error' => $error,
                  'especies' => $especies, 'razas' => $razas, 'clientes' => $clientes,
                  'mascota' => $datos
                ]);
                return;
            }
            if ($this->mascotaModel->crear($datos)) {
                $_SESSION['success_message'] = 'Mascota creada correctamente';
                header('Location: ' . APP_URL . '/mascota');
                exit;
            } else {
                $this->view('mascotas/crear', [
                  'titulo' => 'Crear Mascota',
                  'error' => 'No se pudo crear la mascota',
                  'especies' => $especies, 'razas' => $razas, 'clientes' => $clientes,
                  'mascota' => $datos
                ]);
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
            $mascota0 = $this->mascotaModel->obtenerPorId((int)$id);
            $especies = $this->mascotaModel->obtenerEspecies();
            $razas = $this->mascotaModel->obtenerRazas();
            $clientes = $this->mascotaModel->obtenerClientes();
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'id_cliente' => (int)$_POST['id_cliente'],
                'id_raza' => (int)$_POST['id_raza'],
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento']),
                'sexo' => trim($_POST['sexo']),
                'color' => trim($_POST['color'] ?? ''),
                'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
            ];
            $error = '';
            if ($datos['nombre'] === '') {
                $error = 'Campo requerido: nombre';
            } elseif (mb_strlen($datos['nombre']) > 50) {
                $error = 'El nombre no debe superar 50 caracteres';
            } elseif (!$this->mascotaModel->clienteExiste($datos['id_cliente'])) {
                $error = 'Debe seleccionar un cliente válido';
            } elseif (!$this->mascotaModel->razaExiste($datos['id_raza'])) {
                $error = 'Debe seleccionar una raza válida';
            } elseif ($datos['fecha_nacimiento'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_nacimiento'])) {
                $error = 'La fecha de nacimiento debe tener formato YYYY-MM-DD';
            } elseif (!in_array($datos['sexo'], ['Macho','Hembra'])) {
                $error = 'El sexo debe ser "Macho" o "Hembra"';
            }
            if ($error) {
                $this->view('mascotas/editar', [
                  'titulo' => 'Editar Mascota',
                  'error' => $error,
                  'especies' => $especies, 'razas' => $razas, 'clientes' => $clientes,
                  'mascota' => array_merge($mascota0, $datos)
                ]);
                return;
            }
            if ($this->mascotaModel->actualizar((int)$id, $datos)) {
                $_SESSION['success_message'] = 'Mascota actualizada correctamente';
                header('Location: ' . APP_URL . '/mascota');
                exit;
            } else {
                $this->view('mascotas/editar', [
                  'titulo' => 'Editar Mascota',
                  'error' => 'No se pudo actualizar la mascota',
                  'especies' => $especies, 'razas' => $razas, 'clientes' => $clientes,
                  'mascota' => array_merge($mascota0, $datos)
                ]);
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
