# Documentación API - Sistema de Facturación

## Autenticación

Todas las rutas (excepto `/register` y `/login`) requieren autenticación mediante Bearer Token.

### Header de Autenticación
```
Authorization: Bearer {token}
```

---

## Endpoints de Autenticación

### POST /api/register
Registrar un nuevo usuario.

**Request:**
```json
{
    "name": "Usuario Test",
    "email": "test@example.com",
    "password": "password123"
}
```

**Response (201):**
```json
{
    "message": "Usuario registrado exitosamente",
    "token": "1|xxx...",
    "user": {
        "id": 1,
        "name": "Usuario Test",
        "email": "test@example.com"
    }
}
```

### POST /api/login
Iniciar sesión.

**Request:**
```json
{
    "email": "test@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "message": "Login exitoso",
    "token": "2|xxx...",
    "user": {
        "id": 1,
        "name": "Usuario Test",
        "email": "test@example.com"
    }
}
```

---

## Endpoints de Clientes

### GET /api/clientes
Listar todos los clientes (con paginación).

**Query Parameters:**
- `per_page` (opcional): Cantidad de resultados por página (default: 15)
- `search` (opcional): Término de búsqueda (busca en nombre, email, identificación)
- `page` (opcional): Número de página

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "nombre": "Juan Pérez",
            "email": "juan@example.com",
            "telefono": "555-0101",
            "direccion": "Calle 123 #45-67",
            "identificacion": "1234567890",
            "created_at": "2025-10-24 10:00:00",
            "updated_at": "2025-10-24 10:00:00",
            "facturas_count": 5
        }
    ],
    "links": {...},
    "meta": {...}
}
```

### POST /api/clientes
Crear un nuevo cliente.

**Request:**
```json
{
    "nombre": "María García",
    "email": "maria@example.com",
    "telefono": "555-0102",
    "direccion": "Avenida 456 #78-90",
    "identificacion": "0987654321"
}
```

**Response (201):**
```json
{
    "message": "Cliente creado exitosamente",
    "data": {
        "id": 2,
        "nombre": "María García",
        "email": "maria@example.com",
        "telefono": "555-0102",
        "direccion": "Avenida 456 #78-90",
        "identificacion": "0987654321",
        "created_at": "2025-10-24 10:05:00",
        "updated_at": "2025-10-24 10:05:00"
    }
}
```

### GET /api/clientes/{id}
Obtener un cliente específico.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "nombre": "Juan Pérez",
        "email": "juan@example.com",
        "telefono": "555-0101",
        "direccion": "Calle 123 #45-67",
        "identificacion": "1234567890",
        "facturas": [...],
        "facturas_count": 5,
        "created_at": "2025-10-24 10:00:00",
        "updated_at": "2025-10-24 10:00:00"
    }
}
```

### PUT/PATCH /api/clientes/{id}
Actualizar un cliente.

**Request:**
```json
{
    "nombre": "Juan Pérez Actualizado",
    "telefono": "555-9999"
}
```

**Response (200):**
```json
{
    "message": "Cliente actualizado exitosamente",
    "data": {...}
}
```

### DELETE /api/clientes/{id}
Eliminar un cliente.

**Response (200):**
```json
{
    "message": "Cliente eliminado exitosamente"
}
```

**Error (500) - Si tiene facturas:**
```json
{
    "message": "Error al eliminar el cliente",
    "error": "No se puede eliminar el cliente porque tiene facturas asociadas"
}
```

---

## Endpoints de Productos

### GET /api/productos
Listar todos los productos (con paginación).

**Query Parameters:**
- `per_page` (opcional): Cantidad de resultados por página (default: 15)
- `search` (opcional): Término de búsqueda (busca en nombre, código, descripción)
- `page` (opcional): Número de página

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "nombre": "Laptop Dell XPS 15",
            "codigo": "PROD001",
            "precio_unitario": 1500.00,
            "descripcion": "Laptop de alto rendimiento",
            "created_at": "2025-10-24 10:00:00",
            "updated_at": "2025-10-24 10:00:00"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

### POST /api/productos
Crear un nuevo producto.

**Request:**
```json
{
    "nombre": "Mouse Logitech",
    "codigo": "PROD002",
    "precio_unitario": 89.99,
    "descripcion": "Mouse inalámbrico"
}
```

**Response (201):**
```json
{
    "message": "Producto creado exitosamente",
    "data": {
        "id": 2,
        "nombre": "Mouse Logitech",
        "codigo": "PROD002",
        "precio_unitario": 89.99,
        "descripcion": "Mouse inalámbrico",
        "created_at": "2025-10-24 10:05:00",
        "updated_at": "2025-10-24 10:05:00"
    }
}
```

