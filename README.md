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

## Postman / API

La app es MVC clásico. Para probar vistas:
- `GET http://localhost/Veterinaria_CS_G4/public/cliente`
- `GET http://localhost/Veterinaria_CS_G4/public/home`

Cuando se expongan endpoints JSON se documentarán aquí.
