-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql
-- Tiempo de generación: 05-02-2024 a las 00:24:57
-- Versión del servidor: 11.2.2-MariaDB-1:11.2.2+maria~ubu2204
-- Versión de PHP: 8.2.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `donbano`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bathrooms`
--

CREATE TABLE `bathrooms` (
  `id_Bath` int(11) NOT NULL,
  `codigo_Bath` varchar(40) DEFAULT NULL,
  `fechaCompra_Bath` date DEFAULT NULL,
  `observacion_Bath` varchar(150) DEFAULT NULL,
  `estado_Bath` int(11) DEFAULT NULL,
  `asignado_Bath` bit(1) DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `bathrooms`
--

INSERT INTO `bathrooms` (`id_Bath`, `codigo_Bath`, `fechaCompra_Bath`, `observacion_Bath`, `estado_Bath`, `asignado_Bath`) VALUES
(14, 'AT064', '2023-12-01', 'SO', 1, b'1'),
(15, 'AT063', '2023-12-01', 'SO', 1, b'1'),
(16, 'AT062', '2023-12-01', 'SO', 1, b'1'),
(17, 'AT061', '2023-12-01', 'SO', 1, b'1'),
(18, 'AT060', '2023-12-01', 'SO', 1, b'1'),
(19, 'AT084', '2023-12-01', 'SO', 1, b'1'),
(20, 'AT065', '2023-12-01', 'SO', 1, b'1'),
(21, 'AT077', '2023-12-01', 'SO', 1, b'0'),
(22, 'AT079', '2023-12-01', 'SO', 1, b'1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `category`
--

CREATE TABLE `category` (
  `id_category` int(11) NOT NULL,
  `name_category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `category`
--

