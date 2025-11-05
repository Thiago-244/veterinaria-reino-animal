# 📡 DOCUMENTACIÓN COMPLETA DE ENDPOINTS API - POSTMAN

**Base URL:** `http://localhost/Veterinaria_CS_G4/public`

**Headers requeridos para POST/PUT:**
```
Content-Type: application/json
```

---

## 🔐 AUTENTICACIÓN (Login)

### POST `/apilogin/autenticar`
Autentica un usuario.

**Body (JSON):**
```json
{
  "email": "admin@veterinaria.com",
  "password": "123456"
}
```

**Respuesta 200:**
```json
{
  "message": "Login exitoso",
  "data": {
    "id": 1,
    "nombre": "Administrador",
    "email": "admin@veterinaria.com",
    "rol": "Administrador"
  }
}
```

### POST `/apilogin/cerrar-sesion`
Cierra la sesión del usuario.

### GET `/apilogin/verificar-sesion`
Verifica si hay una sesión activa.

### PUT `/apilogin/cambiar-password`
Cambia la contraseña del usuario autenticado.

**Body (JSON):**
```json
{
  "password_actual": "123456",
  "password_nuevo": "nueva123"
}
```

### GET `/apilogin/perfil`
Obtiene el perfil del usuario autenticado.

### GET `/apilogin/permisos`
Obtiene los permisos del usuario autenticado.

---

## 👥 CLIENTES

### GET `/apicliente/listar`
Lista todos los clientes.

**Respuesta 200:**
```json
{
  "data": [
    {
      "id": 1,
      "dni": "12345678",
      "nombre": "Juan",
      "apellido": "Pérez",
      "telefono": "912345678",
      "direccion": "Av. Principal 123",
      "email": "juan@example.com"
    }
  ]
}
```

### POST `/apicliente/crear`
Crea un nuevo cliente.

**Body (JSON):**
```json
{
  "dni": "55566677",
  "nombre": "Maria",
  "apellido": "Lopez",
  "telefono": "912345678",
  "direccion": "Av. Los Olivos 456",
  "email": "maria@example.com"
}
```

**Respuesta 201:**
```json
{
  "message": "Cliente creado correctamente"
}
```

**Validaciones:**
- DNI: Exactamente 8 dígitos numéricos
- Teléfono: Exactamente 9 dígitos numéricos
- Email: Formato válido (opcional)
- Nombre/Apellido: Máximo 40 caracteres
- DNI y Email deben ser únicos

### PUT `/apicliente/actualizar/{id}`
Actualiza un cliente existente.

**Body (JSON):**
```json
{
  "dni": "55566677",
  "nombre": "Maria",
  "apellido": "Lopez",
  "telefono": "912345678",
  "direccion": "Nueva dirección",
  "email": "maria.nueva@example.com"
}
```

### DELETE `/apicliente/eliminar/{id}`
Elimina un cliente.

**Respuesta 200:**
```json
{
  "message": "Cliente eliminado"
}
```

---

## 🐾 MASCOTAS

### GET `/apimascota/listar`
Lista todas las mascotas con información del cliente y raza.

### POST `/apimascota/crear`
Crea una nueva mascota.

**Body (JSON):**
```json
{
  "nombre": "Firulais",
  "id_cliente": 1,
  "id_raza": 2,
  "fecha_nacimiento": "2020-05-15",
  "sexo": "Macho",
  "color": "Marrón",
  "peso": 25.5
}
```

**Validaciones:**
- Nombre: Máximo 50 caracteres
- Sexo: "Macho" o "Hembra"
- Fecha: Formato YYYY-MM-DD
- Cliente y Raza deben existir

### PUT `/apimascota/actualizar/{id}`
Actualiza una mascota.

### DELETE `/apimascota/eliminar/{id}`
Elimina una mascota.

### GET `/apimascota/obtener/{id}`
Obtiene una mascota por ID.

### GET `/apimascota/por-cliente/{id_cliente}`
Lista todas las mascotas de un cliente.

