<?php

namespace App\Controllers\Dashboard;

use App\Core\BaseController;
use App\Core\Auth;
use App\Models\ClienteModel;
use App\Models\MascotaModel;
use App\Models\VentaModel;
use App\Models\ProductoServicioModel;
use App\Models\CitaModel;
use App\Models\LoginModel;

class DashboardController extends BaseController {
    private $clienteModel;
    private $mascotaModel;
    private $ventaModel;
    private $productoServicioModel;
    private $citaModel;

    public function __construct() {
        // Proteger todas las rutas del dashboard
        Auth::middleware();
        $this->clienteModel = $this->model('ClienteModel');
        $this->mascotaModel = $this->model('MascotaModel');
        $this->ventaModel = $this->model('VentaModel');
        $this->productoServicioModel = $this->model('ProductoServicioModel');
        $this->citaModel = $this->model('CitaModel');
    }

    public function index() {
        // Obtener información del usuario logueado
        $loginModel = new LoginModel();
        $usuarioId = Auth::id();
        $usuario = $usuarioId ? $loginModel->obtenerUsuarioPorId($usuarioId) : null;
        
        $data = [
            'titulo' => 'Dashboard - Veterinaria Reino Animal',
            'estadisticas' => $this->obtenerEstadisticasGenerales(),
            'ventas_del_dia' => $this->ventaModel->obtenerVentasDelDia(),
            'clientes_recientes' => $this->clienteModel->obtenerClientesRecientes(5),
            'productos_stock_bajo' => $this->productoServicioModel->obtenerProductosConStockBajo(5),
            'citas_proximas' => $this->citaModel->obtenerCitasProximas(),
            'top_clientes' => $this->ventaModel->obtenerPorClienteAgrupado(),
            'productos_mas_vendidos' => $this->ventaModel->obtenerProductosMasVendidos(5),
            'usuario' => $usuario
        ];
        
        $this->view('dashboard/index', $data);
    }

    private function obtenerEstadisticasGenerales() {
        $estadisticas = [];
        
        // Estadísticas de clientes
        $estadisticas['clientes'] = $this->clienteModel->obtenerEstadisticas();
        
        // Estadísticas de productos
        $estadisticas['productos'] = $this->productoServicioModel->obtenerEstadisticas();
        
        // Estadísticas de ventas
        $estadisticas['ventas'] = $this->ventaModel->obtenerEstadisticas();
        
        return $estadisticas;
    }

    public function estadisticas() {
        $data = [
            'titulo' => 'Estadísticas Detalladas',
            'estadisticas' => $this->obtenerEstadisticasGenerales(),
            'top_clientes' => $this->ventaModel->obtenerPorClienteAgrupado(),
            'top_usuarios' => $this->ventaModel->obtenerPorUsuarioAgrupado(),
            'productos_mas_vendidos' => $this->ventaModel->obtenerProductosMasVendidos(),
            'clientes_con_mascotas' => $this->clienteModel->obtenerClientesConMascotas()
        ];
        
        $this->view('dashboard/estadisticas', $data);
    }

    //Se busca obtener los reportes principales para poder mostrar en la pagina principal o dashboard
    public function reportes() {
        $data = [
            //Aqui recibimos y obtenemos los datos necesarios para poder mostrar en ciertos espacios
            'titulo' => 'Reportes del Sistema',
            'fecha_inicio' => date('Y-m-01'), // Primer día del mes
            'fecha_fin' => date('Y-m-d'),     // Hoy
            'ventas_mes' => $this->ventaModel->obtenerVentasPorFecha(date('Y-m-01'), date('Y-m-d')),
            'estadisticas_mes' => $this->ventaModel->obtenerEstadisticasPorPeriodo(date('Y-m-01'), date('Y-m-d'))
        ];
        
        $this->view('dashboard/reportes', $data);
    }
}
