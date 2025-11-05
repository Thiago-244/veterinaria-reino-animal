<?php
namespace App\Controllers\Cita;

use App\Core\BaseController;

class CitaController extends BaseController {

    private $citaModel;

    public function __construct() {
        $this->citaModel = $this->model('CitaModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($termino !== '') {
            $citas = $this->citaModel->buscarCitas($termino);
        } else {
            $citas = $this->citaModel->obtenerTodas();
        }
        $data = [
            'titulo' => 'Gestión de Citas Médicas',
            'citas' => $citas,
            'buscar' => $termino
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
            $clientes = $this->citaModel->obtenerClientes();
            $mascotas = $this->citaModel->obtenerMascotas();
            $usuarios = $this->citaModel->obtenerUsuarios();
            $fecha = trim($_POST['fecha_cita'] ?? '');
            $hora = trim($_POST['hora_cita'] ?? '');
            $datos = [
                'codigo' => $this->citaModel->generarCodigo(),
                'id_mascota' => (int)($_POST['id_mascota'] ?? 0),
                'id_cliente' => (int)($_POST['id_cliente'] ?? 0),
                'fecha_cita' => ($fecha && $hora) ? ($fecha . ' ' . $hora . ':00') : '',
                'motivo' => trim($_POST['motivo'] ?? ''),
                'estado' => 'Pendiente'
            ];
            $error = '';
            if ($datos['id_mascota'] === 0) $error = 'Campo requerido: id_mascota';
            elseif ($datos['id_cliente'] === 0) $error = 'Campo requerido: id_cliente';
            elseif ($datos['fecha_cita'] === '') $error = 'Campo requerido: fecha_cita';
            elseif ($datos['motivo'] === '') $error = 'Campo requerido: motivo';
            elseif (mb_strlen($datos['motivo']) > 255) $error = 'El motivo no debe superar 255 caracteres';
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datos['fecha_cita'])) $error = 'La fecha debe tener formato YYYY-MM-DD HH:MM:SS';
            elseif (!$this->citaModel->mascotaExiste($datos['id_mascota'])) $error = 'Mascota no encontrada';
            elseif (!$this->citaModel->clienteExiste($datos['id_cliente'])) $error = 'Cliente no encontrado';
            elseif (!$this->citaModel->mascotaPerteneceACliente($datos['id_mascota'], $datos['id_cliente'])) $error = 'La mascota no pertenece al cliente especificado';
            if ($error) {
                $this->view('citas/crear', [
                    'titulo' => 'Crear Cita Médica',
                    'error' => $error,
                    'mascotas' => $mascotas,
                    'clientes' => $clientes,
                    'usuarios' => $usuarios
                ]);
                return;
            }
            if ($this->citaModel->crear($datos)) {
                $_SESSION['success_message'] = 'Cita creada correctamente';
                header('Location: ' . APP_URL . '/cita');
                exit;
            } else {
                $this->view('citas/crear', [
                    'titulo' => 'Crear Cita Médica',
                    'error' => 'No se pudo crear la cita',
                    'mascotas' => $mascotas,
                    'clientes' => $clientes,
                    'usuarios' => $usuarios
                ]);
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
            $cita0 = $this->citaModel->obtenerPorId((int)$id);
            $clientes = $this->citaModel->obtenerClientes();
            $mascotas = $this->citaModel->obtenerMascotas();
            $usuarios = $this->citaModel->obtenerUsuarios();
            $fecha = trim($_POST['fecha_cita'] ?? '');
            $hora = trim($_POST['hora_cita'] ?? '');
            $datos = [
                'id_mascota' => (int)($_POST['id_mascota'] ?? 0),
                'id_cliente' => (int)($_POST['id_cliente'] ?? 0),
                'fecha_cita' => ($fecha && $hora) ? ($fecha . ' ' . $hora . ':00') : '',
                'motivo' => trim($_POST['motivo'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'Pendiente')
            ];
            $error = '';
            if ($datos['id_mascota'] === 0) $error = 'Campo requerido: id_mascota';
            elseif ($datos['id_cliente'] === 0) $error = 'Campo requerido: id_cliente';
            elseif ($datos['fecha_cita'] === '') $error = 'Campo requerido: fecha_cita';
            elseif ($datos['motivo'] === '') $error = 'Campo requerido: motivo';
            elseif (mb_strlen($datos['motivo']) > 255) $error = 'El motivo no debe superar 255 caracteres';
            elseif (!in_array($datos['estado'], ['Pendiente','Procesada','Cancelada'])) $error = 'El estado debe ser "Pendiente", "Procesada" o "Cancelada"';
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datos['fecha_cita'])) $error = 'La fecha debe tener formato YYYY-MM-DD HH:MM:SS';
            elseif (!$this->citaModel->mascotaExiste($datos['id_mascota'])) $error = 'Mascota no encontrada';
            elseif (!$this->citaModel->clienteExiste($datos['id_cliente'])) $error = 'Cliente no encontrado';
            elseif (!$this->citaModel->mascotaPerteneceACliente($datos['id_mascota'], $datos['id_cliente'])) $error = 'La mascota no pertenece al cliente especificado';
            if ($error) {
                $this->view('citas/editar', [
                    'titulo' => 'Editar Cita Médica',
                    'error' => $error,
                    'cita' => array_merge($cita0, $datos),
                    'mascotas' => $mascotas,
                    'clientes' => $clientes,
                    'usuarios' => $usuarios
                ]);
                return;
            }
            if ($this->citaModel->actualizar((int)$id, $datos)) {
                $_SESSION['success_message'] = 'Cita actualizada correctamente';
                header('Location: ' . APP_URL . '/cita');
                exit;
            } else {
                $this->view('citas/editar', [
                    'titulo' => 'Editar Cita Médica',
                    'error' => 'No se pudo actualizar la cita',
                    'cita' => array_merge($cita0, $datos),
                    'mascotas' => $mascotas,
                    'clientes' => $clientes,
                    'usuarios' => $usuarios
                ]);
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
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $año = isset($_GET['año']) ? (int)$_GET['año'] : (int)date('Y');
        
        // Validar mes y año
        if ($mes < 1 || $mes > 12) $mes = (int)date('m');
        if ($año < 2000 || $año > 2100) $año = (int)date('Y');
        
        $citas = $this->citaModel->obtenerCitasPorMes($mes, $año);
        
        // Organizar citas por día para el calendario
        $citasPorDia = [];
        foreach ($citas as $cita) {
            $fechaObj = new \DateTime($cita['fecha_cita']);
            $dia = (int)$fechaObj->format('d');
            if (!isset($citasPorDia[$dia])) {
                $citasPorDia[$dia] = [];
            }
            $citasPorDia[$dia][] = $cita;
        }
        
        // Calcular información del calendario
        $primerDia = mktime(0, 0, 0, $mes, 1, $año);
        $diasEnMes = (int)date('t', $primerDia);
        $diaSemanaInicio = (int)date('w', $primerDia); // 0 = domingo, 6 = sábado
        
        // Mes anterior y siguiente
        $mesAnterior = $mes - 1;
        $añoAnterior = $año;
        if ($mesAnterior < 1) {
            $mesAnterior = 12;
            $añoAnterior--;
        }
        
        $mesSiguiente = $mes + 1;
        $añoSiguiente = $año;
        if ($mesSiguiente > 12) {
            $mesSiguiente = 1;
            $añoSiguiente++;
        }
        
        $nombresMeses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        $data = [
            'titulo' => 'Calendario de Citas',
            'citas' => $citas,
            'citasPorDia' => $citasPorDia,
            'mes' => $mes,
            'año' => $año,
            'mesNombre' => $nombresMeses[$mes],
            'diasEnMes' => $diasEnMes,
            'diaSemanaInicio' => $diaSemanaInicio,
            'mesAnterior' => $mesAnterior,
            'añoAnterior' => $añoAnterior,
            'mesSiguiente' => $mesSiguiente,
            'añoSiguiente' => $añoSiguiente
        ];
        $this->view('citas/calendario', $data);
    }
}
