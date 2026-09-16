# **Caso de Negocio: "Todo Camisetas"**

La empresa **TodoCamisetas**, fundada en 2023 y con sede en Santiago, Chile, es un proveedor mayorista especializado en camisetas de fútbol de clubes nacionales e internacionales. Sus fundadores, Matías López y Camila Fuentes, detectaron que muchos vendedores locales carecían de acceso directo a productos de calidad a precios competitivos, por lo que decidieron crear un modelo de negocio basado en ventas B2B a tiendas minoristas en varias regiones del país.

Actualmente, TodoCamisetas cuenta con dos clientes principales: **Tienda 90minutos** y la **Tienda tdeportes**. Para sostener esta operación, TodoCamisetas requiere desarrollar una **API RESTful en PHP puro (sin frameworks)** que sirva como columna vertebral de su sistema de gestión de inventario y de relación con clientes.

## **Requerimientos de la API**

### **1. Gestión de Camisetas (Stock)**

CRUD completo de productos, donde cada "camiseta" tiene los siguientes atributos:

* **Título:** Nombre descriptivo (p. ej., "Camiseta Local 2025 Selección Chilena").  
* **Club:** Nombre del club al que pertenece.  
* **País:** Procedencia del diseño.  
* **Tipo:** Clasificación del modelo (p. ej., "Local", "Visita", "Femenino").  
* **Color:** Combinación principal de colores.  
* **Precio:** Valor base en pesos chilenos.  
* **Tallas disponibles:** Una o varias tallas asociadas (p. ej., "S", "M", "L", "XL").  
* **Detalles:** Texto adicional o descripción técnica.  
* **Código de Producto:** SKU único identificador.

### **2. Gestión de Clientes**

CRUD completo de clientes B2B, almacenando:

* **Nombre Comercial:** Identidad de la empresa cliente.  
* **RUT o ID Comercial:** Identificador tributario oficial.  
* **Dirección:** Ubicación física (Ciudad y región).  
* **Categoría:** Definida como "Regular" o "Preferencial".  
* **Contacto:** Nombre y correo electrónico del encargado.  
* **Porcentaje de oferta:** Campo para gestionar descuentos personalizados.

### **3. Logística de Precios de Oferta**

La API debe calcular dinámicamente el precio final según el cliente:

* **Cliente Preferencial ("90minutos"):** Si existe un `precio_oferta` definido para la camiseta, el sistema debe devolver dicho valor en el campo `precio_final`.  
* **Cliente Regular ("tdeportes"):** El sistema debe devolver en el campo `precio_final` el valor de `precio` base.

# **Tareas a Ejecutar**

## **Tarea 1: Arquitectura y Modelo de Datos**

* **Diseño de la arquitectura de archivos:** Trace un diagrama que muestre la estructura de directorios completa (vistas, controladores, rutas, modelos, etc.) y explique la función de cada componente.  
* **Modelo de datos:** Diseñe un modelo relacional sencillo que soporte ambas entidades y sus interconexiones.

## **Tarea 2: Solicitudes HTTP y Enrutamiento**

* **Enrutamiento en PHP puro:** Implemente la lógica de rutas mediante expresiones regulares.

´´´
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET'){
    // Lógica para procesar solicitudes de lectura
    }
´´´

* **Documentación de rutas:** Especifique para cada endpoint la expresión regular, el método HTTP y su propósito funcional.  
* **Controladores:** Defina métodos estáticos (`show`, `store`, `update`, `destroy`) con validación de datos.

## **Tarea 3: Implementación de Modelos y Documentación**

* **Implementación de modelos:** En la capa `models`, defina la interacción con la base de datos.
´´´
    /**
        * Actualiza los datos de una camiseta.
        * @param int $id
        * @param array $data
        * @return bool
    */
    
    public static function update(int $id, array $data): bool {
    // Sentencia SQL: UPDATE WHERE id=:id
    }
´´´

* **Endpoints RESTful:** Documente mediante Swagger (o formato similar) los métodos, rutas, parámetros, cuerpos JSON y códigos de error.

## **Tarea 4: Interacción Frontend-Backend**

* **Respuesta JSON:** Asegure que todos los métodos devuelvan la cabecera `Content-Type: application/json`.  
* **Colección Postman:** Entregue una colección organizada con ejemplos de solicitudes y respuestas para cada servicio.

## **Tarea 5: Operaciones CRUD Avanzadas**

* **Lógica de Negocio:** Implemente restricciones (p. ej., no eliminar clientes que posean camisetas asociadas).  
* **Gestión de Tallas:** Construya la lógica para gestionar la relación de muchos a muchos entre productos y tallas.

## **Tarea 6: Optimización de Consultas**

* **Precio Final:** Implemente el cálculo de reglas de negocio en el endpoint de consulta para determinar el precio final dinámico según el perfil del cliente.