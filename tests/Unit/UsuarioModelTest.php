<?php
use PHPUnit\Framework\TestCase;
use App\Models\UsuarioModel;

class FakeDatabase {
    public array $queries = [];
    public array $bindings = [];
    public bool $executeReturn = true;
    public array $result = [];

    public function query($sql) { $this->queries[] = $sql; }
    public function bind($param, $value, $type = null) { $this->bindings[$param] = $value; }
    public function execute() { return $this->executeReturn; }
    public function resultSet() { return $this->result; }
}

class UsuarioModelTest extends TestCase {
    public function testObtenerTodasReturnsResultSet() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'id' => 1, 
                'nombre' => 'Dr. Carlos Mendoza',
                'email' => 'carlos@veterinaria.com',
                'rol' => 'Editor',
                'estado' => 1
            ],
            [
                'id' => 2, 
                'nombre' => 'Dra. Ana Rodriguez',
                'email' => 'ana@veterinaria.com',
                'rol' => 'Administrador',
                'estado' => 1
            ]
        ];
        $model = new UsuarioModel($fakeDb);
        $all = $model->obtenerTodos();
        $this->assertCount(2, $all);
        $this->assertSame('Dr. Carlos Mendoza', $all[0]['nombre']);
    }

    public function testCrearBindsAndExecutes() {
        $fakeDb = new FakeDatabase();
        $model = new UsuarioModel($fakeDb);
        $ok = $model->crear([
            'nombre' => 'Test Usuario',
            'email' => 'test@veterinaria.com',
            'password' => 'password123',
            'rol' => 'Consultor',
            'estado' => 1
        ]);
        $this->assertTrue($ok);
        $this->assertArrayHasKey(':nombre', $fakeDb->bindings);
        $this->assertArrayHasKey(':email', $fakeDb->bindings);
        $this->assertArrayHasKey(':password', $fakeDb->bindings);
        $this->assertArrayHasKey(':rol', $fakeDb->bindings);
        $this->assertArrayHasKey(':estado', $fakeDb->bindings);
        $this->assertEquals('Test Usuario', $fakeDb->bindings[':nombre']);
        $this->assertEquals('test@veterinaria.com', $fakeDb->bindings[':email']);
        $this->assertNotEmpty($fakeDb->queries);
    }

    public function testActualizarAndEliminarCallsExecute() {
        $fakeDb = new FakeDatabase();
        $model = new UsuarioModel($fakeDb);
        $this->assertTrue($model->actualizar(1, [
            'nombre' => 'Usuario Actualizado',
            'email' => 'actualizado@veterinaria.com',
            'rol' => 'Editor',
            'estado' => 1
        ]));
        $this->assertTrue($model->eliminar(1));
    }

    public function testObtenerPorIdReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Usuario', 'email' => 'test@veterinaria.com']];
        $model = new UsuarioModel($fakeDb);
        $usuario = $model->obtenerPorId(1);
        $this->assertSame('Test Usuario', $usuario['nombre']);
    }

    public function testObtenerPorIdReturnsNullWhenNotFound() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new UsuarioModel($fakeDb);
        $usuario = $model->obtenerPorId(999);
        $this->assertNull($usuario);
    }

    public function testObtenerPorEmailReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Usuario', 'email' => 'test@veterinaria.com']];
        $model = new UsuarioModel($fakeDb);
        $usuario = $model->obtenerPorEmail('test@veterinaria.com');
        $this->assertSame('Test Usuario', $usuario['nombre']);
    }

    public function testObtenerPorRolReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Usuario 1', 'rol' => 'Editor'],
            ['id' => 2, 'nombre' => 'Usuario 2', 'rol' => 'Editor']
        ];
        $model = new UsuarioModel($fakeDb);
        $usuarios = $model->obtenerPorRol('Editor');
        $this->assertCount(2, $usuarios);
        $this->assertSame('Usuario 1', $usuarios[0]['nombre']);
    }

    public function testUsuarioExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new UsuarioModel($fakeDb);
        $this->assertTrue($model->usuarioExiste(1));
    }

    public function testUsuarioExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new UsuarioModel($fakeDb);
        $this->assertFalse($model->usuarioExiste(999));
    }

    public function testEmailExisteReturnsTrueWhenExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1]];
        $model = new UsuarioModel($fakeDb);
        $this->assertTrue($model->emailExiste('test@veterinaria.com'));
    }

    public function testEmailExisteReturnsFalseWhenNotExists() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [];
        $model = new UsuarioModel($fakeDb);
        $this->assertFalse($model->emailExiste('inexistente@veterinaria.com'));
    }

    public function testVerificarLoginReturnsUserWhenCredentialsAreValid() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Usuario', 'email' => 'test@veterinaria.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'estado' => 1]];
        $model = new UsuarioModel($fakeDb);
        $usuario = $model->verificarLogin('test@veterinaria.com', 'password123');
        $this->assertNotNull($usuario);
        $this->assertSame('Test Usuario', $usuario['nombre']);
    }

    public function testVerificarLoginReturnsNullWhenCredentialsAreInvalid() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [['id' => 1, 'nombre' => 'Test Usuario', 'email' => 'test@veterinaria.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'estado' => 1]];
        $model = new UsuarioModel($fakeDb);
        $usuario = $model->verificarLogin('test@veterinaria.com', 'wrongpassword');
        $this->assertNull($usuario);
    }

    public function testCambiarEstadoReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new UsuarioModel($fakeDb);
        $this->assertTrue($model->cambiarEstado(1, 0));
        $this->assertArrayHasKey(':estado', $fakeDb->bindings);
        $this->assertEquals(0, $fakeDb->bindings[':estado']);
    }

    public function testCambiarPasswordReturnsTrue() {
        $fakeDb = new FakeDatabase();
        $model = new UsuarioModel($fakeDb);
        $this->assertTrue($model->cambiarPassword(1, 'newpassword123'));
        $this->assertArrayHasKey(':password', $fakeDb->bindings);
        $this->assertNotEmpty($fakeDb->bindings[':password']);
    }

    public function testObtenerEstadisticasReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            [
                'total_usuarios' => 5,
                'usuarios_activos' => 4,
                'usuarios_inactivos' => 1,
                'administradores' => 1,
                'editores' => 2,
                'consultores' => 2
            ]
        ];
        $model = new UsuarioModel($fakeDb);
        $estadisticas = $model->obtenerEstadisticas();
        $this->assertCount(1, $estadisticas);
        $this->assertEquals(5, $estadisticas[0]['total_usuarios']);
        $this->assertEquals(4, $estadisticas[0]['usuarios_activos']);
    }

    public function testObtenerPorRolAgrupadoReturnsCorrectFormat() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['rol' => 'Administrador', 'total' => 1, 'activos' => 1],
            ['rol' => 'Editor', 'total' => 2, 'activos' => 2],
            ['rol' => 'Consultor', 'total' => 2, 'activos' => 1]
        ];
        $model = new UsuarioModel($fakeDb);
        $usuarios = $model->obtenerPorRolAgrupado();
        $this->assertCount(3, $usuarios);
        $this->assertSame('Administrador', $usuarios[0]['rol']);
        $this->assertEquals(1, $usuarios[0]['total']);
    }

    public function testBuscarReturnsCorrectData() {
        $fakeDb = new FakeDatabase();
        $fakeDb->result = [
            ['id' => 1, 'nombre' => 'Carlos Test', 'email' => 'carlos@test.com'],
            ['id' => 2, 'nombre' => 'Ana Test', 'email' => 'ana@test.com']
        ];
        $model = new UsuarioModel($fakeDb);
        $usuarios = $model->buscar('test');
        $this->assertCount(2, $usuarios);
        $this->assertSame('Carlos Test', $usuarios[0]['nombre']);
    }
}
