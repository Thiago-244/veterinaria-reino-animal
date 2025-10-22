<?php
use PHPUnit\Framework\TestCase;
use App\Models\VentaModel;
use App\Core\Database;

class VentaModelIntegrationTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new VentaModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    public function testCrearVenta() {
        // Crear una venta con detalles
        $datos = [
            'id_usuario' => 1, // Asumiendo que existe usuario con ID 1
            'id_cliente' => 1, // Asumiendo que existe cliente con ID 1
            'total' => 150.00,
            'detalles' => [
                [
                    'id_producto' => 1, // Asumiendo que existe producto con ID 1
                    'cantidad' => 2,
                    'precio' => 75.00
                ]
            ]
        ];
        
        $ventaId = $this->model->crear($datos);
        $this->assertNotFalse($ventaId);
        
        // Verificar que se creó correctamente
        $venta = $this->model->obtenerPorId($ventaId);
        $this->assertNotNull($venta);
        $this->assertEquals(150.00, $venta['total']);
        
        // Verificar que se crearon los detalles
        $detalles = $this->model->obtenerDetalles($ventaId);
        $this->assertNotEmpty($detalles);
        $this->assertCount(1, $detalles);
        $this->assertEquals(2, $detalles[0]['cantidad']);
    }

    public function testEliminarVenta() {
        // Crear una venta primero
        $datos = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'precio' => 100.00
                ]
            ]
        ];
        
        $ventaId = $this->model->crear($datos);
        $this->assertNotFalse($ventaId);
        
        // Eliminar la venta
        $resultado = $this->model->eliminar($ventaId);
        $this->assertTrue($resultado);
        
        // Verificar que se eliminó
        $ventaEliminada = $this->model->obtenerPorId($ventaId);
        $this->assertNull($ventaEliminada);
    }

    public function testObtenerPorCliente() {
        // Crear algunas ventas para el mismo cliente
        $datos1 = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'precio' => 100.00
                ]
            ]
        ];
        
        $datos2 = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 150.00,
            'detalles' => [
                [
                    'id_producto' => 2,
                    'cantidad' => 1,
                    'precio' => 150.00
                ]
            ]
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        // Obtener ventas por cliente
        $ventas = $this->model->obtenerPorCliente(1);
        $this->assertGreaterThanOrEqual(2, count($ventas));
        
        // Verificar que todas las ventas pertenecen al cliente correcto
        foreach ($ventas as $venta) {
            $this->assertEquals(1, $venta['id_cliente']);
        }
    }

    public function testObtenerPorUsuario() {
        // Crear algunas ventas para el mismo usuario
        $datos1 = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'precio' => 100.00
                ]
            ]
        ];
        
        $datos2 = [
            'id_usuario' => 1,
            'id_cliente' => 2,
            'total' => 150.00,
            'detalles' => [
                [
                    'id_producto' => 2,
                    'cantidad' => 1,
                    'precio' => 150.00
                ]
            ]
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        // Obtener ventas por usuario
        $ventas = $this->model->obtenerPorUsuario(1);
        $this->assertGreaterThanOrEqual(2, count($ventas));
        
        // Verificar que todas las ventas pertenecen al usuario correcto
        foreach ($ventas as $venta) {
            $this->assertEquals(1, $venta['id_usuario']);
        }
    }

    public function testObtenerDetalles() {
        // Crear una venta con múltiples detalles
        $datos = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 250.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 2,
                    'precio' => 50.00
                ],
                [
                    'id_producto' => 2,
                    'cantidad' => 1,
                    'precio' => 150.00
                ]
            ]
        ];
        
        $ventaId = $this->model->crear($datos);
        $this->assertNotFalse($ventaId);
        
        // Obtener detalles
        $detalles = $this->model->obtenerDetalles($ventaId);
        $this->assertCount(2, $detalles);
        
        // Verificar los detalles
        $this->assertEquals(1, $detalles[0]['id_producto']);
        $this->assertEquals(2, $detalles[0]['cantidad']);
        $this->assertEquals(50.00, $detalles[0]['precio']);
        
        $this->assertEquals(2, $detalles[1]['id_producto']);
        $this->assertEquals(1, $detalles[1]['cantidad']);
        $this->assertEquals(150.00, $detalles[1]['precio']);
    }

    public function testObtenerEstadisticas() {
        // Crear algunas ventas
        $datos1 = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'precio' => 100.00
                ]
            ]
        ];
        
        $datos2 = [
            'id_usuario' => 1,
            'id_cliente' => 2,
            'total' => 200.00,
            'detalles' => [
                [
                    'id_producto' => 2,
                    'cantidad' => 1,
                    'precio' => 200.00
                ]
            ]
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        // Obtener estadísticas
        $estadisticas = $this->model->obtenerEstadisticas();
        $this->assertNotEmpty($estadisticas);
        $this->assertArrayHasKey('total_ventas', $estadisticas[0]);
        $this->assertArrayHasKey('total_ingresos', $estadisticas[0]);
        $this->assertArrayHasKey('promedio_venta', $estadisticas[0]);
        $this->assertArrayHasKey('clientes_unicos', $estadisticas[0]);
        $this->assertArrayHasKey('usuarios_activos', $estadisticas[0]);
    }

    public function testObtenerProductosMasVendidos() {
        // Crear ventas con diferentes productos
        $datos1 = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 5,
                    'precio' => 20.00
                ]
            ]
        ];
        
        $datos2 = [
            'id_usuario' => 1,
            'id_cliente' => 2,
            'total' => 150.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 3,
                    'precio' => 20.00
                ],
                [
                    'id_producto' => 2,
                    'cantidad' => 2,
                    'precio' => 45.00
                ]
            ]
        ];
        
        $this->model->crear($datos1);
        $this->model->crear($datos2);
        
        // Obtener productos más vendidos
        $productos = $this->model->obtenerProductosMasVendidos(5);
        $this->assertNotEmpty($productos);
        
        // Verificar que el producto 1 tiene más ventas (8 total)
        $producto1 = null;
        foreach ($productos as $producto) {
            if ($producto['id'] == 1) {
                $producto1 = $producto;
                break;
            }
        }
        
        $this->assertNotNull($producto1);
        $this->assertEquals(8, $producto1['total_vendido']); // 5 + 3
    }

    public function testVentaExiste() {
        // Crear una venta
        $datos = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'precio' => 100.00
                ]
            ]
        ];
        
        $ventaId = $this->model->crear($datos);
        $this->assertNotFalse($ventaId);
        
        // Verificar que existe
        $this->assertTrue($this->model->ventaExiste($ventaId));
        $this->assertFalse($this->model->ventaExiste(999));
    }

    public function testObtenerPorRangoFechas() {
        // Crear ventas en diferentes fechas
        $datos = [
            'id_usuario' => 1,
            'id_cliente' => 1,
            'total' => 100.00,
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'precio' => 100.00
                ]
            ]
        ];
        
        $ventaId = $this->model->crear($datos);
        $this->assertNotFalse($ventaId);
        
        // Obtener ventas por rango de fechas (hoy)
        $hoy = date('Y-m-d');
        $ventas = $this->model->obtenerPorRangoFechas($hoy, $hoy);
        $this->assertGreaterThanOrEqual(1, count($ventas));
        
        // Verificar que todas las ventas están en el rango
        foreach ($ventas as $venta) {
            $fechaVenta = date('Y-m-d', strtotime($venta['creado_en']));
            $this->assertEquals($hoy, $fechaVenta);
        }
    }
}
