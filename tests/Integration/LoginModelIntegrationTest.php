<?php

use PHPUnit\Framework\TestCase;
use App\Models\LoginModel;
use App\Core\Database;

class LoginModelIntegrationTest extends TestCase
{
    private $loginModel;
    private $database;

    protected function setUp(): void
    {
        $this->database = new Database();
        $this->loginModel = new LoginModel($this->database);
        
        // Iniciar transacción para cada test
        $this->database->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback después de cada test
        $this->database->rollBack();
    }

    public function testVerificarCredencialesConUsuarioReal()
    {
        // Crear un usuario de prueba
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        // Verificar credenciales
        $resultado = $this->loginModel->verificarCredenciales($email, $password);

        $this->assertNotNull($resultado);
        $this->assertEquals($email, $resultado['email']);
        $this->assertEquals('Test User', $resultado['nombre']);
        $this->assertEquals('Administrador', $resultado['rol']);
        $this->assertArrayNotHasKey('password', $resultado);
    }

    public function testVerificarCredencialesConPasswordIncorrecta()
    {
        // Crear un usuario de prueba
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        // Intentar con password incorrecta
        $resultado = $this->loginModel->verificarCredenciales($email, 'wrongpassword');

        $this->assertNull($resultado);
    }

    public function testVerificarCredencialesConUsuarioInactivo()
    {
        // Crear un usuario inactivo
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 0)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        // Intentar login con usuario inactivo
        $resultado = $this->loginModel->verificarCredenciales($email, $password);

