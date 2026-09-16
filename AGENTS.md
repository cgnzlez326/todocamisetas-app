# AGENTS.md

Reglas y contexto para trabajar en este proyecto. Leer siempre antes de programar.

## Contexto
Este repositorio corresponde **solo a la capa Backend**. La capa Frontend es otro proyecto
que consume estos endpoints.

## Contexto de negocio
**TodoCamisetas** (Santiago, Chile, 2023) es un proveedor **mayorista B2B** de camisetas de
fútbol de clubes nacionales e internacionales. La API RESTful es la columna vertebral de su
sistema de gestión de inventario y relación con clientes.

- Clientes actuales: **Tienda 90minutos** (categoría *Preferencial*) y **Tienda tdeportes**
  (categoría *Regular*).

### Entidades y atributos
- **Camiseta:** título, club, país, tipo ("Local", "Visita", "Femenino"), color, precio (CLP),
  tallas disponibles (una o varias), detalles, código de producto (SKU **único**).
- **Cliente B2B:** nombre comercial, RUT/ID comercial, dirección (ciudad y región),
  categoría ("Regular" | "Preferencial"), contacto (nombre y correo), porcentaje de oferta.

### Reglas de negocio (obligatorias)
1. **Precio final dinámico** según el cliente:
   - Cliente **Preferencial**: `precio_final` = precio base menos su `porcentaje_oferta`
     (redondeado al CLP). Si el porcentaje es 0, se usa el precio base.
   - Cliente **Regular**: `precio_final` siempre es el `precio` base.
   - El descuento vive en el cliente (`porcentaje_oferta`); la camiseta solo tiene `precio`.
2. **No eliminar** un cliente que tenga camisetas asociadas (rechazar con error claro).
3. La relación **camiseta ↔ talla** es **muchos a muchos**.
4. El **SKU** es único; validar antes de persistir.

## Reglas del proyecto (obligatorias)

1. **PHP puro**: no usar Laravel, CodeIgniter ni frameworks equivalentes.
2. **Arquitectura MVC**.
3. **Base de datos MySQL**.
4. **Respetar SOLID**.
5. **Mantenerlo simple**: sin sobreingeniería ni dependencias externas.
   Única excepción: `swagger-ui`, cuyo release oficial se descarga y sirve localmente (no usar CDN).
6. Validar datos obligatorios antes de persistir.
7. Respuestas consistentes y utilizables desde el Frontend.
8. Manejo de errores claro, sin respuestas ambiguas ni fallas no controladas.
9. Documentar la API con Swagger/OpenAPI, verificando que coincida con el comportamiento real.

## Restricciones técnicas

### Arquitectura y enrutamiento
- Estructura MVC explícita: `models`, `controllers`, `routes`, `views` (vistas solo si aplica;
  la API entrega JSON). Cada componente debe tener una única responsabilidad.
- Enrutamiento **manual con expresiones regulares** sobre `$_SERVER['REQUEST_METHOD']` y la
  URI. No usar librerías de routing.
- Cada endpoint debe documentarse indicando su expresión regular, método HTTP y propósito.

### Controladores
- Métodos **estáticos**: `show`, `store`, `update`, `destroy` (y los de consulta necesarios).
- Toda entrada se **valida** antes de persistir; responder error claro si falta un campo
  obligatorio.

### Modelos
- Acceso a MySQL con **sentencias preparadas** (PDO o MySQLi), sin concatenar SQL.
- Métodos estáticos documentados con docblock (`@param`, `@return`).
- El cálculo de `precio_final` según la categoría del cliente vive en la lógica de negocio,
  no duplicado en las vistas.

### Respuestas
- Toda respuesta debe enviar la cabecera `Content-Type: application/json`.
- Estructura JSON consistente (datos y errores) para ser consumible por el Frontend.
- Usar códigos HTTP adecuados (200/201/400/404/409/500 según el caso).

## Entregables
- Documentación de la API con Swagger/OpenAPI (coherente con el comportamiento real).
- Colección Postman organizada con ejemplos de solicitud y respuesta por endpoint.
- Modelo relacional que soporte camisetas, clientes, tallas y sus interconexiones.