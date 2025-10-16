# Veterinaria_CS_G4

Sistema web para la gestión de la Veterinaria Reino Animal.

## Estructura

- `app/` (Controllers, Models, Core, Libs)
- `public/` (css, js, img, views, .htaccess, index.php)
- `config/`
- `tests/`

## Pruebas con PHPUnit

Requisitos: PHP 8.1+, Composer.

1. Instalar dependencias de desarrollo:
```bash
composer install
```

2. Ejecutar pruebas:
```bash
vendor/bin/phpunit
```

Notas:
- La configuración está en `phpunit.xml` y el bootstrap en `tests/bootstrap.php`.
- Por defecto, las constantes apuntan a la base `veterinaria_reino_animal_test`; puedes sobreescribir con variables de entorno `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.

### Usar tu base de datos real en pruebas de integración

1) Asegúrate de que exista la BD y tabla `clientes` en tu servidor.
2) Exporta variables de entorno antes de ejecutar PHPUnit (PowerShell):
```powershell
$env:DB_HOST="localhost"; $env:DB_USER="root"; $env:DB_PASS=""; $env:DB_NAME="veterinaria_reino_animal"
vendor\bin\phpunit --testsuite Integration
```
Cada test de integración abre una transacción y hace `ROLLBACK` al terminar, por lo que no deja datos persistidos.

## Esquema SQL y datos de ejemplo

1. Importa el esquema (MySQL):
```sql
-- estando en tu BD veterinaria_reino_animal
SOURCE database/schema.sql;
```
2. (Opcional) Carga seeds mínimos:
```sql
SOURCE database/seeds.sql;
```

## Postman / API

Para vistas HTML:
- `GET http://localhost/Veterinaria_CS_G4/public/cliente`
- `GET http://localhost/Veterinaria_CS_G4/public/home`

### Endpoints JSON para Postman (Clientes)

- `GET http://localhost/Veterinaria_CS_G4/public/apicliente/listar`
  - Respuesta 200: `{ "data": [ {"id":1, "dni":"...", ...} ] }`

- `POST http://localhost/Veterinaria_CS_G4/public/apicliente/crear`
  - Headers: `Content-Type: application/json`
  - Body (raw JSON):
```json
{
  "dni": "55566677",
  "nombre": "Maria",
  "apellido": "Lopez",
  "telefono": "912345678",
  "email": "maria@example.com"
}
```
  - Respuesta 201: `{ "message": "Cliente creado correctamente" }`

- `PUT http://localhost/Veterinaria_CS_G4/public/apicliente/actualizar/{id}`
  - Headers: `Content-Type: application/json`
  - Body: cualquiera de los campos (`dni,nombre,apellido,telefono,email`).
  - Respuesta 200: `{ "message": "Cliente actualizado" }`

- `DELETE http://localhost/Veterinaria_CS_G4/public/apicliente/eliminar/{id}`
  - Respuesta 200: `{ "message": "Cliente eliminado" }`
