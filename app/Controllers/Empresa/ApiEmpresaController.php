<?php
namespace App\Controllers\Empresa;

use App\Core\BaseController;
use App\Models\EmpresaModel;

class ApiEmpresaController extends BaseController {

    private $empresaModel;

    public function __construct() {
        $this->empresaModel = $this->model('EmpresaModel');
        header('Content-Type: application/json; charset=utf-8');
    }

    // GET /apiempresa/listar
    public function listar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $empresas = $this->empresaModel->obtenerTodas();
        echo json_encode(['data' => $empresas]);
    }

    // POST /apiempresa/crear
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

        $required = ['nombre', 'ruc', 'direccion', 'telefono', 'email'];
        foreach ($required as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                http_response_code(422);
                echo json_encode(['error' => "Campo requerido: $field"]);
                return;
            }
        }

        // Normalización básica
        $nombre = trim((string)$payload['nombre']);
        $ruc = trim((string)$payload['ruc']);
        $direccion = trim((string)$payload['direccion']);
        $telefono = trim((string)$payload['telefono']);
        $email = trim((string)$payload['email']);
        $logo = isset($payload['logo']) ? trim((string)$payload['logo']) : null;
        $iva = isset($payload['iva']) ? (float)$payload['iva'] : 18.00;

        // Validaciones de formato y longitud
        if (strlen($nombre) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 100 caracteres']);
            return;
        }

        if (!preg_match('/^\d{11}$/', $ruc)) {
            http_response_code(422);
            echo json_encode(['error' => 'El RUC debe tener exactamente 11 dígitos']);
            return;
        }

        if (strlen($direccion) > 255) {
            http_response_code(422);
            echo json_encode(['error' => 'La dirección no debe superar 255 caracteres']);
            return;
        }

        if (strlen($telefono) > 15) {
            http_response_code(422);
            echo json_encode(['error' => 'El teléfono no debe superar 15 caracteres']);
            return;
        }

        if (strlen($email) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no debe superar 100 caracteres']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no tiene un formato válido']);
            return;
        }

        if ($iva < 0 || $iva > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El IVA debe estar entre 0 y 100']);
            return;
        }

        // Validaciones de unicidad
        if ($this->empresaModel->rucExiste($ruc)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe una empresa con ese RUC']);
            return;
        }

        if ($this->empresaModel->emailExiste($email)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe una empresa con ese email']);
            return;
        }

        $ok = $this->empresaModel->crear([
            'nombre' => $nombre,
            'ruc' => $ruc,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'email' => $email,
            'logo' => $logo,
            'iva' => $iva
        ]);

        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'Empresa creada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear la empresa']);
        }
    }

    // PUT /apiempresa/actualizar/{id}
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->empresaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Empresa no encontrada']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        if (!is_array($payload)) { 
            http_response_code(400); 
            echo json_encode(['error' => 'JSON inválido']); 
            return; 
        }

        $nombre = isset($payload['nombre']) ? trim((string)$payload['nombre']) : $existente['nombre'];
        $ruc = isset($payload['ruc']) ? trim((string)$payload['ruc']) : $existente['ruc'];
        $direccion = isset($payload['direccion']) ? trim((string)$payload['direccion']) : $existente['direccion'];
        $telefono = isset($payload['telefono']) ? trim((string)$payload['telefono']) : $existente['telefono'];
        $email = isset($payload['email']) ? trim((string)$payload['email']) : $existente['email'];
        $logo = isset($payload['logo']) ? trim((string)$payload['logo']) : $existente['logo'];
        $iva = isset($payload['iva']) ? (float)$payload['iva'] : $existente['iva'];

        // Validaciones
        if (strlen($nombre) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El nombre no debe superar 100 caracteres']);
            return;
        }

        if (!preg_match('/^\d{11}$/', $ruc)) {
            http_response_code(422);
            echo json_encode(['error' => 'El RUC debe tener exactamente 11 dígitos']);
            return;
        }

        if (strlen($direccion) > 255) {
            http_response_code(422);
            echo json_encode(['error' => 'La dirección no debe superar 255 caracteres']);
            return;
        }

        if (strlen($telefono) > 15) {
            http_response_code(422);
            echo json_encode(['error' => 'El teléfono no debe superar 15 caracteres']);
            return;
        }

        if (strlen($email) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no debe superar 100 caracteres']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['error' => 'El email no tiene un formato válido']);
            return;
        }

        if ($iva < 0 || $iva > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El IVA debe estar entre 0 y 100']);
            return;
        }

        // Verificar si ya existe otro con el mismo RUC
        if ($this->empresaModel->rucExiste($ruc, (int)$id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otra empresa con ese RUC']);
            return;
        }

        // Verificar si ya existe otro con el mismo email
        if ($this->empresaModel->emailExiste($email, (int)$id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otra empresa con ese email']);
            return;
        }

        $ok = $this->empresaModel->actualizar((int)$id, [
            'nombre' => $nombre,
            'ruc' => $ruc,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'email' => $email,
            'logo' => $logo,
            'iva' => $iva
        ]);
        
        if ($ok) { 
            echo json_encode(['message' => 'Empresa actualizada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo actualizar']); 
        }
    }

    // DELETE /apiempresa/eliminar/{id}
    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $existente = $this->empresaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Empresa no encontrada']); 
            return; 
        }
        
        $ok = $this->empresaModel->eliminar((int)$id);
        if ($ok) { 
            echo json_encode(['message' => 'Empresa eliminada']); 
        } else { 
            http_response_code(500); 
            echo json_encode(['error' => 'No se pudo eliminar']); 
        }
    }

    // GET /apiempresa/obtener/{id}
    public function obtener($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $empresa = $this->empresaModel->obtenerPorId((int)$id);
        if (!$empresa) {
            http_response_code(404);
            echo json_encode(['error' => 'Empresa no encontrada']);
            return;
        }
        
        echo json_encode(['data' => $empresa]);
    }

    // GET /apiempresa/principal
    public function principal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $empresa = $this->empresaModel->obtenerPrincipal();
        if (!$empresa) {
            http_response_code(404);
            echo json_encode(['error' => 'No hay empresa principal configurada']);
            return;
        }
        
        echo json_encode(['data' => $empresa]);
    }

    // GET /apiempresa/estadisticas
    public function estadisticas() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $estadisticas = $this->empresaModel->obtenerEstadisticas();
        echo json_encode(['data' => $estadisticas]);
    }

    // PUT /apiempresa/actualizar-logo/{id}
    public function actualizarLogo($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->empresaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Empresa no encontrada']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['logo'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: logo']);
            return;
        }

        $logo = trim((string)$payload['logo']);
        
        $ok = $this->empresaModel->actualizarLogo((int)$id, $logo);
        if ($ok) {
            echo json_encode(['message' => 'Logo actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo actualizar el logo']);
        }
    }

    // PUT /apiempresa/actualizar-iva/{id}
    public function actualizarIva($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $existente = $this->empresaModel->obtenerPorId((int)$id);
        if (!$existente) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Empresa no encontrada']); 
            return; 
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        
        if (!isset($payload['iva'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Campo requerido: iva']);
            return;
        }

        $iva = (float)$payload['iva'];
        
        if ($iva < 0 || $iva > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'El IVA debe estar entre 0 y 100']);
            return;
        }
        
        $ok = $this->empresaModel->actualizarIva((int)$id, $iva);
        if ($ok) {
            echo json_encode(['message' => 'IVA actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo actualizar el IVA']);
        }
    }
}
