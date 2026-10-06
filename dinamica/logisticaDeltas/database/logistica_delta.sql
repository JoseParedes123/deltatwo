-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-10-2026 a las 16:23:27
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
-- Base de datos: `logistica_delta`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `choferes`
--

CREATE TABLE `choferes` (
  `id_chofer` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `licencia` varchar(30) NOT NULL,
  `categoria_licencia` varchar(10) NOT NULL,
  `fecha_vencimiento_licencia` date NOT NULL,
  `estado` enum('ACTIVO','INACTIVO','SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_cliente` enum('PARTICULAR','CORPORATIVO') NOT NULL DEFAULT 'PARTICULAR',
  `razon_social` varchar(150) DEFAULT NULL,
  `cuit_dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `estado_validacion` enum('PENDIENTE','VALIDADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
  `aprobado_corporativo` tinyint(1) NOT NULL DEFAULT 0,
  `descuento_porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `id_usuario`, `tipo_cliente`, `razon_social`, `cuit_dni`, `telefono`, `direccion`, `estado_validacion`, `aprobado_corporativo`, `descuento_porcentaje`, `fecha_registro`) VALUES
(1, 1, 'CORPORATIVO', 'Delta Demo', '30-00000000-0', '1100000000', 'Av. Logística 123', 'VALIDADO', 1, 0.00, '2026-10-05 11:12:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes`
--

CREATE TABLE `comprobantes` (
  `id_comprobante` int(11) NOT NULL,
  `id_envio` int(11) NOT NULL,
  `numero_comprobante` varchar(30) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(40) NOT NULL,
  `ruta_archivo` varchar(255) DEFAULT NULL,
  `id_usuario_emisor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id_contacto` int(11) NOT NULL,
  `tipo` enum('REMITENTE','DESTINATARIO') NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `direccion` varchar(200) NOT NULL,
  `id_zona` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id_contacto`, `tipo`, `nombre`, `telefono`, `email`, `direccion`, `id_zona`, `id_cliente`) VALUES
(1, 'REMITENTE', 'Cliente Demo', '1100000001', 'remitente@delta.local', 'CABA', 1, 1),
(2, 'DESTINATARIO', 'María González', '1100000002', 'maria@delta.local', 'CABA', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `depositos`
--

CREATE TABLE `depositos` (
  `id_deposito` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `depositos`
--

INSERT INTO `depositos` (`id_deposito`, `nombre`, `direccion`, `activo`) VALUES
(1, 'Central Delta', 'Av. Logística 123, Buenos Aires', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id_envio` int(11) NOT NULL,
  `codigo_envio` varchar(20) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `id_paquete` int(11) NOT NULL,
  `estado` enum('REGISTRADO','RECIBIDO_DEPOSITO','PREPARADO','EN_TRANSITO','EN_REPARTO','ENTREGADO','NO_ENTREGADO','DEVUELTO','EXTRAVIADO','CANCELADO') NOT NULL DEFAULT 'REGISTRADO',
  `id_zona_destino` int(11) NOT NULL,
  `id_tarifa_aplicada` int(11) DEFAULT NULL,
  `precio_final` decimal(12,2) DEFAULT NULL,
  `id_ruta` int(11) DEFAULT NULL,
  `orden_parada` int(11) DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_estimada` date DEFAULT NULL,
  `id_usuario_registro` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `envios`
--

INSERT INTO `envios` (`id_envio`, `codigo_envio`, `id_cliente`, `id_remitente`, `id_destinatario`, `id_paquete`, `estado`, `id_zona_destino`, `id_tarifa_aplicada`, `precio_final`, `id_ruta`, `orden_parada`, `fecha_solicitud`, `fecha_entrega_estimada`, `id_usuario_registro`, `activo`) VALUES
(1, 'ENV-0001', 1, 1, 2, 1, 'RECIBIDO_DEPOSITO', 1, NULL, NULL, NULL, NULL, '2026-10-05 11:12:42', NULL, 1, 1),
(2, 'ENV-0002', 1, 1, 2, 2, 'PREPARADO', 1, NULL, NULL, NULL, NULL, '2026-10-05 11:12:42', NULL, 1, 1),
(3, 'ENV-0003', 1, 1, 2, 3, 'EN_TRANSITO', 1, NULL, NULL, NULL, NULL, '2026-10-05 11:12:42', NULL, 1, 1),
(4, 'ENV-0004', 1, 1, 2, 4, 'EXTRAVIADO', 1, NULL, NULL, NULL, NULL, '2026-10-05 11:12:42', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id_historial` int(11) NOT NULL,
  `tipo_evento` enum('CAMBIO_ESTADO','MODIFICACION') NOT NULL,
  `tabla_afectada` varchar(60) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `campo_modificado` varchar(60) DEFAULT NULL,
  `valor_anterior` varchar(255) DEFAULT NULL,
  `valor_nuevo` varchar(255) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id_incidencia` int(11) NOT NULL,
  `id_envio` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `lugar` varchar(150) NOT NULL,
  `id_usuario_registro` int(11) NOT NULL,
  `tipo` enum('EXTRAVIO','DANIO','DIRECCION_INCORRECTA','PROBLEMA_ENTREGA','PROBLEMA_PAQUETE','OTRO') NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('ABIERTA','EN_INVESTIGACION','RESUELTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  `resolucion` text DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_entrega`
--

CREATE TABLE `intentos_entrega` (
  `id_intento` int(11) NOT NULL,
  `id_envio` int(11) NOT NULL,
  `numero_intento` tinyint(4) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `id_chofer` int(11) NOT NULL,
  `resultado` enum('EXITOSO','FALLIDO') NOT NULL,
  `motivo_falla` enum('DESTINATARIO_AUSENTE','DIRECCION_INCORRECTA','DOMICILIO_INACCESIBLE','RECHAZO_DESTINATARIO','PROBLEMA_PAQUETE','OTRO') DEFAULT NULL,
  `receptor_nombre` varchar(120) DEFAULT NULL,
  `receptor_documento` varchar(20) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_deposito`
--

CREATE TABLE `movimientos_deposito` (
  `id_movimiento` int(11) NOT NULL,
  `id_paquete` int(11) NOT NULL,
  `id_ubicacion_anterior` int(11) DEFAULT NULL,
  `id_ubicacion_nueva` int(11) NOT NULL,
  `es_actual` tinyint(1) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos_deposito`
--

INSERT INTO `movimientos_deposito` (`id_movimiento`, `id_paquete`, `id_ubicacion_anterior`, `id_ubicacion_nueva`, `es_actual`, `id_usuario`, `fecha_hora`, `motivo`) VALUES
(1, 1, NULL, 1, 1, 1, '2026-10-05 11:12:42', 'Recepción en depósito central'),
(2, 2, NULL, 2, 1, 1, '2026-10-05 11:12:42', 'Clasificación de paquete frágil'),
(3, 4, NULL, 3, 1, 1, '2026-10-05 11:12:42', 'Ingreso y clasificación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetes`
--

CREATE TABLE `paquetes` (
  `id_paquete` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `peso_kg` decimal(10,3) NOT NULL,
  `largo_cm` decimal(10,2) DEFAULT NULL,
  `ancho_cm` decimal(10,2) DEFAULT NULL,
  `alto_cm` decimal(10,2) DEFAULT NULL,
  `cantidad_bultos` int(11) NOT NULL DEFAULT 1,
  `tipo_mercaderia` varchar(80) NOT NULL,
  `valor_declarado` decimal(12,2) DEFAULT NULL,
  `es_fragil` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `paquetes`
--

INSERT INTO `paquetes` (`id_paquete`, `codigo`, `descripcion`, `peso_kg`, `largo_cm`, `ancho_cm`, `alto_cm`, `cantidad_bultos`, `tipo_mercaderia`, `valor_declarado`, `es_fragil`, `fecha_registro`) VALUES
(1, 'DEL-0001', 'Caja de repuestos', 8.500, NULL, NULL, NULL, 1, 'Repuestos', NULL, 0, '2026-10-05 11:12:42'),
(2, 'DEL-0002', 'Monitor profesional', 4.200, NULL, NULL, NULL, 1, 'Electrónica', NULL, 1, '2026-10-05 11:12:42'),
(3, 'DEL-0003', 'Documentación empresarial', 1.100, NULL, NULL, NULL, 1, 'Documentos', NULL, 0, '2026-10-05 11:12:42'),
(4, 'DEL-0004', 'Equipamiento informático', 12.700, NULL, NULL, NULL, 1, 'Electrónica', NULL, 1, '2026-10-05 11:12:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutas`
--

CREATE TABLE `rutas` (
  `id_ruta` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `origen` varchar(200) NOT NULL,
  `destino` varchar(200) NOT NULL,
  `recorrido` text DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `id_chofer` int(11) NOT NULL,
  `id_zona` int(11) DEFAULT NULL,
  `estado` enum('PLANIFICADA','EN_CURSO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PLANIFICADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_zona` int(11) NOT NULL,
  `tipo_envio` varchar(60) NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `precio_por_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_por_m3` decimal(10,2) NOT NULL DEFAULT 0.00,
  `recargo_fragil` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vigente_desde` date NOT NULL,
  `vigente_hasta` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicacion` int(11) NOT NULL,
  `id_deposito` int(11) NOT NULL,
  `sector` varchar(20) NOT NULL,
  `estante` varchar(20) NOT NULL,
  `posicion` varchar(20) NOT NULL,
  `ocupada` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ubicaciones`
--

INSERT INTO `ubicaciones` (`id_ubicacion`, `id_deposito`, `sector`, `estante`, `posicion`, `ocupada`) VALUES
(1, 1, 'A', '01', '01', 1),
(2, 1, 'A', '01', '02', 1),
(3, 1, 'B', '02', '01', 1),
(4, 1, 'B', '02', '02', 0),
(5, 1, 'C', '03', '01', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `nombre_usuario` varchar(60) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('ADMINISTRADOR','OPERADOR_DEPOSITO','CHOFER','ADMINISTRACION','CLIENTE') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `dni`, `email`, `nombre_usuario`, `password_hash`, `rol`, `activo`, `fecha_creacion`, `ultimo_login`) VALUES
(1, 'Juan', 'Pérez', '30111222', 'juan.perez@delta.local', 'operador', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'OPERADOR_DEPOSITO', 1, '2026-10-05 11:12:42', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `patente` varchar(15) NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` smallint(6) DEFAULT NULL,
  `capacidad_peso_kg` decimal(10,2) NOT NULL,
  `capacidad_volumen_m3` decimal(10,2) NOT NULL,
  `estado` enum('DISPONIBLE','EN_RUTA','MANTENIMIENTO','INACTIVO') NOT NULL DEFAULT 'DISPONIBLE',
  `id_zona_restringida` int(11) DEFAULT NULL,
  `motivo_restriccion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `id_zona` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `cobertura` enum('CABA_GBA','INTERIOR') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`id_zona`, `nombre`, `cobertura`, `descripcion`, `activo`) VALUES
(1, 'CABA', 'CABA_GBA', 'Cobertura CABA', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `choferes`
--
ALTER TABLE `choferes`
  ADD PRIMARY KEY (`id_chofer`),
  ADD UNIQUE KEY `uq_choferes_usuario` (`id_usuario`),
  ADD UNIQUE KEY `uq_choferes_licencia` (`licencia`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `uq_clientes_usuario` (`id_usuario`);

--
-- Indices de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD PRIMARY KEY (`id_comprobante`),
  ADD UNIQUE KEY `uq_comprobantes_numero` (`numero_comprobante`),
  ADD KEY `fk_comprobantes_envio` (`id_envio`),
  ADD KEY `fk_comprobantes_usuario` (`id_usuario_emisor`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id_contacto`),
  ADD KEY `fk_contactos_zona` (`id_zona`),
  ADD KEY `fk_contactos_cliente` (`id_cliente`);

--
-- Indices de la tabla `depositos`
--
ALTER TABLE `depositos`
  ADD PRIMARY KEY (`id_deposito`);

--
-- Indices de la tabla `envios`
--
ALTER TABLE `envios`
  ADD PRIMARY KEY (`id_envio`),
  ADD UNIQUE KEY `uq_envios_codigo` (`codigo_envio`),
  ADD UNIQUE KEY `uq_envios_paquete` (`id_paquete`),
  ADD KEY `fk_envios_remitente` (`id_remitente`),
  ADD KEY `fk_envios_destinatario` (`id_destinatario`),
  ADD KEY `fk_envios_zona` (`id_zona_destino`),
  ADD KEY `fk_envios_tarifa` (`id_tarifa_aplicada`),
  ADD KEY `fk_envios_ruta` (`id_ruta`),
  ADD KEY `fk_envios_usuario_reg` (`id_usuario_registro`),
  ADD KEY `idx_envios_estado` (`estado`),
  ADD KEY `idx_envios_cliente` (`id_cliente`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_usuario` (`id_usuario`),
  ADD KEY `idx_historial_registro` (`tabla_afectada`,`registro_id`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `fk_incidencias_usuario` (`id_usuario_registro`),
  ADD KEY `idx_incidencias_envio` (`id_envio`);

--
-- Indices de la tabla `intentos_entrega`
--
ALTER TABLE `intentos_entrega`
  ADD PRIMARY KEY (`id_intento`),
  ADD UNIQUE KEY `uq_intento_envio_numero` (`id_envio`,`numero_intento`),
  ADD KEY `fk_intentos_chofer` (`id_chofer`),
  ADD KEY `idx_intentos_envio` (`id_envio`);

--
-- Indices de la tabla `movimientos_deposito`
--
ALTER TABLE `movimientos_deposito`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_movdep_ubic_ant` (`id_ubicacion_anterior`),
  ADD KEY `fk_movdep_ubic_nueva` (`id_ubicacion_nueva`),
  ADD KEY `fk_movdep_usuario` (`id_usuario`),
  ADD KEY `idx_movdep_paquete` (`id_paquete`);

--
-- Indices de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  ADD PRIMARY KEY (`id_paquete`),
  ADD UNIQUE KEY `uq_paquetes_codigo` (`codigo`),
  ADD KEY `idx_paquetes_codigo` (`codigo`);

--
-- Indices de la tabla `rutas`
--
ALTER TABLE `rutas`
  ADD PRIMARY KEY (`id_ruta`),
  ADD KEY `fk_rutas_vehiculo` (`id_vehiculo`),
  ADD KEY `fk_rutas_chofer` (`id_chofer`),
  ADD KEY `fk_rutas_zona` (`id_zona`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`),
  ADD KEY `fk_tarifas_zona` (`id_zona`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD UNIQUE KEY `uq_ubicacion` (`id_deposito`,`sector`,`estante`,`posicion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`),
  ADD UNIQUE KEY `uq_usuarios_nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `uq_usuarios_dni` (`dni`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `uq_vehiculos_patente` (`patente`),
  ADD KEY `fk_vehiculos_zona` (`id_zona_restringida`);

--
-- Indices de la tabla `zonas`
--
ALTER TABLE `zonas`
  ADD PRIMARY KEY (`id_zona`),
  ADD UNIQUE KEY `uq_zonas_nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `choferes`
--
ALTER TABLE `choferes`
  MODIFY `id_chofer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  MODIFY `id_comprobante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id_contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `depositos`
--
ALTER TABLE `depositos`
  MODIFY `id_deposito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id_envio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `intentos_entrega`
--
ALTER TABLE `intentos_entrega`
  MODIFY `id_intento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_deposito`
--
ALTER TABLE `movimientos_deposito`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  MODIFY `id_paquete` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rutas`
--
ALTER TABLE `rutas`
  MODIFY `id_ruta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `id_zona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `choferes`
--
ALTER TABLE `choferes`
  ADD CONSTRAINT `fk_choferes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD CONSTRAINT `fk_comprobantes_envio` FOREIGN KEY (`id_envio`) REFERENCES `envios` (`id_envio`),
  ADD CONSTRAINT `fk_comprobantes_usuario` FOREIGN KEY (`id_usuario_emisor`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD CONSTRAINT `fk_contactos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_contactos_zona` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`);

--
-- Filtros para la tabla `envios`
--
ALTER TABLE `envios`
  ADD CONSTRAINT `fk_envios_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_envios_destinatario` FOREIGN KEY (`id_destinatario`) REFERENCES `contactos` (`id_contacto`),
  ADD CONSTRAINT `fk_envios_paquete` FOREIGN KEY (`id_paquete`) REFERENCES `paquetes` (`id_paquete`),
  ADD CONSTRAINT `fk_envios_remitente` FOREIGN KEY (`id_remitente`) REFERENCES `contactos` (`id_contacto`),
  ADD CONSTRAINT `fk_envios_ruta` FOREIGN KEY (`id_ruta`) REFERENCES `rutas` (`id_ruta`),
  ADD CONSTRAINT `fk_envios_tarifa` FOREIGN KEY (`id_tarifa_aplicada`) REFERENCES `tarifas` (`id_tarifa`),
  ADD CONSTRAINT `fk_envios_usuario_reg` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_envios_zona` FOREIGN KEY (`id_zona_destino`) REFERENCES `zonas` (`id_zona`);

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `fk_incidencias_envio` FOREIGN KEY (`id_envio`) REFERENCES `envios` (`id_envio`),
  ADD CONSTRAINT `fk_incidencias_usuario` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `intentos_entrega`
--
ALTER TABLE `intentos_entrega`
  ADD CONSTRAINT `fk_intentos_chofer` FOREIGN KEY (`id_chofer`) REFERENCES `choferes` (`id_chofer`),
  ADD CONSTRAINT `fk_intentos_envio` FOREIGN KEY (`id_envio`) REFERENCES `envios` (`id_envio`);

--
-- Filtros para la tabla `movimientos_deposito`
--
ALTER TABLE `movimientos_deposito`
  ADD CONSTRAINT `fk_movdep_paquete` FOREIGN KEY (`id_paquete`) REFERENCES `paquetes` (`id_paquete`),
  ADD CONSTRAINT `fk_movdep_ubic_ant` FOREIGN KEY (`id_ubicacion_anterior`) REFERENCES `ubicaciones` (`id_ubicacion`),
  ADD CONSTRAINT `fk_movdep_ubic_nueva` FOREIGN KEY (`id_ubicacion_nueva`) REFERENCES `ubicaciones` (`id_ubicacion`),
  ADD CONSTRAINT `fk_movdep_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `rutas`
--
ALTER TABLE `rutas`
  ADD CONSTRAINT `fk_rutas_chofer` FOREIGN KEY (`id_chofer`) REFERENCES `choferes` (`id_chofer`),
  ADD CONSTRAINT `fk_rutas_vehiculo` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`),
  ADD CONSTRAINT `fk_rutas_zona` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`);

--
-- Filtros para la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD CONSTRAINT `fk_tarifas_zona` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id_zona`);

--
-- Filtros para la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD CONSTRAINT `fk_ubicaciones_deposito` FOREIGN KEY (`id_deposito`) REFERENCES `depositos` (`id_deposito`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `fk_vehiculos_zona` FOREIGN KEY (`id_zona_restringida`) REFERENCES `zonas` (`id_zona`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
