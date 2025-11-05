<?php
namespace App\Controllers\Usuario;

use App\Core\BaseController;

class UsuarioController extends BaseController {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    public function index() {
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $rol_filtro = isset($_GET['rol']) ? trim($_GET['rol']) : '';
        $estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
        
        // Verificar mensajes de sesión
        if (isset($_GET['success'])) {
            if ($_GET['success'] == '1') {
                $_SESSION['success_message'] = 'Usuario creado correctamente';
            } elseif ($_GET['success'] == '2') {
                $_SESSION['success_message'] = 'Usuario actualizado correctamente';
            } elseif ($_GET['success'] == '3') {
                $_SESSION['success_message'] = 'Usuario eliminado correctamente';
            }
        }
        
        $usuarios = [];
        if (!empty($buscar)) {
            $usuarios = $this->buscarUsuarios($buscar, $rol_filtro, $estado_filtro);
        } else {
            $usuarios = $this->obtenerUsuariosFiltrados($rol_filtro, $estado_filtro);
        }
        
        $data = [
            'titulo' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'buscar' => $buscar,
            'rol_filtro' => $rol_filtro,
            'estado_filtro' => $estado_filtro,
            'roles' => ['Administrador', 'Editor', 'Consultor']
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
            // Recoger los datos del formulario
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'rol' => trim($_POST['rol'] ?? ''),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];

            // Validaciones completas (iguales a ApiUsuarioController)
            $errores = [];
            
            if (empty($datos['nombre'])) {
                $errores[] = 'El nombre del usuario es requerido';
            } elseif (strlen($datos['nombre']) > 100) {
                $errores[] = 'El nombre no debe superar 100 caracteres';
            }

            if (empty($datos['email'])) {
                $errores[] = 'El email es requerido';
            } elseif (strlen($datos['email']) > 100) {
                $errores[] = 'El email no debe superar 100 caracteres';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no tiene un formato válido';
            }

            if (empty($datos['password'])) {
                $errores[] = 'La contraseña es requerida';
            } elseif (strlen($datos['password']) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            }

            if (empty($datos['rol'])) {
                $errores[] = 'El rol es requerido';
            } elseif (!in_array($datos['rol'], ['Administrador', 'Editor', 'Consultor'])) {
                $errores[] = 'El rol debe ser Administrador, Editor o Consultor';
            }

            if (!in_array($datos['estado'], [0, 1])) {
                $errores[] = 'El estado debe ser 0 (inactivo) o 1 (activo)';
            }

            // Verificar si ya existe
            if (empty($errores) && $this->usuarioModel->emailExiste($datos['email'])) {
                $errores[] = 'Ya existe un usuario con ese email';
            }

            // Si hay errores, redirigir con mensaje
            if (!empty($errores)) {
                $_SESSION['error_message'] = implode(', ', $errores);
                $data = [
                    'titulo' => 'Crear Usuario',
                    'roles' => ['Administrador', 'Editor', 'Consultor'],
                    'error' => $_SESSION['error_message'],
                    'usuario' => $datos
                ];
                $this->view('usuarios/crear', $data);
                return;
            }

            // Intentar crear el usuario
            if ($this->usuarioModel->crear($datos)) {
                $_SESSION['success_message'] = 'Usuario creado correctamente';
                header('Location: ' . APP_URL . '/usuario?success=1');
                exit;
            } else {
                $_SESSION['error_message'] = 'No se pudo crear el usuario';
                $data = [
                    'titulo' => 'Crear Usuario',
                    'roles' => ['Administrador', 'Editor', 'Consultor'],
                    'error' => $_SESSION['error_message'],
                    'usuario' => $datos
                ];
                $this->view('usuarios/crear', $data);
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $usuario = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$usuario) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            header('Location: ' . APP_URL . '/usuario');
            exit;
        }
        
