# 📁 Estructura de Controladores - Veterinaria Reino Animal

## 🎯 Organización por Módulos

Los controladores están organizados en carpetas por módulo para mantener una estructura clara y escalable:

```
app/Controllers/
├── HomeController.php          # Controlador principal
├── Cliente/                    # Módulo de Clientes ✅
│   ├── ClienteController.php   # Controlador MVC para vistas
│   └── ApiClienteController.php # Controlador API REST
├── Mascota/                    # Módulo de Mascotas ✅
│   ├── MascotaController.php   # Controlador MVC para vistas
│   └── ApiMascotaController.php # Controlador API REST
├── Venta/                      # Módulo de Ventas (pendiente)
├── Usuario/                    # Módulo de Usuarios (pendiente)
└── Cita/                       # Módulo de Citas (pendiente)
```

## 🔧 Funcionamiento del Router

El Router ha sido actualizado para soportar controladores en subcarpetas:

- **URL**: `/cliente` → `App\Controllers\Cliente\ClienteController`
- **URL**: `/apicliente` → `App\Controllers\Cliente\ApiClienteController`
- **URL**: `/mascota` → `App\Controllers\Mascota\MascotaController`
- **URL**: `/apimascota` → `App\Controllers\Mascota\ApiMascotaController`
- **URL**: `/home` → `App\Controllers\HomeController`

## 📋 Convenciones

1. **Controladores MVC**: Para vistas web (`ClienteController`)
2. **Controladores API**: Para endpoints REST (`ApiClienteController`)
3. **Namespaces**: `App\Controllers\{Modulo}\{NombreController}`
4. **Carpetas**: Una carpeta por módulo de negocio

## 🚀 Beneficios

- ✅ Separación clara por módulos de negocio
- ✅ Escalabilidad para futuros módulos
- ✅ Mantenimiento más fácil
- ✅ Estructura profesional
