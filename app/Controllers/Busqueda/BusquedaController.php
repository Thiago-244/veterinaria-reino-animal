<?php

namespace App\Controllers\Busqueda;

use App\Core\BaseController;
use App\Core\Database;
use App\Models\ClienteModel;
use App\Models\MascotaModel;
use App\Models\VentaModel;
use App\Models\ProductoServicioModel;
use App\Models\CitaModel;

class BusquedaController extends BaseController {
    private $db;
    private $clienteModel;
    private $mascotaModel;
    private $ventaModel;
    private $productoServicioModel;
    private $citaModel;

    public function __construct() {
        $this->clienteModel = $this->model('ClienteModel');
        $this->mascotaModel = $this->model('MascotaModel');
        $this->ventaModel = $this->model('VentaModel');
        $this->productoServicioModel = $this->model('ProductoServicioModel');
        $this->citaModel = $this->model('CitaModel');
    }

    public function index() {
        $termino = $_GET['q'] ?? '';
        $tipo = $_GET['tipo'] ?? 'todos';
        
        $resultados = [];
        
        if (!empty($termino)) {
            switch ($tipo) {
                case 'clientes':
                    $resultados['clientes'] = $this->clienteModel->buscarClientes($termino);
                    break;
                case 'mascotas':
                    $resultados['mascotas'] = $this->mascotaModel->buscarMascotas($termino);
                    break;
                case 'productos':
                    $resultados['productos'] = $this->productoServicioModel->buscarProductos($termino);
                    break;
                case 'ventas':
                    $resultados['ventas'] = $this->buscarVentas($termino);
                    break;
                case 'citas':
                    $resultados['citas'] = $this->buscarCitas($termino);
                    break;
                case 'todos':
                default:
                    $resultados['clientes'] = $this->clienteModel->buscarClientes($termino);
                    $resultados['mascotas'] = $this->mascotaModel->buscarMascotas($termino);
                    $resultados['productos'] = $this->productoServicioModel->buscarProductos($termino);
                    $resultados['ventas'] = $this->buscarVentas($termino);
                    $resultados['citas'] = $this->buscarCitas($termino);
                    break;
            }
        }
        
        $data = [
            'titulo' => 'Búsqueda - Veterinaria Reino Animal',
            'termino' => $termino,
            'tipo' => $tipo,
            'resultados' => $resultados,
            'total_resultados' => $this->contarResultados($resultados)
        ];
        
        $this->view('busqueda/index', $data);
    }

    private function buscarVentas(string $termino) {
        // Buscar ventas por cliente o usuario
        if (!$this->db) { $this->db = new Database(); }
        $this->db->query("
            SELECT 
                v.id, v.total, v.creado_en,
                u.nombre as usuario_nombre,
                c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni as cliente_dni
            FROM venta v
            JOIN usuarios u ON v.id_usuario = u.id
            JOIN clientes c ON v.id_cliente = c.id
            WHERE c.nombre LIKE :termino 
            OR c.apellido LIKE :termino 
            OR c.dni LIKE :termino
            OR u.nombre LIKE :termino
            ORDER BY v.creado_en DESC
        ");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }

    private function buscarCitas(string $termino) {
        // Buscar citas por mascota, cliente o motivo
        if (!$this->db) { $this->db = new Database(); }
        $this->db->query("
            SELECT 
                c.id, c.codigo, c.fecha_cita, c.motivo, c.estado,
                m.nombre as mascota_nombre,
                cl.nombre as cliente_nombre, cl.apellido as cliente_apellido
            FROM citas c
            JOIN mascotas m ON c.id_mascota = m.id
            JOIN clientes cl ON m.id_cliente = cl.id
            WHERE m.nombre LIKE :termino 
            OR cl.nombre LIKE :termino 
            OR cl.apellido LIKE :termino
            OR c.motivo LIKE :termino
            OR c.codigo LIKE :termino
            ORDER BY c.fecha_cita DESC
        ");
        $this->db->bind(':termino', '%' . $termino . '%');
        return $this->db->resultSet();
    }

    private function contarResultados(array $resultados) {
        $total = 0;
        foreach ($resultados as $tipo => $items) {
            $total += count($items);
        }
        return $total;
    }

    public function api() {
        header('Content-Type: application/json; charset=utf-8');
        
        $termino = $_GET['q'] ?? '';
        $tipo = $_GET['tipo'] ?? 'todos';
        
        if (empty($termino)) {
            http_response_code(400);
            echo json_encode(['error' => 'Término de búsqueda requerido']);
            return;
        }
        
        $resultados = [];
        
        switch ($tipo) {
            case 'clientes':
                $resultados = $this->clienteModel->buscarClientes($termino);
                break;
            case 'mascotas':
                $resultados = $this->mascotaModel->buscarMascotas($termino);
                break;
            case 'productos':
                $resultados = $this->productoServicioModel->buscarProductos($termino);
                break;
            default:
                $resultados = [
                    'clientes' => $this->clienteModel->buscarClientes($termino),
                    'mascotas' => $this->mascotaModel->buscarMascotas($termino),
                    'productos' => $this->productoServicioModel->buscarProductos($termino)
                ];
                break;
        }
        
        echo json_encode([
            'termino' => $termino,
            'tipo' => $tipo,
            'resultados' => $resultados,
            'total' => is_array($resultados) ? count($resultados) : 0
        ]);
    }
}
