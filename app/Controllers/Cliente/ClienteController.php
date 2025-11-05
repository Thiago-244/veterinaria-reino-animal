<?php
namespace App\Controllers\Cliente;

use App\Core\BaseController;

class ClienteController extends BaseController {

    private $clienteModel;

    public function __construct() {
        $this->clienteModel = $this->model('ClienteModel');
    }

    public function index() {
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($termino !== '') {
            $clientes = $this->clienteModel->buscarClientes($termino);
        } else {
            $clientes = $this->clienteModel->obtenerTodos();
        }
        $data = [
            'titulo' => 'Gestión de Clientes',
            'clientes' => $clientes,
            'buscar' => $termino,
        ];
        $this->view('clientes/index', $data);
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function crear() {
        $data = [
            'titulo' => 'Crear Cliente'
        ];
        $this->view('clientes/crear', $data);
    }

    /**
     * Guarda el nuevo cliente en la base de datos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'dni' => trim($_POST['dni']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
            ];
            $error = '';
            $required = ['dni','nombre','apellido','telefono'];
            foreach ($required as $field) {
                if (!isset($datos[$field]) || $datos[$field] === '') {
                    $error = "Campo requerido: $field";
                    break;
                }
            }
            if (!$error && !preg_match('/^\d{8}$/', $datos['dni'])) {
                $error = 'El DNI debe tener exactamente 8 dígitos numéricos';
            }
            if (!$error && !preg_match('/^\d{9}$/', $datos['telefono'])) {
                $error = 'El teléfono debe tener exactamente 9 dígitos numéricos';
            }
            if (!$error && $datos['email'] !== '' && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'El email no tiene un formato válido';
            }
            if (!$error && (mb_strlen($datos['nombre']) > 40 || mb_strlen($datos['apellido']) > 40)) {
                $error = 'Nombre y Apellido no deben superar 40 caracteres';
            }
            if (!$error && $this->clienteModel->obtenerPorDni($datos['dni'])) {
                $error = 'El DNI ya existe';
            }
            if (!$error && $datos['email'] !== '' && $this->clienteModel->obtenerPorEmail($datos['email'])) {
                $error = 'El email ya existe';
            }
            if ($error) {
                $this->view('clientes/crear', [
                    'titulo' => 'Crear Cliente',
                    'error' => $error,
                    'cliente' => $datos
                ]);
                return;
            }
            try {
                if ($this->clienteModel->crear($datos)) {
                    $_SESSION['success_message'] = 'Cliente creado correctamente';
                    header('Location: ' . APP_URL . '/cliente');
                    exit;
                } else {
                    $this->view('clientes/crear', [
                        'titulo' => 'Crear Cliente',
                        'error' => 'No se pudo crear el cliente',
                        'cliente' => $datos
                    ]);
                }
            } catch (\PDOException $e) {
                $mensaje = 'Ocurrió un error inesperado.';
                if(strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    if(strpos($e->getMessage(), "for key 'dni'") !== false) $mensaje = 'El DNI ya existe.';
                    if(strpos($e->getMessage(), "for key 'email'") !== false) $mensaje = 'El email ya existe.';
                }
                $this->view('clientes/crear', [
                    'titulo' => 'Crear Cliente',
                    'error' => $mensaje,
                    'cliente' => $datos
                ]);
            }
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id) {
        $cliente = $this->clienteModel->obtenerPorId((int)$id);
        if (!$cliente) { die('Cliente no encontrado'); }
        $data = [ 'titulo' => 'Editar Cliente', 'cliente' => $cliente ];
        $this->view('clientes/editar', $data);
    }

    /**
     * Procesa la actualización del cliente.
     */
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'dni' => trim($_POST['dni']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
            ];
            $error = '';
            $required = ['dni','nombre','apellido','telefono'];
            foreach ($required as $field) {
                if (!isset($datos[$field]) || $datos[$field] === '') {
                    $error = "Campo requerido: $field";
                    break;
                }
            }
            if (!$error && !preg_match('/^\d{8}$/', $datos['dni'])) {
                $error = 'El DNI debe tener exactamente 8 dígitos numéricos';
            }
            if (!$error && !preg_match('/^\d{9}$/', $datos['telefono'])) {
                $error = 'El teléfono debe tener exactamente 9 dígitos numéricos';
            }
            if (!$error && $datos['email'] !== '' && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'El email no tiene un formato válido';
            }
            if (!$error && (mb_strlen($datos['nombre']) > 40 || mb_strlen($datos['apellido']) > 40)) {
                $error = 'Nombre y Apellido no deben superar 40 caracteres';
            }
            $existeDni = $this->clienteModel->obtenerPorDni($datos['dni']);
            if (!$error && $existeDni && (int)$existeDni['id'] !== (int)$id) {
                $error = 'El DNI ya existe';
            }
            $existeEmail = $datos['email'] !== '' ? $this->clienteModel->obtenerPorEmail($datos['email']) : null;
            if (!$error && $existeEmail && (int)$existeEmail['id'] !== (int)$id) {
                $error = 'El email ya existe';
            }
            if ($error) {
                $this->view('clientes/editar', [
                    'titulo' => 'Editar Cliente',
                    'error' => $error,
                    'cliente' => $datos
                ]);
                return;
            }
            try {
                if ($this->clienteModel->actualizar((int)$id, $datos)) {
                    $_SESSION['success_message'] = 'Cliente actualizado correctamente';
                    header('Location: ' . APP_URL . '/cliente');
                    exit;
                } else {
                    $this->view('clientes/editar', [
                        'titulo' => 'Editar Cliente',
                        'error' => 'No se pudo actualizar el cliente',
                        'cliente' => $datos
                    ]);
                }
            } catch (\PDOException $e) {
                $mensaje = 'Ocurrió un error inesperado.';
                if(strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    if(strpos($e->getMessage(), "for key 'dni'") !== false) $mensaje = 'El DNI ya existe.';
                    if(strpos($e->getMessage(), "for key 'email'") !== false) $mensaje = 'El email ya existe.';
                }
                $this->view('clientes/editar', [
                    'titulo' => 'Editar Cliente',
                    'error' => $mensaje,
                    'cliente' => $datos
                ]);
            }
        }
    }

    /**
     * Elimina un cliente.
     */
    public function eliminar($id) {
        if ($this->clienteModel->eliminar((int)$id)) {
            header('Location: ' . APP_URL . '/cliente');
        } else {
            die('No se pudo eliminar el cliente.');
        }
    }
}