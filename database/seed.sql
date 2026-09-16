-- =====================================================================
-- TodoCamisetas - Datos de ejemplo
-- =====================================================================

USE todocamisetas;

INSERT INTO tallas (nombre) VALUES
    ('XS'), ('S'), ('M'), ('L'), ('XL'), ('XXL');

INSERT INTO camisetas (titulo, club, pais, tipo, color, precio, precio_oferta, detalles, sku) VALUES
    ('Camiseta Local 2025 Seleccion Chilena', 'Seleccion Chilena', 'Chile', 'Local', 'Rojo', 45000, 39000, 'Tela Dry-Fit, corte regular.', 'CL-SEL-LOC-2025'),
    ('Camiseta Visita 2025 Seleccion Chilena', 'Seleccion Chilena', 'Chile', 'Visita', 'Blanco', 45000, NULL, 'Tela Dry-Fit, corte regular.', 'CL-SEL-VIS-2025'),
    ('Camiseta Local 2025 Colo-Colo', 'Colo-Colo', 'Chile', 'Local', 'Blanco y Negro', 42000, 37000, 'Edicion centenario.', 'CL-COL-LOC-2025'),
    ('Camiseta Local 2025 Universidad de Chile', 'Universidad de Chile', 'Chile', 'Local', 'Azul', 42000, NULL, 'Tela transpirable.', 'CL-UCH-LOC-2025'),
    ('Camiseta Femenino 2025 Barcelona', 'Barcelona', 'Espana', 'Femenino', 'Blaugrana', 52000, 48000, 'Corte femenino ajustado.', 'ES-BAR-FEM-2025'),
    ('Camiseta Local 2025 Real Madrid', 'Real Madrid', 'Espana', 'Local', 'Blanco', 55000, 49990, 'Temporada 2025.', 'ES-RMA-LOC-2025');

INSERT INTO camiseta_talla (camiseta_id, talla_id) VALUES
    (1, 2), (1, 3), (1, 4), (1, 5),
    (2, 2), (2, 3), (2, 4),
    (3, 3), (3, 4), (3, 5), (3, 6),
    (4, 2), (4, 3), (4, 4),
    (5, 1), (5, 2), (5, 3),
    (6, 3), (6, 4), (6, 5);

INSERT INTO clientes (nombre_comercial, rut, ciudad, region, categoria, contacto_nombre, contacto_email, porcentaje_oferta) VALUES
    ('Tienda 90minutos', '76.123.456-7', 'Santiago', 'Metropolitana', 'Preferencial', 'Matias Lopez', 'compras@90minutos.cl', 10.00),
    ('Tienda tdeportes', '77.987.654-3', 'Concepcion', 'Biobio', 'Regular', 'Camila Fuentes', 'contacto@tdeportes.cl', 0.00);

INSERT INTO cliente_camiseta (cliente_id, camiseta_id, cantidad) VALUES
    (1, 1, 20), (1, 3, 15), (1, 6, 10),
    (2, 2, 12), (2, 4, 8);