---

## 💰 VENTAS

### GET `/apiventa/listar`
Lista todas las ventas.

### POST `/apiventa/crear`
Crea una nueva venta con detalles.

**Body (JSON):**
```json
{
  "id_usuario": 1,
  "id_cliente": 1,
  "total": 150.50,
  "detalles": [
    {
      "id_producto": 1,
      "cantidad": 2,
      "precio": 75.25,
      "descuento": 0
    }
  ]
}
```

**Respuesta 201:**
```json
{
  "message": "Venta creada correctamente",
  "venta_id": 123
}
```

**Validaciones:**
- Usuario y Cliente deben existir
- Total > 0
- Al menos un detalle requerido
- Cada detalle debe tener cantidad > 0 y precio > 0

### GET `/apiventa/obtener/{id}`
Obtiene una venta con sus detalles.

### DELETE `/apiventa/eliminar/{id}`
Elimina una venta.

### GET `/apiventa/por-cliente/{id_cliente}`
Lista todas las ventas de un cliente.

### GET `/apiventa/por-usuario/{id_usuario}`
Lista todas las ventas de un usuario.

### GET `/apiventa/por-rango-fechas?fecha_inicio=2024-01-01&fecha_fin=2024-12-31`
Lista ventas en un rango de fechas.

### GET `/apiventa/ventas-del-dia`
Obtiene las ventas del día actual.

### GET `/apiventa/estadisticas`
Obtiene estadísticas generales de ventas.

**Respuesta:**
```json
{
  "data": {
    "total_ventas": 150,
    "total_monto": 25000.50,
    "promedio_venta": 166.67
  }
}
```

### GET `/apiventa/estadisticas-por-periodo?fecha_inicio=2024-01-01&fecha_fin=2024-12-31`
Estadísticas por período específico.

### GET `/apiventa/productos-mas-vendidos?limite=10`
Obtiene los productos más vendidos.

### GET `/apiventa/por-cliente-agrupado`
Ventas agrupadas por cliente.

### GET `/apiventa/por-usuario-agrupado`
Ventas agrupadas por usuario.

---

## 📅 CITAS

### GET `/apicita/listar`
Lista todas las citas.

### POST `/apicita/crear`
Crea una nueva cita.

**Body (JSON):**
```json
{
  "id_mascota": 1,
  "id_cliente": 1,
  "fecha_cita": "2024-12-25 10:00:00",
  "motivo": "Consulta general",
  "estado": "Pendiente"
}
```

**Validaciones:**
- Fecha: Formato YYYY-MM-DD HH:MM:SS
- Estado: "Pendiente", "Procesada" o "Cancelada"
- Mascota debe pertenecer al cliente

### PUT `/apicita/actualizar/{id}`
Actualiza una cita.

### DELETE `/apicita/eliminar/{id}`
Elimina una cita.

### GET `/apicita/obtener/{id}`
Obtiene una cita por ID.

### GET `/apicita/por-cliente/{id_cliente}`
Lista todas las citas de un cliente.

### GET `/apicita/por-mascota/{id_mascota}`
Lista todas las citas de una mascota.

### PUT `/apicita/cambiar-estado/{id}`
Cambia el estado de una cita.

**Body (JSON):**
```json
{
  "estado": "Procesada"
}
```

---

## 👤 USUARIOS

### GET `/apiusuario/listar`
Lista todos los usuarios.

### POST `/apiusuario/crear`
Crea un nuevo usuario.

**Body (JSON):**
```json
{
  "nombre": "Nuevo Usuario",
  "email": "nuevo@example.com",
  "password": "password123",
  "rol": "Editor",
  "estado": 1
}
```

**Validaciones:**
- Rol: "Administrador", "Editor" o "Consultor"
- Estado: 0 (inactivo) o 1 (activo)
- Password: Mínimo 6 caracteres
- Email debe ser único

### PUT `/apiusuario/actualizar/{id}`
Actualiza un usuario.

