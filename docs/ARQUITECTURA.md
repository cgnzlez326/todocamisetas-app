# Arquitectura - TodoCamisetas API

API RESTful en **PHP puro** (sin frameworks) con arquitectura **MVC**, MySQL y
enrutamiento manual por expresiones regulares. La capa Frontend es un proyecto
aparte que consume estos endpoints JSON.

## Estructura de directorios

```
todocamisetas/
├── index.php                  Front controller: única puerta de entrada HTTP.
├── bootstrap.php              Autoload, config y manejo de errores/excepciones.
├── .htaccess                  Sirve estáticos, bloquea .git y enruta a index.php.
├── router.php                 Equivalente a .htaccess para `php -S`.
├── config/
│   └── config.php             Credenciales y parámetros (variables de entorno).
├── core/                      Infraestructura transversal (no de negocio).
│   ├── Database.php           Conexión PDO única (singleton).
│   ├── Request.php            Encapsula método, ruta, query y cuerpo JSON.
│   ├── Response.php           Respuestas JSON y cabeceras consistentes.
│   ├── Router.php             Enrutamiento manual con expresiones regulares.
│   └── Validator.php          Validación reutilizable de entrada.
├── models/                    Acceso a datos (PDO, sentencias preparadas).
│   ├── Camiseta.php           CRUD de camisetas + reglas de unicidad.
│   ├── Cliente.php            CRUD de clientes + RUT único.
│   ├── Talla.php              Catálogo de tallas.
│   ├── CamisetaTalla.php      Pivote muchos-a-muchos camiseta <-> talla.
│   └── ClienteCamiseta.php    Asociación cliente <-> camiseta.
├── services/                  Lógica de negocio.
│   └── PrecioService.php      Cálculo dinámico de precio_final.
├── controllers/               Orquestación HTTP (validar -> modelo -> JSON).
│   ├── HomeController.php     Salud e índice del servicio.
│   ├── CamisetaController.php CRUD de camisetas.
│   ├── ClienteController.php  CRUD de clientes y asociaciones.
│   └── TallaController.php    CRUD de tallas.
├── routes/
│   └── api.php                Tabla de rutas [método, regex, controlador].
├── database/
│   ├── schema.sql             Modelo relacional (DDL).
│   └── seed.sql               Datos de ejemplo.
├── swagger-ui/                Swagger UI (release oficial swagger-ui-dist).
│   ├── index.html             UI servida localmente (sin CDN).
│   ├── swagger-initializer.js Apunta a ./openapi.yaml.
│   └── openapi.yaml           Especificación OpenAPI 3.0.3.
├── docs/
│   ├── ARQUITECTURA.md        Este documento.
│   └── RUTAS.md               Documentación de rutas (regex/método/propósito).
└── postman/
    └── TodoCamisetas.postman_collection.json
```

## Flujo de una solicitud

```
HTTP -> .htaccess -> index.php -> bootstrap.php (autoload + config)
     -> Request (normaliza ruta/JSON)
     -> Router (casa regex -> controlador)
     -> Controller (valida con Validator)
     -> Model / Service (PDO o regla de negocio)
     -> Response (JSON + código HTTP)
```

Cada componente tiene una única responsabilidad, lo que respeta **SOLID**:

- **Controladores**: solo orquestan y validan; no contienen SQL.
- **Modelos**: solo acceso a datos con PDO preparado.
- **Servicios**: reglas de negocio (precio final), desacopladas de las vistas.
- **Core**: infraestructura reutilizable y sustituible.

## Recursos estáticos y Swagger UI

El Swagger UI **no usa CDN ni npm**: se descarga el último release oficial
(`swagger-ui-dist`, versión 5.33.0) y sus archivos se sirven como estáticos
desde `swagger-ui/`.

```bash
# Release publicado en npm (mismo artefacto que el tag v5.33.0)
curl -L https://registry.npmjs.org/swagger-ui-dist/-/swagger-ui-dist-5.33.0.tgz -o swagger-ui-dist.tgz
tar -xzf swagger-ui-dist.tgz
cp -r package/* swagger-ui/
```

La especificación vive junto al bundle (`swagger-ui/openapi.yaml`) y
`swagger-ui/swagger-initializer.js` apunta a `./openapi.yaml`. Al quedar los
archivos del release directamente en `swagger-ui/`, la ruta plana
`/swagger-ui/` sirve `index.html` sin redirects.

Implicaciones en el enrutamiento:

- Las rutas de la API (regex) y los archivos estáticos conviven en el mismo
  árbol; el front controller solo recibe lo que **no** es un archivo real.
- `.htaccess` (Apache) y `router.php` (`php -S`) sirven primero los estáticos y
  delegan el resto a `index.php`.
- Ambos enrutadores **bloquean** el acceso a `.git` y a cualquier archivo oculto.

Swagger UI queda disponible en:

```
http://localhost/todocamisetas/swagger-ui/
```

Para actualizar: descargar el release más reciente de `swagger-ui-dist` y
reemplazar los archivos de `swagger-ui/` (manteniendo `openapi.yaml` y el
`url: "./openapi.yaml"` del initializer).

## Modelo relacional

```
tallas (id, nombre[unique])
   |
   | 1..N
camiseta_talla (camiseta_id FK, talla_id FK)   PK compuesta
   | N..1
camisetas (id, titulo, club, pais, tipo, color,
           precio, precio_oferta, detalles, sku[unique],
           created_at, updated_at)
   | 1..N
cliente_camiseta (cliente_id FK, camiseta_id FK, cantidad)   PK compuesta
   | N..1
clientes (id, nombre_comercial, rut[unique], ciudad, region,
          categoria, contacto_nombre, contacto_email,
          porcentaje_oferta, created_at, updated_at)
```

### Decisiones de diseño

- **`camiseta_talla`** resuelve la relación **muchos-a-muchos** entre camisetas y
  tallas con clave primaria compuesta.
- **`cliente_camiseta`** modela el stock/catálogo de cada cliente y permite
  aplicar la regla "no eliminar un cliente con camisetas asociadas". La FK usa
  `ON DELETE RESTRICT`, reforzada además en la capa de negocio (HTTP 409).
- **`sku`** y **`rut`** tienen índice `UNIQUE` para garantizar unicidad a nivel
  de base de datos, con validación previa en los modelos.
- `precio` y `precio_oferta` se almacenan como enteros (CLP, sin decimales).
  `precio_oferta` admite `NULL` para indicar "sin oferta".

## Reglas de negocio

1. **Precio final dinámico** (`services/PrecioService.php`):
   - Cliente `Preferencial`: `precio_final = precio_oferta` si está definido; si no, `precio`.
   - Cliente `Regular`: `precio_final = precio`.
2. **No eliminar** un cliente con camisetas asociadas → `409 Conflict`.
3. Relación camiseta ↔ talla **muchos-a-muchos** (`camiseta_talla`).
4. **SKU único** validado antes de persistir → `409 Conflict` si se repite.

## Instalación

```bash
# 1. Crear base de datos y cargar datos de ejemplo
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql

# 2. Servir con Apache (XAMPP) apuntando a la raíz del proyecto
#    http://localhost/todocamisetas/

# o con el servidor embebido de PHP
php -S 127.0.0.1:8090 index.php
```

Las credenciales de conexión pueden ajustarse con variables de entorno
(`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) o en `config/config.php`.
