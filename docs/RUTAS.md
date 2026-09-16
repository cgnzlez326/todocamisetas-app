# Rutas de la API - TodoCamisetas

Enrutamiento manual (`core/Router.php`) que casa expresiones regulares sobre la
ruta normalizada (sin query string) y el método `$_SERVER['REQUEST_METHOD']`.
Los grupos con nombre (`(?P<id>\d+)`) se inyectan como argumentos al controlador.

Formato de respuesta: `Content-Type: application/json; charset=utf-8`.

| Método | Expresión regular | Propósito | Controlador |
|--------|-------------------|-----------|-------------|
| GET | `#^/$#` | Salud del servicio | `HomeController::index` |
| GET | `#^/api$#` | Índice de recursos | `HomeController::info` |
| GET | `#^/api/camisetas$#` | Listar camisetas (con tallas) | `CamisetaController::index` |
| POST | `#^/api/camisetas$#` | Crear camiseta | `CamisetaController::store` |
| GET | `#^/api/camisetas/sku/(?P<sku>[^/]+)$#` | Ver camiseta por SKU | `CamisetaController::showBySku` |
| GET | `#^/api/camisetas/(?P<id>\d+)$#` | Ver camiseta | `CamisetaController::show` |
| PUT | `#^/api/camisetas/(?P<id>\d+)$#` | Actualizar camiseta | `CamisetaController::update` |
| DELETE | `#^/api/camisetas/(?P<id>\d+)$#` | Eliminar camiseta | `CamisetaController::destroy` |
| GET | `#^/api/clientes$#` | Listar clientes | `ClienteController::index` |
| POST | `#^/api/clientes$#` | Crear cliente | `ClienteController::store` |
| GET | `#^/api/clientes/(?P<id>\d+)$#` | Ver cliente | `ClienteController::show` |
| PUT | `#^/api/clientes/(?P<id>\d+)$#` | Actualizar cliente | `ClienteController::update` |
| DELETE | `#^/api/clientes/(?P<id>\d+)$#` | Eliminar cliente (409 si tiene camisetas) | `ClienteController::destroy` |
| GET | `#^/api/clientes/(?P<id>\d+)/camisetas$#` | Camisetas del cliente + `precio_final` | `ClienteController::camisetas` |
| POST | `#^/api/clientes/(?P<id>\d+)/camisetas$#` | Asociar camiseta al cliente | `ClienteController::asociar` |
| GET | `#^/api/clientes/(?P<id>\d+)/camisetas/(?P<camisetaId>\d+)/precio$#` | Precio final de una camiseta para el cliente | `ClienteController::precio` |
| DELETE | `#^/api/clientes/(?P<id>\d+)/camisetas/(?P<camisetaId>\d+)$#` | Desasociar camiseta del cliente | `ClienteController::desasociar` |
| GET | `#^/api/tallas$#` | Listar tallas | `TallaController::index` |
| POST | `#^/api/tallas$#` | Crear talla | `TallaController::store` |
| GET | `#^/api/tallas/(?P<id>\d+)$#` | Ver talla | `TallaController::show` |
| DELETE | `#^/api/tallas/(?P<id>\d+)$#` | Eliminar talla | `TallaController::destroy` |

## Códigos HTTP

| Código | Uso |
|--------|-----|
| 200 | Consulta/actualización/eliminación exitosa |
| 201 | Recurso creado o asociación creada |
| 400 | Datos inválidos (detalle por campo en `error.details`) |
| 404 | Recurso no encontrado |
| 405 | Método HTTP no permitido para la ruta |
| 409 | Conflicto: SKU/RUT/talla duplicada o cliente con camisetas |
| 500 | Error interno controlado |

## Ejemplos

```bash
# Listar camisetas
curl http://localhost/todocamisetas/api/camisetas

# Crear camiseta
curl -X POST http://localhost/todocamisetas/api/camisetas \
  -H "Content-Type: application/json" \
  -d '{"titulo":"Camiseta Local 2025","club":"Colo-Colo","pais":"Chile","tipo":"Local","color":"Blanco y Negro","precio":42000,"precio_oferta":37000,"sku":"CL-COL-LOC-2025","tallas":[3,4,5]}'

# Precio final de la camiseta 1 para el cliente preferencial 1
curl http://localhost/todocamisetas/api/clientes/1/camisetas/1/precio
```

La especificación completa (parámetros, cuerpos y respuestas) está en
[`../swagger-ui/openapi.yaml`](../swagger-ui/openapi.yaml) y se visualiza con
Swagger UI (release oficial local, sin CDN) en `/swagger-ui/`.