**Nota:** Para cambiar contraseña, incluye `"password": "nueva123"` en el body.

### DELETE `/apiusuario/eliminar/{id}`
Elimina un usuario.

### GET `/apiusuario/obtener/{id}`
Obtiene un usuario por ID (sin contraseña).

### GET `/apiusuario/por-rol/{rol}`
Lista usuarios por rol (Administrador/Editor/Consultor).

### GET `/apiusuario/activos`
Lista solo usuarios activos.

### PUT `/apiusuario/cambiar-estado/{id}`
Cambia el estado (activo/inactivo) de un usuario.

**Body (JSON):**
```json
{
  "estado": 0
}
```

### PUT `/apiusuario/cambiar-password/{id}`
Cambia la contraseña de un usuario.

**Body (JSON):**
```json
{
  "password": "nuevaPassword123"
}
```

### POST `/apiusuario/login`
Login de usuario (alternativa a apilogin).

### GET `/apiusuario/estadisticas`
Obtiene estadísticas de usuarios.

### GET `/apiusuario/buscar/{termino}`
Busca usuarios por término.

---

## 🛍️ PRODUCTOS Y SERVICIOS

### GET `/apiproductoservicio/listar`
Lista todos los productos y servicios.

### POST `/apiproductoservicio/crear`
Crea un nuevo producto o servicio.

**Body (JSON):**
```json
{
  "codigo": "PROD-001",
  "nombre": "Alimento para perros",
  "tipo": "Producto",
  "precio": 50.00,
  "stock": 100,
  "descripcion": "Alimento premium",
  "estado": 1
}
```

**Validaciones:**
- Tipo: "Producto" o "Servicio"
- Código debe ser único
- Precio > 0
- Stock >= 0 (solo productos)

### PUT `/apiproductoservicio/actualizar/{id}`
Actualiza un producto o servicio.

### DELETE `/apiproductoservicio/eliminar/{id}`
Elimina un producto o servicio.

### GET `/apiproductoservicio/obtener/{id}`
Obtiene un producto o servicio por ID.

### GET `/apiproductoservicio/productos`
Lista solo productos.

### GET `/apiproductoservicio/servicios`
Lista solo servicios.

### GET `/apiproductoservicio/por-tipo/{tipo}`
Lista por tipo (Producto/Servicio).

### GET `/apiproductoservicio/buscar/{termino}`
Busca productos/servicios por término.

### PUT `/apiproductoservicio/actualizar-stock/{id}`
Actualiza el stock de un producto.

**Body (JSON):**
```json
{
  "stock": 150,
  "tipo_operacion": "agregar"
}
```

**tipo_operacion:** "agregar", "reducir" o "establecer"

### GET `/apiproductoservicio/stock-bajo`
Lista productos con stock bajo.

### GET `/apiproductoservicio/estadisticas`
Obtiene estadísticas de productos y servicios.

### GET `/apiproductoservicio/mas-caros?limite=10`
Lista los productos/servicios más caros.

### GET `/apiproductoservicio/mas-baratos?limite=10`
Lista los productos/servicios más baratos.

---

## 🐕 ESPECIES

### GET `/apiespecie/listar`
Lista todas las especies.

### POST `/apiespecie/crear`
Crea una nueva especie.

**Body (JSON):**
```json
{
  "nombre": "Canino"
}
```

### PUT `/apiespecie/actualizar/{id}`
Actualiza una especie.

### DELETE `/apiespecie/eliminar/{id}`
Elimina una especie.

---

## 🐕 RAZAS

### GET `/apiraza/listar`
Lista todas las razas con información de especie.

### POST `/apiraza/crear`
Crea una nueva raza.

**Body (JSON):**
```json
{
  "nombre": "Labrador",
  "id_especie": 1
}
```

### PUT `/apiraza/actualizar/{id}`
Actualiza una raza.

### DELETE `/apiraza/eliminar/{id}`
Elimina una raza.

### GET `/apiraza/obtener/{id}`
Obtiene una raza por ID.

