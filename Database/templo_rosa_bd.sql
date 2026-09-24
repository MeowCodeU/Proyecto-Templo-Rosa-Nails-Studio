-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-09-2026 a las 05:25:06
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
-- Base de datos: `templo_rosa_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agendamientos`
--

CREATE TABLE `agendamientos` (
  `id_agendamiento` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario_registra` int(10) UNSIGNED NOT NULL,
  `id_usuario_asignado` int(10) UNSIGNED NOT NULL,
  `id_estado_agendamiento` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `id_tipo_agendamiento` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `duracion_minutos` smallint(5) UNSIGNED NOT NULL,
  `observacion_inicial` text DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloqueos_horarios`
--

CREATE TABLE `bloqueos_horarios` (
  `id_bloqueo` int(10) UNSIGNED NOT NULL,
  `id_usuario_especialista` int(10) UNSIGNED NOT NULL,
  `id_usuario_registra` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `estado_bloqueo` enum('BLOQUEADO','DESBLOQUEADO') NOT NULL DEFAULT 'BLOQUEADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_persona` int(10) UNSIGNED NOT NULL,
  `alergias` text DEFAULT NULL,
  `estado_cliente` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consumos_insumos`
--

CREATE TABLE `consumos_insumos` (
  `id_consumo` int(10) UNSIGNED NOT NULL,
  `id_agendamiento` int(10) UNSIGNED NOT NULL,
  `id_insumo` int(10) UNSIGNED NOT NULL,
  `cantidad_usada` decimal(10,2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_agendamientos`
--

CREATE TABLE `detalles_agendamientos` (
  `id_detalle_agendamiento` int(10) UNSIGNED NOT NULL,
  `id_agendamiento` int(10) UNSIGNED NOT NULL,
  `id_servicio` int(10) UNSIGNED NOT NULL,
  `nota_tecnica` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_agendamientos`
--

CREATE TABLE `estados_agendamientos` (
  `id_estado_agendamiento` int(10) UNSIGNED NOT NULL,
  `nombre_estado` varchar(50) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `estado_registro` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados_agendamientos`
--

INSERT INTO `estados_agendamientos` (`id_estado_agendamiento`, `nombre_estado`, `descripcion`, `estado_registro`) VALUES
(1, 'AGENDADA', 'La cita fue registrada y se encuentra agendada', 'ACTIVO'),
(2, 'CONFIRMADA', 'La cita fue confirmada', 'ACTIVO'),
(3, 'EN ESPERA', 'La clienta se encuentra esperando para ser atendida', 'ACTIVO'),
(4, 'DEMORADA', 'La atención presenta una demora', 'ACTIVO'),
(5, 'EN ATENCIÓN', 'La atención se encuentra en proceso', 'ACTIVO'),
(6, 'REALIZADA', 'La atención fue completada', 'ACTIVO'),
(7, 'CANCELADA', 'La cita fue cancelada', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_servicios`
--

CREATE TABLE `fotos_servicios` (
  `id_foto` int(10) UNSIGNED NOT NULL,
  `id_detalle_agendamiento` int(10) UNSIGNED NOT NULL,
  `foto` mediumblob NOT NULL,
  `destacada_catalogo` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insumos`
--

CREATE TABLE `insumos` (
  `id_insumo` int(10) UNSIGNED NOT NULL,
  `nombre_insumo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `presentacion` varchar(80) DEFAULT NULL,
  `tipo_control` enum('UNITARIO','POR_ENVASE') NOT NULL DEFAULT 'UNITARIO',
  `stock_actual` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stock_minimo` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado_insumo` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id_persona` int(10) UNSIGNED NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `apellido` varchar(60) NOT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(10) UNSIGNED NOT NULL,
  `id_persona_contacto` int(10) UNSIGNED NOT NULL,
  `rif` varchar(25) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `estado_proveedor` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores_insumos`
--

CREATE TABLE `proveedores_insumos` (
  `id_proveedor_insumo` int(10) UNSIGNED NOT NULL,
  `id_proveedor` int(10) UNSIGNED NOT NULL,
  `id_insumo` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre_rol` varchar(40) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `estado_rol` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`, `estado_rol`) VALUES
(1, 'ADMINISTRADORA DEL NEGOCIO', 'Gestiona los procesos operativos del negocio', 'ACTIVO'),
(2, 'ESPECIALISTA EN MANICURA', 'Realiza y registra los servicios de manicura', 'ACTIVO'),
(3, 'ADMINISTRADOR DEL SISTEMA', 'Gestiona los usuarios, roles y accesos del sistema', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_servicio` int(10) UNSIGNED NOT NULL,
  `nombre_servicio` varchar(120) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duracion_estimada` smallint(5) UNSIGNED DEFAULT NULL,
  `estado_servicio` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_servicio`, `nombre_servicio`, `descripcion`, `precio`, `duracion_estimada`, `estado_servicio`) VALUES
(1, 'Manicura rusa', 'Servicio de manicura rusa', 10.00, 60, 'ACTIVO'),
(2, 'Manicura con nivelación', 'Servicio de manicura que incluye nivelación', 10.00, 90, 'ACTIVO'),
(3, 'Manicura con capping de polygel', 'Servicio de manicura realizado con capping de polygel', 10.00, 120, 'ACTIVO'),
(4, 'Manicura con sistema soft gel', 'Servicio de manicura realizado con sistema soft gel', 10.00, 120, 'ACTIVO'),
(5, 'Manicura con Jelly Tips', 'Servicio de manicura realizado con Jelly Tips', 10.00, 120, 'ACTIVO'),
(6, 'Pedicura', 'Servicio de pedicura', 10.00, 60, 'ACTIVO'),
(7, 'Extensiones', 'Extensiones agregadas al servicio realizado', 3.00, 30, 'ACTIVO'),
(8, 'Reparaciones', 'Reparación de una o varias uñas', 1.00, 15, 'ACTIVO'),
(9, 'Full set', 'Servicio de full set', 3.00, 30, 'ACTIVO'),
(10, 'Encapsulados e incrustaciones', 'Servicio de encapsulados o incrustaciones', 3.00, 30, 'ACTIVO'),
(11, 'Retiro de esmalte', 'Retiro del esmalte anterior', 2.00, 30, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_agendamientos`
--

CREATE TABLE `tipos_agendamientos` (
  `id_tipo_agendamiento` int(10) UNSIGNED NOT NULL,
  `nombre_tipo_agendamiento` varchar(60) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `estado_tipo_agendamiento` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_agendamientos`
--

INSERT INTO `tipos_agendamientos` (`id_tipo_agendamiento`, `nombre_tipo_agendamiento`, `descripcion`, `estado_tipo_agendamiento`) VALUES
(1, 'CITA PROGRAMADA', 'Cita reservada previamente con fecha y hora acordadas', 'ACTIVO'),
(2, 'CITA INMEDIATA', 'Cita solicitada sin reserva previa, sujeta a disponibilidad', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_persona` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `clave` varchar(255) NOT NULL,
  `estado_usuario` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agendamientos`
--
ALTER TABLE `agendamientos`
  ADD PRIMARY KEY (`id_agendamiento`),
  ADD KEY `idx_agendamientos_cliente` (`id_cliente`),
  ADD KEY `idx_agendamientos_usuario_registra` (`id_usuario_registra`),
  ADD KEY `idx_agendamientos_usuario_asignado` (`id_usuario_asignado`),
  ADD KEY `idx_agendamientos_estado` (`id_estado_agendamiento`),
  ADD KEY `idx_agendamientos_tipo` (`id_tipo_agendamiento`),
  ADD KEY `idx_agendamientos_fecha_hora` (`fecha`,`hora`);

--
-- Indices de la tabla `bloqueos_horarios`
--
ALTER TABLE `bloqueos_horarios`
  ADD PRIMARY KEY (`id_bloqueo`),
  ADD KEY `idx_bloqueo_especialista` (`id_usuario_especialista`),
  ADD KEY `idx_bloqueo_usuario_registra` (`id_usuario_registra`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `uq_clientes_persona` (`id_persona`);

--
-- Indices de la tabla `consumos_insumos`
--
ALTER TABLE `consumos_insumos`
  ADD PRIMARY KEY (`id_consumo`),
  ADD KEY `idx_consumos_agendamiento` (`id_agendamiento`),
  ADD KEY `idx_consumos_insumo` (`id_insumo`);

--
-- Indices de la tabla `detalles_agendamientos`
--
ALTER TABLE `detalles_agendamientos`
  ADD PRIMARY KEY (`id_detalle_agendamiento`),
  ADD UNIQUE KEY `uq_detalle_agendamiento_servicio` (`id_agendamiento`,`id_servicio`),
  ADD KEY `idx_detalles_servicio` (`id_servicio`);

--
-- Indices de la tabla `estados_agendamientos`
--
ALTER TABLE `estados_agendamientos`
  ADD PRIMARY KEY (`id_estado_agendamiento`),
  ADD UNIQUE KEY `uq_estados_agendamientos_nombre` (`nombre_estado`);

--
-- Indices de la tabla `fotos_servicios`
--
ALTER TABLE `fotos_servicios`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `idx_fotos_detalle` (`id_detalle_agendamiento`);

--
-- Indices de la tabla `insumos`
--
ALTER TABLE `insumos`
  ADD PRIMARY KEY (`id_insumo`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`),
  ADD UNIQUE KEY `uq_personas_cedula` (`cedula`),
  ADD KEY `idx_personas_correo` (`correo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `uq_proveedores_rif` (`rif`),
  ADD KEY `idx_proveedores_persona_contacto` (`id_persona_contacto`),
  ADD KEY `idx_proveedores_estado` (`estado_proveedor`);

--
-- Indices de la tabla `proveedores_insumos`
--
ALTER TABLE `proveedores_insumos`
  ADD PRIMARY KEY (`id_proveedor_insumo`),
  ADD UNIQUE KEY `uq_proveedor_insumo` (`id_proveedor`,`id_insumo`),
  ADD KEY `idx_proveedores_insumos_insumo` (`id_insumo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre_rol`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`),
  ADD UNIQUE KEY `uq_servicios_nombre` (`nombre_servicio`);

--
-- Indices de la tabla `tipos_agendamientos`
--
ALTER TABLE `tipos_agendamientos`
  ADD PRIMARY KEY (`id_tipo_agendamiento`),
  ADD UNIQUE KEY `uq_tipos_agendamientos_nombre` (`nombre_tipo_agendamiento`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_persona` (`id_persona`),
  ADD KEY `idx_usuarios_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agendamientos`
--
ALTER TABLE `agendamientos`
  MODIFY `id_agendamiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bloqueos_horarios`
--
ALTER TABLE `bloqueos_horarios`
  MODIFY `id_bloqueo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consumos_insumos`
--
ALTER TABLE `consumos_insumos`
  MODIFY `id_consumo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_agendamientos`
--
ALTER TABLE `detalles_agendamientos`
  MODIFY `id_detalle_agendamiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_agendamientos`
--
ALTER TABLE `estados_agendamientos`
  MODIFY `id_estado_agendamiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `fotos_servicios`
--
ALTER TABLE `fotos_servicios`
  MODIFY `id_foto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `insumos`
--
ALTER TABLE `insumos`
  MODIFY `id_insumo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores_insumos`
--
ALTER TABLE `proveedores_insumos`
  MODIFY `id_proveedor_insumo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_servicio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipos_agendamientos`
--
ALTER TABLE `tipos_agendamientos`
  MODIFY `id_tipo_agendamiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agendamientos`
--
ALTER TABLE `agendamientos`
  ADD CONSTRAINT `fk_agendamientos_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agendamientos_estados` FOREIGN KEY (`id_estado_agendamiento`) REFERENCES `estados_agendamientos` (`id_estado_agendamiento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agendamientos_tipos` FOREIGN KEY (`id_tipo_agendamiento`) REFERENCES `tipos_agendamientos` (`id_tipo_agendamiento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agendamientos_usuario_asignado` FOREIGN KEY (`id_usuario_asignado`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agendamientos_usuario_registra` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `bloqueos_horarios`
--
ALTER TABLE `bloqueos_horarios`
  ADD CONSTRAINT `fk_bloqueo_especialista` FOREIGN KEY (`id_usuario_especialista`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bloqueo_usuario_registra` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_personas` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `consumos_insumos`
--
ALTER TABLE `consumos_insumos`
  ADD CONSTRAINT `fk_consumos_insumos_agendamientos` FOREIGN KEY (`id_agendamiento`) REFERENCES `agendamientos` (`id_agendamiento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consumos_insumos_insumos` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_agendamientos`
--
ALTER TABLE `detalles_agendamientos`
  ADD CONSTRAINT `fk_detalles_agendamientos_agendamientos` FOREIGN KEY (`id_agendamiento`) REFERENCES `agendamientos` (`id_agendamiento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalles_agendamientos_servicios` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `fotos_servicios`
--
ALTER TABLE `fotos_servicios`
  ADD CONSTRAINT `fk_fotos_servicios_detalles` FOREIGN KEY (`id_detalle_agendamiento`) REFERENCES `detalles_agendamientos` (`id_detalle_agendamiento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `fk_proveedores_persona_contacto` FOREIGN KEY (`id_persona_contacto`) REFERENCES `personas` (`id_persona`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedores_insumos`
--
ALTER TABLE `proveedores_insumos`
  ADD CONSTRAINT `fk_proveedores_insumos_insumos` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proveedores_insumos_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_personas` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
