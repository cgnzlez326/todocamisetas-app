# TodoCamisetas API

API RESTful en **PHP puro** (sin frameworks) para la gestión de inventario y
clientes B2B de **TodoCamisetas**, proveedor mayorista de camisetas de fútbol
(Santiago, Chile).

- **Stack:** PHP 8.2 + MySQL/MariaDB (PDO), arquitectura MVC, enrutamiento
  manual con expresiones regulares.
- **Salida:** JSON con códigos HTTP consistentes para el Frontend.
- **Sin dependencias web:** Swagger UI se sirve desde un release local
  (`swagger-ui-dist` 5.33.0), no desde CDN.

## Estructura

```
todocamisetas/
├── index.php            Front controller (única puerta de entrada HTTP)
├── bootstrap.php        Autoload, config y manejo de errores/excepciones
├── .htaccess            Sirve estáticos, bloquea dotfiles, enruta a index.php
├── router.php           Equivalente a .htaccess para `php -S`
├── config/              Credenciales y parámetros
├── core/                Database, Request, Response, Router, Validator
├── models/              Camiseta, Cliente, Talla + pivotes
├── services/            PrecioService (precio_final dinámico)
├── controllers/         Endpoints (métodos estáticos, validación)
├── routes/              Tabla de rutas [método, regex, controlador]
├── database/            schema.sql + seed.sql
├── swagger-ui/          Swagger UI (release) + openapi.yaml
├── docs/                ARQUITECTURA.md y RUTAS.md
└── postman/             Colección Postman
```

## Endpoints

| Método | Ruta | Propósito |
|--------|------|-----------|
| GET | `/api/camisetas` | Listar camisetas (con tallas) |
| POST | `/api/camisetas` | Crear camiseta |
| GET | `/api/camisetas/{id}` | Ver camiseta |
| GET | `/api/camisetas/sku/{sku}` | Ver camiseta por SKU |
| PUT | `/api/camisetas/{id}` | Actualizar camiseta |
| DELETE | `/api/camisetas/{id}` | Eliminar camiseta |
| GET | `/api/clientes` | Listar clientes |
| POST | `/api/clientes` | Crear cliente |
| GET | `/api/clientes/{id}` | Ver cliente |
| PUT | `/api/clientes/{id}` | Actualizar cliente |
| DELETE | `/api/clientes/{id}` | Eliminar cliente |
| GET | `/api/clientes/{id}/camisetas` | Camisetas del cliente con `precio_final` |
| POST | `/api/clientes/{id}/camisetas` | Asociar camiseta al cliente |
| GET | `/api/clientes/{id}/camisetas/{camisetaId}/precio` | Precio final puntual |
| DELETE | `/api/clientes/{id}/camisetas/{camisetaId}` | Desasociar camiseta |
| GET/POST | `/api/tallas` | Listar / crear tallas |
| GET/DELETE | `/api/tallas/{id}` | Ver / eliminar talla |

Códigos: `200`, `201`, `400` (validación), `404`, `405`, `409` (conflicto), `500`.

## Reglas de negocio

1. **Precio final dinámico:** cliente `Preferencial` usa `precio_oferta` si está
   definido (si no, `precio`); cliente `Regular` siempre usa `precio`.
2. **No eliminar** un cliente con camisetas asociadas (`409`).
3. Relación **camiseta ↔ talla muchos-a-muchos** (`camiseta_talla`).
4. **SKU único** y **RUT único**, validados antes de persistir (`409`).

## Modelo de datos

`tallas` — `camiseta_talla` — `camisetas` — `cliente_camiseta` — `clientes`,
con claves primarias compuestas en las tablas pivote e integridad referencial
(ver [`database/schema.sql`](database/schema.sql)).

## Puesta en marcha

```bash
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql
```

Con XAMPP/Apache: `http://localhost/todocamisetas/`
Con servidor embebido: `php -S 127.0.0.1:8090 router.php`

- Swagger UI: `http://localhost/todocamisetas/swagger-ui/`
- Especificación: [`swagger-ui/openapi.yaml`](swagger-ui/openapi.yaml)
- Postman: [`postman/TodoCamisetas.postman_collection.json`](postman/TodoCamisetas.postman_collection.json)

## Criterios de diseño

- Modelos simples con **PDO y sentencias preparadas** (sin repositorios,
  interfaces, DTOs ni contenedor de dependencias).
- Reglas de negocio aisladas en `services/PrecioService.php`.
- Validación con `core/Validator.php`; errores claros y consistentes.
