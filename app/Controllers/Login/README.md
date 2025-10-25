# Módulo de Login - Sistema Veterinaria Reino Animal

## Descripción
Módulo completo de autenticación y gestión de sesiones para el sistema veterinario. Incluye login, logout, cambio de contraseñas, gestión de permisos y middleware de seguridad.

## Estructura del Módulo

### Controladores
- **LoginController.php**: Controlador web para formularios de login
- **ApiLoginController.php**: Controlador API para autenticación REST

### Modelo
- **LoginModel.php**: Lógica de negocio para autenticación

### Vistas
- **login/index.php**: Formulario de login
- **login/cambiar-password.php**: Formulario de cambio de contraseña
- **login/perfil.php**: Perfil del usuario autenticado

### Core
- **Auth.php**: Middleware y utilidades de autenticación

### Tests
- **LoginModelTest.php**: Tests unitarios
- **LoginModelIntegrationTest.php**: Tests de integración

## Funcionalidades

### 1. Autenticación
- Login con email y contraseña
- Verificación de usuarios activos
- Validación de credenciales
- Gestión de sesiones

### 2. Seguridad
- Contraseñas hasheadas con `password_hash()`
- Validación de fortaleza de contraseñas
- Verificación de formato de email
- Middleware de autenticación

### 3. Gestión de Usuarios
- Cambio de contraseñas
- Perfil de usuario
- Verificación de permisos por rol
- Estadísticas de login

### 4. Roles y Permisos
- **Administrador**: Acceso completo
- **Editor**: Gestión de datos (sin usuarios)
- **Consultor**: Solo lectura

## Endpoints Web

### Login
```
GET  /login                    # Formulario de login
POST /login/procesar           # Procesar login
GET  /login/logout             # Cerrar sesión
GET  /login/cambiar-password   # Formulario cambio contraseña
POST /login/procesar-cambio-password # Procesar cambio
GET  /login/perfil             # Perfil del usuario
```

## Endpoints API

### Autenticación
```
POST /apilogin/autenticar              # Autenticar usuario
POST /apilogin/cerrar-sesion           # Cerrar sesión
GET  /apilogin/verificar-sesion        # Verificar sesión activa
```

### Gestión
```
POST /apilogin/cambiar-password        # Cambiar contraseña
GET  /apilogin/perfil                  # Obtener perfil
GET  /apilogin/permisos                # Obtener permisos
POST /apilogin/validar-email           # Validar email
GET  /apilogin/estadisticas            # Estadísticas (solo admin)
```

## Uso del Middleware

### En Controladores
```php
use App\Core\Auth;

class MiController extends BaseController {
    public function index() {
        // Verificar autenticación
        Auth::middleware();
        
        // Verificar permisos específicos
        Auth::middlewarePermission('clientes');
        
        // Verificar rol de administrador
        Auth::middlewareAdmin();
        
        // Verificar permisos de edición
        Auth::middlewareEdit();
    }
}
```

### Verificaciones
```php
// Verificar si está autenticado
if (Auth::check()) {
    // Usuario autenticado
}

// Obtener información del usuario
$usuario = Auth::user();
$nombre = Auth::nombre();
$rol = Auth::rol();

// Verificar permisos
if (Auth::hasPermission('usuarios')) {
    // Tiene permiso
}

// Verificar rol
if (Auth::isAdmin()) {
    // Es administrador
}
```

## Seguridad

### Validaciones
- Email válido
- Contraseña segura (mínimo 8 caracteres, letra y número)
- Usuario activo
- Sesión válida

### Protecciones
- Prepared statements
- Sanitización de entrada
- Verificación de sesión
- Timeout de sesión

## Base de Datos

### Tabla usuarios (existente)
```sql
CREATE TABLE usuarios (
    id int(11) NOT NULL AUTO_INCREMENT,
    nombre varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    rol enum('Administrador','Editor','Consultor') NOT NULL DEFAULT 'Consultor',
    estado tinyint(1) NOT NULL DEFAULT 1,
    created_at timestamp DEFAULT current_timestamp(),
    updated_at timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
);
```

## Testing

### Ejecutar Tests
```bash
# Tests unitarios
vendor/bin/phpunit tests/Unit/LoginModelTest.php

# Tests de integración
vendor/bin/phpunit tests/Integration/LoginModelIntegrationTest.php

# Todos los tests
vendor/bin/phpunit
```

### Cobertura
- Verificación de credenciales
- Gestión de sesiones
- Cambio de contraseñas
- Validaciones de seguridad
- Permisos y roles

## Configuración

### Variables de Entorno
```php
// En config/config.php
define('APP_URL', 'http://localhost/Veterinaria_CS_G4/public');
```

### Sesiones
Las sesiones se manejan automáticamente con PHP sessions. No requiere configuración adicional.

## Ejemplos de Uso

### Login desde JavaScript
```javascript
fetch('/apilogin/autenticar', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        email: 'usuario@example.com',
        password: 'password123'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Login exitoso
        window.location.href = '/dashboard';
    } else {
        // Error de login
        console.error(data.error);
    }
});
```

### Verificar Sesión
```javascript
fetch('/apilogin/verificar-sesion')
.then(response => response.json())
.then(data => {
    if (data.authenticated) {
        // Usuario autenticado
        console.log('Usuario:', data.data.usuario);
    } else {
        // Redirigir al login
        window.location.href = '/login';
    }
});
```

## Notas de Implementación

1. **No se requieren modificaciones en la base de datos** - La tabla `usuarios` ya tiene todos los campos necesarios.

2. **Compatibilidad total** - El módulo se integra perfectamente con la arquitectura existente.

3. **Seguridad robusta** - Implementa las mejores prácticas de seguridad web.

4. **Testing completo** - Cobertura de tests unitarios e integración.

5. **Documentación completa** - Incluye ejemplos y guías de uso.
