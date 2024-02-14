-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql
-- Tiempo de generación: 13-02-2024 a las 23:13:53
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
(22, 'AT079', '2023-12-01', 'SO', 1, b'1'),
(23, 'AT 041', '2023-12-01', 'SO', 1, b'1'),
(24, 'AT038', '2023-12-01', 'SO', 1, b'1'),
(25, 'AT037', '2023-12-01', 'SO', 1, b'1'),
(26, 'AT075', '2023-12-01', 'SO', 1, b'1'),
(27, 'AT044', '2023-12-01', 'SO', 1, b'1'),
(28, 'AT034', '2023-12-01', 'SO', 1, b'1'),
(29, 'AT040', '2023-12-01', 'SO', 1, b'1'),
(30, 'AT035', '2023-12-01', 'SO', 1, b'1'),
(31, 'AT043', '2023-12-01', 'SO', 1, b'1'),
(32, 'AT045', '2023-12-01', 'SO', 1, b'1'),
(33, 'AT046', '2023-12-01', 'SO', 1, b'1'),
(34, 'AT048', '2023-12-01', 'SO', 1, b'1'),
(35, 'AT042', '2023-12-01', 'SO', 1, b'1'),
(36, 'AT050', '2023-12-01', 'SO', 1, b'1'),
(37, 'AT025', '2023-12-01', 'SO', 1, b'1'),
(38, 'AT069', '2023-12-01', 'SO', 1, b'1'),
(39, 'AT070', '2023-12-01', 'SO', 1, b'1'),
(40, 'AT071', '2023-12-01', 'SO', 1, b'1'),
(41, 'AT072', '2023-12-01', 'SO', 1, b'1'),
(42, 'AT073', '2023-12-01', 'SO', 1, b'1'),
(43, 'AT074', '2023-12-01', 'SO', 1, b'1'),
(44, 'AT001', '2023-12-01', 'SO', 1, b'1'),
(45, 'AT002', '2023-12-01', 'SO', 1, b'1'),
(46, 'AT003', '2023-12-01', 'SO', 1, b'1'),
(47, 'AT004', '2023-12-01', 'SO', 1, b'1'),
(48, 'AT005', '2023-12-01', 'SO', 1, b'1'),
(49, 'AT006', '2023-12-01', 'SO', 1, b'1'),
(50, 'AT066', '2023-12-01', 'SO', 1, b'1'),
(51, 'AT067', '2023-12-01', 'SO', 1, b'1'),
(52, 'AT068', '2023-12-01', 'SO', 1, b'1'),
(53, 'AT039', '2023-12-01', 'SO', 1, b'1'),
(54, 'AT026', '2023-12-01', 'SO', 1, b'1'),
(55, 'AT082', '2023-12-01', 'SO', 1, b'1'),
(56, 'AT007', '2023-12-01', 'SO', 1, b'1'),
(57, 'AT008', '2023-12-01', 'SO', 1, b'0'),
(58, 'AT032', '2023-12-01', 'SO', 1, b'1'),
(59, 'AT049', '2023-12-01', 'SO', 1, b'1'),
(61, 'AT078', '2023-12-01', 'SO', 1, b'0'),
(62, 'AT036', '2023-12-01', 'SO', 1, b'1'),
(63, 'AT076', '2023-12-01', 'SO', 1, b'1'),
(64, 'AT033', '2023-12-01', 'SO', 1, b'1'),
(65, 'AT060', '2023-12-01', 'SO', 1, b'1'),
(66, 'AT 009', '2024-02-01', 'SO', 1, b'1'),
(67, 'AT010', '2024-02-01', 'SO', 1, b'1'),
(68, 'AT011', '2024-02-01', 'SO', 1, b'1'),
(69, 'AT012', '2024-02-01', 'SO', 1, b'1');

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
(17, '777753002', 'CONSTRUCTORA PUERTO OCTAY LTDA', 'ChinChin Grande Lote 36', 'Puerto Montt', 'Puerto Montt', 'Región de Los Lagos', '990789324', 'fdelrio@constructoraoctay.cl', 1),
(18, '779751708', 'CONSTRUCTORA CAMPODONICO Y CIA LTDA', 'CARRETERA AUSTRAL KM 8.5 CHAMIZA', 'PUERTO MONTT', 'PUERTO MONTT', 'Región de Los Lagos', '652257788', 'mp.elguetab@gmail.com', 1),
(19, '776625817', 'INGENIERIA Y CONSTRUCTORA DINAMARCA Y JARA LIMITADA', 'PIRUQUINA S/N', 'CASTRO', 'CASTRO', 'Región de Los Lagos', '988093197', 'Carlaasanchezb@gmail.com', 1),
(20, '777160702', 'C.A.V. CONSTRUCCIONES', 'CAMINO TEPUAL KM 2 PARCELA 31', 'PUERTO MONTT', 'PUERTO MONTT', 'Región de Los Lagos', '652436046', 'amendez@cavconstrucciones.cl', 1),
(21, '761445871', 'AGONI CONSTRUCCIONES LTDA', 'LOS CARRERA 480 INTERIOR', 'CASTRO', 'CASTRO', 'Región de Los Lagos', '996992298', 'MANDRADE@CHILOEMOTORES.CL', 1),
(22, '765125170', 'CONSTRUCTORA ORLANDO CARRILLO EIRL ', 'LA UNION ', 'LA UNION', 'LA UNION ', 'Región de Los Ríos', '961479349', 'cvergara@carrilloic.com', 1),
(23, '85712292', 'JUAN JOSE SILES CARVAJAL', 'ORELLA N°641', 'TEMUCO', 'TEMUCO', 'Región de La Araucanía', '452334581', 'patricioolave@jjsiles.cl', 1),
(24, '761445871', 'AGONI CONSTRUCCIONES LIMITADA', 'LOS CARRERAS 480 INTERIOR', 'CASTRO', 'CASTRO', 'Región de Los Lagos', '56996992298', 'mandrade@jasachiloe.cl', 1),
(25, '692305000', 'ILUSTRE MUNICIPALIDAD DE CHONCHI', 'Pedro Montt 254', 'CHONCHI', 'CHONCHI', 'Región de Los Lagos', '652671255', 'ofpartes@municipalidadchonchi.cl', 1),
(26, '764180453', 'BIMAC INGENIERIA Y CONSTRUCCIÓN SpA', 'rauco', 'CHONCHI', 'CHONCHI', 'Región de Los Lagos', '956099186', 'vmaciasaraneda@gmail.com', 1);

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
(17, 17, 'GM CASTRO  - PENINSULA DE RILAN', 'PENINSULA DE RILAN - CASTRO', 2, '2023-10-31', '2023-10-31', 89250, 6426000, 'OC GLOBAL PENINSULA DE RILAN\r\n8 BAÑOS / OC 008060 /008513 /008139'),
(18, 17, 'OBRA BASICO PUQUELDON 2023', 'SECTOR LINCAY - PUQUELDON ', 2, '2023-10-25', '2023-10-25', 89250, 535500, '6 BAÑOS SEGUN OC 008059'),
(19, 18, 'OBRA VEREDAS CHONCHI', 'SECTOR ROTONDA - CHONCHI', 2, '2023-11-07', '2023-11-07', 109480, 437920, '4 BAÑOS SEGUN OC NROS 5443 / 5466 / 5485'),
(20, 19, 'OBRA NOTUCO', 'SECTOR NOTUCO - CHONCHI', 0, '2023-11-27', '2023-11-27', 87480, 262440, '3 BAÑOS SEGUN OC 31-2023'),
(21, 19, 'OBRA DETICO', 'SECTOR DETICO - QUEILEN', 0, '2023-11-27', '2023-11-27', 174960, 1049760, '4 BAÑOS - SEGUN OC 29/2023, OC 32/2023, OC 33/2023'),
(22, 19, 'OBRA SAN JUAN DE CHADMO', 'SECTOR SAN JUAN DE CHADMO - QUELLON', 0, '2023-11-26', '2023-11-26', 174960, 1049760, '2 BAÑOS SEGUN OC 31-2023'),
(23, 19, 'OBRA CHONCHI', 'CHONCHI', 0, '2023-11-26', '2023-11-26', 200000, 2417000, '3 BAÑOS SEGUN OC 28/2023'),
(24, 20, 'OBRA APR PULUTAUCO', 'SECTOR PULUTAUCO - DALCAHUE', 2, '2023-11-03', '2023-11-03', 328440, 328440, '3 BAÑOS SEGUN OC 2023-02'),
(25, 20, 'OBRA APR CHALIHUE', 'SECTOR CHALIHUE - PUQUELDON', 2, '2023-11-03', '2023-11-03', 328440, 328440, '3 BAÑOS SEGUN OC  2023-01'),
(26, 17, 'OBRA TEY - SAN JOSE DALCAHUE 2023 ', 'SECTOR TEY ', 2, '2023-12-28', '2023-12-28', 404600, 404600, '4 BAÑOS SEGUN OC 008451'),
(27, 21, 'OBRA QUELLON', 'QUELLON ', 2, '2024-01-02', '2024-01-02', 168000, 168000, '1 BAÑO + LIMPIEZA BAÑO EXTERNO'),
(28, 22, 'OBRA TORRE QUEMCHI', 'QUEMCHI', 2, '2023-11-27', '2023-11-27', 200000, 200000, '2 BAÑOS '),
(29, 23, 'OBRA CENTRO COMERCIAL CASTRO', 'SECTOR PUENTE GAMBOA', 2, '2024-02-02', '2024-02-02', 523600, 3100000, '4 BAÑOS SEGUN OC 004-020224'),
(30, 19, 'OBRA SAN JUAN DE CHADMO', 'SAN JUAN DE CHADMO', 0, '2023-12-04', '2023-12-04', 87480, 262440, '2 BAÑOS SEGUN OC 31/2023 '),
(31, 19, 'OBRA CHONCHI', 'CHONCHI', 0, '2023-12-04', '2023-12-04', 87480, 262440, '3 BAÑOS SEGUN OC 28/2023'),
(32, 23, 'OBRA CENTRO COMERCIAL CASTRO', 'SECTOR PUENTE GAMBOA S/N', 0, '2024-02-02', '2024-02-02', 523600, 523600, '4 BAÑOS SEGUN OC 004-020224'),
(33, 19, 'OBRA DETICO', 'DETICO QUEILEN', 2, '2023-12-01', '2023-12-01', 17496, 1049760, '4 BAÑOS SEGUN OC 29/2023, OC 32/2023, OC 33/2023'),
(34, 19, 'OBRA CHONCHI', 'CHONCHI', 2, '2023-12-01', '2023-12-01', 174960, 1049760, '3 BAÑOS SEGUN OC28/2023'),
(35, 19, 'OBRA CHADMO', 'SAN JUAN DE CHADMO', 2, '2023-12-01', '2023-12-01', 174960, 1049760, '4 BAÑOS SEGUN OC 30/2023, OC 36/2023'),
(36, 19, 'OBRA NOTUCO', 'NOTUCO', 2, '2023-12-01', '2023-12-01', 174960, 1049760, '4 BAÑOS SEGUN OC 31/2023, OC34/2023, OC 35/2023'),
(37, 21, 'OBRA QUELLON', 'QUELLON', 2, '2024-01-01', '2024-01-01', 1233464, 24520, '1 BAÑO '),
(38, 25, 'MUNICIPALIDAD DE CHONCHI ', 'Pedro Montt 254', 2, '2023-12-01', '2023-12-01', 25621, 46598, '1 BAÑO'),
(39, 26, 'CENTRO COMERCIAL - CHONCHI', 'CHONCHI URBANO', 2, '2024-01-12', '2024-01-12', 100000, 600000, '1 BAÑO SEGUN OC');

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
(43, 17, 14),
(44, 17, 15),
(45, 17, 16),
(46, 17, 17),
(47, 17, 18),
(48, 17, 19),
(49, 17, 20),
(50, 17, 65),
(51, 18, 38),
(52, 18, 39),
(53, 18, 40),
(54, 18, 41),
(55, 18, 42),
(56, 18, 43),
(37, 19, 50),
(38, 19, 51),
(39, 19, 52),
(40, 19, 53),
(68, 20, 33),
(67, 20, 34),
(66, 20, 36),
(69, 21, 24),
(70, 21, 25),
(71, 21, 27),
(62, 22, 26),
(61, 22, 35),
(63, 23, 23),
(64, 23, 32),
(65, 23, 37),
(31, 24, 47),
(32, 24, 48),
(33, 24, 49),
(34, 25, 44),
(35, 25, 45),
(36, 25, 46),
(58, 26, 22),
(57, 26, 54),
(59, 26, 55),
(60, 26, 56),
(41, 28, 62),
(42, 28, 63),
(72, 29, 66),
(73, 29, 67),
(74, 29, 68),
(75, 29, 69),
(79, 33, 24),
(80, 33, 25),
(81, 33, 33),
(82, 33, 34),
(76, 34, 27),
(77, 34, 31),
(78, 34, 32),
(85, 35, 23),
(84, 35, 26),
(83, 35, 35),
(86, 35, 53),
(87, 36, 28),
(91, 36, 29),
(88, 36, 30),
(89, 36, 36),
(90, 36, 37),
(92, 37, 59),
(93, 38, 64),
(94, 39, 58);

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

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_Factura`, `numero_Factura`, `id_Cliente`, `id_Contrato`, `fecha_Factura`, `valor_Factura`, `estado_Factura`) VALUES
(18, '666', 17, 17, '2024-11-29', 535000, 1),
(19, '667', 17, 18, '2023-11-30', 535000, 1),
(20, '672', 20, 25, '2023-12-06', 328440, 1),
(21, '673', 20, 24, '2023-12-06', 328440, 1),
(22, '674', 18, 19, '2023-12-06', 254660, 1),
(23, '681', 20, 25, '2023-12-21', 202300, 1),
(24, '683', 21, 27, '2024-01-03', 199920, 1),
(25, '684', 20, 25, '2024-01-03', 328440, 1),
(26, '685', 20, 24, '2024-01-03', 328440, 1),
(27, '687', 18, 19, '2024-01-03', 218960, 1),
(28, '686', 18, 19, '2024-01-03', 109480, 1),
(29, '688', 17, 17, '2024-01-03', 535500, 1),
(30, '689', 17, 18, '2024-01-03', 535500, 1),
(31, '690', 17, 17, '2024-01-03', 89250, 1),
(32, '691', 19, 21, '2024-01-04', 284821, 1),
(33, '692', 19, 23, '2024-01-04', 427233, 1),
(34, '693', 19, 20, '2024-01-04', 427233, 1),
(35, '694', 19, 22, '2024-01-04', 284821, 1),
(36, '695', 19, 22, '2024-01-15', 142456, 1),
(37, '697', 19, 22, '2024-01-15', 142456, 1),
(38, '702', 19, 20, '2024-01-18', 285600, 1),
(39, '698', 26, 39, '2024-01-15', 154700, 1);

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

--
-- Volcado de datos para la tabla `factura_servicio`
--

INSERT INTO `factura_servicio` (`id_Relacion`, `id_Factura`, `id_Servicio`) VALUES
(9, 18, 1045),
(10, 18, 1045),
(11, 18, 1046),
(12, 18, 1047),
(13, 18, 1048),
(14, 18, 1049),
(15, 19, 1073);

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
(1045, 17, 448738, '2023-10-31', 'Entrega de 6 baños quimicos.', 1),
(1046, 17, 179717, '2023-11-08', 'Limpieza y desinfección de 6 baños químicos.', 1),
(1047, 17, 604141, '2023-11-16', 'Limpieza y Desinfección de 7 baños químicos.', 1),
(1048, 17, 819710, '2023-11-23', 'Limpieza y desinfección de 7 baños químicos.', 1),
(1049, 17, 734999, '2023-11-30', 'Limpieza y desinfección de 7 baños químicos.', 1),
(1050, 17, 388576, '2023-12-07', 'Limpieza y desinfección de 7 baños químicos.', 1),
(1051, 17, 803917, '2023-12-15', 'Limpieza y desinfeccion de 7 baños químicos.', 1),
(1052, 17, 54769, '2023-12-21', 'Limpieza y desinfeccion de 7 baños químicos.', 1),
(1053, 17, 220081, '2023-12-28', 'Limpieza y desinfeccion de 7 baños quimicos.', 1),
(1054, 17, 530333, '2024-01-03', 'Limpieza y desinfeccion de baños quimicos.', 1),
(1055, 17, 163252, '2024-01-10', 'Limpieza y desinfeccion de baños 8 baños quimicos.', 1),
(1056, 17, 900067, '2024-01-17', 'Limpieza y desinfeccion de 8 baños químicos.', 1),
(1057, 17, 302965, '2024-01-24', 'Limpieza y desinfeccion de 8 baños quimicos.', 1),
(1058, 17, 976464, '2024-02-01', 'Limpieza y desinfeccion de 8 baños químicos.', 1),
(1059, 18, 171524, '2023-10-31', 'Instalación y entrega de 6 baños químicos.', 1),
(1060, 18, 480589, '2023-11-07', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1061, 18, 579827, '2023-11-15', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1062, 18, 103296, '2023-11-22', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1063, 18, 90418, '2023-11-29', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1064, 18, 731796, '2023-12-06', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1065, 18, 373485, '2023-12-14', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1066, 18, 14108, '2023-12-20', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1067, 18, 738387, '2023-12-27', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1068, 18, 313285, '2024-01-04', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1069, 18, 46492, '2024-01-11', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1070, 18, 681956, '2024-01-18', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1071, 18, 184077, '2024-01-24', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1072, 18, 803921, '2024-01-31', 'Limpieza y desinfeccion de 6 baños químicos.', 1),
(1073, 19, 182559, '2023-11-07', 'Instalación de 2 baños químicos.', 1),
(1074, 19, 577886, '2023-11-15', 'Limpieza y desinfeccion de 2 baños químicos.', 1),
(1075, 19, 118931, '2023-11-22', 'Limpieza y desinfeccion de 2 baños químicos.', 1),
(1076, 19, 643891, '2023-11-29', 'Limpieza y desinfeccion de 2 baños químicos.', 1),
(1077, 19, 476674, '2023-11-29', 'Instalación de 1 baño químico.', 1),
(1078, 19, 226941, '2023-12-06', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1079, 19, 802076, '2023-12-13', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1080, 19, 603796, '2023-12-20', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1081, 19, 74586, '2023-12-27', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1082, 19, 13276, '2024-01-03', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1083, 19, 66382, '2024-01-10', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1084, 19, 401188, '2024-01-17', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1085, 19, 601643, '2024-01-23', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1086, 19, 102885, '2024-01-30', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1087, 17, 558671, '2024-02-06', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1088, 24, 208534, '2023-11-08', 'Instalación de 3 baños químicos.', 1),
(1089, 25, 163633, '2023-11-08', 'Instalación de 3 baños químicos.', 1),
(1090, 25, 380302, '2023-11-15', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1091, 25, 921312, '2023-11-22', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1092, 25, 718312, '2023-11-29', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1093, 25, 4677, '2023-12-06', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1094, 25, 950658, '2023-12-13', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1095, 25, 810742, '2023-12-20', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1096, 25, 631837, '2023-12-12', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1097, 25, 526306, '2024-01-03', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1098, 25, 819504, '2024-01-10', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1099, 25, 118522, '2024-01-17', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1100, 25, 481504, '2024-01-24', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1101, 25, 244929, '2024-01-31', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1102, 24, 914326, '2023-11-30', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1103, 24, 81521, '2023-12-07', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1104, 24, 39188, '2023-12-14', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1105, 24, 140698, '2023-12-12', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1106, 24, 148636, '2023-12-28', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1107, 24, 973969, '2024-01-04', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1108, 24, 369368, '2024-01-11', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1109, 24, 629929, '2024-01-18', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1110, 24, 832544, '2024-01-22', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1111, 24, 16612, '2024-01-31', 'Limpieza y desinfeccion de 3 baños químicos.', 1),
(1112, 17, 479419, '2024-01-02', 'Instalación de 4 baños químicos.', 1),
(1113, 26, 362243, '2024-01-02', 'Instalación de 4 baños químicos.', 1),
(1114, 26, 545570, '2024-01-09', 'Limpieza y desinfeccion de 4 baños químicos.', 1),
(1115, 26, 239813, '2024-01-19', 'Limpieza y desinfeccion de 4 baños químicos.', 1),
(1116, 26, 967266, '2024-01-25', 'Limpieza y desinfeccion de 4 baños químicos.', 1),
(1117, 26, 691102, '2024-02-01', 'Limpieza y desinfeccion de 4 baños químicos.', 1),
(1118, 29, 637112, '2024-02-02', 'Instalación de 4 baños químicos.', 1),
(1119, 17, 240525, '2024-02-08', 'Limpieza y desinfeccion de Baños quimicos.', 1),
(1120, 18, 125299, '2024-02-06', 'Limpieza y desinfeccion de baños químicos.', 1),
(1121, 26, 785447, '2024-02-08', 'Limpieza y desinfeccion de baños químicos.', 1),
(1122, 20, 942359, '2023-12-05', 'Instalación de 2 baños químicos.', 1),
(1123, 20, 940435, '2023-12-12', 'Limpieza y desinfeccion de baños químicos.', 1),
(1124, 20, 300269, '2023-12-19', 'Limpieza y desinfeccion de baños químicos.', 1),
(1125, 20, 295149, '2023-12-26', 'Limpieza y desinfeccion de baños químicos.', 1),
(1126, 20, 710767, '2024-01-02', 'Limpieza y desinfeccion de baños químicos.', 1),
(1127, 20, 511201, '2024-01-09', 'Limpieza y desinfeccion de baños químicos.', 1),
(1128, 20, 846075, '2024-01-16', 'Limpieza y desinfeccion de baños químicos.', 1),
(1129, 20, 337045, '2024-01-23', 'Limpieza y desinfeccion de baños químicos.', 1),
(1130, 20, 625616, '2024-01-30', 'Limpieza y desinfeccion de baños químicos.', 1),
(1131, 20, 706197, '2024-02-06', 'Limpieza y desinfeccion de baños químicos.', 1),
(1132, 21, 9708, '2023-12-05', 'Instalación de 2 baños químicos.', 1),
(1133, 21, 637992, '2023-12-12', 'Limpieza y desinfeccion de baños químicos.', 1),
(1134, 21, 17905, '2023-12-12', 'Limpieza y desinfeccion de baños químicos.', 1),
(1135, 21, 921018, '2023-12-19', 'Limpieza y desinfeccion de baños químicos.', 1),
(1136, 21, 427572, '2023-12-26', 'Limpieza y desinfeccion de baños químicos.', 1),
(1137, 21, 491461, '2024-01-02', 'Limpieza y desinfeccion de baños químicos.', 1),
(1138, 21, 435863, '2024-01-02', 'Limpieza y desinfeccion de baños químicos.', 1),
(1139, 21, 446098, '2024-01-09', 'Limpieza y desinfeccion de baños químicos.', 1),
(1140, 21, 173456, '2024-01-16', 'Limpieza y desinfeccion de baños químicos.', 1),
(1141, 21, 682207, '2024-01-23', 'Limpieza y desinfeccion de baños químicos.', 1),
(1142, 21, 697638, '2024-01-30', 'Limpieza y desinfeccion de baños químicos.', 1),
(1143, 21, 262017, '2024-02-06', 'Limpieza y desinfeccion de baños químicos.', 1),
(1144, 21, 704508, '2024-02-06', 'Limpieza y desinfeccion de baños químicos.', 1),
(1145, 20, 464338, '2023-12-04', 'Instalación de baños químicos.', 1),
(1146, 20, 5503, '2023-12-13', 'Limpieza y desinfeccion de baños  quimicos.', 1),
(1147, 20, 171331, '2023-12-20', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1148, 20, 89217, '2023-12-27', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1149, 20, 719095, '2023-12-27', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1150, 20, 565372, '2024-01-03', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1151, 20, 835405, '2024-01-10', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1152, 20, 866368, '2024-01-17', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1153, 20, 235543, '2024-01-24', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1154, 20, 837305, '2024-01-31', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1155, 20, 893476, '2024-02-07', 'Limpieza y desinfeccion de baños  químicos.', 1),
(1156, 22, 846111, '2023-12-04', 'Instalación de baños químicos.', 1),
(1157, 22, 594405, '2023-12-12', 'Limpieza y desinfeccion de baños químicos.', 1),
(1158, 22, 202171, '2023-12-19', 'Limpieza y desinfeccion de baños químicos.', 1),
(1159, 22, 480128, '2023-12-26', 'Limpieza y desinfeccion de baños químicos.', 1),
(1160, 22, 983966, '2024-01-02', 'Limpieza y desinfeccion de baños químicos.', 1),
(1161, 22, 144044, '2024-01-09', 'Limpieza y desinfeccion de baños químicos.', 1),
(1162, 22, 528044, '2024-01-16', 'Limpieza y desinfeccion de baños químicos.', 1),
(1163, 22, 303543, '2024-01-23', 'Limpieza y desinfeccion de baños químicos.', 1),
(1164, 22, 433128, '2024-01-30', 'Limpieza y desinfeccion de baños químicos.', 1),
(1165, 22, 768138, '2024-02-06', 'Limpieza y desinfeccion de baños químicos.', 1),
(1166, 23, 393986, '2023-12-04', 'Instalación de baños químicos.', 1),
(1167, 23, 94658, '2023-12-12', 'Limpieza y desinfeccion de baños químicos.', 1),
(1168, 22, 695427, '2023-12-19', 'Limpieza y desinfeccion de baños químicos.', 1),
(1169, 23, 552153, '2023-12-19', 'Limpieza y desinfeccion de baños químicos.', 1),
(1170, 23, 48527, '2023-12-26', 'Limpieza y desinfeccion de baños químicos.', 1),
(1171, 23, 556541, '2023-12-26', 'Limpieza y desinfeccion de baños químicos.', 1),
(1172, 23, 830888, '2024-01-02', 'Limpieza y desinfeccion de baños químicos.', 1),
(1173, 23, 120809, '2024-01-09', 'Limpieza y desinfeccion de baños químicos.', 1),
(1174, 23, 724363, '2024-01-23', 'Limpieza y desinfeccion de baños químicos.', 1),
(1175, 23, 958902, '2024-01-30', 'Limpieza y desinfeccion de baños químicos.', 1),
(1176, 23, 409306, '2024-01-30', 'Limpieza y desinfeccion de baños químicos.', 1),
(1177, 23, 964571, '2024-02-06', 'Limpieza y desinfeccion de baños químicos.', 1),
(1178, 29, 242972, '2024-02-12', 'Limpieza y desinfeccion de baños quimicos', 1),
(1179, 39, 647894, '2024-01-12', 'INSTALACION 1 BAÑO', 1),
(1180, 39, 469422, '2024-01-16', 'LIMPIEZA Y DESINFECCION', 1),
(1181, 39, 852845, '2024-01-23', 'L', 1),
(1182, 39, 612412, '2024-01-30', 'LIMPIEZA Y DESINFECCION', 1),
(1183, 39, 552715, '2024-02-06', 'LIMPIEZA Y DESINFECCION', 1),
(1184, 39, 466206, '2024-02-13', 'LIMPIEZA Y DESINFECCION', 1);

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
(107, 1045, 14),
(108, 1045, 15),
(109, 1045, 16),
(110, 1045, 17),
(111, 1045, 18),
(112, 1045, 19),
(113, 1047, 14),
(114, 1047, 15),
(115, 1047, 16),
(116, 1047, 17),
(117, 1047, 18),
(118, 1047, 19),
(119, 1047, 20),
(120, 1046, 14),
(121, 1046, 15),
(122, 1046, 16),
(123, 1046, 17),
(124, 1046, 18),
(125, 1046, 19),
(126, 1048, 14),
(127, 1048, 15),
(128, 1048, 16),
(129, 1048, 17),
(130, 1048, 18),
(131, 1048, 19),
(132, 1048, 20),
(133, 1049, 14),
(134, 1049, 15),
(135, 1049, 16),
(136, 1049, 17),
(137, 1049, 18),
(138, 1049, 19),
(139, 1049, 20),
(140, 1050, 14),
(141, 1050, 15),
(142, 1050, 16),
(143, 1050, 17),
(144, 1050, 18),
(145, 1050, 19),
(146, 1050, 20),
(147, 1051, 14),
(148, 1051, 15),
(149, 1051, 16),
(150, 1051, 17),
(151, 1051, 18),
(152, 1051, 19),
(153, 1051, 20),
(154, 1052, 14),
(155, 1052, 15),
(156, 1052, 16),
(157, 1052, 17),
(158, 1052, 18),
(159, 1052, 19),
(160, 1052, 20),
(161, 1053, 14),
(162, 1053, 15),
(163, 1053, 16),
(164, 1053, 17),
(165, 1053, 18),
(166, 1053, 19),
(167, 1053, 20),
(168, 1054, 14),
(169, 1054, 15),
(170, 1054, 16),
(171, 1054, 17),
(172, 1054, 18),
(173, 1054, 19),
(174, 1054, 20),
(175, 1055, 14),
(176, 1055, 15),
(177, 1055, 16),
(178, 1055, 17),
(179, 1055, 18),
(180, 1055, 19),
(181, 1055, 20),
(182, 1055, 65),
(183, 1056, 14),
(184, 1056, 15),
(185, 1056, 16),
(186, 1056, 17),
(187, 1056, 18),
(188, 1056, 19),
(189, 1056, 20),
(190, 1056, 65),
(191, 1057, 14),
(192, 1057, 15),
(193, 1057, 16),
(194, 1057, 17),
(195, 1057, 18),
(196, 1057, 19),
(197, 1057, 20),
(198, 1057, 65),
(199, 1058, 14),
(200, 1058, 15),
(201, 1058, 16),
(202, 1058, 17),
(203, 1058, 18),
(204, 1058, 19),
(205, 1058, 20),
(206, 1058, 65),
(207, 1059, 38),
(208, 1059, 39),
(209, 1059, 40),
(210, 1059, 41),
(211, 1059, 42),
(212, 1059, 43),
(213, 1060, 38),
(214, 1060, 39),
(215, 1060, 40),
(216, 1060, 41),
(217, 1060, 42),
(218, 1060, 43),
(219, 1061, 38),
(220, 1061, 39),
(221, 1061, 40),
(222, 1061, 41),
(223, 1061, 42),
(224, 1061, 43),
(225, 1062, 38),
(226, 1062, 39),
(227, 1062, 40),
(228, 1062, 41),
(229, 1062, 42),
(230, 1062, 43),
(231, 1063, 38),
(232, 1063, 39),
(233, 1063, 40),
(234, 1063, 41),
(235, 1063, 42),
(236, 1063, 43),
(237, 1064, 38),
(238, 1064, 39),
(239, 1064, 40),
(240, 1064, 41),
(241, 1064, 42),
(242, 1064, 43),
(243, 1065, 38),
(244, 1065, 39),
(245, 1065, 40),
(246, 1065, 41),
(247, 1065, 42),
(248, 1065, 43),
(249, 1066, 38),
(250, 1066, 39),
(251, 1066, 40),
(252, 1066, 41),
(253, 1066, 42),
(254, 1066, 43),
(255, 1067, 38),
(256, 1067, 39),
(257, 1067, 40),
(258, 1067, 41),
(259, 1067, 42),
(260, 1067, 43),
(261, 1068, 38),
(262, 1068, 39),
(263, 1068, 40),
(264, 1068, 41),
(265, 1068, 42),
(266, 1068, 43),
(267, 1069, 38),
(268, 1069, 39),
(269, 1069, 40),
(270, 1069, 41),
(271, 1069, 42),
(272, 1069, 43),
(273, 1070, 38),
(274, 1070, 39),
(275, 1070, 40),
(276, 1070, 41),
(277, 1070, 42),
(278, 1070, 43),
(279, 1071, 38),
(280, 1071, 39),
(281, 1071, 40),
(282, 1071, 41),
(283, 1071, 42),
(284, 1071, 43),
(285, 1073, 50),
(286, 1073, 51),
(287, 1074, 50),
(288, 1074, 51),
(289, 1075, 50),
(290, 1075, 51),
(291, 1076, 50),
(292, 1076, 51),
(293, 1077, 52),
(294, 1078, 50),
(295, 1078, 51),
(296, 1078, 52),
(297, 1079, 50),
(298, 1079, 51),
(299, 1079, 52),
(300, 1080, 50),
(301, 1080, 51),
(302, 1080, 52),
(303, 1081, 50),
(304, 1081, 51),
(305, 1081, 52),
(306, 1082, 50),
(307, 1082, 51),
(308, 1082, 52),
(309, 1083, 50),
(310, 1083, 51),
(311, 1083, 52),
(312, 1084, 50),
(313, 1084, 51),
(314, 1084, 52),
(315, 1085, 50),
(316, 1085, 51),
(317, 1085, 52),
(318, 1086, 50),
(319, 1086, 51),
(320, 1086, 52),
(321, 1088, 47),
(322, 1088, 48),
(323, 1088, 49),
(324, 1089, 44),
(325, 1089, 45),
(326, 1089, 46),
(327, 1090, 44),
(328, 1090, 45),
(329, 1090, 46),
(330, 1091, 44),
(331, 1091, 45),
(332, 1091, 46),
(333, 1092, 44),
(334, 1092, 45),
(335, 1092, 46),
(336, 1093, 44),
(337, 1093, 45),
(338, 1093, 46),
(339, 1094, 44),
(340, 1094, 45),
(341, 1094, 46),
(342, 1095, 44),
(343, 1095, 45),
(344, 1095, 46),
(345, 1096, 44),
(346, 1096, 45),
(347, 1096, 46),
(348, 1097, 44),
(349, 1097, 45),
(350, 1097, 46),
(351, 1098, 44),
(352, 1098, 45),
(353, 1098, 46),
(354, 1099, 44),
(355, 1099, 45),
(356, 1099, 46),
(357, 1100, 44),
(358, 1100, 45),
(359, 1100, 46),
(360, 1101, 44),
(361, 1101, 45),
(362, 1101, 46),
(363, 1102, 47),
(364, 1102, 48),
(365, 1102, 49),
(366, 1103, 47),
(367, 1103, 48),
(368, 1103, 49),
(369, 1104, 47),
(370, 1104, 48),
(371, 1104, 49),
(372, 1105, 47),
(373, 1105, 48),
(374, 1105, 49),
(375, 1106, 47),
(376, 1106, 48),
(377, 1106, 49),
(378, 1107, 47),
(379, 1107, 48),
(380, 1107, 49),
(381, 1108, 47),
(382, 1108, 48),
(383, 1108, 49),
(384, 1109, 47),
(385, 1109, 48),
(386, 1109, 49),
(387, 1110, 47),
(388, 1110, 48),
(389, 1110, 49),
(390, 1111, 47),
(391, 1111, 48),
(392, 1111, 49),
(393, 1113, 22),
(394, 1113, 54),
(395, 1113, 55),
(396, 1113, 56),
(397, 1114, 22),
(398, 1114, 54),
(399, 1114, 55),
(400, 1114, 56),
(401, 1115, 22),
(402, 1115, 54),
(403, 1115, 55),
(404, 1115, 56),
(405, 1116, 22),
(406, 1116, 54),
(407, 1116, 55),
(408, 1116, 56),
(409, 1117, 22),
(410, 1117, 54),
(411, 1117, 55),
(412, 1117, 56),
(413, 1118, 66),
(414, 1118, 67),
(415, 1118, 68),
(416, 1118, 69),
(417, 1119, 14),
(418, 1119, 15),
(419, 1119, 16),
(420, 1119, 17),
(421, 1119, 18),
(422, 1119, 19),
(423, 1119, 20),
(424, 1119, 65),
(425, 1120, 38),
(426, 1120, 39),
(427, 1120, 40),
(428, 1120, 41),
(429, 1120, 42),
(430, 1120, 43),
(431, 1121, 22),
(432, 1121, 54),
(433, 1121, 55),
(434, 1121, 56),
(435, 1122, 33),
(436, 1122, 34),
(437, 1123, 33),
(438, 1123, 34),
(439, 1124, 33),
(440, 1124, 34),
(441, 1125, 33),
(442, 1125, 34),
(443, 1126, 33),
(444, 1126, 34),
(445, 1127, 33),
(446, 1127, 34),
(447, 1128, 33),
(448, 1128, 34),
(449, 1129, 33),
(450, 1129, 34),
(451, 1129, 36),
(452, 1130, 33),
(453, 1130, 34),
(454, 1130, 36),
(455, 1131, 33),
(456, 1131, 34),
(457, 1131, 36),
(458, 1132, 24),
(459, 1132, 25),
(460, 1133, 24),
(461, 1133, 25),
(462, 1133, 27),
(463, 1134, 24),
(464, 1134, 25),
(465, 1134, 27),
(466, 1135, 24),
(467, 1135, 25),
(468, 1135, 27),
(469, 1136, 24),
(470, 1136, 25),
(471, 1136, 27),
(472, 1137, 24),
(473, 1137, 25),
(474, 1137, 27),
(475, 1138, 24),
(476, 1138, 25),
(477, 1138, 27),
(478, 1139, 24),
(479, 1139, 25),
(480, 1139, 27),
(481, 1140, 24),
(482, 1140, 25),
(483, 1140, 27),
(484, 1141, 24),
(485, 1141, 25),
(486, 1141, 27),
(487, 1142, 24),
(488, 1142, 25),
(489, 1142, 27),
(490, 1143, 24),
(491, 1143, 25),
(492, 1143, 27),
(493, 1144, 24),
(494, 1144, 25),
(495, 1144, 27),
(496, 1145, 33),
(497, 1145, 34),
(498, 1145, 36),
(499, 1146, 33),
(500, 1146, 34),
(501, 1146, 36),
(502, 1147, 33),
(503, 1147, 34),
(504, 1147, 36),
(505, 1148, 33),
(506, 1148, 34),
(507, 1148, 36),
(508, 1149, 33),
(509, 1149, 34),
(510, 1149, 36),
(511, 1150, 33),
(512, 1150, 34),
(513, 1150, 36),
(514, 1151, 33),
(515, 1151, 34),
(516, 1151, 36),
(517, 1152, 33),
(518, 1152, 34),
(519, 1152, 36),
(520, 1153, 33),
(521, 1153, 34),
(522, 1153, 36),
(523, 1154, 33),
(524, 1154, 34),
(525, 1154, 36),
(526, 1155, 33),
(527, 1155, 34),
(528, 1155, 36),
(529, 1156, 26),
(530, 1156, 35),
(531, 1157, 26),
(532, 1157, 35),
(533, 1158, 26),
(534, 1158, 35),
(535, 1159, 26),
(536, 1159, 35),
(537, 1160, 26),
(538, 1160, 35),
(539, 1161, 26),
(540, 1161, 35),
(541, 1162, 26),
(542, 1162, 35),
(543, 1163, 26),
(544, 1163, 35),
(545, 1164, 26),
(546, 1164, 35),
(547, 1165, 26),
(548, 1165, 35),
(549, 1166, 23),
(550, 1166, 32),
(551, 1166, 37),
(552, 1167, 23),
(553, 1167, 32),
(554, 1167, 37),
(555, 1168, 26),
(556, 1168, 35),
(557, 1169, 23),
(558, 1169, 32),
(559, 1169, 37),
(560, 1170, 23),
(561, 1170, 32),
(562, 1170, 37),
(563, 1171, 23),
(564, 1171, 32),
(565, 1171, 37),
(566, 1172, 23),
(567, 1172, 32),
(568, 1172, 37),
(569, 1173, 23),
(570, 1173, 32),
(571, 1173, 37),
(572, 1174, 23),
(573, 1174, 32),
(574, 1174, 37),
(575, 1175, 23),
(576, 1175, 32),
(577, 1175, 37),
(578, 1176, 23),
(579, 1176, 32),
(580, 1176, 37),
(581, 1177, 23),
(582, 1177, 32),
(583, 1177, 37),
(584, 1178, 66),
(585, 1178, 67),
(586, 1178, 68),
(587, 1178, 69),
(588, 1181, 58),
(589, 1179, 58),
(590, 1180, 58),
(591, 1182, 58),
(592, 1183, 58),
(593, 1184, 58);

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
(31, 448738, 1, 0, 0, 0, 0, 1, 1, 0),
(32, 179717, 0, 0, 1, 1, 0, 1, 1, 0),
(33, 604141, 0, 0, 1, 1, 0, 1, 1, 0),
(34, 819710, 0, 0, 1, 1, 0, 1, 1, 0),
(35, 734999, 0, 0, 1, 1, 0, 0, 1, 0),
(36, 388576, 0, 0, 1, 1, 0, 1, 1, 0),
(37, 803917, 0, 0, 1, 1, 0, 1, 1, 0),
(38, 54769, 0, 0, 1, 1, 0, 1, 1, 0),
(39, 220081, 0, 0, 1, 1, 0, 1, 1, 0),
(40, 530333, 0, 0, 1, 1, 0, 1, 1, 0),
(41, 163252, 0, 0, 1, 1, 0, 1, 1, 0),
(42, 900067, 0, 0, 1, 1, 0, 1, 1, 0),
(43, 302965, 0, 0, 1, 1, 0, 1, 1, 0),
(44, 976464, 0, 0, 1, 1, 0, 1, 1, 0),
(45, 171524, 1, 0, 0, 1, 0, 1, 1, 0),
(46, 480589, 0, 0, 1, 1, 0, 1, 1, 0),
(47, 579827, 0, 0, 1, 1, 0, 1, 1, 0),
(48, 103296, 0, 0, 1, 1, 0, 1, 1, 0),
(49, 90418, 0, 0, 1, 1, 0, 1, 1, 0),
(50, 731796, 0, 0, 1, 1, 0, 1, 1, 0),
(51, 373485, 0, 0, 1, 1, 0, 1, 1, 0),
(52, 14108, 0, 0, 1, 1, 0, 1, 1, 0),
(53, 738387, 0, 0, 1, 1, 0, 1, 1, 0),
(54, 313285, 0, 0, 1, 1, 0, 1, 1, 0),
(55, 46492, 0, 0, 1, 1, 0, 1, 1, 0),
(56, 681956, 0, 0, 1, 1, 0, 1, 1, 0),
(57, 184077, 0, 0, 1, 1, 0, 1, 1, 0),
(58, 803921, 0, 0, 1, 1, 0, 1, 1, 0),
(59, 182559, 1, 0, 0, 1, 0, 1, 1, 0),
(60, 577886, 0, 0, 1, 1, 0, 1, 1, 0),
(61, 118931, 0, 0, 1, 1, 0, 1, 1, 0),
(62, 643891, 0, 0, 1, 1, 0, 1, 1, 0),
(63, 476674, 1, 0, 0, 1, 0, 1, 1, 0),
(64, 226941, 0, 0, 1, 1, 0, 1, 1, 0),
(65, 802076, 0, 0, 1, 1, 0, 1, 1, 0),
(66, 603796, 0, 0, 1, 1, 0, 1, 1, 0),
(67, 74586, 0, 0, 1, 1, 0, 1, 1, 0),
(68, 13276, 0, 0, 1, 1, 0, 1, 1, 0),
(69, 66382, 0, 0, 1, 1, 0, 1, 1, 0),
(70, 401188, 0, 0, 1, 1, 0, 1, 1, 0),
(71, 601643, 0, 0, 1, 1, 0, 1, 1, 0),
(72, 102885, 0, 0, 1, 1, 0, 1, 1, 0),
(73, 558671, 0, 0, 1, 1, 0, 1, 1, 0),
(74, 208534, 1, 0, 0, 0, 0, 0, 0, 0),
(75, 163633, 1, 0, 0, 0, 1, 1, 1, 0),
(76, 380302, 0, 0, 1, 1, 0, 1, 1, 0),
(77, 921312, 0, 0, 1, 1, 0, 1, 1, 0),
(78, 718312, 0, 0, 1, 1, 0, 1, 1, 0),
(79, 4677, 0, 0, 1, 1, 0, 1, 1, 0),
(80, 950658, 0, 0, 1, 1, 0, 1, 1, 0),
(81, 810742, 0, 0, 1, 1, 0, 1, 0, 0),
(82, 631837, 0, 0, 1, 1, 0, 1, 1, 0),
(83, 526306, 0, 0, 1, 1, 0, 1, 1, 0),
(84, 819504, 0, 0, 1, 1, 0, 1, 1, 0),
(85, 118522, 0, 0, 1, 1, 0, 1, 1, 0),
(86, 481504, 0, 0, 1, 1, 0, 1, 1, 0),
(87, 244929, 0, 0, 1, 1, 0, 1, 1, 0),
(88, 914326, 0, 0, 1, 1, 0, 1, 1, 0),
(89, 81521, 0, 0, 1, 1, 0, 1, 1, 0),
(90, 39188, 0, 0, 1, 1, 0, 1, 1, 0),
(91, 140698, 0, 0, 1, 1, 0, 1, 1, 0),
(92, 148636, 0, 0, 1, 1, 0, 1, 1, 0),
(93, 973969, 0, 0, 1, 1, 0, 1, 1, 0),
(94, 369368, 0, 0, 1, 1, 0, 1, 1, 0),
(95, 629929, 0, 0, 1, 1, 0, 1, 1, 0),
(96, 832544, 0, 0, 1, 1, 0, 1, 1, 0),
(97, 16612, 0, 0, 1, 1, 0, 1, 1, 0),
(98, 479419, 1, 0, 0, 1, 0, 1, 1, 0),
(99, 362243, 1, 0, 0, 0, 0, 0, 0, 0),
(100, 545570, 0, 0, 1, 1, 0, 1, 1, 0),
(101, 239813, 0, 0, 1, 1, 0, 1, 1, 0),
(102, 967266, 0, 0, 1, 1, 0, 1, 1, 0),
(103, 691102, 0, 0, 1, 0, 1, 1, 1, 0),
(104, 637112, 1, 0, 0, 1, 0, 1, 1, 0),
(105, 240525, 0, 0, 1, 1, 0, 1, 1, 0),
(106, 125299, 0, 0, 1, 1, 0, 1, 1, 0),
(107, 785447, 0, 0, 1, 1, 0, 1, 1, 0),
(108, 942359, 1, 0, 0, 1, 0, 1, 1, 0),
(109, 940435, 0, 0, 1, 1, 0, 1, 1, 0),
(110, 300269, 0, 0, 1, 1, 0, 1, 1, 0),
(111, 295149, 0, 0, 1, 1, 0, 1, 1, 0),
(112, 710767, 0, 0, 1, 1, 0, 1, 1, 0),
(113, 511201, 0, 0, 1, 1, 0, 1, 1, 0),
(114, 846075, 0, 0, 1, 1, 0, 1, 1, 0),
(115, 337045, 0, 0, 1, 1, 0, 1, 1, 0),
(116, 625616, 0, 0, 1, 1, 0, 1, 1, 0),
(117, 706197, 0, 0, 1, 1, 0, 1, 1, 0),
(118, 9708, 0, 0, 1, 1, 1, 1, 1, 0),
(119, 637992, 0, 0, 1, 1, 1, 1, 1, 0),
(120, 17905, 0, 0, 1, 1, 0, 1, 1, 0),
(121, 921018, 0, 0, 1, 1, 0, 1, 1, 0),
(122, 427572, 0, 0, 1, 1, 0, 1, 1, 0),
(123, 491461, 0, 0, 1, 1, 0, 1, 1, 0),
(124, 435863, 0, 0, 1, 1, 0, 1, 1, 0),
(125, 446098, 0, 0, 1, 1, 0, 1, 1, 0),
(126, 173456, 0, 0, 1, 1, 0, 1, 1, 0),
(127, 682207, 0, 0, 1, 0, 0, 1, 1, 0),
(128, 697638, 0, 0, 1, 1, 0, 1, 1, 0),
(129, 262017, 0, 0, 1, 1, 0, 1, 1, 0),
(130, 704508, 0, 0, 1, 1, 0, 1, 1, 0),
(131, 464338, 1, 0, 0, 0, 0, 1, 1, 0),
(132, 5503, 0, 0, 1, 1, 0, 1, 1, 0),
(133, 171331, 0, 0, 1, 1, 0, 1, 1, 0),
(134, 89217, 0, 0, 1, 1, 0, 1, 1, 0),
(135, 719095, 0, 0, 1, 1, 0, 1, 0, 0),
(136, 565372, 0, 0, 1, 1, 0, 1, 1, 0),
(137, 835405, 0, 0, 1, 1, 0, 1, 1, 0),
(138, 866368, 0, 0, 1, 1, 0, 1, 1, 0),
(139, 235543, 0, 0, 1, 1, 0, 1, 1, 0),
(140, 837305, 0, 0, 1, 1, 0, 1, 1, 0),
(141, 893476, 0, 0, 1, 1, 0, 1, 1, 0),
(142, 846111, 1, 0, 0, 1, 0, 1, 1, 0),
(143, 594405, 0, 0, 1, 1, 0, 1, 1, 0),
(144, 202171, 0, 0, 1, 1, 0, 1, 1, 0),
(145, 480128, 0, 0, 1, 1, 0, 1, 1, 0),
(146, 983966, 0, 0, 1, 1, 0, 1, 1, 0),
(147, 144044, 0, 0, 1, 1, 0, 1, 1, 0),
(148, 528044, 0, 0, 1, 1, 0, 1, 1, 0),
(149, 303543, 0, 0, 1, 1, 0, 1, 1, 0),
(150, 433128, 0, 0, 1, 1, 0, 1, 1, 0),
(151, 768138, 0, 0, 1, 1, 0, 1, 1, 0),
(152, 393986, 0, 0, 1, 1, 0, 1, 1, 0),
(153, 94658, 0, 0, 1, 1, 0, 1, 1, 0),
(154, 695427, 0, 0, 1, 1, 0, 1, 1, 0),
(155, 552153, 0, 0, 1, 1, 0, 1, 1, 0),
(156, 48527, 0, 0, 1, 1, 0, 1, 1, 0),
(157, 556541, 0, 0, 1, 1, 0, 1, 1, 0),
(158, 830888, 0, 0, 1, 1, 0, 1, 1, 0),
(159, 120809, 0, 0, 1, 1, 0, 1, 1, 0),
(160, 724363, 0, 0, 1, 1, 0, 1, 1, 0),
(161, 958902, 0, 0, 1, 1, 0, 1, 1, 0),
(162, 409306, 0, 0, 1, 1, 0, 1, 1, 0),
(163, 964571, 0, 0, 1, 1, 0, 1, 1, 0),
(164, 242972, 0, 0, 1, 1, 1, 1, 1, 0),
(165, 647894, 1, 0, 0, 0, 0, 0, 0, 0),
(166, 469422, 0, 0, 1, 1, 1, 1, 1, 0),
(167, 852845, 0, 1, 1, 1, 1, 1, 1, 0),
(168, 612412, 0, 1, 1, 1, 1, 1, 1, 0),
(169, 552715, 0, 0, 1, 1, 0, 0, 0, 0),
(170, 466206, 0, 0, 1, 1, 1, 1, 1, 0);

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
  MODIFY `id_Bath` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `certificados`
--
ALTER TABLE `certificados`
  MODIFY `id_Certificado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_Cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id_Contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id_Contrato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `contrato_bathroom`
--
ALTER TABLE `contrato_bathroom`
  MODIFY `id_Relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_Factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  MODIFY `id_Relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_Servicio` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1185;

--
-- AUTO_INCREMENT de la tabla `servicios_bathrooms`
--
ALTER TABLE `servicios_bathrooms`
  MODIFY `id_Relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=594;

--
-- AUTO_INCREMENT de la tabla `tipo_servicio`
--
ALTER TABLE `tipo_servicio`
  MODIFY `id_Tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

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
