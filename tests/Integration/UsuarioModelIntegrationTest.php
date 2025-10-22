<?php
use PHPUnit\Framework\TestCase;
use App\Models\UsuarioModel;
use App\Core\Database;

class UsuarioModelIntegrationTest extends TestCase {
    private $db;
    private $model;

    protected function setUp(): void {
        $this->db = new Database();
        $this->model = new UsuarioModel($this->db);
        
        // Iniciar transacción para rollback
        $this->db->beginTransaction();
    }

    protected function tearDown(): void {
        // Rollback de la transacción
        $this->db->rollBack();
    }

    public function testCrearUsuario() {
        $datos = [
            'nombre' => 'Test Usuario',
            'email' => 'test@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Editor',
            'estado' => 1
        ];
        
        $resultado = $this->model->crear($datos);
        $this->assertTrue($resultado);
        
        // Verificar que se creó correctamente
        $usuarios = $this->model->obtenerTodos();
        $this->assertNotEmpty($usuarios);
        
        // Buscar el usuario creado
        $encontrado = false;
        foreach ($usuarios as $usuario) {
            if ($usuario['nombre'] === 'Test Usuario') {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado);
    }

    public function testActualizarUsuario() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario Original',
            'email' => 'original@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Obtener el usuario creado
        $usuarios = $this->model->obtenerTodos();
        $usuarioId = null;
        foreach ($usuarios as $usuario) {
            if ($usuario['nombre'] === 'Usuario Original') {
                $usuarioId = $usuario['id'];
                break;
            }
        }
        
        $this->assertNotNull($usuarioId);
        
        // Actualizar el usuario
        $nuevosDatos = [
            'nombre' => 'Usuario Actualizado',
            'email' => 'actualizado@veterinaria.com',
            'rol' => 'Editor',
            'estado' => 1
        ];
        $resultado = $this->model->actualizar($usuarioId, $nuevosDatos);
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $usuarioActualizado = $this->model->obtenerPorId($usuarioId);
        $this->assertEquals('Usuario Actualizado', $usuarioActualizado['nombre']);
        $this->assertEquals('actualizado@veterinaria.com', $usuarioActualizado['email']);
    }

    public function testEliminarUsuario() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario para Eliminar',
            'email' => 'eliminar@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Obtener el usuario creado
        $usuarios = $this->model->obtenerTodos();
        $usuarioId = null;
        foreach ($usuarios as $usuario) {
            if ($usuario['nombre'] === 'Usuario para Eliminar') {
                $usuarioId = $usuario['id'];
                break;
            }
        }
        
        $this->assertNotNull($usuarioId);
        
        // Eliminar el usuario
        $resultado = $this->model->eliminar($usuarioId);
        $this->assertTrue($resultado);
        
