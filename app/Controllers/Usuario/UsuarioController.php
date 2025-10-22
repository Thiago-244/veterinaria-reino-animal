<?php
namespace App\Controllers\Usuario;

use App\Core\BaseController;

class UsuarioController extends BaseController {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    public function index() {
        $usuarios = $this->usuarioModel->obtenerTodos();
        $data = [
            'titulo' => 'Gestión de Usuarios',
            'usuarios' => $usuarios 
        ];
        $this->view('usuarios/index', $data);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function crear() {
        $data = [
            'titulo' => 'Crear Usuario',
            'roles' => ['Administrador', 'Editor', 'Consultor']
        ];
        $this->view('usuarios/crear', $data);
    }

    /**
     * Guarda el nuevo usuario en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recoger los datos del formulario
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'rol' => trim($_POST['rol']),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];

            // 2. Validaciones básicas
            if (empty($datos['nombre'])) {
                die('El nombre del usuario es requerido.');
            }

            if (empty($datos['email'])) {
                die('El email es requerido.');
            }

            if (empty($datos['password'])) {
                die('La contraseña es requerida.');
            }

            if (empty($datos['rol'])) {
                die('El rol es requerido.');
            }

            // 3. Validaciones de formato
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                die('El email no tiene un formato válido.');
            }

            if (strlen($datos['password']) < 6) {
                die('La contraseña debe tener al menos 6 caracteres.');
            }

            if (!in_array($datos['rol'], ['Administrador', 'Editor', 'Consultor'])) {
                die('El rol seleccionado no es válido.');
            }

            // 4. Verificar si ya existe
            if ($this->usuarioModel->emailExiste($datos['email'])) {
                die('Ya existe un usuario con ese email.');
            }

            // 5. Llamar al método del modelo para guardar
            if ($this->usuarioModel->crear($datos)) {
                // 6. Redirigir al listado de usuarios
                header('Location: ' . APP_URL . '/usuario');
            } else {
                die('Algo salió mal al guardar el usuario.');
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $usuario = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$usuario) { 
            die('Usuario no encontrado'); 
        }
        
        $data = [
            'titulo' => 'Editar Usuario',
            'usuario' => $usuario,
            'roles' => ['Administrador', 'Editor', 'Consultor']
        ];
        $this->view('usuarios/editar', $data);
    }

    /**
     * Procesa la actualización del usuario.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'email' => trim($_POST['email']),
                'rol' => trim($_POST['rol']),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];

            // Si se proporciona una nueva contraseña
            if (!empty($_POST['password'])) {
                $datos['password'] = $_POST['password'];
            }

            // Validaciones
            if (empty($datos['nombre'])) {
                die('El nombre del usuario es requerido.');
            }

            if (empty($datos['email'])) {
                die('El email es requerido.');
            }

            if (empty($datos['rol'])) {
                die('El rol es requerido.');
            }

            // Validaciones de formato
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                die('El email no tiene un formato válido.');
            }

            if (isset($datos['password']) && strlen($datos['password']) < 6) {
                die('La contraseña debe tener al menos 6 caracteres.');
            }

            if (!in_array($datos['rol'], ['Administrador', 'Editor', 'Consultor'])) {
                die('El rol seleccionado no es válido.');
            }

            // Verificar si ya existe otro con el mismo email
            if ($this->usuarioModel->emailExiste($datos['email'], (int)$id)) {
                die('Ya existe otro usuario con ese email.');
            }
            
            if ($this->usuarioModel->actualizar((int)$id, $datos)) {
                header('Location: ' . APP_URL . '/usuario');
            } else {
                die('Algo salió mal al actualizar el usuario.');
            }
        }
    }

    /**
     * Elimina un usuario.
     */
    public function eliminar($id) {
        if ($this->usuarioModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/usuario');
        } else {
            die('No se pudo eliminar el usuario.');
        }
    }

    /**
     * Cambia el estado del usuario (activar/desactivar)
     */
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado'])) {
            $estado = (int)$_POST['estado'];
            if ($this->usuarioModel->cambiarEstado((int)$id, $estado)) {
                header('Location: ' . APP_URL . '/usuario');
            } else {
                die('No se pudo cambiar el estado del usuario.');
            }
        }
    }
}
