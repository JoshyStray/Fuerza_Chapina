-- =====================================================================
-- Fuerza Chapina · Sistema de gestión de membresías
-- Crea la base de datos desde cero, el usuario de la aplicación y
-- datos de prueba. Ejecutar con privilegios de root:
--   mysql -u root < database.sql     (o importar en phpMyAdmin)
-- =====================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS fuerza_chapina;
CREATE DATABASE fuerza_chapina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fuerza_chapina;

-- ---------------------------------------------------------------------
-- Usuario de la aplicación con privilegios mínimos (la app nunca usa root).
-- Solo puede leer, insertar, actualizar y borrar datos de esta base.
-- ---------------------------------------------------------------------
DROP USER IF EXISTS 'fc_app'@'localhost';
DROP USER IF EXISTS 'fc_app'@'127.0.0.1';
CREATE USER 'fc_app'@'localhost' IDENTIFIED BY 'FcApp2026!';
CREATE USER 'fc_app'@'127.0.0.1' IDENTIFIED BY 'FcApp2026!';
GRANT SELECT, INSERT, UPDATE, DELETE ON fuerza_chapina.* TO 'fc_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON fuerza_chapina.* TO 'fc_app'@'127.0.0.1';
FLUSH PRIVILEGES;

-- ---------------------------------------------------------------------
-- Tablas
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario       VARCHAR(30)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    rol           ENUM('administrador','recepcionista') NOT NULL,
    activo        TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT uq_usuarios_usuario UNIQUE (usuario)
) ENGINE=InnoDB;

CREATE TABLE socios (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo         VARCHAR(10)  NOT NULL,
    nombre         VARCHAR(100) NOT NULL,
    telefono       VARCHAR(15)  NULL,
    fecha_registro DATE         NOT NULL,
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT uq_socios_codigo UNIQUE (codigo)
) ENGINE=InnoDB;

CREATE TABLE planes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(50)   NOT NULL,
    duracion_dias SMALLINT UNSIGNED NOT NULL,
    precio        DECIMAL(10,2) NOT NULL,
    activo        TINYINT(1)    NOT NULL DEFAULT 1,
    CONSTRAINT uq_planes_nombre   UNIQUE (nombre),
    CONSTRAINT ck_planes_duracion CHECK (duracion_dias > 0),
    CONSTRAINT ck_planes_precio   CHECK (precio >= 0)
) ENGINE=InnoDB;

CREATE TABLE membresias (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    socio_id     INT UNSIGNED  NOT NULL,
    plan_id      INT UNSIGNED  NOT NULL,
    usuario_id   INT UNSIGNED  NULL,
    fecha_inicio DATE          NOT NULL,
    fecha_fin    DATE          NOT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    CONSTRAINT ck_membresias_fechas CHECK (fecha_fin >= fecha_inicio),
    CONSTRAINT ck_membresias_monto  CHECK (monto_pagado >= 0),
    CONSTRAINT fk_membresias_socio   FOREIGN KEY (socio_id)
        REFERENCES socios (id)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_membresias_plan    FOREIGN KEY (plan_id)
        REFERENCES planes (id)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_membresias_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_membresias_socio_fin (socio_id, fecha_fin)
) ENGINE=InnoDB;