        $this->assertNull($resultado);
    }

    public function testUsuarioActivoConUsuarioReal()
    {
        // Crear un usuario activo
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar que está activo
        $resultado = $this->loginModel->usuarioActivo($usuarioId);

        $this->assertTrue($resultado);
    }

    public function testUsuarioActivoConUsuarioInactivo()
    {
        // Crear un usuario inactivo
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 0)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar que no está activo
        $resultado = $this->loginModel->usuarioActivo($usuarioId);

        $this->assertFalse($resultado);
    }

    public function testEmailExisteConEmailReal()
    {
        // Crear un usuario
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        // Verificar que el email existe
        $resultado = $this->loginModel->emailExiste($email);

        $this->assertTrue($resultado);
    }

    public function testEmailExisteConEmailNoExistente()
    {
        $email = 'nonexistent@example.com';
        $resultado = $this->loginModel->emailExiste($email);
        $this->assertFalse($resultado);
    }

    public function testObtenerUsuarioPorIdConUsuarioReal()
    {
        // Crear un usuario
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Obtener usuario por ID
        $resultado = $this->loginModel->obtenerUsuarioPorId($usuarioId);

        $this->assertNotNull($resultado);
        $this->assertEquals($usuarioId, $resultado['id']);
        $this->assertEquals('Test User', $resultado['nombre']);
        $this->assertEquals($email, $resultado['email']);
        $this->assertEquals('Administrador', $resultado['rol']);
        $this->assertArrayNotHasKey('password', $resultado);
    }

    public function testCambiarPasswordConPasswordCorrecta()
    {
        // Crear un usuario
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();
        $nuevaPassword = 'newpassword456';

        // Cambiar password
        $resultado = $this->loginModel->cambiarPassword($usuarioId, $password, $nuevaPassword);

        $this->assertTrue($resultado);

        // Verificar que la nueva password funciona
        $usuario = $this->loginModel->verificarCredenciales($email, $nuevaPassword);
        $this->assertNotNull($usuario);
    }

    public function testCambiarPasswordConPasswordIncorrecta()
    {
        // Crear un usuario
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();
        $nuevaPassword = 'newpassword456';

        // Intentar cambiar password con password actual incorrecta
        $resultado = $this->loginModel->cambiarPassword($usuarioId, 'wrongpassword', $nuevaPassword);

        $this->assertFalse($resultado);
    }

    public function testActualizarUltimaSesion()
    {
        // Crear un usuario
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Test User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Obtener updated_at inicial
        $this->database->query("SELECT updated_at FROM usuarios WHERE id = :id");
        $this->database->bind(':id', $usuarioId);
        $updatedAtInicial = $this->database->single()['updated_at'];

        // Esperar un poco para asegurar diferencia de tiempo
        sleep(1);

        // Actualizar última sesión
        $resultado = $this->loginModel->actualizarUltimaSesion($usuarioId);

        $this->assertTrue($resultado);

        // Verificar que updated_at cambió
        $this->database->query("SELECT updated_at FROM usuarios WHERE id = :id");
        $this->database->bind(':id', $usuarioId);
        $updatedAtFinal = $this->database->single()['updated_at'];

        $this->assertNotEquals($updatedAtInicial, $updatedAtFinal);
    }

    public function testEsAdministradorConUsuarioReal()
    {
        // Crear un administrador
        $email = 'admin@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Admin User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar que es administrador
        $resultado = $this->loginModel->esAdministrador($usuarioId);

        $this->assertTrue($resultado);
    }

    public function testEsAdministradorConEditor()
    {
        // Crear un editor
        $email = 'editor@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Editor User', :email, :password, 'Editor', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar que no es administrador
        $resultado = $this->loginModel->esAdministrador($usuarioId);

        $this->assertFalse($resultado);
    }

    public function testPuedeEditarConEditor()
    {
        // Crear un editor
        $email = 'editor@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Editor User', :email, :password, 'Editor', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar que puede editar
        $resultado = $this->loginModel->puedeEditar($usuarioId);

        $this->assertTrue($resultado);
    }

    public function testPuedeEditarConConsultor()
    {
        // Crear un consultor
        $email = 'consultor@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Consultor User', :email, :password, 'Consultor', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar que no puede editar
        $resultado = $this->loginModel->puedeEditar($usuarioId);

        $this->assertFalse($resultado);
    }

    public function testTienePermisoConAdministrador()
    {
        // Crear un administrador
        $email = 'admin@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Admin User', :email, :password, 'Administrador', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar permisos
        $this->assertTrue($this->loginModel->tienePermiso($usuarioId, 'usuarios'));
        $this->assertTrue($this->loginModel->tienePermiso($usuarioId, 'dashboard'));
        $this->assertTrue($this->loginModel->tienePermiso($usuarioId, 'configuracion'));
    }

    public function testTienePermisoConConsultor()
    {
        // Crear un consultor
        $email = 'consultor@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->database->query("
            INSERT INTO usuarios (nombre, email, password, rol, estado) 
            VALUES ('Consultor User', :email, :password, 'Consultor', 1)
        ");
        $this->database->bind(':email', $email);
        $this->database->bind(':password', $hashedPassword);
        $this->database->execute();

        $usuarioId = $this->database->lastInsertId();

        // Verificar permisos
        $this->assertTrue($this->loginModel->tienePermiso($usuarioId, 'dashboard'));
        $this->assertTrue($this->loginModel->tienePermiso($usuarioId, 'clientes'));
        $this->assertFalse($this->loginModel->tienePermiso($usuarioId, 'usuarios'));
        $this->assertFalse($this->loginModel->tienePermiso($usuarioId, 'configuracion'));
    }

    public function testObtenerEstadisticasLogin()
    {
        // Crear varios usuarios con diferentes roles
        $usuarios = [
            ['Admin', 'admin@example.com', 'Administrador', 1],
            ['Editor', 'editor@example.com', 'Editor', 1],
            ['Consultor', 'consultor@example.com', 'Consultor', 1],
            ['Inactivo', 'inactivo@example.com', 'Consultor', 0]
        ];

        foreach ($usuarios as $usuario) {
            $this->database->query("
                INSERT INTO usuarios (nombre, email, password, rol, estado) 
                VALUES (:nombre, :email, :password, :rol, :estado)
            ");
            $this->database->bind(':nombre', $usuario[0]);
            $this->database->bind(':email', $usuario[1]);
            $this->database->bind(':password', password_hash('password123', PASSWORD_DEFAULT));
            $this->database->bind(':rol', $usuario[2]);
            $this->database->bind(':estado', $usuario[3]);
            $this->database->execute();
        }

        // Obtener estadísticas
        $estadisticas = $this->loginModel->obtenerEstadisticasLogin();

        $this->assertNotNull($estadisticas);
        $this->assertEquals(4, $estadisticas['total_usuarios']);
        $this->assertEquals(3, $estadisticas['usuarios_activos']);
        $this->assertEquals(1, $estadisticas['usuarios_inactivos']);
        $this->assertEquals(1, $estadisticas['administradores']);
        $this->assertEquals(1, $estadisticas['editores']);
        $this->assertEquals(2, $estadisticas['consultores']);
    }
}
