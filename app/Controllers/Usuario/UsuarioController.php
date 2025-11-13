<?php
namespace App\Controllers\Usuario;

use App\Core\BaseController;

/**
 * Controlador para la gestión de usuarios (CRUD - Crear, Leer, Actualizar, Eliminar)
 * e interacción con la vista y el modelo de usuarios.
 */
class UsuarioController extends BaseController {

    private $usuarioModel;

    // El constructor inicializa el modelo de usuario, que maneja la lógica de la base de datos.
    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    /**
     * Función principal que gestiona la vista de la lista de usuarios.
     * Muestra la lista de usuarios, maneja la búsqueda, los filtros y los mensajes de sesión.
     */
    public function index() {
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $rol_filtro = isset($_GET['rol']) ? trim($_GET['rol']) : '';
        $estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

        // Comprueba si hay mensajes de éxito en la URL y los establece en la sesión.
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
        // Determina si se debe realizar una búsqueda o solo aplicar filtros.
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
        // Renderiza la vista 'usuarios/index' con los datos preparados.
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
     * Procesa los datos del formulario POST, valida y guarda el nuevo usuario en la base de datos.
     * Si falla, redirige al formulario con errores; si tiene éxito, redirige a la lista.
     */
    public function guardar() {
        // Solo procesa si la solicitud es de tipo POST.
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoger los datos del formulario
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'rol' => trim($_POST['rol'] ?? ''),
                // Convierte el estado de checkbox a 1 (activo) o 0 (inactivo).
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];

            // Validaciones completas (iguales a ApiUsuarioController)
            $errores = [];

            // Lógica de validación para nombre, email, contraseña, rol y estado.
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

            // Verificar si ya existe el email en la base de datos.
            if (empty($errores) && $this->usuarioModel->emailExiste($datos['email'])) {
                $errores[] = 'Ya existe un usuario con ese email';
            }

            // Si hay errores, redirigir con mensaje y volver a mostrar el formulario de 'crear'.
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

            // Intentar crear el usuario a través del modelo.
            if ($this->usuarioModel->crear($datos)) {
                // Redirecciona con un código de éxito si la creación es exitosa.
                $_SESSION['success_message'] = 'Usuario creado correctamente';
                header('Location: ' . APP_URL . '/usuario?success=1');
                exit;
            } else {
                // Muestra la vista de creación nuevamente con un error si falla la creación en el modelo.
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
     * Muestra el formulario de edición de un usuario específico.
     * Busca el usuario por ID y lo pasa a la vista.
     */
    public function editar($id) {
        $usuario = $this->usuarioModel->obtenerPorId((int)$id);
        // Verifica si el usuario existe, si no, redirige con un error
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
     * Procesa los datos del formulario POST, valida y actualiza el usuario en la base de datos.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $existente = $this->usuarioModel->obtenerPorId((int)$id);
            if (!$existente) {
                $_SESSION['error_message'] = 'Usuario no encontrado';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            }

            // Recoger los datos del formulario, excluyendo la contraseña si está vacía.
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'rol' => trim($_POST['rol'] ?? ''),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];

           // Si se proporciona una nueva contraseña, se añade a los datos para la actualización.
            if (!empty($_POST['password'])) {
                $datos['password'] = $_POST['password'];
            }

            // Se realizan las validaciones del formulario de edición
            $errores = [];

            // ... [Lógica de validación similar a 'guardar', adaptada para la edición] ...

            // Condición que verifica si el nombre del usuario no sobrepasa 100 caracteres al actualizar
            if (empty($datos['nombre'])) {
                $errores[] = 'El nombre del usuario es requerido';
            } elseif (strlen($datos['nombre']) > 100) {
                // Validación de longitud máxima para el nombre.
                $errores[] = 'El nombre no debe superar 100 caracteres';
            }

            // Validaciones para el campo 'email'
            if (empty($datos['email'])) {
                $errores[] = 'El email es requerido';
            } elseif (strlen($datos['email']) > 100) {
                // Validación de longitud máxima para el email.
                $errores[] = 'El email no debe superar 100 caracteres';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                // Verifica que el formato del email sea válido.
                $errores[] = 'El email no tiene un formato válido';
            }

            // Validaciones para el campo 'rol'
            if (empty($datos['rol'])) {
                $errores[] = 'El rol es requerido';
            } elseif (!in_array($datos['rol'], ['Administrador', 'Editor', 'Consultor'])) {
                // Asegura que el rol seleccionado sea uno de los permitidos.
                $errores[] = 'El rol debe ser Administrador, Editor o Consultor';
            }

            // Validación para el campo 'estado'
            if (!in_array($datos['estado'], [0, 1])) {
                // El estado solo puede ser 0 (inactivo) o 1 (activo).
                $errores[] = 'El estado debe ser 0 (inactivo) o 1 (activo)';
            }
            // Validación de longitud mínima para la contraseña si ha sido proporcionada.
            if (isset($datos['password']) && strlen($datos['password']) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            }

            // Verificar si ya existe otro con el mismo email
            if (empty($errores) && $this->usuarioModel->emailExiste($datos['email'], (int)$id)) {
                // Comprueba si el email ya está registrado por otro usuario (excluyendo el actual).
                $errores[] = 'Ya existe otro usuario con ese email';
            }

            // Si hay errores, redirigir con mensaje
            if (!empty($errores)) {
                // Almacena y prepara el mensaje de error para mostrar en la vista de edición.
                $_SESSION['error_message'] = implode(', ', $errores);
                $data = [
                    'titulo' => 'Editar Usuario',
                    // Combina los datos existentes y los nuevos datos para rellenar el formulario.
                    'usuario' => array_merge($existente, $datos),
                    'roles' => ['Administrador', 'Editor', 'Consultor'],
                    'error' => $_SESSION['error_message']
                ];
                $this->view('usuarios/editar', $data);
                return;
            }

            // Intentar actualizar
            if ($this->usuarioModel->actualizar((int)$id, $datos)) {
                // Si la actualización es exitosa, redirige a la lista con código de éxito (success=2).
                $_SESSION['success_message'] = 'Usuario actualizado correctamente';
                header('Location: ' . APP_URL . '/usuario?success=2');
                exit;
            } else {
                // Si falla la actualización en el modelo, muestra la vista de edición con un error.
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
     * @param int $id El ID del usuario a eliminar.
     */
    public function eliminar($id) {
        // Obtiene el usuario para verificar su existencia antes de intentar eliminar.
        $existente = $this->usuarioModel->obtenerPorId((int)$id);
        if (!$existente) {
            // Si el usuario no existe, establece un mensaje de error y redirige.
            $_SESSION['error_message'] = 'Usuario no encontrado';
            header('Location: ' . APP_URL . '/usuario');
            exit;
        }

        // Llama al método del modelo para eliminar el registro.
        if ($this->usuarioModel->eliminar((int)$id)) {
            // Si la eliminación es exitosa, redirige a la lista con código de éxito (success=3).
            $_SESSION['success_message'] = 'Usuario eliminado correctamente';
            header('Location: ' . APP_URL . '/usuario?success=3');
            exit;
        } else {
            // Si falla la eliminación, establece un mensaje de error y redirige.
            $_SESSION['error_message'] = 'No se pudo eliminar el usuario';
            header('Location: ' . APP_URL . '/usuario');
            exit;
        }
    }

    /**
     * Cambia el estado del usuario (activar/desactivar)
     * @param int $id El ID del usuario.
     */
    public function cambiarEstado($id) {
        // Asegura que la solicitud sea POST y que el campo 'estado' esté presente.
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado'])) {
            $estado = (int)$_POST['estado'];
            if (!in_array($estado, [0, 1])) {
                // Valida que el valor de estado sea 0 o 1.
                $_SESSION['error_message'] = 'Estado inválido';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            }

            // Llama al método del modelo para actualizar el estado.
            if ($this->usuarioModel->cambiarEstado((int)$id, $estado)) {
                // Muestra éxito y redirige.
                $_SESSION['success_message'] = 'Estado del usuario actualizado correctamente';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            } else {
                // Muestra error y redirige si el cambio falla.
                $_SESSION['error_message'] = 'No se pudo cambiar el estado del usuario';
                header('Location: ' . APP_URL . '/usuario');
                exit;
            }
        }
    }

    /**
     * Busca usuarios por término (nombre o email) y filtra por rol y estado.
     * @param string $termino Término de búsqueda.
     * @param string $rol Filtro de rol.
     * @param string $estado Filtro de estado (0 o 1).
     * @return array Lista de usuarios que cumplen con los criterios.
     */
    private function buscarUsuarios($termino, $rol = '', $estado = '') {
        $db = new \App\Core\Database();
        $sql = "
            SELECT id, nombre, email, rol, estado
            FROM usuarios
            WHERE (nombre LIKE :termino OR email LIKE :termino)
        ";

        // Agrega la condición de filtro por rol si se proporciona.
        if (!empty($rol)) {
            $sql .= " AND rol = :rol";
        }
        // Agrega la condición de filtro por estado si se proporciona.
        if ($estado !== '') {
            $sql .= " AND estado = :estado";
        }

        $sql .= " ORDER BY nombre ASC";

        $db->query($sql);
        // Vincula el término de búsqueda con comodines (%) para búsqueda parcial.
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
     * Obtiene usuarios con filtros de rol y estado.
     * @param string $rol Filtro de rol.
     * @param string $estado Filtro de estado (0 o 1).
     * @return array Lista de usuarios que cumplen con los criterios.
     */
    private function obtenerUsuariosFiltrados($rol = '', $estado = '') {
        $db = new \App\Core\Database();
        // Base de la consulta, WHERE 1=1 permite añadir condiciones fácilmente.
        $sql = "SELECT id, nombre, email, rol, estado FROM usuarios WHERE 1=1";

        // Agrega la condición de filtro por rol si se proporciona.
        if (!empty($rol)) {
            $sql .= " AND rol = :rol";
        }
        // Agrega la condición de filtro por estado si se proporciona.
        if ($estado !== '') {
            $sql .= " AND estado = :estado";
        }

        $sql .= " ORDER BY nombre ASC";

        $db->query($sql);
        // Vincula los valores a los parámetros de la consulta SQL.
        if (!empty($rol)) {
            $db->bind(':rol', $rol);
        }
        if ($estado !== '') {
            $db->bind(':estado', (int)$estado);
        }

        return $db->resultSet();
    }
}