        $data = [
            'titulo' => 'Editar Usuario',
            'usuario' => $usuario,
            'roles' => ['Administrador', 'Editor', 'Consultor'],
            'error' => isset($_SESSION['error_message']) ? $_SESSION['error_message'] : ''
        ];
        unset($_SESSION['error_message']);
        $this->view('usuarios/editar', $data);
    }

    /**
     * Procesa la actualización del usuario.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $existente = $this->usuarioModel->obtenerPorId((int)$id);
            if (!$existente) {
                $_SESSION['error_message'] = 'Usuario no encontrado';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            }
            
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'rol' => trim($_POST['rol'] ?? ''),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];

            // Si se proporciona una nueva contraseña
            if (!empty($_POST['password'])) {
                $datos['password'] = $_POST['password'];
            }

            // Validaciones completas (iguales a ApiUsuarioController)
            $errores = [];
            
            if (empty($datos['nombre'])) {
                $errores[] = 'El nombre del usuario es requerido';
            } elseif (strlen($datos['nombre']) > 100) {
                $errores[] = 'El nombre no debe superar 100 caracteres';
            }

            if (empty($datos['email'])) {
                $errores[] = 'El email es requerido';
            } elseif (strlen($datos['email']) > 100) {
                $errores[] = 'El email no debe superar 100 caracteres';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no tiene un formato válido';
            }

            if (empty($datos['rol'])) {
                $errores[] = 'El rol es requerido';
            } elseif (!in_array($datos['rol'], ['Administrador', 'Editor', 'Consultor'])) {
                $errores[] = 'El rol debe ser Administrador, Editor o Consultor';
            }

            if (!in_array($datos['estado'], [0, 1])) {
                $errores[] = 'El estado debe ser 0 (inactivo) o 1 (activo)';
            }

            if (isset($datos['password']) && strlen($datos['password']) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            }

            // Verificar si ya existe otro con el mismo email
            if (empty($errores) && $this->usuarioModel->emailExiste($datos['email'], (int)$id)) {
                $errores[] = 'Ya existe otro usuario con ese email';
            }

            // Si hay errores, redirigir con mensaje
            if (!empty($errores)) {
                $_SESSION['error_message'] = implode(', ', $errores);
                $data = [
                    'titulo' => 'Editar Usuario',
                    'usuario' => array_merge($existente, $datos),
                    'roles' => ['Administrador', 'Editor', 'Consultor'],
                    'error' => $_SESSION['error_message']
                ];
                $this->view('usuarios/editar', $data);
                return;
            }

            // Intentar actualizar
            if ($this->usuarioModel->actualizar((int)$id, $datos)) {
                $_SESSION['success_message'] = 'Usuario actualizado correctamente';
                header('Location: ' . APP_URL . '/usuario?success=2');
                exit;
            } else {
                $_SESSION['error_message'] = 'No se pudo actualizar el usuario';
                $data = [
                    'titulo' => 'Editar Usuario',
                    'usuario' => $existente,
                    'roles' => ['Administrador', 'Editor', 'Consultor'],
                    'error' => $_SESSION['error_message']
                ];
                $this->view('usuarios/editar', $data);
            }
        }
    }

    /**
     * Elimina un usuario.
     */
    public function eliminar($id) {
        $existente = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$existente) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            header('Location: ' . APP_URL . '/usuario');
            exit;
        }
        
        if ($this->usuarioModel->eliminar((int)$id)) {
            $_SESSION['success_message'] = 'Usuario eliminado correctamente';
            header('Location: ' . APP_URL . '/usuario?success=3');
            exit;
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar el usuario';
            header('Location: ' . APP_URL . '/usuario');
            exit;
        }
    }

    /**
     * Cambia el estado del usuario (activar/desactivar)
     */
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado'])) {
            $estado = (int)$_POST['estado'];
            if (!in_array($estado, [0, 1])) {
                $_SESSION['error_message'] = 'Estado inválido';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            }
            
            if ($this->usuarioModel->cambiarEstado((int)$id, $estado)) {
                $_SESSION['success_message'] = 'Estado del usuario actualizado correctamente';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            } else {
                $_SESSION['error_message'] = 'No se pudo cambiar el estado del usuario';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            }
        }
    }

    /**
     * Busca usuarios por término y filtra por rol y estado
     */
    private function buscarUsuarios($termino, $rol = '', $estado = '') {
        $db = new \App\Core\Database();
        $sql = "
            SELECT id, nombre, email, rol, estado 
            FROM usuarios 
            WHERE (nombre LIKE :termino OR email LIKE :termino)
        ";
        
        if (!empty($rol)) {
            $sql .= " AND rol = :rol";
        }
        if ($estado !== '') {
            $sql .= " AND estado = :estado";
        }
        
        $sql .= " ORDER BY nombre ASC";
        
        $db->query($sql);
        $db->bind(':termino', '%' . $termino . '%');
        if (!empty($rol)) {
            $db->bind(':rol', $rol);
        }
        if ($estado !== '') {
            $db->bind(':estado', (int)$estado);
        }
        
        return $db->resultSet();
    }

    /**
     * Obtiene usuarios con filtros de rol y estado
     */
    private function obtenerUsuariosFiltrados($rol = '', $estado = '') {
        $db = new \App\Core\Database();
        $sql = "SELECT id, nombre, email, rol, estado FROM usuarios WHERE 1=1";
        
        if (!empty($rol)) {
            $sql .= " AND rol = :rol";
        }
        if ($estado !== '') {
            $sql .= " AND estado = :estado";
        }
        
        $sql .= " ORDER BY nombre ASC";
        
        $db->query($sql);
        if (!empty($rol)) {
            $db->bind(':rol', $rol);
        }
        if ($estado !== '') {
            $db->bind(':estado', (int)$estado);
        }
        
        return $db->resultSet();
    }
}