### GET /api/productos/{id}
Obtener un producto específico.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "nombre": "Laptop Dell XPS 15",
        "codigo": "PROD001",
        "precio_unitario": 1500.00,
        "descripcion": "Laptop de alto rendimiento",
        "created_at": "2025-10-24 10:00:00",
        "updated_at": "2025-10-24 10:00:00"
    }
}
```

### PUT/PATCH /api/productos/{id}
Actualizar un producto.

**Request:**
```json
{
    "precio_unitario": 1400.00,
    "descripcion": "Laptop actualizada"
}
```

**Response (200):**
```json
{
    "message": "Producto actualizado exitosamente",
    "data": {...}
}
```

### DELETE /api/productos/{id}
Eliminar un producto.

**Response (200):**
```json
{
    "message": "Producto eliminado exitosamente"
}
```

---

## Endpoints de Facturas

### GET /api/facturas
Listar todas las facturas (con paginación).

**Query Parameters:**
- `per_page` (opcional): Cantidad de resultados por página (default: 15)
- `cliente_id` (opcional): Filtrar por cliente
- `fecha_inicio` (opcional): Fecha de inicio (YYYY-MM-DD)
- `fecha_fin` (opcional): Fecha de fin (YYYY-MM-DD)
- `page` (opcional): Número de página

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "numero_factura": "FAC000001",
            "fecha_emision": "2025-10-24",
            "total": 1679.98,
            "cliente_id": 1,
            "consecutivo_id": 1,
            "cliente": {
                "id": 1,
                "nombre": "Juan Pérez",
                "email": "juan@example.com",
                "identificacion": "1234567890"
            },
            "created_at": "2025-10-24 10:00:00",
            "updated_at": "2025-10-24 10:00:00"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

### POST /api/facturas
Crear una nueva factura.

**Request:**
```json
{
    "cliente_id": 1,
    "consecutivo_id": 1,
    "fecha_emision": "2025-10-24",
    "detalles": [
        {
            "producto_id": 1,
            "cantidad": 1,
            "precio_unitario": 1500.00
        },
        {
            "producto_id": 2,
            "cantidad": 2,
            "precio_unitario": 89.99
        }
    ]
}
```

**Response (201):**
```json
{
    "message": "Factura creada exitosamente",
    "data": {
        "id": 1,
        "numero_factura": "FAC000001",
        "fecha_emision": "2025-10-24",
        "total": 1679.98,
        "cliente": {...},
        "detalles": [
            {
                "id": 1,
                "producto_id": 1,
                "cantidad": 1,
                "precio_unitario": 1500.00,
                "subtotal": 1500.00,
                "producto": {
                    "id": 1,
                    "nombre": "Laptop Dell XPS 15",
                    "codigo": "PROD001"
                }
            },
            {
                "id": 2,
                "producto_id": 2,
                "cantidad": 2,
                "precio_unitario": 89.99,
                "subtotal": 179.98,
                "producto": {
                    "id": 2,
                    "nombre": "Mouse Logitech",
                    "codigo": "PROD002"
                }
            }
        ],
        "created_at": "2025-10-24 10:00:00",
        "updated_at": "2025-10-24 10:00:00"
    }
}
```

### GET /api/facturas/{id}
Obtener una factura específica con sus detalles.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "numero_factura": "FAC000001",
        "fecha_emision": "2025-10-24",
        "total": 1679.98,
        "cliente": {...},
        "detalles": [...],
        "created_at": "2025-10-24 10:00:00",
        "updated_at": "2025-10-24 10:00:00"
    }
}
```

### PUT/PATCH /api/facturas/{id}
Actualizar una factura.

**Request:**
```json
{
    "fecha_emision": "2025-10-25",
    "detalles": [
        {
            "producto_id": 1,
            "cantidad": 2,
            "precio_unitario": 1500.00
        }
    ]
}
```

**Response (200):**
```json
{
    "message": "Factura actualizada exitosamente",
    "data": {...}
}
```

### DELETE /api/facturas/{id}
Eliminar una factura.

**Response (200):**
```json
{
    "message": "Factura eliminada exitosamente"
}
```

### GET /api/facturas/estadisticas
Obtener estadísticas de facturas.

**Response (200):**
```json
{
    "data": {
        "total_facturas": 150,
        "total_ventas": 250000.50,
        "promedio_venta": 1666.67,
        "facturas_hoy": 5,
        "ventas_hoy": 8500.00,
        "facturas_mes": 45,
        "ventas_mes": 75000.00
    }
}
```

### GET /api/facturas/numero/{numero}
Buscar una factura por su número.

**Ejemplo:** `/api/facturas/numero/FAC000001`

**Response (200):**
```json
{
    "data": {...}
}
```

---

## Códigos de Respuesta HTTP

- `200` - OK: Solicitud exitosa
- `201` - Created: Recurso creado exitosamente
- `400` - Bad Request: Error en la solicitud
- `401` - Unauthorized: No autenticado
- `404` - Not Found: Recurso no encontrado
- `422` - Unprocessable Entity: Error de validación
- `500` - Internal Server Error: Error del servidor

---

## Ejemplos de Uso con cURL

### Registrar Usuario
```bash
curl -X POST https://app.facturacion-ms.test/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Usuario Test",
    "email": "test@example.com",
    "password": "password123"
  }'
```

### Listar Clientes
```bash
curl -X GET https://app.facturacion-ms.test/api/clientes \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Crear Factura
```bash
curl -X POST https://app.facturacion-ms.test/api/facturas \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "cliente_id": 1,
    "consecutivo_id": 1,
    "fecha_emision": "2025-10-24",
    "detalles": [
      {
        "producto_id": 1,
        "cantidad": 1,
        "precio_unitario": 1500.00
      }
    ]
  }'
```

---