-- fecha es una columna generada: siempre coincide con la fecha de fecha_hora.
CREATE TABLE asistencias (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    socio_id   INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    fecha_hora DATETIME     NOT NULL,
    fecha      DATE GENERATED ALWAYS AS (DATE(fecha_hora)) STORED,
    CONSTRAINT uq_asistencias_socio_fecha UNIQUE (socio_id, fecha),
    CONSTRAINT fk_asistencias_socio   FOREIGN KEY (socio_id)
        REFERENCES socios (id)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_asistencias_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Intentos fallidos de inicio de sesión (protección contra fuerza bruta).
CREATE TABLE intentos_login (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario    VARCHAR(30) NOT NULL,
    ip         VARCHAR(45) NOT NULL,
    fecha_hora DATETIME    NOT NULL,
    INDEX idx_intentos_busqueda (usuario, ip, fecha_hora)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Datos de prueba
-- Contraseñas:  admin / Admin2026!   ·   recepcion / Recep2026!
-- ---------------------------------------------------------------------
INSERT INTO usuarios (usuario, password_hash, nombre, rol) VALUES
('admin',     '$2y$10$POzQqiqEHSpf4Niqb0CMJ.h9ZeYkIhIb2n.hv317FqMBG9jAM00yu', 'Administrador General', 'administrador'),
('recepcion', '$2y$10$nx/Occf6lfyK2KzjorvaIuTi1ViKP3GsYDRZa17ko/2FmXDVDrNOW', 'Recepción Principal',   'recepcionista');

INSERT INTO planes (nombre, duracion_dias, precio) VALUES
('Mensual',    30,  250.00),
('Trimestral', 90,  675.00),
('Anual',     365, 2400.00);

INSERT INTO socios (codigo, nombre, telefono, fecha_registro) VALUES
('S-001', 'Carlos Pérez',        '5512-3401', DATE_SUB(CURDATE(), INTERVAL 400 DAY)),
('S-002', 'María López',         '5523-8812', DATE_SUB(CURDATE(), INTERVAL 300 DAY)),
('S-003', 'José Ramírez',        '4455-9023', DATE_SUB(CURDATE(), INTERVAL 250 DAY)),
('S-004', 'Ana Morales',         '3098-1177', DATE_SUB(CURDATE(), INTERVAL 200 DAY)),
('S-005', 'Luis Hernández',      '5544-2210', DATE_SUB(CURDATE(), INTERVAL 150 DAY)),
('S-006', 'Sofía Castillo',      '4120-7765', DATE_SUB(CURDATE(), INTERVAL 120 DAY)),
('S-007', 'Diego Méndez',        '5033-6642', DATE_SUB(CURDATE(), INTERVAL 90 DAY)),
('S-008', 'Lucía García',        '5788-3300', DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
('S-009', 'Pedro Alvarado',      '4677-1289', DATE_SUB(CURDATE(), INTERVAL 45 DAY)),
('S-010', 'Karla Súchite',       '5390-4471', DATE_SUB(CURDATE(), INTERVAL 30 DAY)),
('S-011', 'Andrés Cifuentes',    '3211-9054', DATE_SUB(CURDATE(), INTERVAL 20 DAY)),
('S-012', 'Gabriela Orellana',   '5902-1166', DATE_SUB(CURDATE(), INTERVAL 10 DAY));

-- fecha_fin = fecha_inicio + duración del plan (RN-01). Fechas relativas a CURDATE().
INSERT INTO membresias (socio_id, plan_id, usuario_id, fecha_inicio, fecha_fin, monto_pagado) VALUES
(1,  3, 1, DATE_SUB(CURDATE(), INTERVAL 100 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 100 DAY), INTERVAL 365 DAY), 2400.00),
(2,  1, 2, DATE_SUB(CURDATE(), INTERVAL 25 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 25 DAY),  INTERVAL 30 DAY),   250.00),
(3,  2, 2, DATE_SUB(CURDATE(), INTERVAL 40 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 40 DAY),  INTERVAL 90 DAY),   675.00),
(4,  1, 2, DATE_SUB(CURDATE(), INTERVAL 60 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 60 DAY),  INTERVAL 30 DAY),   250.00),
(5,  1, 1, DATE_SUB(CURDATE(), INTERVAL 28 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 28 DAY),  INTERVAL 30 DAY),   250.00),
(6,  2, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 10 DAY),  INTERVAL 90 DAY),   675.00),
(7,  1, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY),   DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 5 DAY),   INTERVAL 30 DAY),   250.00),
(8,  1, 2, DATE_SUB(CURDATE(), INTERVAL 55 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 55 DAY),  INTERVAL 30 DAY),   250.00),
(9,  3, 1, DATE_SUB(CURDATE(), INTERVAL 200 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 200 DAY), INTERVAL 365 DAY), 2400.00),
(10, 1, 2, DATE_SUB(CURDATE(), INTERVAL 27 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 27 DAY),  INTERVAL 30 DAY),   250.00),
(11, 2, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY),   DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 2 DAY),   INTERVAL 90 DAY),   675.00),
(12, 1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY),   DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 DAY),   INTERVAL 30 DAY),   250.00),
-- Renovación anterior de María López (historial: un socio puede tener varias membresías)
(2,  1, 2, DATE_SUB(CURDATE(), INTERVAL 55 DAY),  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 55 DAY),  INTERVAL 30 DAY),   250.00);

INSERT INTO asistencias (socio_id, usuario_id, fecha_hora) VALUES
(1, 2, NOW()),
(2, 2, NOW()),
(1, 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 2, DATE_SUB(NOW(), INTERVAL 3 DAY));