### GET `/apiraza/por-especie/{id_especie}`
Lista razas de una especie.

### GET `/apiraza/agrupadas-por-especie`
Lista razas agrupadas por especie.

### GET `/apiraza/estadisticas`
Obtiene estadísticas de razas.

---

## 🏢 EMPRESA

### GET `/apiempresa/listar`
Lista todas las empresas.

### POST `/apiempresa/crear`
Crea una nueva empresa.

**Body (JSON):**
```json
{
  "nombre": "Veterinaria Reino Animal",
  "ruc": "20123456789",
  "direccion": "Av. Principal 123",
  "telefono": "012345678",
  "email": "contacto@veterinaria.com",
  "iva": 18.0
}
```

### PUT `/apiempresa/actualizar/{id}`
Actualiza una empresa.

### DELETE `/apiempresa/eliminar/{id}`
Elimina una empresa.

### GET `/apiempresa/obtener/{id}`
Obtiene una empresa por ID.

### GET `/apiempresa/principal`
Obtiene la empresa principal.

### GET `/apiempresa/estadisticas`
Obtiene estadísticas de empresas.

### PUT `/apiempresa/actualizar-logo/{id}`
Actualiza el logo de una empresa.

**Body (JSON):**
```json
{
  "logo": "nuevo_logo.png"
}
```

### PUT `/apiempresa/actualizar-iva/{id}`
Actualiza el IVA de una empresa.

**Body (JSON):**
```json
{
  "iva": 18.0
}
```

---

## 🛒 DETALLE TEMPORAL (Carrito)

### GET `/apidetalletemp/listar`
Lista todos los detalles temporales.

### POST `/apidetalletemp/agregar`
Agrega un producto al carrito temporal.

**Body (JSON):**
```json
{
  "id_producto": 1,
  "cantidad": 2,
  "precio": 50.00,
  "token_usuario": "user123"
}
```

### PUT `/apidetalletemp/actualizar/{id}`
Actualiza un detalle temporal.

### PUT `/apidetalletemp/actualizar-cantidad/{id}`
Actualiza solo la cantidad.

**Body (JSON):**
```json
{
  "cantidad": 5
}
```

### DELETE `/apidetalletemp/eliminar/{id}`
Elimina un detalle temporal.

### GET `/apidetalletemp/obtener/{id}`
Obtiene un detalle temporal por ID.

### GET `/apidetalletemp/carrito/{token_usuario}`
Obtiene el carrito completo de un usuario.

### DELETE `/apidetalletemp/vaciar-carrito/{token_usuario}`
Vacía el carrito de un usuario.

### DELETE `/apidetalletemp/eliminar-producto/{id_producto}/{token_usuario}`
Elimina un producto específico del carrito.

### GET `/apidetalletemp/total-items/{token_usuario}`
Obtiene el total de items en el carrito.

### GET `/apidetalletemp/total-carrito/{token_usuario}`
Obtiene el total monetario del carrito.

### GET `/apidetalletemp/estadisticas/{token_usuario}`
Obtiene estadísticas del carrito.

### GET `/apidetalletemp/productos-mas-agregados`
Lista los productos más agregados al carrito.

### DELETE `/apidetalletemp/limpiar-antiguos`
Limpia detalles temporales antiguos.

---

## 📋 DETALLE DE VENTA

### GET `/apidetalleventa/listar`
Lista todos los detalles de venta.

### GET `/apidetalleventa/por-venta/{id_venta}`
Lista detalles de una venta específica.

### GET `/apidetalleventa/obtener/{id}`
Obtiene un detalle de venta por ID.

### POST `/apidetalleventa/crear`
Crea un nuevo detalle de venta.

**Body (JSON):**
```json
{
  "id_venta": 1,
  "id_producto": 1,
  "cantidad": 2,
  "precio": 50.00,
  "descuento": 5.00
}
```

### PUT `/apidetalleventa/actualizar/{id}`
Actualiza un detalle de venta.

