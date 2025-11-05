<?php
namespace App\Controllers\Especie;

use App\Core\BaseController;

class EspecieController extends BaseController {

    private $especieModel;

    public function __construct() {
        $this->especieModel = $this->model('EspecieModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        
        if ($termino !== '') {
            $especies = $this->buscarEspecies($termino);
        } else {
            $especies = $this->especieModel->obtenerConRazas();
        }
        
        $data = [
            'titulo' => 'Gestión de Especies',
            'especies' => $especies,
            'buscar' => $termino
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
            $datos = [
                'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : ''
            ];

            // Validaciones completas (iguales a ApiEspecieController)
            $error = '';
            
            if (empty($datos['nombre'])) {
                $error = 'Campo requerido: nombre';
            } elseif (strlen($datos['nombre']) > 50) {
                $error = 'El nombre no debe superar 50 caracteres';
            } elseif (strlen($datos['nombre']) < 2) {
                $error = 'El nombre debe tener al menos 2 caracteres';
            } elseif ($this->especieModel->nombreExiste($datos['nombre'])) {
                $error = 'Ya existe una especie con ese nombre';
            }

            if ($error) {
                $this->view('especies/crear', [
                    'titulo' => 'Crear Especie',
                    'error' => $error,
                    'especie' => $datos
                ]);
                return;
            }

            if ($this->especieModel->crear($datos)) {
                $_SESSION['success_message'] = 'Especie creada correctamente';
                header('Location: ' . APP_URL . '/especie');
                exit;
            } else {
                $this->view('especies/crear', [
                    'titulo' => 'Crear Especie',
                    'error' => 'No se pudo crear la especie',
                    'especie' => $datos
                ]);
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $especie = $this->especieModel->obtenerPorId((int)$id);
        if (!$especie) { 
            $_SESSION['error_message'] = 'Especie no encontrada';
            header('Location: ' . APP_URL . '/especie');
            exit;
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
            $existente = $this->especieModel->obtenerPorId((int)$id);
            if (!$existente) {
                $_SESSION['error_message'] = 'Especie no encontrada';
                header('Location: ' . APP_URL . '/especie');
                exit;
            }
            
            $datos = [
                'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : $existente['nombre']
            ];

            // Validaciones completas (iguales a ApiEspecieController)
            $error = '';
            
            if (empty($datos['nombre'])) {
                $error = 'Campo requerido: nombre';
            } elseif (strlen($datos['nombre']) > 50) {
                $error = 'El nombre no debe superar 50 caracteres';
            } elseif (strlen($datos['nombre']) < 2) {
                $error = 'El nombre debe tener al menos 2 caracteres';
            } elseif ($this->especieModel->nombreExiste($datos['nombre'], (int)$id)) {
                $error = 'Ya existe otra especie con ese nombre';
            }

            if ($error) {
                $this->view('especies/editar', [
                    'titulo' => 'Editar Especie',
                    'error' => $error,
                    'especie' => array_merge($existente, $datos)
                ]);
                return;
            }
            
            if ($this->especieModel->actualizar((int)$id, $datos)) {
                $_SESSION['success_message'] = 'Especie actualizada correctamente';
                header('Location: ' . APP_URL . '/especie');
                exit;
            } else {
                $this->view('especies/editar', [
                    'titulo' => 'Editar Especie',
                    'error' => 'No se pudo actualizar la especie',
                    'especie' => array_merge($existente, $datos)
                ]);
            }
        }
    }

    /**
     * Elimina una especie.
     */
    public function eliminar($id) {
        $existente = $this->especieModel->obtenerPorId((int)$id);
        if (!$existente) {
            $_SESSION['error_message'] = 'Especie no encontrada';
            header('Location: ' . APP_URL . '/especie');
            exit;
        }
        
        // Verificar si la especie tiene razas asociadas
        $especiesConRazas = $this->especieModel->obtenerConRazas();
        foreach ($especiesConRazas as $e) {
            if ($e['id'] == $id && $e['total_razas'] > 0) {
                $_SESSION['error_message'] = 'No se puede eliminar la especie porque tiene razas asociadas';
                header('Location: ' . APP_URL . '/especie');
                exit;
            }
        }

        if ($this->especieModel->eliminar((int)$id)) {
            $_SESSION['success_message'] = 'Especie eliminada correctamente';
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar la especie';
        }
        header('Location: ' . APP_URL . '/especie');
        exit;
    }

    /**
     * Busca especies por nombre
     */
    private function buscarEspecies($termino) {
        $db = new \App\Core\Database();
        $db->query("
            SELECT 
                e.id,
                e.nombre as especie_nombre,
                COUNT(r.id) as total_razas
            FROM especies e
            LEFT JOIN razas r ON e.id = r.id_especie
            WHERE e.nombre LIKE :termino
            GROUP BY e.id, e.nombre
            ORDER BY e.nombre ASC
        ");
        $db->bind(':termino', '%' . $termino . '%');
        return $db->resultSet();
    }
}
