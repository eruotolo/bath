-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql
-- Tiempo de generación: 28-12-2023 a las 20:21:33
-- Versión del servidor: 10.11.6-MariaDB-1:10.11.6+maria~ubu2204
-- Versión de PHP: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `donbano_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bathrooms`
--

CREATE TABLE `bathrooms` (
  `id_Bath` int(11) NOT NULL,
  `codigo_Bath` varchar(40) DEFAULT NULL,
  `fechaCompra_Bath` date DEFAULT NULL,
  `observacion_Bath` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `bathrooms`
--

INSERT INTO `bathrooms` (`id_Bath`, `codigo_Bath`, `fechaCompra_Bath`, `observacion_Bath`) VALUES
(1, 'BATH001', '2023-01-01', 'Nuevo baño'),
(2, 'BATH002', '2022-10-15', 'Baño principal'),
(3, 'BATH003', '2023-05-20', 'Baño de visitas'),
(4, 'BATH004', '2023-03-10', 'Baño compartido'),
(5, 'BATH005', '2022-12-28', 'Baño de niños'),
(6, 'BATH006', '2023-08-05', 'Baño de lujo'),
(7, 'BATH007', '2023-06-18', 'Baño moderno'),
(8, 'BATH008', '2023-02-25', 'Baño clásico'),
(9, 'BATH009', '2023-11-11', 'Baño minimalista'),
(10, 'BATH010', '2023-09-07', 'Baño rústico');

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
(1, 'Admin'),
(2, 'User');

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
(1, '270396615', 'Juan Pérez', 'Calle A #123', 'Castro', 'Castro', 'Los Lagos', '967553841', 'juan@example.com', 1),
(2, '222222222', 'Carlos Rodríguez', 'Avenida B #456', 'Chonchi', 'Chonchi', 'Los Lagos', '987654321', 'carlos@example.com', 1),
(3, '333333333', 'Carlos Gutiérrez', 'Calle C #789', 'Dalcahue', 'Dalcahue', 'Los Lagos', '456789123', 'carlos@example.com', 1),
(4, '444444444', 'Ana López', 'Avenida D #1011', 'Ancud', 'Ancud', 'Los Lagos', '789123456', 'ana@example.com', 1),
(5, '555555555', 'Luisa Martínez', 'Calle E #1213', 'Quellon', 'Quellon', 'Los Lagos', '321654987', 'luisa@example.com', 1),
(6, '666666666', 'Pedro Sánchez', 'Avenida F #1415', 'Castro', 'Castro', 'Los Lagos', '654987321', 'pedro@example.com', 1),
(7, '777777777', 'Laura García', 'Calle G #1617', 'Chonchi', 'Chonchi', 'Los Lagos', '147258369', 'laura@example.com', 1),
(8, '888888888', 'Sofía Torres', 'Avenida H #1819', 'Dalcahue', 'Dalcahue', 'Los Lagos', '369258147', 'sofia@example.com', 1),
(9, '999999999', 'Daniel Ramírez', 'Calle I #2021', 'Ancud', 'Ancud', 'Los Lagos', '258369147', 'daniel@example.com', 1),
(10, '101010100', 'Elena Castro', 'Avenida J #2223', 'Quellon', 'Quellon', 'Los Lagos', '147369258', 'elena@example.com', 1),
(11, '270396356', 'Crow Advance', 'Nercon s/n', 'Nercon', 'Castro', 'Región de Los Lagos', '967553841', 'crow@gmail.com', 1),
(12, '270396356', 'Crow Advance', 'Nercon Rural', 'Nercon', 'Castro', 'Región de Los Lagos', '967553841', 'edgardoruotolo@gmail.com', 1);

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
  `estado_Contacto` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id_Contacto`, `id_Cliente`, `nombre_Contacto`, `apellido_Contacto`, `rut_Contacto`, `telefono_Contacto`, `direccion_Contacto`, `estado_Contacto`) VALUES
(3, 2, 'Carlos', 'Talagorria', '280990995', '22222222', 'Blanco Encalada', 1),
(4, 2, 'Gaston', 'Pombo', '1234567', '444444444', 'Piriapolis ', 1),
(5, 3, 'Contacto1-Cliente3', 'Apellido1-Cliente3', '33333333-3', '555555555', 'Dirección1-Cliente3', 1),
(7, 4, 'Contacto1-Cliente4', 'Apellido1-Cliente4', '44444444-4', '777777777', 'Dirección1-Cliente4', 1),
(8, 4, 'Contacto2-Cliente4', 'Apellido2-Cliente4', '44444444-4', '888888888', 'Dirección2-Cliente4', 1),
(9, 5, 'Contacto1-Cliente5', 'Apellido1-Cliente5', '55555555-5', '999999999', 'Dirección1-Cliente5', 1),
(11, 4, 'dsfadsfdsf', 'fdasdsfsdaf23143214', '213423142314', '2314234213423', 'sdafdsfasdfsd', 1),
(12, 1, 'Edgardo', 'Ruotolo', '270396356', '967553841', 'Nercon S/N', 1),
(13, 1, 'Felipe', 'Rodriguez', '2314234235345', '234134123423', 'Gamboa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `id_Contrato` int(11) NOT NULL,
  `id_Cliente` int(11) DEFAULT NULL,
  `obra_Contrato` varchar(50) DEFAULT NULL,
  `estado_Contrato` int(11) DEFAULT NULL,
  `fechaInicio_Contrato` date DEFAULT NULL,
  `fechaFin_Contrato` date DEFAULT NULL,
  `valorMensual_Contrato` int(11) DEFAULT NULL,
  `valorTotal_Contrato` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `contratos`
--

INSERT INTO `contratos` (`id_Contrato`, `id_Cliente`, `obra_Contrato`, `estado_Contrato`, `fechaInicio_Contrato`, `fechaFin_Contrato`, `valorMensual_Contrato`, `valorTotal_Contrato`) VALUES
(1, 1, 'Obra 01', 2, '2023-01-01', '2023-12-31', 1000, 12000),
(2, 1, 'Obra 02', 1, '2023-02-01', '2023-11-30', 800, 9600),
(3, 2, 'Obra 03', 2, '2023-03-15', '2023-10-15', 1200, 14400),
(4, 2, 'Obra 04', 2, '2023-04-10', '2023-09-30', 900, 10800),
(5, 3, 'Obra 05', 2, '2023-05-20', '2023-08-20', 750, 9000),
(6, 3, 'Obra 06', 1, '2023-06-01', '2023-12-31', 1100, 13200),
(7, 4, 'Obra 07', 2, '2023-07-10', '2023-10-31', 950, 11400),
(8, 4, 'Obra ruta 5 camino Chonchi', 1, '2023-08-05', '2023-09-30', 700, 8400),
(9, 5, 'Obra 09', 1, '2023-09-15', '2023-12-15', 1300, 15600),
(10, 5, 'Obra 10', 2, '2023-10-01', '2023-11-30', 850, 10200),
(11, 1, 'Obra ruta 5 camino Quellon', 2, '2024-01-01', '2024-01-01', 100, 1000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_Factura` int(11) NOT NULL,
  `id_Servicio` int(11) DEFAULT NULL,
  `estado_Factura` int(2) DEFAULT NULL,
  `fecha_Factura` date DEFAULT NULL,
  `valor_Factura` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_Factura`, `id_Servicio`, `estado_Factura`, `fecha_Factura`, `valor_Factura`) VALUES
(1, 1, 0, '2023-01-10', 200),
(2, 2, 0, '2023-02-15', 150),
(3, 3, 0, '2023-03-20', 300),
(4, 4, 0, '2023-04-25', 250),
(5, 5, 0, '2023-05-30', 180),
(6, 6, 0, '2023-06-05', 220),
(7, 7, 0, '2023-07-10', 270),
(8, 8, 0, '2023-08-15', 190),
(9, 9, 0, '2023-09-20', 320),
(10, 10, 0, '2023-10-25', 240);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_Servicio` int(11) NOT NULL,
  `id_Contrato` int(11) DEFAULT NULL,
  `id_Bath` int(11) DEFAULT NULL,
  `tipo_Servicio` varchar(50) DEFAULT NULL,
  `fecha_Servicio` date DEFAULT NULL,
  `observaciones_Servicio` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_Servicio`, `id_Contrato`, `id_Bath`, `tipo_Servicio`, `fecha_Servicio`, `observaciones_Servicio`) VALUES
(1, 1, 1, 'Mantenimiento', '2023-01-05', 'Reparación de grifos'),
(2, 2, 2, 'Limpieza', '2023-02-10', 'Limpieza profunda'),
(3, 3, 3, 'Reparación', '2023-03-15', 'Arreglo de sanitario'),
(4, 4, 4, 'Instalación', '2023-04-20', 'Instalación de accesorios'),
(5, 5, 5, 'Mantenimiento', '2023-05-25', 'Revisión de cañerías'),
(6, 6, 6, 'Limpieza', '2023-06-30', 'Limpieza de azulejos'),
(7, 7, 7, 'Reparación', '2023-07-05', 'Arreglo de ducha'),
(8, 8, 8, 'Instalación', '2023-08-10', 'Instalación de espejo'),
(9, 9, 9, 'Mantenimiento', '2023-09-15', 'Reemplazo de grifería'),
(10, 10, 10, 'Limpieza', '2023-10-20', 'Limpieza exhaustiva');

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
(7, 'edgardoruotolo@gmail.com', 'eruotolo', '$2y$10$vC41AOMLc.nfBlZFOwukkuN/44tpQIlIjnGdRMMOVdlzOTf5fT5zq', '754bcf4b23f6b6f887e30182f22ac0b7bd577256d26e7e744546ac8403ee855a3aa236909dd98571731913e85f8dd1b1e7c9', 'Edgardo', 'Ruotolo', 'avatar-4.jpg', 1, 1, '2020-09-24 17:53:37');

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
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_Factura`),
  ADD KEY `facturas_servicios_fk` (`id_Servicio`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_Servicio`),
  ADD KEY `seguimientos_bathrooms_fk` (`id_Bath`),
  ADD KEY `seguimientos_contratos_fk` (`id_Contrato`);

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
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_Cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id_Contacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id_Contrato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_Factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_Servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

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
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_servicios_fk` FOREIGN KEY (`id_Servicio`) REFERENCES `servicios` (`id_Servicio`);

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `seguimientos_bathrooms_fk` FOREIGN KEY (`id_Bath`) REFERENCES `bathrooms` (`id_Bath`),
  ADD CONSTRAINT `seguimientos_contratos_fk` FOREIGN KEY (`id_Contrato`) REFERENCES `contratos` (`id_Contrato`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_category_fk` FOREIGN KEY (`category`) REFERENCES `category` (`id_category`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