### DELETE `/apidetalleventa/eliminar/{id}`
Elimina un detalle de venta.

### DELETE `/apidetalleventa/eliminar-por-venta/{id_venta}`
Elimina todos los detalles de una venta.

### GET `/apidetalleventa/estadisticas`
Obtiene estadísticas de detalles de venta.

### GET `/apidetalleventa/por-producto/{id_producto}`
Lista detalles de venta de un producto.

### POST `/apidetalleventa/calcular-subtotal`
Calcula el subtotal de un detalle.

**Body (JSON):**
```json
{
  "cantidad": 2,
  "precio": 50.00,
  "descuento": 5.00
}
```

### GET `/apidetalleventa/obtener-precio-producto/{id_producto}`
Obtiene el precio actual de un producto.

### GET `/apidetalleventa/obtener-total-venta/{id_venta}`
Obtiene el total de una venta.

---

## 📊 CÓDIGOS DE RESPUESTA HTTP

| Código | Significado |
|--------|-------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - JSON inválido o datos incorrectos |
| 401 | Unauthorized - Credenciales inválidas |
| 404 | Not Found - Recurso no encontrado |
| 405 | Method Not Allowed - Método HTTP no permitido |
| 409 | Conflict - Conflicto (ej: DNI duplicado) |
| 422 | Unprocessable Entity - Validación fallida |
| 500 | Internal Server Error - Error del servidor |

---

## 🎯 EJEMPLOS DE USO CON POSTMAN

### 1. Crear un Cliente
```
POST http://localhost/Veterinaria_CS_G4/public/apicliente/crear
Headers: Content-Type: application/json
Body (raw JSON):
{
  "dni": "12345678",
  "nombre": "Juan",
  "apellido": "Pérez",
  "telefono": "912345678",
  "email": "juan@example.com"
}
```

### 2. Listar Clientes
```
GET http://localhost/Veterinaria_CS_G4/public/apicliente/listar
```

### 3. Crear una Mascota
```
POST http://localhost/Veterinaria_CS_G4/public/apimascota/crear
Headers: Content-Type: application/json
Body:
{
  "nombre": "Firulais",
  "id_cliente": 1,
  "id_raza": 1,
  "fecha_nacimiento": "2020-05-15",
  "sexo": "Macho"
}
```

### 4. Crear una Venta Completa
```
POST http://localhost/Veterinaria_CS_G4/public/apiventa/crear
Headers: Content-Type: application/json
Body:
{
  "id_usuario": 1,
  "id_cliente": 1,
  "total": 150.50,
  "detalles": [
    {
      "id_producto": 1,
      "cantidad": 2,
      "precio": 75.25
    }
  ]
}
```

---

## ✅ VALIDACIONES COMUNES

### DNI
- Formato: 8 dígitos numéricos exactos
- Ejemplo válido: `"12345678"`
- Ejemplo inválido: `"1234567"` o `"123456789"`

### Teléfono
- Formato: 9 dígitos numéricos exactos
- Ejemplo válido: `"912345678"`

### Email
- Formato estándar de email
- Ejemplo válido: `"usuario@example.com"`

### Fechas
- Formato YYYY-MM-DD para fechas simples
- Formato YYYY-MM-DD HH:MM:SS para fechas con hora
- Ejemplo: `"2024-12-25 10:00:00"`

---

## 📝 NOTAS IMPORTANTES

1. **Autenticación:** Algunos endpoints pueden requerir sesión activa (verificar con `/apilogin/verificar-sesion`)

2. **Códigos Únicos:** DNI, Email, Código de producto deben ser únicos en la base de datos

3. **Relaciones:** Al crear registros con relaciones (mascota → cliente, venta → cliente), verificar que los IDs existan

4. **Transacciones:** Las ventas se crean con transacciones, si falla algún detalle, se revierte todo

5. **Validaciones:** Todos los endpoints validan datos antes de procesar

---

*Documentación generada para facilitar el trabajo con Postman*  
*Última actualización: $(date)*

