<?php
namespace App\Controllers\Cita;

use App\Core\BaseController;

class CitaController extends BaseController {

    private $citaModel;

    public function __construct() {
        $this->citaModel = $this->model('CitaModel');
    }

    public function index() {
        $citas = $this->citaModel->obtenerTodas();
        $data = [
            'titulo' => 'Gestión de Citas Médicas',
            'citas' => $citas 
        ];
        $this->view('citas/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva cita.
     */
    public function crear() {
        // Obtener datos necesarios para el formulario
        $mascotas = $this->citaModel->obtenerMascotas();
        $clientes = $this->citaModel->obtenerClientes();
        $usuarios = $this->citaModel->obtenerUsuarios();
        
        $data = [
            'titulo' => 'Crear Cita Médica',
            'mascotas' => $mascotas,
            'clientes' => $clientes,
            'usuarios' => $usuarios
        ];
        $this->view('citas/crear', $data);
    }

    /**
     * Guarda la nueva cita en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'codigo' => $this->citaModel->generarCodigo(),
                'id_mascota' => (int)$_POST['id_mascota'],
                'id_cliente' => (int)$_POST['id_cliente'],
                'fecha_cita' => trim($_POST['fecha_cita']) . ' ' . trim($_POST['hora_cita']),
                'motivo' => trim($_POST['motivo']),
                'estado' => 'Pendiente'
            ];

            // 2. Llamar al método del modelo para guardar
            if ($this->citaModel->crear($datos)) {
                // 3. Redirigir al listado de citas
                header('Location: ' . APP_URL . '/cita');
            } else {
                die('Algo salió mal al guardar la cita.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $cita = $this->citaModel->obtenerPorId((int)$id);
        if (!$cita) { 
            die('Cita no encontrada'); 
        }
        
        // Obtener datos necesarios para el formulario
        $mascotas = $this->citaModel->obtenerMascotas();
        $clientes = $this->citaModel->obtenerClientes();
        $usuarios = $this->citaModel->obtenerUsuarios();
        
        $data = [
            'titulo' => 'Editar Cita Médica',
            'cita' => $cita,
            'mascotas' => $mascotas,
            'clientes' => $clientes,
            'usuarios' => $usuarios
        ];
        $this->view('citas/editar', $data);
    }

    /**
     * Procesa la actualización de la cita.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'id_mascota' => (int)$_POST['id_mascota'],
                'id_cliente' => (int)$_POST['id_cliente'],
                'fecha_cita' => trim($_POST['fecha_cita']) . ' ' . trim($_POST['hora_cita']),
                'motivo' => trim($_POST['motivo']),
                'estado' => trim($_POST['estado'])
            ];
            
            if ($this->citaModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/cita');
            } else {
                die('Algo salió mal al actualizar la cita.');
            }
        }
    }

    /**
     * Elimina una cita.
     */
    public function eliminar($id) {
        if ($this->citaModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/cita');
        } else {
            die('No se pudo eliminar la cita.');
        }
    }

    /**
     * Cambia el estado de una cita
     */
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado'])) {
            $estado = trim($_POST['estado']);
            if (in_array($estado, ['Pendiente', 'Procesada', 'Cancelada'])) {
                if ($this->citaModel->cambiarEstado((int)$id, $estado)) {
                    header('Location: ' . APP_URL . '/cita');
                } else {
                    die('No se pudo cambiar el estado de la cita.');
                }
            }
        }
    }

    /**
     * Obtiene las mascotas por cliente (AJAX)
     */
    public function obtenerMascotasPorCliente() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cliente'])) {
            $id_cliente = (int)$_POST['id_cliente'];
            $mascotas = $this->citaModel->obtenerMascotasPorCliente($id_cliente);
            
            header('Content-Type: application/json');
            echo json_encode($mascotas);
        }
    }

    /**
     * Vista de calendario de citas
     */
    public function calendario() {
        $citas = $this->citaModel->obtenerCitasPorMes();
        $data = [
            'titulo' => 'Calendario de Citas',
            'citas' => $citas
        ];
        $this->view('citas/calendario', $data);
    }
}