INSERT INTO `category` (`id_category`, `name_category`) VALUES
(1, 'Administrador'),
(2, 'Usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificados`
--

CREATE TABLE `certificados` (
  `id_Certificado` int(11) NOT NULL,
  `nro_Certificado` varchar(100) NOT NULL,
  `id_Cliente` int(11) DEFAULT NULL,
  `id_Contrato` int(11) DEFAULT NULL,
  `fechahoy_Certificado` date DEFAULT NULL,
  `fecha_Servicio` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_Cliente` int(11) NOT NULL,
  `rut_Cliente` varchar(15) DEFAULT NULL,
  `nombre_Cliente` varchar(60) DEFAULT NULL,
  `direccion_Cliente` varchar(50) DEFAULT NULL,
  `comuna_Cliente` varchar(60) DEFAULT NULL,
  `ciudad_Cliente` varchar(70) DEFAULT NULL,
  `region_Cliente` varchar(100) DEFAULT NULL,
  `telefono_Cliente` varchar(15) DEFAULT NULL,
  `email_Cliente` varchar(50) DEFAULT NULL,
  `estado_Cliente` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_Cliente`, `rut_Cliente`, `nombre_Cliente`, `direccion_Cliente`, `comuna_Cliente`, `ciudad_Cliente`, `region_Cliente`, `telefono_Cliente`, `email_Cliente`, `estado_Cliente`) VALUES
(17, '777753002', 'CONSTRUCTORA PUERTO OCTAY LTDA', 'ChinChin Grande Lote 36', 'Puerto Montt', 'Puerto Montt', 'Región de Los Lagos', '990789324', 'fdelrio@constructoraoctay.cl', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id_Contacto` int(11) NOT NULL,
  `id_Cliente` int(11) DEFAULT NULL,
  `nombre_Contacto` varchar(50) DEFAULT NULL,
  `apellido_Contacto` varchar(50) DEFAULT NULL,
  `rut_Contacto` varchar(15) DEFAULT NULL,
  `telefono_Contacto` varchar(15) DEFAULT NULL,
  `direccion_Contacto` varchar(50) DEFAULT NULL,
  `observacion_Contacto` text DEFAULT NULL,
  `estado_Contacto` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id_Contacto`, `id_Cliente`, `nombre_Contacto`, `apellido_Contacto`, `rut_Contacto`, `telefono_Contacto`, `direccion_Contacto`, `observacion_Contacto`, `estado_Contacto`) VALUES
(17, 17, 'KARINA', 'INOSTROZA', '1', '945131805', 'MOCOPULLI S/N DALCAHUE', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `id_Contrato` int(11) NOT NULL,
  `id_Cliente` int(11) DEFAULT NULL,
  `obra_Contrato` varchar(50) DEFAULT NULL,
  `direccion_Contrato` varchar(155) DEFAULT NULL,
  `estado_Contrato` int(11) DEFAULT NULL,
  `fechaInicio_Contrato` date DEFAULT NULL,
  `fechaFin_Contrato` date DEFAULT NULL,
  `valorMensual_Contrato` int(11) DEFAULT NULL,
  `valorTotal_Contrato` int(11) DEFAULT NULL,
  `observacion_Contrato` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `contratos`
--

INSERT INTO `contratos` (`id_Contrato`, `id_Cliente`, `obra_Contrato`, `direccion_Contrato`, `estado_Contrato`, `fechaInicio_Contrato`, `fechaFin_Contrato`, `valorMensual_Contrato`, `valorTotal_Contrato`, `observacion_Contrato`) VALUES
(17, 17, 'GLOBAL PENINSULA DE RILAN', 'PENINSULA DE RILAN - CASTRO', 2, '2023-10-31', '2023-10-31', 89250, 6426000, 'OC GLOBAL PENINSULA DE RILAN\r\n6 BAÑOS\r\nOC 008060 (6 BAÑOS)\r\nOC 008139 (1 BAÑO)\r\nOC 008513 (1 BAÑO)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contrato_bathroom`
--

CREATE TABLE `contrato_bathroom` (
  `id_Relacion` int(11) NOT NULL,
  `id_Contrato` int(11) DEFAULT NULL,
  `id_Bath` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contrato_bathroom`
--

INSERT INTO `contrato_bathroom` (`id_Relacion`, `id_Contrato`, `id_Bath`) VALUES
(23, 17, 14),
(24, 17, 15),
(25, 17, 16),
(26, 17, 17),
(27, 17, 18),
(28, 17, 19),
(29, 17, 20),
(30, 17, 22);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_Factura` int(11) NOT NULL,
  `numero_Factura` varchar(50) DEFAULT NULL,
  `id_Cliente` int(11) DEFAULT NULL,
  `id_Contrato` int(11) DEFAULT NULL,
  `fecha_Factura` date DEFAULT NULL,
  `valor_Factura` int(20) DEFAULT NULL,
  `estado_Factura` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_estado`
--

CREATE TABLE `factura_estado` (
  `id_Estado` int(11) NOT NULL,
  `nombre_Estado` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura_estado`
--

INSERT INTO `factura_estado` (`id_Estado`, `nombre_Estado`) VALUES
(1, 'Pendiente'),
(2, 'Pagado'),
(3, 'Anulado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_servicio`
--

CREATE TABLE `factura_servicio` (
  `id_Relacion` int(11) NOT NULL,
  `id_Factura` int(255) NOT NULL,
  `id_Servicio` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_Servicio` int(255) NOT NULL,
  `id_Contrato` int(11) DEFAULT NULL,
  `nro_Servicio` int(11) DEFAULT NULL,
  `fecha_Servicio` date DEFAULT NULL,
  `observaciones_Servicio` text DEFAULT NULL,
  `estado_Servicio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_Servicio`, `id_Contrato`, `nro_Servicio`, `fecha_Servicio`, `observaciones_Servicio`, `estado_Servicio`) VALUES
(1023, 17, 393686, '2023-10-31', 'ENTREGA DE 6 BAÑOS', 1),
(1024, 17, 509288, '2023-11-08', '6 BAÑOS QUIMICOS - LIMPIEZA Y DESINFECCION', 1),
(1025, 17, 374612, '2023-11-16', 'LIMPIEZA DE 7 BAÑOS', 1),
(1026, 17, 142785, '2023-11-09', 'INSTALACION 1 BAÑO (OC 008139)', 1),
(1027, 17, 289438, '2023-11-23', 'LIMPIEZA Y DESINFECION 7 BAÑOS', 1),
(1028, 17, 200247, '2023-11-30', 'limpieza de 7 baños', 1),
(1029, 17, 726332, '2023-12-07', 'limpieza de 7 baños', 1),
(1030, 17, 589990, '2023-12-15', 'limpieza 7 baños', 1),
(1031, 17, 207125, '2023-12-21', 'limpieza de 7 baños', 1),
(1032, 17, 542825, '2023-12-28', 'limpieza de 7 baños', 1),
(1033, 17, 468435, '2024-01-03', 'limpieza de 7 baños', 1),
(1034, 17, 41947, '2024-01-10', 'limpieza de 7 baños', 1),
(1035, 17, 237988, '2023-01-10', 'instalacion 1 baño', 0),
(1036, 17, 319315, '2024-01-10', 'instalacion 1 baño', 1),
(1037, 17, 998793, '2024-01-17', 'limpieza de 8 baños', 1),
(1038, 17, 474079, '2024-01-24', 'limpieza 8 baños', 1),
(1039, 17, 162810, '2024-01-31', 'prueba', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_bathrooms`
--

CREATE TABLE `servicios_bathrooms` (
  `id_Relacion` int(11) NOT NULL,
  `id_Servicio` int(11) DEFAULT NULL,
  `id_Bath` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_bathrooms`
--

INSERT INTO `servicios_bathrooms` (`id_Relacion`, `id_Servicio`, `id_Bath`) VALUES
(21, 1023, 14),
(22, 1023, 15),
(23, 1023, 16),
(24, 1023, 17),
(25, 1023, 18),
(26, 1023, 19),
(27, 1024, 14),
(28, 1024, 15),
(29, 1024, 16),
(30, 1024, 17),
(31, 1024, 18),
(32, 1024, 19),
(33, 1026, 20),
(34, 1025, 14),
(35, 1025, 15),
(36, 1025, 16),
(37, 1025, 17),
(38, 1025, 18),
(39, 1025, 19),
(40, 1025, 20),
(41, 1027, 14),
(42, 1027, 15),
(43, 1027, 16),
(44, 1027, 17),
(45, 1027, 18),
(46, 1027, 19),
(47, 1027, 20),
(48, 1028, 14),
(49, 1028, 15),
(50, 1028, 16),
(51, 1028, 17),
(52, 1028, 18),
(53, 1028, 19),
(54, 1028, 20),
(55, 1030, 14),
(56, 1030, 15),
(57, 1030, 16),
(58, 1030, 17),
(59, 1030, 18),
(60, 1030, 19),
(61, 1030, 20),
(62, 1031, 14),
(63, 1031, 15),
(64, 1031, 16),
(65, 1031, 17),
(66, 1031, 18),
(67, 1031, 19),
(68, 1031, 20),
(69, 1032, 14),
(70, 1032, 15),
(71, 1032, 16),
(72, 1032, 17),
(73, 1032, 18),
(74, 1032, 19),
(75, 1032, 20),
(76, 1033, 14),
(77, 1033, 15),
(78, 1033, 16),
(79, 1033, 17),
(80, 1033, 18),
(81, 1033, 19),
(82, 1033, 20),
(83, 1034, 14),
(84, 1034, 15),
(85, 1034, 16),
(86, 1034, 17),
(87, 1034, 18),
(88, 1034, 19),
(89, 1034, 20),
(90, 1036, 22),
(91, 1037, 14),
(92, 1037, 15),
(93, 1037, 16),
(94, 1037, 17),
(95, 1037, 18),
(96, 1037, 19),
(97, 1037, 20),
(98, 1037, 22),
(99, 1038, 14),
(100, 1038, 15),
(101, 1038, 16),
(102, 1038, 17),
(103, 1038, 18),
(104, 1038, 19),
(105, 1038, 20),
(106, 1038, 22);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_servicio`
--

CREATE TABLE `tipo_servicio` (
  `id_Tipo` int(11) NOT NULL,
  `nro_Servicio` int(11) DEFAULT NULL,
  `instalacion_Tipo` tinyint(1) DEFAULT NULL,
  `reparacion_Tipo` tinyint(1) DEFAULT NULL,
  `limpieza_Tipo` tinyint(1) DEFAULT NULL,
  `desinfeccion_Tipo` tinyint(1) DEFAULT NULL,
  `sanitizacion_Tipo` tinyint(1) DEFAULT NULL,
  `higienico_Tipo` tinyint(1) DEFAULT NULL,
  `jabon_Tipo` tinyint(1) DEFAULT NULL,
  `otros_Tipo` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_servicio`
--

INSERT INTO `tipo_servicio` (`id_Tipo`, `nro_Servicio`, `instalacion_Tipo`, `reparacion_Tipo`, `limpieza_Tipo`, `desinfeccion_Tipo`, `sanitizacion_Tipo`, `higienico_Tipo`, `jabon_Tipo`, `otros_Tipo`) VALUES
(9, 393686, 1, 0, 0, 0, 0, 1, 1, 0),
(10, 509288, 0, 0, 1, 1, 1, 1, 1, 0),
(11, 374612, 0, 0, 0, 1, 1, 1, 1, 0),
(12, 142785, 1, 0, 0, 0, 0, 0, 0, 0),
(13, 289438, 0, 0, 1, 1, 1, 1, 1, 0),
(14, 200247, 0, 1, 1, 1, 1, 1, 1, 0),
(15, 726332, 0, 1, 1, 1, 1, 1, 1, 0),
(16, 589990, 0, 1, 1, 1, 1, 1, 1, 0),
(17, 207125, 0, 1, 1, 1, 1, 1, 1, 0),
(18, 542825, 0, 1, 1, 1, 1, 1, 1, 0),
(19, 468435, 0, 1, 1, 1, 1, 1, 1, 0),
(20, 41947, 0, 1, 1, 1, 1, 1, 1, 0),
(21, 237988, 1, 0, 0, 0, 0, 0, 0, 0),
(22, 319315, 1, 0, 0, 0, 0, 0, 0, 0),
(23, 998793, 0, 1, 1, 1, 1, 1, 1, 0),
(24, 474079, 0, 1, 1, 1, 1, 1, 1, 0),
(25, 162810, 1, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `useremail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `useremail`, `username`, `password`, `token`, `name`, `lastname`, `image`, `category`, `state`, `created_at`) VALUES
(1, 'edgardoruotolo@gmail.com', 'eruotolo', '$2y$10$vgqSRqRZXReamBbh.cGFjOOraeRSVGkDgrG0uGLW/K7QgtN9oFp02', '754bcf4b23f6b6f887e30182f22ac0b7bd577256d26e7e744546ac8403ee855a3aa236909dd98571731913e85f8dd1b1e7c9', 'Edgardo', 'Ruotolo', 'avatar-4.jpg', 1, 1, '2020-09-24 17:53:37'),
(3, 'jsanchez@expanda.cl', 'jsanchez', '$2y$10$KkYSgM7kPtvMn1OrG2gyR./StWQpW0q3XNl9NY6yxxqhOd3clqMze', '2a099c73b63cea21f169206e83ad3d3d356618ac16846410f33430907b5a06aab31491ead41e705345c5ce035a313a285f78', 'Juan Manuel', 'Sanchez', '3630-avatar-1.jpg', 1, 1, '2024-01-24 16:58:36'),
(4, 'administracion@ratacop.cl', 'kimberling', '$2y$10$5YrKgFDmOdAeugopXLeQaesBUm9eK3S3EfNSHOBpzwTUmBEVSXseS', '9c04a1dbb1c1c7119689d7d1d0856ac8c04a327912ffeabcd1d635958a07020e9cd294feba11e799ffc150545937c81c1da5', 'Kimberling', 'Añez', '4336-avatar-8.jpg', 1, 1, '2024-01-24 16:59:54');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bathrooms`
--
ALTER TABLE `bathrooms`
  ADD PRIMARY KEY (`id_Bath`);

--
-- Indices de la tabla `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`);

--
-- Indices de la tabla `certificados`
--
ALTER TABLE `certificados`
  ADD PRIMARY KEY (`id_Certificado`),
  ADD KEY `certificados_clientes_fk` (`id_Cliente`),
  ADD KEY `certificados_contratos_fk` (`id_Contrato`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_Cliente`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id_Contacto`),
  ADD KEY `contactos_clientes_fk` (`id_Cliente`);

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id_Contrato`),
  ADD KEY `contratos_clientes_id_Cliente_fk` (`id_Cliente`);

--
-- Indices de la tabla `contrato_bathroom`
--
ALTER TABLE `contrato_bathroom`
  ADD PRIMARY KEY (`id_Relacion`),
  ADD UNIQUE KEY `unique_relacion` (`id_Contrato`,`id_Bath`),
  ADD KEY `id_Bath` (`id_Bath`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_Factura`),
  ADD KEY `factura_estado__fk` (`estado_Factura`),
  ADD KEY `facturas_clientes_fk` (`id_Cliente`),
  ADD KEY `facturas_Contrato_fk` (`id_Contrato`);

--
-- Indices de la tabla `factura_estado`
--
ALTER TABLE `factura_estado`
  ADD PRIMARY KEY (`id_Estado`);

--
-- Indices de la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  ADD PRIMARY KEY (`id_Relacion`),
  ADD KEY `relacion_factura_fk` (`id_Factura`),
  ADD KEY `relacion_servicio_fk` (`id_Servicio`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_Servicio`),
  ADD UNIQUE KEY `nro_servicios_pk` (`nro_Servicio`),
  ADD KEY `seguimientos_contratos_fk` (`id_Contrato`);

--
-- Indices de la tabla `servicios_bathrooms`
--
ALTER TABLE `servicios_bathrooms`
  ADD PRIMARY KEY (`id_Relacion`),
  ADD KEY `relation_servicio_fk` (`id_Servicio`),
  ADD KEY `relation_bathrooms_fk` (`id_Bath`);

--
-- Indices de la tabla `tipo_servicio`
--
ALTER TABLE `tipo_servicio`
  ADD PRIMARY KEY (`id_Tipo`),
  ADD KEY `nro_Servicio_fk` (`nro_Servicio`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_category_fk` (`category`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bathrooms`
--
ALTER TABLE `bathrooms`
  MODIFY `id_Bath` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `certificados`
--
ALTER TABLE `certificados`
  MODIFY `id_Certificado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_Cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id_Contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id_Contrato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `contrato_bathroom`
--
ALTER TABLE `contrato_bathroom`
  MODIFY `id_Relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_Factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  MODIFY `id_Relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_Servicio` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1040;

--
-- AUTO_INCREMENT de la tabla `servicios_bathrooms`
--
ALTER TABLE `servicios_bathrooms`
  MODIFY `id_Relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `tipo_servicio`
--
ALTER TABLE `tipo_servicio`
  MODIFY `id_Tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `certificados`
--
ALTER TABLE `certificados`
  ADD CONSTRAINT `certificados_clientes_fk` FOREIGN KEY (`id_Cliente`) REFERENCES `clientes` (`id_Cliente`),
  ADD CONSTRAINT `certificados_contratos_fk` FOREIGN KEY (`id_Contrato`) REFERENCES `contratos` (`id_Contrato`);

--
-- Filtros para la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD CONSTRAINT `contactos_clientes_fk` FOREIGN KEY (`id_Cliente`) REFERENCES `clientes` (`id_Cliente`);

--
-- Filtros para la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_clientes_id_Cliente_fk` FOREIGN KEY (`id_Cliente`) REFERENCES `clientes` (`id_Cliente`);

--
-- Filtros para la tabla `contrato_bathroom`
--
ALTER TABLE `contrato_bathroom`
  ADD CONSTRAINT `contrato_bathroom_ibfk_1` FOREIGN KEY (`id_Contrato`) REFERENCES `contratos` (`id_Contrato`),
  ADD CONSTRAINT `contrato_bathroom_ibfk_2` FOREIGN KEY (`id_Bath`) REFERENCES `bathrooms` (`id_Bath`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `factura_estado__fk` FOREIGN KEY (`estado_Factura`) REFERENCES `factura_estado` (`id_Estado`),
  ADD CONSTRAINT `facturas_Contrato_fk` FOREIGN KEY (`id_Contrato`) REFERENCES `contratos` (`id_Contrato`),
  ADD CONSTRAINT `facturas_clientes_fk` FOREIGN KEY (`id_Cliente`) REFERENCES `clientes` (`id_Cliente`);

--
-- Filtros para la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  ADD CONSTRAINT `relacion_factura_fk` FOREIGN KEY (`id_Factura`) REFERENCES `facturas` (`id_Factura`),
  ADD CONSTRAINT `relacion_servicio_fk` FOREIGN KEY (`id_Servicio`) REFERENCES `servicios` (`id_Servicio`);

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `seguimientos_contratos_fk` FOREIGN KEY (`id_Contrato`) REFERENCES `contratos` (`id_Contrato`);

--
-- Filtros para la tabla `servicios_bathrooms`
--
ALTER TABLE `servicios_bathrooms`
  ADD CONSTRAINT `relation_bathrooms_fk` FOREIGN KEY (`id_Bath`) REFERENCES `bathrooms` (`id_Bath`),
  ADD CONSTRAINT `relation_servicio_fk` FOREIGN KEY (`id_Servicio`) REFERENCES `servicios` (`id_Servicio`);

--
-- Filtros para la tabla `tipo_servicio`
--
ALTER TABLE `tipo_servicio`
  ADD CONSTRAINT `nro_Servicio_fk` FOREIGN KEY (`nro_Servicio`) REFERENCES `servicios` (`nro_Servicio`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_category_fk` FOREIGN KEY (`category`) REFERENCES `category` (`id_category`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
