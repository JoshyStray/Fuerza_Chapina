-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-10-2026 a las 03:26:58
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fuerza_chapina`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(10) UNSIGNED NOT NULL,
  `socio_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `socio_id`, `usuario_id`, `fecha`, `fecha_hora`) VALUES
(1, 1, 2, '2026-10-24', '2026-10-24 20:25:22'),
(2, 2, 2, '2026-10-24', '2026-10-24 20:25:22'),
(3, 1, 2, '2026-10-23', '2026-10-23 20:25:22'),
(4, 5, 2, '2026-10-23', '2026-10-23 20:25:22'),
(5, 6, 2, '2026-10-22', '2026-10-22 20:25:22'),
(6, 7, 2, '2026-10-21', '2026-10-21 20:25:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresias`
--

CREATE TABLE `membresias` (
  `id` int(10) UNSIGNED NOT NULL,
  `socio_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `monto_pagado` decimal(10,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `membresias`
--

INSERT INTO `membresias` (`id`, `socio_id`, `plan_id`, `usuario_id`, `fecha_inicio`, `fecha_fin`, `monto_pagado`) VALUES
(1, 1, 3, 1, '2026-07-16', '2027-07-16', 2400.00),
(2, 2, 1, 2, '2026-09-29', '2026-10-29', 250.00),
(3, 3, 2, 2, '2026-09-14', '2026-12-13', 675.00),
(4, 4, 1, 2, '2026-08-25', '2026-09-24', 250.00),
(5, 5, 1, 1, '2026-09-26', '2026-10-26', 250.00),
(6, 6, 2, 2, '2026-10-14', '2027-01-12', 675.00),
(7, 7, 1, 2, '2026-10-19', '2026-11-18', 250.00),
(8, 8, 1, 2, '2026-08-30', '2026-09-29', 250.00),
(9, 9, 3, 1, '2026-04-07', '2027-04-07', 2400.00),
(10, 10, 1, 2, '2026-09-27', '2026-10-27', 250.00),
(11, 11, 2, 2, '2026-10-22', '2027-01-20', 675.00),
(12, 12, 1, 2, '2026-10-23', '2026-11-22', 250.00),
(13, 2, 1, 2, '2026-08-30', '2026-09-29', 250.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `duracion_dias` smallint(5) UNSIGNED NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `nombre`, `duracion_dias`, `precio`, `activo`) VALUES
(1, 'Mensual', 30, 250.00, 1),
(2, 'Trimestral', 90, 675.00, 1),
(3, 'Anual', 365, 2400.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id`, `codigo`, `nombre`, `telefono`, `fecha_registro`, `activo`) VALUES
(1, 'S-001', 'Carlos Pérez', '5512-3401', '2025-09-19', 1),
(2, 'S-002', 'María López', '5523-8812', '2025-12-28', 1),
(3, 'S-003', 'José Ramírez', '4455-9023', '2026-02-16', 1),
(4, 'S-004', 'Ana Morales', '3098-1177', '2026-04-07', 1),
(5, 'S-005', 'Luis Hernández', '5544-2210', '2026-05-27', 1),
(6, 'S-006', 'Sofía Castillo', '4120-7765', '2026-06-26', 1),
(7, 'S-007', 'Diego Méndez', '5033-6642', '2026-07-26', 1),
(8, 'S-008', 'Lucía García', '5788-3300', '2026-08-25', 1),
(9, 'S-009', 'Pedro Alvarado', '4677-1289', '2026-09-09', 1),
(10, 'S-010', 'Karla Súchite', '5390-4471', '2026-09-24', 1),
(11, 'S-011', 'Andrés Cifuentes', '3211-9054', '2026-10-04', 1),
(12, 'S-012', 'Gabriela Orellana', '5902-1166', '2026-10-14', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('administrador','recepcionista') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password_hash`, `nombre`, `rol`, `activo`) VALUES
(1, 'admin', '$2y$10$POzQqiqEHSpf4Niqb0CMJ.h9ZeYkIhIb2n.hv317FqMBG9jAM00yu', 'Administrador General', 'administrador', 1),
(2, 'recepcion', '$2y$10$nx/Occf6lfyK2KzjorvaIuTi1ViKP3GsYDRZa17ko/2FmXDVDrNOW', 'Recepción Principal', 'recepcionista', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_asistencias_socio_fecha` (`socio_id`,`fecha`),
  ADD KEY `fk_asistencias_usuario` (`usuario_id`);

--
-- Indices de la tabla `membresias`
--
ALTER TABLE `membresias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_membresias_plan` (`plan_id`),
  ADD KEY `fk_membresias_usuario` (`usuario_id`),
  ADD KEY `idx_membresias_socio_fin` (`socio_id`,`fecha_fin`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_planes_nombre` (`nombre`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_socios_codigo` (`codigo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `membresias`
--
ALTER TABLE `membresias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencias_socio` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asistencias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `membresias`
--
ALTER TABLE `membresias`
  ADD CONSTRAINT `fk_membresias_plan` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_membresias_socio` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_membresias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
