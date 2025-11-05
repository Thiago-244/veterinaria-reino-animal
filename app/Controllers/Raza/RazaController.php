<?php
namespace App\Controllers\Raza;

use App\Core\BaseController;

class RazaController extends BaseController {

    private $razaModel;

    public function __construct() {
        $this->razaModel = $this->model('RazaModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $id_especie = isset($_GET['especie']) ? (int)$_GET['especie'] : 0;
        
        if ($id_especie > 0) {
            $razas = $this->razaModel->obtenerPorEspecie($id_especie);
        } elseif ($termino !== '') {
            $razas = $this->buscarRazas($termino);
        } else {
            $razas = $this->razaModel->obtenerTodas();
        }
        
        // Obtener especies para el filtro
        $especies = $this->razaModel->obtenerEspecies();
        
        $data = [
            'titulo' => 'Gestión de Razas',
            'razas' => $razas,
            'buscar' => $termino,
            'id_especie' => $id_especie,
            'especies' => $especies
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
            $datos = [
                'id_especie' => isset($_POST['id_especie']) ? (int)$_POST['id_especie'] : 0,
                'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : ''
            ];

            // Validaciones completas (iguales a ApiRazaController)
            $error = '';
            
            if (empty($datos['nombre'])) {
                $error = 'Campo requerido: nombre';
            } elseif (empty($datos['id_especie']) || $datos['id_especie'] <= 0) {
                $error = 'Campo requerido: id_especie';
            } elseif (strlen($datos['nombre']) > 50) {
                $error = 'El nombre no debe superar 50 caracteres';
            } elseif (strlen($datos['nombre']) < 2) {
                $error = 'El nombre debe tener al menos 2 caracteres';
            } elseif (!$this->razaModel->especieExiste($datos['id_especie'])) {
                $error = 'Especie no encontrada';
            } elseif ($this->razaModel->nombreExisteEnEspecie($datos['nombre'], $datos['id_especie'])) {
                $error = 'Ya existe una raza con ese nombre en la especie seleccionada';
            }

            if ($error) {
                $especies = $this->razaModel->obtenerEspecies();
                $this->view('razas/crear', [
                    'titulo' => 'Crear Raza',
                    'error' => $error,
                    'especies' => $especies,
                    'raza' => $datos
                ]);
                return;
            }

            if ($this->razaModel->crear($datos)) {
                $_SESSION['success_message'] = 'Raza creada correctamente';
                header('Location: ' . APP_URL . '/raza');
                exit;
            } else {
                $especies = $this->razaModel->obtenerEspecies();
                $this->view('razas/crear', [
                    'titulo' => 'Crear Raza',
                    'error' => 'No se pudo crear la raza',
                    'especies' => $especies,
                    'raza' => $datos
                ]);
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $raza = $this->razaModel->obtenerPorId((int)$id);
        if (!$raza) { 
            $_SESSION['error_message'] = 'Raza no encontrada';
            header('Location: ' . APP_URL . '/raza');
            exit;
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
            $existente = $this->razaModel->obtenerPorId((int)$id);
            if (!$existente) {
                $_SESSION['error_message'] = 'Raza no encontrada';
                header('Location: ' . APP_URL . '/raza');
                exit;
            }
            
            $datos = [
                'id_especie' => isset($_POST['id_especie']) ? (int)$_POST['id_especie'] : $existente['id_especie'],
                'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : $existente['nombre']
            ];

            // Validaciones completas (iguales a ApiRazaController)
            $error = '';
            
            if (empty($datos['nombre'])) {
                $error = 'Campo requerido: nombre';
            } elseif (empty($datos['id_especie']) || $datos['id_especie'] <= 0) {
                $error = 'Campo requerido: id_especie';
            } elseif (strlen($datos['nombre']) > 50) {
                $error = 'El nombre no debe superar 50 caracteres';
            } elseif (strlen($datos['nombre']) < 2) {
                $error = 'El nombre debe tener al menos 2 caracteres';
            } elseif (!$this->razaModel->especieExiste($datos['id_especie'])) {
                $error = 'Especie no encontrada';
            } elseif ($this->razaModel->nombreExisteEnEspecie($datos['nombre'], $datos['id_especie'], (int)$id)) {
                $error = 'Ya existe otra raza con ese nombre en la especie seleccionada';
            }

            if ($error) {
                $especies = $this->razaModel->obtenerEspecies();
                $this->view('razas/editar', [
                    'titulo' => 'Editar Raza',
                    'error' => $error,
                    'especies' => $especies,
                    'raza' => array_merge($existente, $datos)
                ]);
                return;
            }
            
            if ($this->razaModel->actualizar((int)$id, $datos)) {
                $_SESSION['success_message'] = 'Raza actualizada correctamente';
                header('Location: ' . APP_URL . '/raza');
                exit;
            } else {
                $especies = $this->razaModel->obtenerEspecies();
                $this->view('razas/editar', [
                    'titulo' => 'Editar Raza',
                    'error' => 'No se pudo actualizar la raza',
                    'especies' => $especies,
                    'raza' => array_merge($existente, $datos)
                ]);
            }
        }
    }

    /**
     * Elimina una raza.
     */
    public function eliminar($id) {
        $existente = $this->razaModel->obtenerPorId((int)$id);
        if (!$existente) {
            $_SESSION['error_message'] = 'Raza no encontrada';
            header('Location: ' . APP_URL . '/raza');
            exit;
        }
        
        if ($this->razaModel->eliminar((int)$id)) {
            $_SESSION['success_message'] = 'Raza eliminada correctamente';
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar la raza';
        }
        header('Location: ' . APP_URL . '/raza');
        exit;
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

    /**
     * Busca razas por nombre
     */
    private function buscarRazas($termino) {
        $db = new \App\Core\Database();
        $db->query("
            SELECT 
                r.id,
                r.nombre,
                r.id_especie,
                e.nombre as especie_nombre
            FROM razas r
            LEFT JOIN especies e ON r.id_especie = e.id
            WHERE r.nombre LIKE :termino
            ORDER BY e.nombre, r.nombre ASC
        ");
        $db->bind(':termino', '%' . $termino . '%');
        return $db->resultSet();
    }
}
