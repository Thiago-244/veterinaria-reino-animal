# Veterinaria_CS_G4 - Estructura de carpetas

Esqueleto creado siguiendo la "Arquitectura de Carpetas Profesional" solicitada.

Estructura creada:

- app/
  - Controllers/
  - Models/
  - Core/
  - Libs/
- public/
  - css/
  - js/
  - img/
  - views/
    - clientes/
    - mascotas/
    - citas/
    - layouts/
  - .htaccess
  - index.php
- config/
- tests/

Siguientes pasos recomendados:
- Añadir un `Bootstrap.php` o `Router` en `app/Core/`.
- Crear controladores en `app/Controllers/` y modelos en `app/Models/`.
- Mantener datos sensibles fuera del repo o en variables de entorno y `config/` con plantillas.
