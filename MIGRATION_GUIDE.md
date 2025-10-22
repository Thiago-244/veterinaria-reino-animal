# 🚀 GUÍA DE MIGRACIÓN - VETERINARIA REINO ANIMAL

## 📋 **INSTRUCCIONES PARA ACTUALIZAR LA BASE DE DATOS**

### ⚠️ **IMPORTANTE: HACER BACKUP ANTES DE CONTINUAR**

```sql
-- 1. Crear backup de la base de datos actual
mysqldump -u root -p veterinaria_reino_animal > backup_veterinaria_$(date +%Y%m%d_%H%M%S).sql
```

---

## 🔄 **OPCIÓN 1: MIGRACIÓN SEGURA (RECOMENDADA)**

Si ya tienes datos en tu base de datos actual:

```sql
-- Ejecutar el script de migración
mysql -u root -p veterinaria_reino_animal < database/migration_complete.sql
```

**Ventajas:**
- ✅ Preserva todos los datos existentes
- ✅ Actualiza la estructura gradualmente
- ✅ No hay pérdida de información

---

## 🆕 **OPCIÓN 2: INSTALACIÓN FRESCA**

Si quieres empezar desde cero o no tienes datos importantes:

```sql
-- 1. Eliminar base de datos actual (¡CUIDADO!)
DROP DATABASE IF EXISTS veterinaria_reino_animal;

-- 2. Crear nueva base de datos
CREATE DATABASE veterinaria_reino_animal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Usar la base de datos
USE veterinaria_reino_animal;

-- 4. Ejecutar esquema completo
mysql -u root -p veterinaria_reino_animal < database/schema_complete.sql

-- 5. Insertar datos de prueba
mysql -u root -p veterinaria_reino_animal < database/seeds_complete.sql
```

---

## 📊 **CAMBIOS PRINCIPALES EN EL ESQUEMA**

### ✅ **Nuevas Tablas Agregadas:**
- `citas` - Gestión de citas médicas
- `historialmascota` - Historial clínico
- `vacunas` - Catálogo de vacunas
- `historialvacuna` - Registro de vacunas aplicadas

### 🔄 **Tablas Actualizadas:**
- `empresa` - Agregado campo `iva`
- `usuarios` - Agregados timestamps
- `clientes` - Agregados campos `direccion`, `foto`, timestamps
- `mascotas` - Agregados campos `color`, `peso`, `foto`, timestamps
- `productoservicio` - Agregados campos `codigo`, `descripcion`, `estado`
- `venta` - Agregados campos `subtotal`, `iva`, `tipo_comprobante`
- `detalleventa` - Agregados campos `precio_unitario`, `descuento`, `subtotal`

### 🔧 **Mejoras Técnicas:**
- Charset actualizado a `utf8mb4`
- Índices optimizados
- Constraints mejoradas
- Campos obligatorios definidos

---

## 🧪 **VERIFICACIÓN POST-MIGRACIÓN**

Después de ejecutar la migración, verifica que todo esté correcto:

```sql
-- Verificar estructura de tablas
SHOW TABLES;

-- Verificar datos
SELECT COUNT(*) as total_clientes FROM clientes;
SELECT COUNT(*) as total_usuarios FROM usuarios;
SELECT COUNT(*) as total_empresa FROM empresa;

-- Verificar nuevas tablas
SELECT COUNT(*) as total_citas FROM citas;
SELECT COUNT(*) as total_vacunas FROM vacunas;
```

---

## 🔧 **ACTUALIZACIONES EN EL CÓDIGO**

### ✅ **Controladores Actualizados:**
- `ApiClienteController` - Soporte para campo `direccion`
- `ClienteController` - Soporte para campo `direccion`
- `ClienteModel` - Consultas actualizadas

### ✅ **Vistas Actualizadas:**
- Formularios incluyen campo `direccion`
- Validaciones mejoradas

---

## 🚨 **SOLUCIÓN DE PROBLEMAS**

### **Error de Foreign Key:**
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Ejecutar migración
SET FOREIGN_KEY_CHECKS = 1;
```

### **Error de Charset:**
```sql
ALTER DATABASE veterinaria_reino_animal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **Verificar Permisos:**
```sql
SHOW GRANTS FOR 'root'@'localhost';
```

---

## 📞 **SOPORTE**

Si encuentras algún problema durante la migración:

1. **Verifica el backup** - Asegúrate de tener un respaldo
2. **Revisa los logs** - MySQL puede mostrar errores específicos
3. **Ejecuta paso a paso** - Puedes ejecutar partes del script por separado

---

## ✅ **CHECKLIST FINAL**

- [ ] Backup creado
- [ ] Migración ejecutada
- [ ] Datos verificados
- [ ] APIs funcionando
- [ ] Vistas actualizadas
- [ ] Pruebas realizadas

**¡Migración completada exitosamente!** 🎉
