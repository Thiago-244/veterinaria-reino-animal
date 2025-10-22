<?php
namespace App\Controllers\Cita;

use App\Core\BaseController;
use App\Models\CitaModel;

class ApiCitaController extends BaseController {

    private $citaModel;

    public function __construct() {
        $this->citaModel = $this->model('CitaModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apicita/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $citas = $this->citaModel->obtenerTodas();
        echo json_encode(['data' => $citas]);
    }

    // POST /apicita/crear
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            return;
        }

        $required = ['id_mascota', 'id_cliente', 'fecha_cita', 'motivo'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $id_mascota = (int)$payload['id_mascota'];
        $id_cliente = (int)$payload['id_cliente'];
        $fecha_cita = trim((string)$payload['fecha_cita']);
        $motivo = trim((string)$payload['motivo']);
        $estado = isset($payload['estado']) ? trim((string)$payload['estado']) : 'Pendiente';

        // Validaciones de formato y longitud
        if (strlen($motivo) > 255) {
            http_response_code(422);
            echo json_encode(['error' => 'El motivo no debe superar 255 caracteres']);
            return;
        }
        
        if (!in_array($estado, ['Pendiente', 'Procesada', 'Cancelada'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado debe ser "Pendiente", "Procesada" o "Cancelada"']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha_cita)) {
            http_response_code(422);
            echo json_encode(['error' => 'La fecha debe tener formato YYYY-MM-DD HH:MM:SS']);
            return;
        }

        // Validar que la mascota existe
        if (!$this->citaModel->mascotaExiste($id_mascota)) {
            http_response_code(404);
            echo json_encode(['error' => 'Mascota no encontrada']);
            return;
        }

        // Validar que el cliente existe
        if (!$this->citaModel->clienteExiste($id_cliente)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }

        // Verificar que la mascota pertenece al cliente
        if (!$this->citaModel->mascotaPerteneceACliente($id_mascota, $id_cliente)) {
            http_response_code(422);
            echo json_encode(['error' => 'La mascota no pertenece al cliente especificado']);
            return;
        }

        $ok = $this->citaModel->crear([
            'codigo' => $this->citaModel->generarCodigo(),
            'id_mascota' => $id_mascota,
            'id_cliente' => $id_cliente,
            'fecha_cita' => $fecha_cita,
            'motivo' => $motivo,
            'estado' => $estado
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Cita creada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la cita']);
        }
    }

    // PUT /apicita/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->citaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Cita no encontrada']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) { 
            http_response_code(400); 
            echo json_encode(['error' => 'JSON inválido']); 
            return; 
        }

        $id_mascota = isset($payload['id_mascota']) ? (int)$payload['id_mascota'] : $existente['id_mascota'];
        $id_cliente = isset($payload['id_cliente']) ? (int)$payload['id_cliente'] : $existente['id_cliente'];
        $fecha_cita = isset($payload['fecha_cita']) ? trim((string)$payload['fecha_cita']) : $existente['fecha_cita'];
        $motivo = isset($payload['motivo']) ? trim((string)$payload['motivo']) : $existente['motivo'];
        $estado = isset($payload['estado']) ? trim((string)$payload['estado']) : $existente['estado'];

        // Validaciones
        if (strlen($motivo) > 255) {
            http_response_code(422);
            echo json_encode(['error' => 'El motivo no debe superar 255 caracteres']);
            return;
        }
        
        if (!in_array($estado, ['Pendiente', 'Procesada', 'Cancelada'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado debe ser "Pendiente", "Procesada" o "Cancelada"']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha_cita)) {
            http_response_code(422);
            echo json_encode(['error' => 'La fecha debe tener formato YYYY-MM-DD HH:MM:SS']);
            return;
        }

        // Validar que la mascota existe
        if (!$this->citaModel->mascotaExiste($id_mascota)) {
            http_response_code(404);
            echo json_encode(['error' => 'Mascota no encontrada']);
            return;
        }

        // Validar que el cliente existe
        if (!$this->citaModel->clienteExiste($id_cliente)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            return;
        }

        // Verificar que la mascota pertenece al cliente
        if (!$this->citaModel->mascotaPerteneceACliente($id_mascota, $id_cliente)) {
            http_response_code(422);
            echo json_encode(['error' => 'La mascota no pertenece al cliente especificado']);
            return;
        }

        $ok = $this->citaModel->actualizar((int)$id, [
            'id_mascota' => $id_mascota,
            'id_cliente' => $id_cliente,
            'fecha_cita' => $fecha_cita,
            'motivo' => $motivo,
            'estado' => $estado
        ]);
        
        if ($ok) { 
            echo json_encode(['message' => 'Cita actualizada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apicita/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->citaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Cita no encontrada']); 
            return; 
        }
        
        $ok = $this->citaModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Cita eliminada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apicita/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $cita = $this->citaModel->obtenerPorId((int)$id);
        if (!$cita) {
            http_response_code(404);
            echo json_encode(['error' => 'Cita no encontrada']);
            return;
        }
        
        echo json_encode(['data' => $cita]);
    }

    // GET /apicita/por-cliente/{id_cliente}
    public function porCliente($id_cliente) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $citas = $this->citaModel->obtenerPorCliente((int)$id_cliente);
        echo json_encode(['data' => $citas]);
    }

    // GET /apicita/por-mascota/{id_mascota}
    public function porMascota($id_mascota) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $citas = $this->citaModel->obtenerPorMascota((int)$id_mascota);
        echo json_encode(['data' => $citas]);
    }

    // PUT /apicita/cambiar-estado/{id}
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['estado'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: estado']);
            return;
        }

        $estado = trim((string)$payload['estado']);
        if (!in_array($estado, ['Pendiente', 'Procesada', 'Cancelada'])) {
            http_response_code(422);
            echo json_encode(['error' => 'El estado debe ser "Pendiente", "Procesada" o "Cancelada"']);
            return;
        }

        $ok = $this->citaModel->cambiarEstado((int)$id, $estado);
        if ($ok) {
            echo json_encode(['message' => 'Estado de cita actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo cambiar el estado']);
        }
    }
}