        // Verificar que se eliminó
        $usuarioEliminado = $this->model->obtenerPorId($usuarioId);
        $this->assertNull($usuarioEliminado);
    }

    public function testObtenerPorEmail() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario Test Email',
            'email' => 'email@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Buscar por email
        $usuario = $this->model->obtenerPorEmail('email@veterinaria.com');
        $this->assertNotNull($usuario);
        $this->assertEquals('Usuario Test Email', $usuario['nombre']);
    }

    public function testObtenerPorRol() {
        // Crear algunos usuarios con diferentes roles
        $this->model->crear([
            'nombre' => 'Editor 1',
            'email' => 'editor1@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Editor',
            'estado' => 1
        ]);
        
        $this->model->crear([
            'nombre' => 'Editor 2',
            'email' => 'editor2@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Editor',
            'estado' => 1
        ]);
        
        $this->model->crear([
            'nombre' => 'Consultor 1',
            'email' => 'consultor1@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ]);
        
        // Obtener usuarios por rol
        $editores = $this->model->obtenerPorRol('Editor');
        $consultores = $this->model->obtenerPorRol('Consultor');
        
        $this->assertGreaterThanOrEqual(2, count($editores));
        $this->assertGreaterThanOrEqual(1, count($consultores));
        
        // Verificar que todos los editores tienen el rol correcto
        foreach ($editores as $editor) {
            $this->assertEquals('Editor', $editor['rol']);
        }
    }

    public function testEmailExiste() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario Test Existencia',
            'email' => 'existencia@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Verificar que existe
        $this->assertTrue($this->model->emailExiste('existencia@veterinaria.com'));
        $this->assertFalse($this->model->emailExiste('inexistente@veterinaria.com'));
    }

    public function testVerificarLogin() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario Test Login',
            'email' => 'login@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Verificar login con credenciales correctas
        $usuario = $this->model->verificarLogin('login@veterinaria.com', 'password123');
        $this->assertNotNull($usuario);
        $this->assertEquals('Usuario Test Login', $usuario['nombre']);
        
        // Verificar login con credenciales incorrectas
        $usuarioIncorrecto = $this->model->verificarLogin('login@veterinaria.com', 'wrongpassword');
        $this->assertNull($usuarioIncorrecto);
    }

    public function testCambiarEstado() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario Test Estado',
            'email' => 'estado@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Obtener el usuario creado
        $usuarios = $this->model->obtenerTodos();
        $usuarioId = null;
        foreach ($usuarios as $usuario) {
            if ($usuario['nombre'] === 'Usuario Test Estado') {
                $usuarioId = $usuario['id'];
                break;
            }
        }
        
        $this->assertNotNull($usuarioId);
        
        // Cambiar estado a inactivo
        $resultado = $this->model->cambiarEstado($usuarioId, 0);
        $this->assertTrue($resultado);
        
        // Verificar el cambio de estado
        $usuarioActualizado = $this->model->obtenerPorId($usuarioId);
        $this->assertEquals(0, $usuarioActualizado['estado']);
    }

    public function testCambiarPassword() {
        // Crear un usuario primero
        $datos = [
            'nombre' => 'Usuario Test Password',
            'email' => 'password@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ];
        $this->model->crear($datos);
        
        // Obtener el usuario creado
        $usuarios = $this->model->obtenerTodos();
        $usuarioId = null;
        foreach ($usuarios as $usuario) {
            if ($usuario['nombre'] === 'Usuario Test Password') {
                $usuarioId = $usuario['id'];
                break;
            }
        }
        
        $this->assertNotNull($usuarioId);
        
        // Cambiar contraseña
        $resultado = $this->model->cambiarPassword($usuarioId, 'newpassword123');
        $this->assertTrue($resultado);
        
        // Verificar que la nueva contraseña funciona
        $usuario = $this->model->verificarLogin('password@veterinaria.com', 'newpassword123');
        $this->assertNotNull($usuario);
        $this->assertEquals('Usuario Test Password', $usuario['nombre']);
    }

    public function testBuscar() {
        // Crear algunos usuarios
        $this->model->crear([
            'nombre' => 'Carlos Test',
            'email' => 'carlos@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Editor',
            'estado' => 1
        ]);
        
        $this->model->crear([
            'nombre' => 'Ana Test',
            'email' => 'ana@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ]);
        
        // Buscar usuarios
        $usuarios = $this->model->buscar('test');
        $this->assertGreaterThanOrEqual(2, count($usuarios));
        
        // Verificar que los resultados contienen el término de búsqueda
        foreach ($usuarios as $usuario) {
            $this->assertTrue(
                stripos($usuario['nombre'], 'test') !== false || 
                stripos($usuario['email'], 'test') !== false
            );
        }
    }

    public function testObtenerEstadisticas() {
        // Crear algunos usuarios
        $this->model->crear([
            'nombre' => 'Admin 1',
            'email' => 'admin1@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Administrador',
            'estado' => 1
        ]);
        
        $this->model->crear([
            'nombre' => 'Editor 1',
            'email' => 'editor1@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Editor',
            'estado' => 1
        ]);
        
        $this->model->crear([
            'nombre' => 'Consultor 1',
            'email' => 'consultor1@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 0
        ]);
        
        // Obtener estadísticas
        $estadisticas = $this->model->obtenerEstadisticas();
        $this->assertNotEmpty($estadisticas);
        $this->assertArrayHasKey('total_usuarios', $estadisticas[0]);
        $this->assertArrayHasKey('usuarios_activos', $estadisticas[0]);
        $this->assertArrayHasKey('usuarios_inactivos', $estadisticas[0]);
        $this->assertArrayHasKey('administradores', $estadisticas[0]);
        $this->assertArrayHasKey('editores', $estadisticas[0]);
        $this->assertArrayHasKey('consultores', $estadisticas[0]);
    }
}
