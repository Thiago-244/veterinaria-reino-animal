<?php

use PHPUnit\Framework\TestCase;
use App\Models\LoginModel;
use App\Core\Database;

class LoginModelTest extends TestCase
{
    private $loginModel;
    private $database;

    protected function setUp(): void
    {
        $this->database = $this->createMock(Database::class);
        $this->loginModel = new LoginModel($this->database);
    }

    public function testVerificarCredencialesConCredencialesValidas()
    {
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $usuarioMock = [
            'id' => 1,
            'nombre' => 'Test User',
            'email' => $email,
            'password' => $hashedPassword,
            'rol' => 'Administrador',
            'estado' => 1,
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00'
        ];

        $this->database->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT id, nombre, email, password, rol, estado, created_at, updated_at'));

        $this->database->expects($this->once())
            ->method('bind')
            ->with(':email', $email);

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn($usuarioMock);

        $resultado = $this->loginModel->verificarCredenciales($email, $password);

        $this->assertNotNull($resultado);
        $this->assertEquals($usuarioMock['id'], $resultado['id']);
        $this->assertEquals($usuarioMock['email'], $resultado['email']);
        $this->assertArrayNotHasKey('password', $resultado);
    }

    public function testVerificarCredencialesConCredencialesInvalidas()
    {
        $email = 'test@example.com';
        $password = 'wrongpassword';

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(null);

        $resultado = $this->loginModel->verificarCredenciales($email, $password);

        $this->assertNull($resultado);
    }

    public function testVerificarCredencialesConUsuarioInactivo()
    {
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $usuarioMock = [
            'id' => 1,
            'nombre' => 'Test User',
            'email' => $email,
            'password' => $hashedPassword,
            'rol' => 'Administrador',
            'estado' => 0, // Usuario inactivo
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00'
        ];

        $this->database->expects($this->once())
            ->method('query')
            ->with($this->stringContains('WHERE email = :email AND estado = 1'));

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(null);

        $resultado = $this->loginModel->verificarCredenciales($email, $password);

        $this->assertNull($resultado);
    }

    public function testUsuarioActivoConUsuarioActivo()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT estado FROM usuarios WHERE id = :id'));

        $this->database->expects($this->once())
            ->method('bind')
            ->with(':id', $id);

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['estado' => 1]);

        $resultado = $this->loginModel->usuarioActivo($id);

        $this->assertTrue($resultado);
    }

    public function testUsuarioActivoConUsuarioInactivo()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['estado' => 0]);

        $resultado = $this->loginModel->usuarioActivo($id);

        $this->assertFalse($resultado);
    }

    public function testEmailExisteConEmailExistente()
    {
        $email = 'test@example.com';

        $this->database->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT id FROM usuarios WHERE email = :email'));

        $this->database->expects($this->once())
            ->method('bind')
            ->with(':email', $email);

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['id' => 1]);

        $resultado = $this->loginModel->emailExiste($email);

        $this->assertTrue($resultado);
    }

    public function testEmailExisteConEmailNoExistente()
    {
        $email = 'nonexistent@example.com';

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(null);

        $resultado = $this->loginModel->emailExiste($email);

        $this->assertFalse($resultado);
    }

    public function testValidarEmailConEmailValido()
    {
        $email = 'test@example.com';
        $resultado = $this->loginModel->validarEmail($email);
        $this->assertTrue($resultado);
    }

    public function testValidarEmailConEmailInvalido()
    {
        $email = 'invalid-email';
        $resultado = $this->loginModel->validarEmail($email);
        $this->assertFalse($resultado);
    }

    public function testValidarPasswordConPasswordValida()
    {
        $password = 'password123';
        $resultado = $this->loginModel->validarPassword($password);
        $this->assertTrue($resultado);
    }

    public function testValidarPasswordConPasswordInvalida()
    {
        $password = '123'; // Muy corta
        $resultado = $this->loginModel->validarPassword($password);
        $this->assertFalse($resultado);
    }

    public function testValidarPasswordConPasswordSinNumeros()
    {
        $password = 'password'; // Sin números
        $resultado = $this->loginModel->validarPassword($password);
        $this->assertFalse($resultado);
    }

    public function testValidarPasswordConPasswordSinLetras()
    {
        $password = '12345678'; // Sin letras
        $resultado = $this->loginModel->validarPassword($password);
        $this->assertFalse($resultado);
    }

    public function testGenerarTokenSesion()
    {
        $token1 = $this->loginModel->generarTokenSesion();
        $token2 = $this->loginModel->generarTokenSesion();

        $this->assertIsString($token1);
        $this->assertIsString($token2);
        $this->assertEquals(64, strlen($token1)); // 32 bytes * 2 (hex)
        $this->assertEquals(64, strlen($token2));
        $this->assertNotEquals($token1, $token2);
    }

    public function testEsAdministradorConAdministrador()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT rol FROM usuarios WHERE id = :id AND estado = 1'));

        $this->database->expects($this->once())
            ->method('bind')
            ->with(':id', $id);

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Administrador']);

        $resultado = $this->loginModel->esAdministrador($id);

        $this->assertTrue($resultado);
    }

    public function testEsAdministradorConEditor()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Editor']);

        $resultado = $this->loginModel->esAdministrador($id);

        $this->assertFalse($resultado);
    }

    public function testPuedeEditarConAdministrador()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Administrador']);

        $resultado = $this->loginModel->puedeEditar($id);

        $this->assertTrue($resultado);
    }

    public function testPuedeEditarConEditor()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Editor']);

        $resultado = $this->loginModel->puedeEditar($id);

        $this->assertTrue($resultado);
    }

    public function testPuedeEditarConConsultor()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Consultor']);

        $resultado = $this->loginModel->puedeEditar($id);

        $this->assertFalse($resultado);
    }

    public function testTienePermisoConAdministrador()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Administrador']);

        $resultado = $this->loginModel->tienePermiso($id, 'usuarios');

        $this->assertTrue($resultado);
    }

    public function testTienePermisoConConsultorYFuncionalidadNoPermitida()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Consultor']);

        $resultado = $this->loginModel->tienePermiso($id, 'usuarios');

        $this->assertFalse($resultado);
    }

    public function testTienePermisoConConsultorYFuncionalidadPermitida()
    {
        $id = 1;

        $this->database->expects($this->once())
            ->method('query');

        $this->database->expects($this->once())
            ->method('bind');

        $this->database->expects($this->once())
            ->method('single')
            ->willReturn(['rol' => 'Consultor']);

        $resultado = $this->loginModel->tienePermiso($id, 'dashboard');

        $this->assertTrue($resultado);
    }
}
