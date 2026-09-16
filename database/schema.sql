-- =====================================================================
-- TodoCamisetas - Modelo relacional
-- Motor: MySQL / MariaDB
-- =====================================================================

CREATE DATABASE IF NOT EXISTS todocamisetas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE todocamisetas;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS cliente_camiseta;
DROP TABLE IF EXISTS camiseta_talla;
DROP TABLE IF EXISTS camisetas;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS tallas;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Tallas (catálogo). Relación muchos-a-muchos con camisetas.
-- ---------------------------------------------------------------------
CREATE TABLE tallas (
    id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(10)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tallas_nombre (nombre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Camisetas (stock).
-- ---------------------------------------------------------------------
CREATE TABLE camisetas (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo        VARCHAR(150) NOT NULL,
    club          VARCHAR(100) NOT NULL,
    pais          VARCHAR(80)  NOT NULL,
    tipo          ENUM('Local','Visita','Femenino') NOT NULL,
    color         VARCHAR(80)  NOT NULL,
    precio        INT UNSIGNED NOT NULL,
    detalles      TEXT         NULL,
    sku           VARCHAR(50)  NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_camisetas_sku (sku)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Relación muchos-a-muchos camiseta <-> talla.
-- ---------------------------------------------------------------------
CREATE TABLE camiseta_talla (
    camiseta_id INT UNSIGNED NOT NULL,
    talla_id    INT UNSIGNED NOT NULL,
    PRIMARY KEY (camiseta_id, talla_id),
    KEY idx_ct_talla (talla_id),
    CONSTRAINT fk_ct_camiseta FOREIGN KEY (camiseta_id) REFERENCES camisetas (id) ON DELETE CASCADE,
    CONSTRAINT fk_ct_talla    FOREIGN KEY (talla_id)    REFERENCES tallas (id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Clientes B2B.
-- ---------------------------------------------------------------------
CREATE TABLE clientes (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_comercial  VARCHAR(150) NOT NULL,
    rut               VARCHAR(20)  NOT NULL,
    ciudad            VARCHAR(100) NOT NULL,
    region            VARCHAR(100) NOT NULL,
    categoria         ENUM('Regular','Preferencial') NOT NULL DEFAULT 'Regular',
    contacto_nombre   VARCHAR(120) NOT NULL,
    contacto_email    VARCHAR(150) NOT NULL,
    porcentaje_oferta DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_rut (rut)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Asociación cliente <-> camiseta (stock por cliente).
-- Impide eliminar un cliente con camisetas asociadas (ON DELETE RESTRICT).
-- ---------------------------------------------------------------------
CREATE TABLE cliente_camiseta (
    cliente_id  INT UNSIGNED NOT NULL,
    camiseta_id INT UNSIGNED NOT NULL,
    cantidad    INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (cliente_id, camiseta_id),
    KEY idx_cc_camiseta (camiseta_id),
    CONSTRAINT fk_cc_cliente  FOREIGN KEY (cliente_id)  REFERENCES clientes (id)  ON DELETE RESTRICT,
    CONSTRAINT fk_cc_camiseta FOREIGN KEY (camiseta_id) REFERENCES camisetas (id) ON DELETE RESTRICT
) ENGINE=InnoDB;
