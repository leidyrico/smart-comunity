-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-10-2025 a las 13:42:40
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
-- Base de datos: `smart_comunity`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actas`
--

CREATE TABLE `actas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_documento` enum('Correspondencia','Comunicado','Actas') NOT NULL DEFAULT 'Actas',
  `nro_doc` varchar(255) NOT NULL,
  `nombre_doc` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `archivo_contenido` longtext DEFAULT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `archivo_tipo` varchar(255) DEFAULT NULL,
  `archivo_tamaño` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apartamentos`
--

CREATE TABLE `apartamentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `numero` varchar(255) NOT NULL,
  `piso` int(11) DEFAULT NULL,
  `torre` varchar(255) DEFAULT NULL,
  `propietario` varchar(255) NOT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `area_m2` decimal(8,2) DEFAULT NULL,
  `tipo` enum('apartamento','local','parqueadero','deposito','estudio') DEFAULT 'apartamento',
  `estado` enum('ocupado','desocupado','en_arriendo') NOT NULL DEFAULT 'ocupado',
  `estatus_financiero` enum('solvente','deudor','moroso') DEFAULT 'solvente',
  `fecha_cambio_estatus` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `apartamentos`
--

INSERT INTO `apartamentos` (`id`, `numero`, `piso`, `torre`, `propietario`, `telefono`, `email`, `area_m2`, `tipo`, `estado`, `estatus_financiero`, `fecha_cambio_estatus`, `observaciones`, `created_at`, `updated_at`) VALUES
(32, 'PB-1', 0, 'A', 'Belkis Navarro', '04142619130', 'belkis147@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:34:34'),
(33, 'PB-4', 0, 'A', 'Leidy Rico', '04228152210', 'leidyrico12@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-05', '', '2025-08-27 02:45:26', '2025-10-06 01:30:53'),
(34, '11', 1, 'A', 'Juan Roman', '', 'jaouking@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-29', '', '2025-08-27 02:45:26', '2025-09-02 18:53:41'),
(35, '12', 1, 'A', 'Gherlys Cabrera', '04149172345', 'gherlys.cabrera@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:34:43'),
(37, '14', 1, 'A', 'Milagros Fermin', '04129392234', 'milafer1056@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:26', '2025-09-02 23:36:14'),
(38, '21', 2, 'A', 'Florangel Torres', '04142496690', 'florangeltorrest@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:24:04'),
(39, '22', 2, 'A', 'Luis Seijas', NULL, 'luis.seijas@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:26', '2025-09-02 23:36:31'),
(40, '23', 2, 'A', 'Maura Infante', '04142326032', 'may6390inf@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:24:59'),
(41, '24', 2, 'A', 'Carmen Gutierrez', NULL, 'carmengr224@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:34:53'),
(42, '31', 3, 'A', 'Edrey Eligon', '+56933307335', 'edreyeligon@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:25:30'),
(43, '32', 3, 'A', 'Henry Castellanos', '04122822820', 'henrycastellanos32@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:34:56'),
(44, '33', 3, 'A', 'Gloria Uzcategui', '04122098254', 'gloriasoledadu@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:34:58'),
(45, '34', 3, 'A', 'Xiouja Diaz', NULL, 'xiojuad@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:00'),
(46, '41', 4, 'A', 'Ailin Velis', '+50672649484', 'avelis07@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:51:23'),
(47, '42', 4, 'A', 'Marian Gonzalez', '04242529099', 'mariangonzalezr31@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:26', '2025-09-02 18:53:41'),
(48, '43', 4, 'A', 'Isabel Zacarias', '04141530501', 'isabelzacarias333@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 03:19:43'),
(49, '44', 4, 'A', 'Ana Lozano', NULL, 'manitalozano15@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:27:06'),
(50, '51', 5, 'A', 'Jose Dominicis', NULL, 'josemanueldominicis@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:26', '2025-10-02 12:24:56'),
(51, '52', 5, 'A', 'Clara Marina Gonzalez', NULL, 'claramarina2007@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:12'),
(52, '53', 5, 'A', 'Yohely Meza', '04242400163', 'yohelymeza231615@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:26', '2025-09-02 18:53:41'),
(53, '54', 5, 'A', 'Pablo Cardozo', '04166310967', 'pablomcardozo08@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:17'),
(54, '61', 6, 'A', 'Sabrina Casaña', '04241208302', 'yuritzacasana@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-09-02', '', '2025-08-27 02:45:26', '2025-09-02 23:55:50'),
(55, '62', 6, 'A', 'Carmen Quintero', '04127205359', 'carmenamalia.54.12@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-09-02', '', '2025-08-27 02:45:26', '2025-09-02 23:56:09'),
(56, '63', 6, 'A', 'Nasdelida Perez', '04265132129', 'pereznasdelida@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:27:42'),
(57, '64', 6, 'A', 'Miguel Godoy', NULL, 'arkangeldejesus88@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:30:46'),
(58, '71', 7, 'A', 'Gladys Combariza', '04169186237', 'gladyscombariza93@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:31:03'),
(59, '72', 7, 'A', 'Ramón Rojas', '04143664857', 'carmenele.te69@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-03 02:31:53'),
(60, '73', 7, 'A', 'Rodolfo Uzcategui', '04241433443', 'antouzca0707@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:30'),
(61, '74', 7, 'A', 'Ciro Chirinos', NULL, 'franklinnavas427@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:32'),
(62, '81', 8, 'A', 'Argenis Simancas', '04143100934', 'asimancas74@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:33'),
(63, '82', 8, 'A', 'Gladys Hernandez', '04142229488', 'gladysjher@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:35'),
(64, '83', 8, 'A', 'Luis Rivas', '+573132654694', 'drivas.9986@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:26', '2025-09-02 18:53:41'),
(65, '84', 8, 'A', 'Dinora Lombardo', '04125799626', 'flialopezl@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-03', '', '2025-08-27 02:45:26', '2025-10-04 01:50:20'),
(66, '91', 9, 'A', 'Angela Jimenez', NULL, 'angel04jb@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-05', '', '2025-08-27 02:45:26', '2025-10-06 00:28:44'),
(67, '92', 9, 'A', 'Euridys Urribarri', '04122643937', 'liseth.hernandez.u@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:26', '2025-10-02 13:35:43'),
(68, '93', 9, 'A', 'Natalia Muñoz', '04128193692', 'nsabrina7@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:35:45'),
(69, '94', 9, 'A', 'Maria Bonelli', '04143274690', 'drambonelli@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-03 02:32:17'),
(70, '101', 10, 'A', 'Cleyburn Saint', NULL, 'clbyburn@yahoo.es', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-05', '', '2025-08-27 02:45:27', '2025-10-06 00:26:30'),
(71, '102', 10, 'A', 'Nerys Zerpa', NULL, 'joanquer.davila@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-06', '', '2025-08-27 02:45:27', '2025-10-07 01:25:55'),
(72, '103', 10, 'A', 'Betty Romay', NULL, 'jaouking@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:35:53'),
(73, '104', 10, 'A', 'Brayan Fernandez', '04141054063', 'brayanjf21@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:35:56'),
(74, '111', 11, 'A', 'Jackelin Giraldo', '04264071607', 'yesenirgg@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-05', '', '2025-08-27 02:45:27', '2025-10-06 00:28:04'),
(75, '112', 11, 'A', 'Mackdy Quevedo', '04128081012', 'verdurivero@hotmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-03', '', '2025-08-27 02:45:27', '2025-10-04 01:39:20'),
(76, '113', 11, 'A', 'Henry Verdú', NULL, 'jaouking@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:36:02'),
(77, '114', 11, 'A', 'Erika Ovalles', '04241692701', 'eovalles.albero@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:27', '2025-10-02 12:14:47'),
(78, '121', 12, 'A', 'Oreste Roldan', '04127019897', 'roldanoar25@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-03 02:32:40'),
(79, '122', 12, 'A', 'Maria Dominguez', '04141388270', 'aledominguez.1977@hotmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-09-02', '', '2025-08-27 02:45:27', '2025-10-02 12:14:09'),
(80, '123', 12, 'A', 'Simona Lucena', '04241859547', 'lucenasimona2@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:36:10'),
(81, '124', 12, 'A', 'Erick Guerrero', '04128475724', 'erickpgdiaz@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-03 02:33:10'),
(82, '131', 13, 'A', 'Carmen Hernandez', '04141736568', 'chernaraez.61@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:36:13'),
(83, '132', 13, 'A', 'Sonia Magaly Ruiz', '04168796721', 'ruizsoniamagaly@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:36:15'),
(84, '133', 13, 'A', 'Franklin Barboza', '04163015802', 'franklinbarbozasuarez@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-06', '', '2025-08-27 02:45:27', '2025-10-07 01:24:15'),
(85, '134', 13, 'A', 'Luz Zerpa', '+56934264477', 'luzdaceli@gmail.com', 80.00, 'apartamento', 'ocupado', 'solvente', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-03 02:33:30'),
(86, '141', 14, 'A', 'Luis Viloria', '04265196539', 'viloriale@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-09-02', '', '2025-08-27 02:45:27', '2025-10-02 12:11:14'),
(87, '142', 14, 'A', 'Raquel Colmenares', NULL, 'ing.monterola10@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-30', '', '2025-08-27 02:45:27', '2025-10-02 12:11:50'),
(88, '143', 14, 'A', 'Nurys Hernandez', '+5491155687944', 'nuher001@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-10-02', '', '2025-08-27 02:45:27', '2025-10-02 13:36:25'),
(89, '144', 14, 'A', 'Deyanir Mora', '+14352396531', 'deyanirmora@gmail.com', 80.00, 'apartamento', 'ocupado', 'deudor', '2025-09-30', '', '2025-08-27 02:45:27', '2025-10-02 12:10:41'),
(91, '13', 1, 'A', 'Mairyn Burgos', NULL, 'violinistica.derecho@gmail.com', 80.00, 'apartamento', 'ocupado', 'moroso', '2025-08-29', NULL, '2025-08-30 02:36:08', '2025-10-02 12:26:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos_new`
--

CREATE TABLE `egresos_new` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nro_factura` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `monto_en_bs` decimal(15,2) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `proveedor_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inquilinos`
--

CREATE TABLE `inquilinos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nombre_inquilino` varchar(255) NOT NULL,
  `nro_apartamento` varchar(255) NOT NULL,
  `monto_deuda` decimal(10,2) NOT NULL,
  `fecha_deuda` date NOT NULL,
  `monto_ultimo_pago` decimal(10,2) DEFAULT NULL,
  `fecha_ultimo_pago` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventarios`
--

CREATE TABLE `inventarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(255) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'disponible',
  `fecha_adquisicion` date DEFAULT NULL,
  `proveedor` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventarios`
--

INSERT INTO `inventarios` (`id`, `nombre`, `descripcion`, `categoria`, `cantidad`, `precio_unitario`, `ubicacion`, `estado`, `fecha_adquisicion`, `proveedor`, `observaciones`, `created_at`, `updated_at`) VALUES
(2, 'TV LED 24\" MARCA LG', 'TV utilizado para la visualizacion del CCTV', 'ELECTRONICOS', 1, NULL, 'OFICINA', 'en_uso', '2025-08-15', 'DONACION', NULL, '2025-09-02 16:55:53', '2025-09-03 00:40:25'),
(3, 'SISTEMA CCTV 4 CAMARAS', 'SISTEMA DE CCTV UBICADO EN OFICINA CON INSTALACION DE 2 CAMARAS INTERNAS Y 2 EXTERNAS', 'ELECTRONICOS', 1, NULL, 'OFICINA', 'en_uso', '2025-09-02', 'DONACION', NULL, '2025-09-03 00:41:38', '2025-09-03 00:41:38'),
(4, 'TABLERO DE LLAVES', 'TABLERO PARA LAS LLAVES DE ACCESO DEL EDIFICIO', 'MOBILIARIO', 1, NULL, 'OFICINA', 'en_uso', '2025-09-02', 'DONACION', NULL, '2025-09-03 00:42:44', '2025-09-03 00:42:44'),
(5, 'REGLETA DE 2 ENCHUFES + USB', 'Se utiliza para la conexion de las camaras y el tv del cctv', 'ELECTRONICOS', 1, NULL, 'OFICINA', 'en_uso', '2025-09-02', 'DONACION', NULL, '2025-09-03 00:43:49', '2025-09-03 00:43:49'),
(6, 'LAPTOP DELL', 'Equipo para uso en oficina y manejo de los sistemas', 'ELECTRONICOS', 1, NULL, 'OFICINA', 'disponible', '2025-09-02', 'DONACION', NULL, '2025-09-03 00:48:22', '2025-09-03 00:48:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_12_143900_create_actas_table', 1),
(5, '2025_08_14_195212_create_inquilinos_table', 1),
(6, '2025_08_14_195400_add_columns_to_inquilinos_table', 1),
(7, '2025_08_18_174815_add_role_and_status_to_users_table', 1),
(8, '2025_08_18_230554_add_tipo_documento_to_actas_table', 1),
(9, '2025_08_19_113219_rename_acta_columns_to_doc_columns', 1),
(10, '2025_08_22_153935_create_apartamentos_table', 1),
(11, '2025_08_22_154231_create_recibo_gasto_comuns_table', 1),
(12, '2025_08_22_162126_create_pagos_table', 1),
(13, '2025_08_23_001400_add_estudio_to_apartamentos_tipo', 1),
(14, '2025_08_25_105754_make_recibo_gasto_comun_id_nullable_in_pagos_table', 1),
(15, '2025_08_25_122557_add_estatus_financiero_to_apartamentos_table', 1),
(16, '2025_08_25_151151_make_fecha_pago_nullable_in_pagos_table', 1),
(17, '2025_08_25_183010_add_fecha_cambio_estatus_to_apartamentos_table', 1),
(18, '2025_08_27_162645_add_moroso_to_estatus_financiero_apartamentos', 1),
(20, '2025_09_02_113456_create_inventarios_table', 2),
(21, '2025_09_02_130010_add_archivo_adjunto_to_recibo_gasto_comuns_table', 3),
(22, '2025_09_05_002107_create_egresos_table', 4),
(23, '2025_10_04_162031_add_monto_en_bs_to_pagos_table', 5),
(24, '2025_10_04_162036_add_monto_en_bs_to_pagos_table', 5),
(25, '2025_10_05_003736_create_spaces_table', 6),
(27, '2025_10_05_003858_create_reservations_table', 7),
(28, '2025_10_04_171553_add_monto_en_bs_to_egresos_table', 8),
(29, '2025_10_05_023103_create_proveedors_table', 8),
(30, '2025_10_05_023222_add_proveedor_id_to_egresos_table', 8),
(31, '2025_10_05_223732_create_egresos_from_temp_table', 999);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `apartamento_id` bigint(20) UNSIGNED NOT NULL,
  `recibo_gasto_comun_id` bigint(20) UNSIGNED DEFAULT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `monto_en_bs` decimal(10,2) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `metodo_pago` varchar(255) DEFAULT NULL,
  `numero_comprobante` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('confirmado','pendiente_confirmacion','rechazado') NOT NULL DEFAULT 'confirmado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `apartamento_id`, `recibo_gasto_comun_id`, `monto_pagado`, `monto_en_bs`, `fecha_pago`, `metodo_pago`, `numero_comprobante`, `observaciones`, `estado`, `created_at`, `updated_at`) VALUES
(40, 34, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(41, 34, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(42, 34, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(43, 34, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(44, 34, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(45, 34, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(46, 34, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(47, 34, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(48, 34, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(49, 34, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(50, 34, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:45:38', '2025-08-30 07:45:38'),
(51, 34, 254, 34.31, NULL, '2025-08-01', 'transferencia', '0000001', 'GA', 'confirmado', '2025-08-30 07:46:25', '2025-08-30 07:46:25'),
(52, 91, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(53, 91, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(54, 91, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(55, 91, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(56, 91, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(57, 91, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(58, 91, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(59, 91, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(60, 91, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(61, 91, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(62, 91, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(63, 91, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(64, 91, 252, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(65, 91, 251, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(66, 91, 250, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(67, 91, 249, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(68, 91, 248, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(69, 91, 247, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(70, 91, 246, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(71, 91, 239, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 07:47:45', '2025-08-30 07:47:45'),
(75, 91, 239, 27.43, NULL, '2025-08-01', 'transferencia', '0000005', 'ga', 'confirmado', '2025-08-30 08:14:28', '2025-08-30 08:14:28'),
(76, 91, 261, 25.00, NULL, '2025-08-01', 'transferencia', '0000006', 'ga', 'confirmado', '2025-08-30 08:15:32', '2025-08-30 08:15:32'),
(77, 91, 246, 9.21, NULL, '2025-08-01', 'transferencia', '0000007', 'ga', 'confirmado', '2025-08-30 08:16:36', '2025-08-30 08:16:36'),
(86, 91, 265, 20.90, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(87, 91, 239, 10.00, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(88, 91, 246, 7.44, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(89, 91, 247, 14.15, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(90, 91, 248, 12.20, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(91, 91, 249, 13.18, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(92, 91, 250, 18.61, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(93, 91, 251, 3.52, NULL, '2025-08-18', 'transferencia', '672832207870', 'julio + abono | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 09:03:43', '2025-08-30 09:03:43'),
(94, 37, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 19:57:54', '2025-08-30 19:57:54'),
(95, 37, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 19:57:54', '2025-08-30 19:57:54'),
(96, 37, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 19:57:55', '2025-08-30 19:57:55'),
(97, 37, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 19:57:55', '2025-08-30 19:57:55'),
(98, 37, 239, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 19:57:55', '2025-08-30 19:57:55'),
(99, 37, 239, 12.43, NULL, '2025-08-01', 'transferencia', '0000008', 'ga', 'confirmado', '2025-08-30 19:58:56', '2025-08-30 19:58:56'),
(100, 37, 261, 38.36, NULL, '2025-08-25', 'transferencia', '20436432515', 'pago abril', 'confirmado', '2025-08-30 20:00:40', '2025-08-30 20:00:40'),
(101, 39, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(102, 39, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(103, 39, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(104, 39, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(105, 39, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(106, 39, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(107, 39, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(108, 39, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(109, 39, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(110, 39, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(111, 39, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(112, 39, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(113, 39, 252, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(114, 39, 251, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(115, 39, 250, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:46', '2025-08-30 20:03:46'),
(116, 39, 249, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:47', '2025-08-30 20:03:47'),
(117, 39, 248, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:47', '2025-08-30 20:03:47'),
(118, 39, 234, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:03:47', '2025-08-30 20:03:47'),
(119, 39, 248, 5.49, NULL, '2025-08-01', 'transferencia', '0000009', 'ga', 'confirmado', '2025-08-30 20:04:47', '2025-08-30 20:04:47'),
(120, 41, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:06:53', '2025-08-30 20:06:53'),
(121, 42, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:07:26', '2025-08-30 20:07:26'),
(122, 45, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:07:50', '2025-08-30 20:07:50'),
(123, 45, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:07:50', '2025-08-30 20:07:50'),
(124, 45, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 20:07:50', '2025-08-30 20:07:50'),
(125, 42, 255, 13.79, NULL, '2025-08-01', 'transferencia', '00000010', 'ga', 'confirmado', '2025-08-30 20:09:25', '2025-08-30 20:09:25'),
(129, 45, 262, 12.01, NULL, '2025-08-01', 'transferencia', '00000011', 'ga', 'confirmado', '2025-08-30 23:26:07', '2025-08-30 23:26:07'),
(130, 45, 265, 20.90, NULL, '2025-08-28', 'transferencia', '52417832949', 'JULIO Y ABONO A DEUDA | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:27:33', '2025-08-30 23:27:33'),
(131, 45, 262, 6.97, NULL, '2025-08-28', 'transferencia', '52417832949', 'JULIO Y ABONO A DEUDA | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:27:33', '2025-08-30 23:27:33'),
(132, 45, 263, 22.13, NULL, '2025-08-28', 'transferencia', '52417832949', 'JULIO Y ABONO A DEUDA | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:27:33', '2025-08-30 23:27:33'),
(133, 46, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:28:44', '2025-08-30 23:28:44'),
(134, 46, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:28:44', '2025-08-30 23:28:44'),
(135, 46, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:28:44', '2025-08-30 23:28:44'),
(136, 46, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:28:44', '2025-08-30 23:28:44'),
(137, 46, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:28:44', '2025-08-30 23:28:44'),
(138, 46, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:28:44', '2025-08-30 23:28:44'),
(139, 47, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(140, 47, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(141, 47, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(142, 47, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(143, 47, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(144, 47, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(145, 47, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(146, 47, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(147, 47, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(148, 47, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(149, 47, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(150, 47, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:29:22', '2025-08-30 23:29:22'),
(151, 46, 259, 12.70, NULL, '2025-08-01', 'transferencia', '000000012', 'GA', 'confirmado', '2025-08-30 23:30:32', '2025-08-30 23:30:32'),
(152, 47, 253, 8.77, NULL, '2025-08-01', 'transferencia', '00000013', 'GA', 'confirmado', '2025-08-30 23:34:03', '2025-08-30 23:34:03'),
(153, 50, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(154, 50, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(155, 50, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(156, 50, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(157, 50, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(158, 50, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(159, 50, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(160, 50, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(161, 50, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(162, 50, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(163, 50, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(164, 50, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(165, 50, 252, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(166, 50, 251, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(167, 50, 250, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(168, 50, 249, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(169, 50, 248, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(170, 50, 247, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(171, 50, 246, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(172, 50, 245, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(173, 50, 244, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(174, 50, 242, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(175, 50, 241, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(176, 50, 243, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(177, 50, 240, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(178, 50, 239, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(179, 50, 238, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(180, 50, 237, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:38:12', '2025-08-30 23:38:12'),
(182, 50, 237, 16.73, NULL, '2025-08-01', 'transferencia', '00000015', 'GA', 'confirmado', '2025-08-30 23:39:37', '2025-08-30 23:39:37'),
(183, 50, 265, 20.90, NULL, '2025-08-19', 'transferencia', '672832496586', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:42:09', '2025-08-30 23:42:09'),
(184, 50, 237, 20.31, NULL, '2025-08-19', 'transferencia', '672832496586', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:42:09', '2025-08-30 23:42:09'),
(185, 50, 238, 24.43, NULL, '2025-08-19', 'transferencia', '672832496586', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:42:09', '2025-08-30 23:42:09'),
(186, 50, 239, 0.10, NULL, '2025-08-19', 'transferencia', '672832496586', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:42:09', '2025-08-30 23:42:09'),
(187, 52, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(188, 52, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(189, 52, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(190, 52, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(191, 52, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(192, 52, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(193, 52, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(194, 52, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(195, 52, 252, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(196, 52, 251, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(197, 52, 250, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(198, 52, 249, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(199, 52, 248, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(200, 52, 239, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(201, 52, 238, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(202, 52, 237, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(203, 52, 236, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:45:55', '2025-08-30 23:45:55'),
(204, 52, 239, 12.43, NULL, '2025-08-01', 'transferencia', '0000015', 'GA', 'confirmado', '2025-08-30 23:47:14', '2025-08-30 23:47:14'),
(205, 52, 263, 10.00, NULL, '2025-08-01', 'transferencia', '00000016', 'GA', 'confirmado', '2025-08-30 23:47:56', '2025-08-30 23:47:56'),
(206, 52, 238, 10.13, NULL, '2025-08-01', 'transferencia', '00000017', 'GA', 'confirmado', '2025-08-30 23:48:31', '2025-08-30 23:48:31'),
(207, 52, 237, 12.04, NULL, '2025-08-01', 'transferencia', '00000018', 'GA', 'confirmado', '2025-08-30 23:49:27', '2025-08-30 23:49:27'),
(208, 52, 236, 26.51, NULL, '2025-08-01', 'transferencia', '000000019', 'GA', 'confirmado', '2025-08-30 23:50:06', '2025-08-30 23:50:06'),
(209, 52, 248, 7.06, NULL, '2025-08-01', 'transferencia', '00000020', 'GA', 'confirmado', '2025-08-30 23:50:55', '2025-08-30 23:50:55'),
(210, 52, 258, 30.10, NULL, '2025-08-01', 'transferencia', '00000021', 'GA', 'confirmado', '2025-08-30 23:51:34', '2025-08-30 23:51:34'),
(211, 57, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:57:01', '2025-08-30 23:57:01'),
(212, 57, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-30 23:57:01', '2025-08-30 23:57:01'),
(213, 57, 263, 12.18, NULL, '2025-08-01', 'transferencia', '00000022', 'GA', 'confirmado', '2025-08-30 23:57:50', '2025-08-30 23:57:50'),
(214, 57, 265, 20.90, NULL, '2025-08-29', 'transferencia', '0590548316493', 'PAGO TOTAL | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:59:08', '2025-08-30 23:59:08'),
(215, 57, 263, 12.46, NULL, '2025-08-29', 'transferencia', '0590548316493', 'PAGO TOTAL | Pago global distribuido automáticamente', 'confirmado', '2025-08-30 23:59:08', '2025-08-30 23:59:08'),
(216, 59, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:01:23', '2025-08-31 00:01:23'),
(217, 59, 266, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:01:23', '2025-08-31 00:01:23'),
(218, 59, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:01:23', '2025-08-31 00:01:23'),
(219, 59, 264, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:01:23', '2025-08-31 00:01:23'),
(220, 64, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(221, 64, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(222, 64, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(223, 64, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(224, 64, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(225, 64, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(226, 64, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(227, 64, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(228, 64, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(229, 64, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(230, 64, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(231, 64, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(232, 64, 252, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(233, 64, 251, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(234, 64, 250, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(235, 64, 249, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(236, 64, 248, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(237, 64, 247, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(238, 64, 246, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:05:09', '2025-08-31 00:05:09'),
(239, 64, 246, 8.40, NULL, '2025-08-01', 'transferencia', '00000023', 'GA', 'confirmado', '2025-08-31 00:05:51', '2025-08-31 00:05:51'),
(240, 64, 265, 20.90, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(241, 64, 246, 8.25, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(242, 64, 247, 14.15, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(243, 64, 248, 12.20, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(244, 64, 249, 13.18, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(245, 64, 250, 18.61, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(246, 64, 251, 17.95, NULL, '2025-08-16', 'transferencia', '13184423360', 'JULIO MAS ABONO | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:10:04', '2025-08-31 00:10:04'),
(247, 77, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(248, 77, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(249, 77, 262, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(250, 77, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(251, 77, 260, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(252, 77, 259, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(253, 77, 258, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(254, 77, 257, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(255, 77, 256, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(256, 77, 255, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(257, 77, 254, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(258, 77, 253, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(259, 77, 252, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(260, 77, 251, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(261, 77, 250, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(262, 77, 249, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(263, 77, 248, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(264, 77, 247, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(265, 77, 246, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(266, 77, 245, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(267, 77, 244, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(268, 77, 242, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(269, 77, 241, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(270, 77, 240, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(271, 77, 239, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(272, 77, 238, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(273, 77, 237, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:15:58', '2025-08-31 00:15:58'),
(274, 77, 263, 10.00, NULL, '2025-08-01', 'transferencia', '00000025', 'GA', 'confirmado', '2025-08-31 00:17:26', '2025-08-31 00:17:26'),
(275, 77, 238, 9.83, NULL, '2025-08-01', 'transferencia', '00000026', 'GA', 'confirmado', '2025-08-31 00:18:11', '2025-08-31 00:18:11'),
(276, 77, 237, 12.04, NULL, '2025-08-01', 'transferencia', '00000027', 'GA', 'confirmado', '2025-08-31 00:18:47', '2025-08-31 00:18:47'),
(277, 78, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:21:05', '2025-08-31 00:21:05'),
(278, 78, 238, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:21:05', '2025-08-31 00:21:05'),
(279, 78, 238, 43.40, NULL, '2025-08-01', 'transferencia', '00000028', 'GA', 'confirmado', '2025-08-31 00:21:58', '2025-08-31 00:21:58'),
(280, 78, 265, 20.90, NULL, '2025-08-10', 'transferencia', '590541271216', 'JULIO', 'confirmado', '2025-08-31 00:22:51', '2025-08-31 00:22:51'),
(281, 79, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:24:16', '2025-08-31 00:24:16'),
(282, 79, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:24:16', '2025-08-31 00:24:16'),
(285, 86, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:30:35', '2025-08-31 00:30:35'),
(286, 86, 261, 13.36, NULL, '2025-08-01', 'transferencia', '00000030', 'GA', 'confirmado', '2025-08-31 00:32:06', '2025-08-31 00:32:06'),
(287, 87, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:47:40', '2025-08-31 00:47:40'),
(288, 87, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:47:40', '2025-08-31 00:47:40'),
(289, 87, 239, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:47:40', '2025-08-31 00:47:40'),
(290, 87, 238, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:47:40', '2025-08-31 00:47:40'),
(291, 87, 237, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:47:40', '2025-08-31 00:47:40'),
(292, 87, 239, 12.43, NULL, '2025-08-01', 'transferencia', '00000031', 'GA', 'confirmado', '2025-08-31 00:48:22', '2025-08-31 00:48:22'),
(293, 87, 237, 33.59, NULL, '2025-08-01', 'transferencia', '00000032', 'GA', 'confirmado', '2025-08-31 00:49:21', '2025-08-31 00:49:21'),
(294, 87, 238, 10.13, NULL, '2025-08-01', 'transferencia', '00000033', 'GA', 'confirmado', '2025-08-31 00:50:00', '2025-08-31 00:50:00'),
(295, 87, 263, 10.00, NULL, '2025-08-01', 'transferencia', '00000034', 'GA', 'confirmado', '2025-08-31 00:50:39', '2025-08-31 00:50:39'),
(296, 87, 263, 14.64, NULL, '2025-08-21', 'transferencia', '13187382569', 'JUNIO', 'confirmado', '2025-08-31 00:51:38', '2025-08-31 00:51:38'),
(297, 89, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:53:51', '2025-08-31 00:53:51'),
(298, 89, 263, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:53:51', '2025-08-31 00:53:51'),
(299, 89, 261, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 00:53:51', '2025-08-31 00:53:51'),
(300, 89, 261, 17.81, NULL, '2025-08-01', 'transferencia', '00000035', 'GA', 'confirmado', '2025-08-31 00:54:29', '2025-08-31 00:54:29'),
(301, 89, 265, 20.90, NULL, '2025-08-19', 'transferencia', '13186184455', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:56:02', '2025-08-31 00:56:02'),
(302, 89, 261, 20.55, NULL, '2025-08-19', 'transferencia', '13186184455', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:56:02', '2025-08-31 00:56:02'),
(303, 89, 263, 24.64, NULL, '2025-08-19', 'transferencia', '13186184455', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:56:02', '2025-08-31 00:56:02'),
(304, 89, 265, 0.00, NULL, '2025-08-19', 'transferencia', '13186184455', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 00:56:02', '2025-08-31 00:56:02'),
(305, 32, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:02:32', '2025-08-31 01:02:32'),
(306, 32, 265, 20.90, NULL, '2025-08-15', 'transferencia', '672829279401', 'PAGO JULIO', 'confirmado', '2025-08-31 01:03:17', '2025-08-31 01:03:17'),
(307, 33, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:04:06', '2025-08-31 01:04:06'),
(308, 33, 265, 20.90, NULL, '2025-08-18', 'transferencia', '52306746636', 'JULIO', 'confirmado', '2025-08-31 01:04:44', '2025-08-31 01:04:44'),
(309, 35, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:06:02', '2025-08-31 01:06:02'),
(310, 35, 265, 20.90, NULL, '2025-08-14', 'transferencia', '68272', 'JULIO', 'confirmado', '2025-08-31 01:06:33', '2025-08-31 01:06:33'),
(311, 38, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:08:44', '2025-08-31 01:08:44'),
(312, 38, 265, 20.90, NULL, '2025-08-10', 'transferencia', '52227092508', 'JULIO', 'confirmado', '2025-08-31 01:09:17', '2025-08-31 01:09:17'),
(313, 40, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:10:46', '2025-08-31 01:10:46'),
(314, 40, 265, 20.90, NULL, '2025-08-10', 'transferencia', '652825445158', 'JULIO', 'confirmado', '2025-08-31 01:11:18', '2025-08-31 01:11:18'),
(315, 42, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:12:23', '2025-08-31 01:12:23'),
(316, 42, 265, 20.90, NULL, '2025-08-12', 'transferencia', '52241304993', 'JULIO', 'confirmado', '2025-08-31 01:13:15', '2025-08-31 01:13:15'),
(317, 43, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:13:38', '2025-08-31 01:13:38'),
(318, 43, 265, 20.90, NULL, '2025-08-11', 'transferencia', '672825971250', 'JULIO', 'confirmado', '2025-08-31 01:14:05', '2025-08-31 01:14:05'),
(319, 44, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:14:55', '2025-08-31 01:14:55'),
(320, 44, 265, 20.90, NULL, '2025-08-22', 'transferencia', '52348683917', 'JULIO', 'confirmado', '2025-08-31 01:15:27', '2025-08-31 01:15:27'),
(321, 46, 265, 20.90, NULL, '2025-08-15', 'transferencia', '672828279255', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 01:18:59', '2025-08-31 01:18:59'),
(322, 46, 259, 14.18, NULL, '2025-08-15', 'transferencia', '672828279255', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 01:18:59', '2025-08-31 01:18:59'),
(323, 46, 260, 14.92, NULL, '2025-08-15', 'transferencia', '672828279255', ' | Pago global distribuido automáticamente', 'confirmado', '2025-08-31 01:18:59', '2025-08-31 01:18:59'),
(324, 48, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:20:04', '2025-08-31 01:20:04'),
(325, 48, 265, 20.90, NULL, '2025-08-10', 'transferencia', '677233584555', NULL, 'confirmado', '2025-08-31 01:21:05', '2025-08-31 01:21:05'),
(326, 49, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:21:33', '2025-08-31 01:21:33'),
(327, 49, 265, 20.90, NULL, '2025-08-10', 'transferencia', '62863', NULL, 'confirmado', '2025-08-31 01:22:00', '2025-08-31 01:22:00'),
(328, 51, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:24:00', '2025-08-31 01:24:00'),
(329, 51, 265, 20.90, NULL, '2025-08-12', 'transferencia', '7643', NULL, 'confirmado', '2025-08-31 01:24:36', '2025-08-31 01:24:36'),
(330, 53, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:25:31', '2025-08-31 01:25:31'),
(331, 53, 265, 20.90, NULL, '2025-08-16', 'transferencia', NULL, NULL, 'confirmado', '2025-08-31 01:26:04', '2025-08-31 01:26:04'),
(332, 54, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:26:39', '2025-08-31 01:26:39'),
(333, 54, 265, 20.90, NULL, '2025-08-26', 'transferencia', '590547421314', NULL, 'confirmado', '2025-08-31 01:27:06', '2025-08-31 01:27:06'),
(334, 55, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:28:11', '2025-08-31 01:28:11'),
(335, 55, 265, 20.90, NULL, '2025-08-14', 'transferencia', '13183244116', NULL, 'confirmado', '2025-08-31 01:29:26', '2025-08-31 01:29:26'),
(336, 56, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:31:44', '2025-08-31 01:31:44'),
(337, 56, 265, 20.90, NULL, '2025-08-10', 'transferencia', '53306', NULL, 'confirmado', '2025-08-31 01:32:15', '2025-08-31 01:32:15'),
(338, 58, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:33:14', '2025-08-31 01:33:14'),
(339, 58, 265, 20.90, NULL, '2025-08-10', 'transferencia', '52227052191', NULL, 'confirmado', '2025-08-31 01:33:40', '2025-08-31 01:33:40'),
(341, 59, 266, 10.00, NULL, '2025-08-22', 'transferencia', '672834991236', NULL, 'confirmado', '2025-08-31 01:35:39', '2025-08-31 01:35:39'),
(342, 59, 265, 20.90, NULL, '2025-08-22', 'transferencia', '980634', NULL, 'confirmado', '2025-08-31 01:36:28', '2025-08-31 01:36:28'),
(343, 59, 263, 5.00, NULL, '2025-08-01', 'transferencia', '00000036', 'GA', 'confirmado', '2025-08-31 01:45:07', '2025-08-31 01:45:07'),
(344, 59, 263, 19.64, NULL, '2025-08-21', 'transferencia', '672834725477', NULL, 'confirmado', '2025-08-31 01:46:25', '2025-08-31 01:46:25'),
(345, 59, 264, 5.00, NULL, '2025-08-21', 'transferencia', '000000037', 'RESTANTE DE PAGO DE JUNIO', 'confirmado', '2025-08-31 01:47:20', '2025-08-31 01:47:20'),
(346, 60, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:48:22', '2025-08-31 01:48:22'),
(347, 60, 265, 20.90, NULL, '2025-08-22', 'transferencia', '347658243', NULL, 'confirmado', '2025-08-31 01:48:48', '2025-08-31 01:48:48'),
(348, 61, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 01:49:24', '2025-08-31 01:49:24'),
(349, 61, 265, 20.90, NULL, '2025-08-16', 'transferencia', '590543357165', NULL, 'confirmado', '2025-08-31 01:49:55', '2025-08-31 01:49:55'),
(350, 62, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:06:43', '2025-08-31 02:06:43'),
(351, 62, 265, 20.90, NULL, '2025-08-21', 'transferencia', '672834617353', NULL, 'confirmado', '2025-08-31 02:07:15', '2025-08-31 02:07:15'),
(352, 63, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:08:45', '2025-08-31 02:08:45'),
(353, 63, 265, 20.90, NULL, '2025-08-22', 'transferencia', '944052', NULL, 'confirmado', '2025-08-31 02:09:09', '2025-08-31 02:09:09'),
(354, 65, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:10:37', '2025-08-31 02:10:37'),
(355, 65, 265, 20.90, NULL, '2025-08-11', 'transferencia', '20432892660', NULL, 'confirmado', '2025-08-31 02:11:09', '2025-08-31 02:11:09'),
(356, 66, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:11:31', '2025-08-31 02:11:31'),
(357, 67, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:11:50', '2025-08-31 02:11:50'),
(358, 67, 265, 20.90, NULL, '2025-08-11', 'transferencia', '52239649247', NULL, 'confirmado', '2025-08-31 02:12:19', '2025-08-31 02:12:19'),
(359, 68, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:12:38', '2025-08-31 02:12:38'),
(360, 68, 265, 20.90, NULL, '2025-08-12', 'transferencia', '672826759809', NULL, 'confirmado', '2025-08-31 02:13:08', '2025-08-31 02:13:08'),
(361, 69, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:13:27', '2025-08-31 02:13:27'),
(362, 69, 265, 20.90, NULL, '2025-08-10', 'transferencia', '52227106865', NULL, 'confirmado', '2025-08-31 02:13:52', '2025-08-31 02:13:52'),
(363, 70, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:14:39', '2025-08-31 02:14:39'),
(364, 71, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:14:59', '2025-08-31 02:14:59'),
(365, 72, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:15:14', '2025-08-31 02:15:14'),
(366, 73, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:15:23', '2025-08-31 02:15:23'),
(367, 71, 265, 20.90, NULL, '2025-08-10', 'transferencia', '52227170783', NULL, 'confirmado', '2025-08-31 02:16:00', '2025-08-31 02:16:00'),
(368, 72, 265, 20.90, NULL, '2025-08-13', 'transferencia', '13182355012', NULL, 'confirmado', '2025-08-31 02:16:30', '2025-08-31 02:16:30'),
(369, 73, 265, 20.90, NULL, '2025-08-27', 'transferencia', '12771035', NULL, 'confirmado', '2025-08-31 02:17:02', '2025-08-31 02:17:02'),
(370, 74, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:18:03', '2025-08-31 02:18:03'),
(371, 75, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:18:12', '2025-08-31 02:18:12'),
(372, 76, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:18:33', '2025-08-31 02:18:33'),
(373, 74, 265, 20.90, NULL, '2025-08-18', 'transferencia', '672832385131', NULL, 'confirmado', '2025-08-31 02:18:59', '2025-08-31 02:18:59'),
(374, 75, 265, 20.90, NULL, '2025-08-11', 'transferencia', '52239535771', NULL, 'confirmado', '2025-08-31 02:19:28', '2025-08-31 02:19:28'),
(375, 76, 265, 20.90, NULL, '2025-08-14', 'transferencia', '590542418808', NULL, 'confirmado', '2025-08-31 02:20:11', '2025-08-31 02:20:11'),
(378, 80, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:25:25', '2025-08-31 02:25:25'),
(379, 81, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:25:35', '2025-08-31 02:25:35'),
(380, 80, 265, 20.90, NULL, '2025-08-11', 'transferencia', '59041323506', NULL, 'confirmado', '2025-08-31 02:25:58', '2025-08-31 02:25:58'),
(381, 81, 265, 20.90, NULL, '2025-08-11', 'transferencia', '672825815899', NULL, 'confirmado', '2025-08-31 02:26:26', '2025-08-31 02:26:26'),
(382, 82, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:27:04', '2025-08-31 02:27:04');
INSERT INTO `pagos` (`id`, `apartamento_id`, `recibo_gasto_comun_id`, `monto_pagado`, `monto_en_bs`, `fecha_pago`, `metodo_pago`, `numero_comprobante`, `observaciones`, `estado`, `created_at`, `updated_at`) VALUES
(383, 83, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:27:17', '2025-08-31 02:27:17'),
(384, 84, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:27:31', '2025-08-31 02:27:31'),
(385, 85, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:27:41', '2025-08-31 02:27:41'),
(386, 82, 265, 20.90, NULL, '2025-08-22', 'transferencia', '85664255', NULL, 'confirmado', '2025-08-31 02:28:09', '2025-08-31 02:28:09'),
(387, 83, 265, 20.90, NULL, '2025-08-13', 'transferencia', '672827376511', NULL, 'confirmado', '2025-08-31 02:28:39', '2025-08-31 02:28:39'),
(388, 84, 265, 20.90, NULL, '2025-08-11', 'transferencia', '13181297820', NULL, 'confirmado', '2025-08-31 02:29:08', '2025-08-31 02:29:08'),
(389, 85, 265, 20.90, NULL, '2025-08-10', 'transferencia', '67285491544', NULL, 'confirmado', '2025-08-31 02:29:38', '2025-08-31 02:29:38'),
(390, 86, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:30:14', '2025-08-31 02:30:14'),
(391, 86, 265, 20.90, NULL, '2025-08-18', 'transferencia', '672832303152', NULL, 'confirmado', '2025-08-31 02:30:55', '2025-08-31 02:30:55'),
(392, 88, 265, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-08-31 02:31:26', '2025-08-31 02:31:26'),
(393, 88, 265, 20.90, NULL, '2025-08-19', 'transferencia', '590544282817', NULL, 'confirmado', '2025-08-31 02:31:53', '2025-08-31 02:31:53'),
(395, 55, 267, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-09-01 17:04:51', '2025-09-01 17:04:51'),
(396, 59, 264, 5.00, NULL, '2025-08-31', 'transferencia', '672800561816', NULL, 'confirmado', '2025-09-01 17:06:09', '2025-09-01 17:06:09'),
(397, 79, 263, 16.60, NULL, '2025-08-01', 'transferencia', '00000040', 'ga', 'confirmado', '2025-09-01 17:07:54', '2025-09-01 17:07:54'),
(912, 32, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:39:57', '2025-09-02 23:39:57'),
(913, 33, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:01', '2025-09-02 23:40:01'),
(914, 34, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:03', '2025-09-02 23:40:03'),
(915, 35, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:05', '2025-09-02 23:40:05'),
(916, 37, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:06', '2025-09-02 23:40:06'),
(917, 38, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:09', '2025-09-02 23:40:09'),
(918, 39, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:11', '2025-09-02 23:40:11'),
(919, 40, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:13', '2025-09-02 23:40:13'),
(920, 41, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:14', '2025-09-02 23:40:14'),
(921, 42, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:16', '2025-09-02 23:40:16'),
(922, 43, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:18', '2025-09-02 23:40:18'),
(923, 44, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:20', '2025-09-02 23:40:20'),
(924, 45, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:22', '2025-09-02 23:40:22'),
(925, 46, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:24', '2025-09-02 23:40:24'),
(926, 47, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:26', '2025-09-02 23:40:26'),
(927, 48, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:28', '2025-09-02 23:40:28'),
(928, 49, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:30', '2025-09-02 23:40:30'),
(929, 50, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:32', '2025-09-02 23:40:32'),
(930, 51, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:34', '2025-09-02 23:40:34'),
(931, 52, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:36', '2025-09-02 23:40:36'),
(932, 53, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:38', '2025-09-02 23:40:38'),
(933, 54, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:40', '2025-09-02 23:40:40'),
(934, 55, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:42', '2025-09-02 23:40:42'),
(935, 56, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:44', '2025-09-02 23:40:44'),
(936, 57, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:46', '2025-09-02 23:40:46'),
(937, 58, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:49', '2025-09-02 23:40:49'),
(938, 59, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:51', '2025-09-02 23:40:51'),
(939, 60, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:52', '2025-09-02 23:40:52'),
(940, 61, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:55', '2025-09-02 23:40:55'),
(941, 62, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:57', '2025-09-02 23:40:57'),
(942, 63, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:40:59', '2025-09-02 23:40:59'),
(943, 64, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:01', '2025-09-02 23:41:01'),
(944, 65, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:04', '2025-09-02 23:41:04'),
(945, 66, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:06', '2025-09-02 23:41:06'),
(946, 67, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:08', '2025-09-02 23:41:08'),
(947, 68, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:10', '2025-09-02 23:41:10'),
(948, 69, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:12', '2025-09-02 23:41:12'),
(949, 70, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:14', '2025-09-02 23:41:14'),
(950, 71, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:16', '2025-09-02 23:41:16'),
(951, 72, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:18', '2025-09-02 23:41:18'),
(952, 73, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:19', '2025-09-02 23:41:19'),
(953, 74, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:21', '2025-09-02 23:41:21'),
(954, 75, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:23', '2025-09-02 23:41:23'),
(955, 76, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:25', '2025-09-02 23:41:25'),
(956, 77, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:27', '2025-09-02 23:41:27'),
(957, 78, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:29', '2025-09-02 23:41:29'),
(958, 79, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:31', '2025-09-02 23:41:31'),
(959, 80, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:34', '2025-09-02 23:41:34'),
(960, 81, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:35', '2025-09-02 23:41:35'),
(961, 82, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:37', '2025-09-02 23:41:37'),
(962, 83, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:39', '2025-09-02 23:41:39'),
(963, 84, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:42', '2025-09-02 23:41:42'),
(964, 85, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:43', '2025-09-02 23:41:43'),
(965, 86, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:45', '2025-09-02 23:41:45'),
(966, 87, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:47', '2025-09-02 23:41:47'),
(967, 88, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:49', '2025-09-02 23:41:49'),
(968, 89, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:51', '2025-09-02 23:41:51'),
(969, 91, 278, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-09-02 23:41:53', '2025-09-02 23:41:53'),
(970, 48, 278, 21.23, NULL, '2025-09-02', 'transferencia', '677262364801', NULL, 'confirmado', '2025-09-03 00:07:34', '2025-09-03 00:07:34'),
(971, 38, 278, 21.23, NULL, '2025-09-02', 'transferencia', '52458534158', NULL, 'confirmado', '2025-09-03 00:08:12', '2025-09-03 00:08:12'),
(972, 33, 278, 21.23, NULL, '2025-09-02', 'transferencia', '52458126939', NULL, 'confirmado', '2025-09-03 00:14:39', '2025-09-03 00:14:39'),
(973, 40, 278, 21.23, NULL, '2025-09-02', 'transferencia', '672843789380', NULL, 'confirmado', '2025-09-03 00:15:12', '2025-09-03 00:15:12'),
(974, 56, 278, 21.23, NULL, '2025-09-02', 'transferencia', '573957', NULL, 'confirmado', '2025-09-03 00:16:33', '2025-09-03 00:16:33'),
(975, 58, 278, 21.23, NULL, '2025-09-02', 'transferencia', '52458383138', NULL, 'confirmado', '2025-09-03 00:16:53', '2025-09-03 00:16:53'),
(976, 69, 278, 21.23, NULL, '2025-09-02', 'transferencia', '52458136716', NULL, 'confirmado', '2025-09-03 00:17:13', '2025-09-03 00:17:13'),
(977, 78, 278, 21.23, NULL, '2025-09-02', 'transferencia', '590549959695', NULL, 'confirmado', '2025-09-03 00:17:46', '2025-09-03 00:17:46'),
(978, 81, 279, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-09-03 00:20:32', '2025-09-03 00:20:32'),
(979, 81, 278, 21.23, NULL, '2025-09-02', 'transferencia', '672843984809', NULL, 'confirmado', '2025-09-03 00:21:15', '2025-09-03 00:21:15'),
(980, 81, 279, 10.00, NULL, '2025-09-02', 'transferencia', '672843984809-2', NULL, 'confirmado', '2025-09-03 00:21:30', '2025-09-03 00:21:30'),
(981, 85, 278, 21.23, NULL, '2025-09-02', 'transferencia', '672843843391', NULL, 'confirmado', '2025-09-03 00:21:58', '2025-09-03 00:21:58'),
(982, 59, 279, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-09-03 00:54:43', '2025-09-03 00:54:43'),
(983, 32, 278, 21.23, NULL, '2025-09-22', 'transferencia', '523927', NULL, 'confirmado', '2025-09-30 18:32:52', '2025-09-30 18:32:52'),
(984, 35, 278, 21.23, NULL, '2025-09-15', 'transferencia', '13088', NULL, 'confirmado', '2025-09-30 18:41:33', '2025-09-30 18:41:33'),
(985, 41, 278, 21.23, NULL, '2025-09-23', 'transferencia', '1086566', NULL, 'confirmado', '2025-09-30 18:42:36', '2025-09-30 18:42:36'),
(986, 42, 278, 21.23, NULL, '2025-09-05', 'transferencia', '52487168697', NULL, 'confirmado', '2025-09-30 18:43:26', '2025-09-30 18:43:26'),
(987, 42, 255, 11.03, NULL, '2025-09-05', 'transferencia', '52487168697-2', NULL, 'confirmado', '2025-09-30 18:43:43', '2025-09-30 18:43:43'),
(988, 41, 265, 20.90, NULL, '2025-08-29', 'transferencia', '672840772583', NULL, 'confirmado', '2025-09-30 18:45:20', '2025-09-30 18:45:20'),
(989, 43, 278, 21.23, NULL, '2025-09-05', 'transferencia', '672846320223', NULL, 'confirmado', '2025-09-30 18:45:58', '2025-09-30 18:45:58'),
(990, 44, 278, 21.23, NULL, '2025-09-10', 'transferencia', '52530065140', NULL, 'confirmado', '2025-09-30 18:46:50', '2025-09-30 18:46:50'),
(991, 45, 278, 21.23, NULL, '2025-09-16', 'transferencia', '52598898689', NULL, 'confirmado', '2025-09-30 18:48:12', '2025-09-30 18:48:12'),
(992, 45, 263, 2.51, NULL, '2025-09-16', 'transferencia', '52598898689-2', NULL, 'confirmado', '2025-09-30 18:48:42', '2025-09-30 18:48:42'),
(993, 49, 278, 21.23, NULL, '2025-09-02', 'transferencia', '85763', NULL, 'confirmado', '2025-09-30 18:50:41', '2025-09-30 18:50:41'),
(994, 51, 278, 21.23, NULL, '2025-09-16', 'transferencia', '7963', NULL, 'confirmado', '2025-09-30 18:54:03', '2025-09-30 18:54:03'),
(995, 53, 278, 21.23, NULL, '2025-09-12', 'transferencia', '672851559493', NULL, 'confirmado', '2025-09-30 18:54:35', '2025-09-30 18:54:35'),
(996, 55, 278, 21.23, NULL, '2025-09-11', 'transferencia', '13198433381', NULL, 'confirmado', '2025-09-30 18:57:55', '2025-09-30 18:57:55'),
(997, 57, 278, 21.23, NULL, '2025-09-11', 'transferencia', '672850959557', NULL, 'confirmado', '2025-09-30 18:58:31', '2025-09-30 18:58:31'),
(998, 59, 279, 10.00, NULL, '2025-09-23', 'transferencia', '672861437409', NULL, 'confirmado', '2025-09-30 19:00:03', '2025-09-30 19:00:03'),
(999, 60, 278, 21.23, NULL, '2025-09-04', 'transferencia', '677265213018', NULL, 'confirmado', '2025-09-30 19:00:47', '2025-09-30 19:00:47'),
(1000, 61, 278, 21.23, NULL, '2025-09-12', 'transferencia', '590553609118', NULL, 'confirmado', '2025-09-30 19:01:37', '2025-09-30 19:01:37'),
(1001, 62, 278, 21.23, NULL, '2025-09-22', 'transferencia', '672860363149', NULL, 'confirmado', '2025-09-30 19:02:18', '2025-09-30 19:02:18'),
(1002, 63, 278, 21.23, NULL, '2025-09-23', 'transferencia', '898989', NULL, 'confirmado', '2025-09-30 19:03:19', '2025-09-30 19:03:19'),
(1003, 65, 278, 21.23, NULL, '2025-09-03', 'transferencia', '20438622432', NULL, 'confirmado', '2025-09-30 19:03:48', '2025-09-30 19:03:48'),
(1004, 66, 278, 21.23, NULL, '2025-09-11', 'transferencia', '98252', NULL, 'confirmado', '2025-09-30 19:58:27', '2025-09-30 19:58:27'),
(1005, 66, 265, 20.90, NULL, '2025-09-14', 'transferencia', '000378333131', NULL, 'confirmado', '2025-09-30 19:59:01', '2025-09-30 19:59:01'),
(1006, 67, 278, 21.23, NULL, '2025-09-03', 'transferencia', '52461676486', NULL, 'confirmado', '2025-09-30 19:59:37', '2025-09-30 19:59:37'),
(1007, 68, 278, 21.23, NULL, '2025-09-07', 'transferencia', '672847458506', NULL, 'confirmado', '2025-09-30 20:00:24', '2025-09-30 20:00:24'),
(1008, 70, 278, 21.23, NULL, '2025-09-04', 'transferencia', '52474849785', NULL, 'confirmado', '2025-09-30 20:01:37', '2025-09-30 20:01:37'),
(1009, 72, 278, 21.23, NULL, '2025-09-03', 'transferencia', '13193650462', NULL, 'confirmado', '2025-09-30 20:02:53', '2025-09-30 20:02:53'),
(1010, 73, 278, 21.23, NULL, '2025-09-24', 'transferencia', '13413481', NULL, 'confirmado', '2025-09-30 20:04:01', '2025-09-30 20:04:01'),
(1011, 70, 265, 20.90, NULL, '2025-09-04', 'transferencia', '052473581280', NULL, 'confirmado', '2025-09-30 20:05:25', '2025-09-30 20:05:25'),
(1012, 74, 278, 21.23, NULL, '2025-09-27', 'transferencia', '672864264510', NULL, 'confirmado', '2025-09-30 20:09:04', '2025-09-30 20:09:04'),
(1013, 75, 278, 21.23, NULL, '2025-09-04', 'transferencia', '20438811751', NULL, 'confirmado', '2025-09-30 20:09:50', '2025-09-30 20:09:50'),
(1014, 76, 278, 21.23, NULL, '2025-09-11', 'transferencia', '590553179684', NULL, 'confirmado', '2025-09-30 20:10:25', '2025-09-30 20:10:25'),
(1015, 78, 238, 1.73, NULL, '2025-09-02', 'transferencia', '590550421316', NULL, 'confirmado', '2025-09-30 20:11:43', '2025-09-30 20:11:43'),
(1017, 80, 278, 21.23, NULL, '2025-09-03', 'transferencia', '590550062392', NULL, 'confirmado', '2025-09-30 20:14:41', '2025-09-30 20:14:41'),
(1018, 82, 278, 21.23, NULL, '2025-09-14', 'transferencia', '9893', NULL, 'confirmado', '2025-09-30 20:15:38', '2025-09-30 20:15:38'),
(1019, 83, 278, 21.23, NULL, '2025-09-11', 'transferencia', '672850489390', NULL, 'confirmado', '2025-09-30 20:16:00', '2025-09-30 20:16:00'),
(1020, 84, 278, 21.23, NULL, '2025-09-05', 'transferencia', '3093', NULL, 'confirmado', '2025-09-30 20:16:21', '2025-09-30 20:16:21'),
(1021, 86, 278, 21.23, NULL, '2025-09-04', 'transferencia', '672845305859', NULL, 'confirmado', '2025-09-30 20:17:09', '2025-09-30 20:17:09'),
(1022, 87, 265, 20.90, NULL, '2025-09-23', 'transferencia', '13204642623', NULL, 'confirmado', '2025-09-30 20:17:52', '2025-09-30 20:17:52'),
(1023, 88, 278, 21.23, NULL, '2025-09-26', 'transferencia', '590559046963', NULL, 'confirmado', '2025-09-30 20:18:34', '2025-09-30 20:18:34'),
(1024, 37, 262, 18.98, NULL, '2025-09-27', 'transferencia', '20445508105', NULL, 'confirmado', '2025-09-30 20:23:53', '2025-09-30 20:23:53'),
(1025, 46, 278, 21.23, NULL, '2025-09-11', 'transferencia', '672850762337', NULL, 'confirmado', '2025-09-30 20:27:30', '2025-09-30 20:27:30'),
(1026, 46, 260, 12.55, NULL, '2025-09-11', 'transferencia', '672850762337-2', NULL, 'confirmado', '2025-09-30 20:28:22', '2025-09-30 20:28:22'),
(1027, 46, 261, 6.22, NULL, '2025-09-11', 'transferencia', '672850762337-3', NULL, 'confirmado', '2025-09-30 20:29:13', '2025-09-30 20:29:13'),
(1028, 50, 278, 21.23, NULL, '2025-09-16', 'transferencia', '6295', NULL, 'confirmado', '2025-09-30 20:30:39', '2025-09-30 20:30:39'),
(1029, 50, 238, 20.70, NULL, '2025-09-16', 'transferencia', '6295-2', NULL, 'confirmado', '2025-09-30 20:37:50', '2025-09-30 20:37:50'),
(1031, 50, 239, 8.07, NULL, '2025-09-16', 'transferencia', '6295-3', NULL, 'confirmado', '2025-09-30 20:42:07', '2025-09-30 20:42:07'),
(1032, 64, 278, 21.23, NULL, '2025-09-16', 'transferencia', '7755', NULL, 'confirmado', '2025-09-30 20:44:17', '2025-09-30 20:44:17'),
(1033, 64, 251, 0.92, NULL, '2025-09-16', 'transferencia', '7755-2', NULL, 'confirmado', '2025-09-30 20:47:01', '2025-09-30 20:47:01'),
(1034, 64, 252, 19.46, NULL, '2025-09-16', 'transferencia', '7755-3', NULL, 'confirmado', '2025-09-30 20:48:22', '2025-09-30 20:48:22'),
(1035, 64, 253, 8.60, NULL, '2025-09-16', 'transferencia', '7755-4', NULL, 'confirmado', '2025-09-30 20:49:06', '2025-09-30 20:49:06'),
(1037, 79, 263, 8.04, NULL, '2025-08-15', 'transferencia', '85036315', NULL, 'confirmado', '2025-09-30 20:56:12', '2025-09-30 20:56:12'),
(1038, 79, 265, 10.39, NULL, '2025-08-15', 'transferencia', '85036315-2', NULL, 'confirmado', '2025-09-30 20:57:31', '2025-09-30 20:57:31'),
(1039, 79, 265, 10.51, NULL, '2025-09-11', 'transferencia', '1455', NULL, 'confirmado', '2025-09-30 20:58:10', '2025-09-30 20:58:10'),
(1040, 79, 278, 5.34, NULL, '2025-09-11', 'transferencia', '1455-2', NULL, 'confirmado', '2025-09-30 20:58:45', '2025-09-30 20:58:45'),
(1161, 59, 278, 21.23, NULL, '2025-10-01', 'transferencia', '672868052084', NULL, 'confirmado', '2025-10-02 12:38:44', '2025-10-02 12:38:44'),
(1168, 32, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:34', '2025-10-02 13:34:34'),
(1169, 33, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:38', '2025-10-02 13:34:38'),
(1170, 34, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:41', '2025-10-02 13:34:41'),
(1171, 35, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:43', '2025-10-02 13:34:43'),
(1172, 37, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:45', '2025-10-02 13:34:45'),
(1173, 38, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:47', '2025-10-02 13:34:47'),
(1174, 39, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:49', '2025-10-02 13:34:49'),
(1175, 40, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:51', '2025-10-02 13:34:51'),
(1176, 41, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:53', '2025-10-02 13:34:53'),
(1177, 42, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:55', '2025-10-02 13:34:55'),
(1178, 43, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:56', '2025-10-02 13:34:56'),
(1179, 44, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:34:58', '2025-10-02 13:34:58'),
(1180, 45, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:00', '2025-10-02 13:35:00'),
(1181, 46, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:02', '2025-10-02 13:35:02'),
(1182, 47, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:04', '2025-10-02 13:35:04'),
(1183, 48, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:06', '2025-10-02 13:35:06'),
(1184, 49, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:08', '2025-10-02 13:35:08'),
(1185, 50, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:10', '2025-10-02 13:35:10'),
(1186, 51, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:12', '2025-10-02 13:35:12'),
(1187, 52, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:14', '2025-10-02 13:35:14'),
(1188, 53, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:17', '2025-10-02 13:35:17'),
(1189, 54, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:19', '2025-10-02 13:35:19'),
(1190, 55, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:21', '2025-10-02 13:35:21'),
(1191, 56, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:22', '2025-10-02 13:35:22'),
(1192, 57, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:24', '2025-10-02 13:35:24'),
(1193, 58, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:26', '2025-10-02 13:35:26'),
(1194, 59, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:28', '2025-10-02 13:35:28'),
(1195, 60, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:30', '2025-10-02 13:35:30'),
(1196, 61, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:32', '2025-10-02 13:35:32'),
(1197, 62, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:33', '2025-10-02 13:35:33'),
(1198, 63, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:35', '2025-10-02 13:35:35'),
(1199, 64, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:37', '2025-10-02 13:35:37'),
(1200, 65, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:39', '2025-10-02 13:35:39'),
(1201, 66, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:41', '2025-10-02 13:35:41'),
(1202, 67, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:43', '2025-10-02 13:35:43'),
(1203, 68, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:45', '2025-10-02 13:35:45'),
(1204, 69, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:47', '2025-10-02 13:35:47'),
(1205, 70, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:49', '2025-10-02 13:35:49'),
(1206, 71, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:51', '2025-10-02 13:35:51'),
(1207, 72, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:53', '2025-10-02 13:35:53'),
(1208, 73, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:56', '2025-10-02 13:35:56'),
(1209, 74, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:35:58', '2025-10-02 13:35:58'),
(1210, 75, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:00', '2025-10-02 13:36:00'),
(1211, 76, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:02', '2025-10-02 13:36:02'),
(1212, 77, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:04', '2025-10-02 13:36:04'),
(1213, 78, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:06', '2025-10-02 13:36:06'),
(1214, 79, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:08', '2025-10-02 13:36:08'),
(1215, 80, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:10', '2025-10-02 13:36:10'),
(1216, 81, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:12', '2025-10-02 13:36:12'),
(1217, 82, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:13', '2025-10-02 13:36:13'),
(1218, 83, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:15', '2025-10-02 13:36:15'),
(1219, 84, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:17', '2025-10-02 13:36:17'),
(1220, 85, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:19', '2025-10-02 13:36:19'),
(1221, 86, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:21', '2025-10-02 13:36:21'),
(1222, 87, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:23', '2025-10-02 13:36:23'),
(1223, 88, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:25', '2025-10-02 13:36:25'),
(1224, 89, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:27', '2025-10-02 13:36:27'),
(1225, 91, 288, 0.00, NULL, NULL, NULL, NULL, 'Recibo asignado automáticamente', 'pendiente_confirmacion', '2025-10-02 13:36:29', '2025-10-02 13:36:29'),
(1226, 33, 288, 23.16, NULL, '2025-10-02', 'transferencia', '52753426138', NULL, 'confirmado', '2025-10-02 14:14:50', '2025-10-02 14:14:50'),
(1228, 91, 278, 21.23, NULL, '2025-10-02', 'efectivo', '83412066L', NULL, 'confirmado', '2025-10-03 02:21:41', '2025-10-03 02:21:41'),
(1229, 38, 288, 23.16, NULL, '2025-10-02', 'transferencia', '52753594981', NULL, 'confirmado', '2025-10-03 02:24:03', '2025-10-03 02:24:03'),
(1230, 40, 288, 23.16, NULL, '2025-10-02', 'transferencia', '13613770', NULL, 'confirmado', '2025-10-03 02:24:59', '2025-10-03 02:24:59'),
(1231, 42, 288, 23.16, NULL, '2025-10-02', 'transferencia', '52754029748', NULL, 'confirmado', '2025-10-03 02:25:30', '2025-10-03 02:25:30'),
(1232, 49, 288, 23.16, NULL, '2025-10-02', 'transferencia', '93100', NULL, 'confirmado', '2025-10-03 02:27:06', '2025-10-03 02:27:06'),
(1233, 55, 288, 23.16, NULL, '2025-10-02', 'transferencia', '13209909489', NULL, 'confirmado', '2025-10-03 02:27:27', '2025-10-03 02:27:27'),
(1234, 56, 288, 23.16, NULL, '2025-10-02', 'transferencia', '9931937', NULL, 'confirmado', '2025-10-03 02:27:42', '2025-10-03 02:27:42'),
(1235, 57, 288, 23.16, NULL, '2025-10-02', 'transferencia', '672868974085', NULL, 'confirmado', '2025-10-03 02:28:02', '2025-10-03 02:28:02'),
(1236, 57, 289, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-10-03 02:29:51', '2025-10-03 02:29:51'),
(1237, 81, 289, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-10-03 02:30:14', '2025-10-03 02:30:14'),
(1238, 57, 289, 10.00, NULL, '2025-10-02', 'transferencia', '672868974085-2', NULL, 'confirmado', '2025-10-03 02:30:46', '2025-10-03 02:30:46'),
(1239, 58, 288, 23.16, NULL, '2025-10-02', 'transferencia', '52754361589', NULL, 'confirmado', '2025-10-03 02:31:03', '2025-10-03 02:31:03'),
(1240, 59, 288, 23.16, NULL, '2025-10-02', 'transferencia', '52753925483', NULL, 'confirmado', '2025-10-03 02:31:29', '2025-10-03 02:31:29'),
(1241, 59, 289, 0.00, NULL, NULL, 'pendiente', NULL, 'Asignación manual de recibo', 'pendiente_confirmacion', '2025-10-03 02:31:49', '2025-10-03 02:31:49'),
(1242, 69, 288, 23.16, NULL, '2025-10-02', 'transferencia', '52753456927', NULL, 'confirmado', '2025-10-03 02:32:17', '2025-10-03 02:32:17'),
(1243, 78, 288, 23.16, NULL, '2025-10-02', 'transferencia', '590561122452', NULL, 'confirmado', '2025-10-03 02:32:40', '2025-10-03 02:32:40'),
(1244, 81, 288, 23.16, NULL, '2025-10-02', 'transferencia', '590561173585', NULL, 'confirmado', '2025-10-03 02:32:57', '2025-10-03 02:32:57'),
(1245, 81, 289, 10.00, NULL, '2025-10-02', 'transferencia', '590561173585-2', NULL, 'confirmado', '2025-10-03 02:33:10', '2025-10-03 02:33:10'),
(1246, 85, 288, 23.16, NULL, '2025-10-02', 'transferencia', '672868819083', NULL, 'confirmado', '2025-10-03 02:33:30', '2025-10-03 02:33:30'),
(1247, 48, 288, 23.16, NULL, '2025-10-02', 'transferencia', '403029291', NULL, 'confirmado', '2025-10-03 03:19:43', '2025-10-03 03:19:43'),
(1248, 75, 288, 23.16, NULL, '2025-10-03', 'transferencia', '52766476153', NULL, 'confirmado', '2025-10-04 01:39:20', '2025-10-04 01:39:20'),
(1249, 65, 288, 23.16, NULL, '2025-10-03', 'transferencia', '52766517306', NULL, 'confirmado', '2025-10-04 01:40:06', '2025-10-04 01:40:06'),
(1252, 70, 288, 23.16, 4293.62, '2025-10-05', 'transferencia', '023294743946', NULL, 'confirmado', '2025-10-06 00:26:30', '2025-10-06 00:26:30'),
(1253, 60, 288, 10.00, 1853.90, '2025-10-05', 'transferencia', '023294752487', NULL, 'confirmado', '2025-10-06 00:27:18', '2025-10-06 00:27:18'),
(1254, 74, 288, 23.16, 4241.52, '2025-10-05', 'transferencia', '672871811723', NULL, 'confirmado', '2025-10-06 00:28:04', '2025-10-06 00:28:04'),
(1255, 66, 288, 23.16, 4241.52, '2025-10-05', 'pago_movil', '78920378', NULL, 'confirmado', '2025-10-06 00:28:44', '2025-10-06 00:28:44'),
(1256, 52, 288, 23.16, 4241.52, '2025-10-05', 'transferencia', '1315346858', NULL, 'confirmado', '2025-10-06 00:31:22', '2025-10-06 00:31:22'),
(1257, 52, 278, 21.23, 3888.06, '2025-10-05', 'transferencia', '1315346858-2', NULL, 'confirmado', '2025-10-06 00:32:59', '2025-10-06 00:32:59'),
(1258, 52, 265, 5.61, 1027.41, '2025-10-05', 'transferencia', '1315346858-3', NULL, 'confirmado', '2025-10-06 00:35:25', '2025-10-06 00:35:25'),
(1259, 84, 288, 23.16, 4241.29, '2025-10-06', 'transferencia', '52795157233', NULL, 'confirmado', '2025-10-07 01:24:15', '2025-10-07 01:24:15'),
(1260, 71, 278, 21.23, 3936.01, '2025-10-06', 'transferencia', '13211871628', NULL, 'confirmado', '2025-10-07 01:24:58', '2025-10-07 01:24:58'),
(1261, 71, 288, 23.16, 4293.82, '2025-10-06', 'transferencia', '13211874369', NULL, 'confirmado', '2025-10-07 01:25:55', '2025-10-07 01:25:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('admin@sc.com', '$2y$12$8hxXD.oQ2oJHDBPM7P7E/eoFWOpE2nx5wg7/GkbYvNQDwzD3FI0Du', '2025-08-19 02:53:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedors`
--

CREATE TABLE `proveedors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `rif` varchar(255) NOT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `estatus` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedors`
--

INSERT INTO `proveedors` (`id`, `nombre`, `rif`, `telefono`, `email`, `direccion`, `contacto`, `estatus`, `created_at`, `updated_at`) VALUES
(1, 'Maria Marin', '20009869', '04125555555', 'mariamarin@dddd.com', NULL, NULL, 'activo', '2025-10-06 02:21:54', '2025-10-06 02:21:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibo_gasto_comuns`
--

CREATE TABLE `recibo_gasto_comuns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `numero_recibo` varchar(255) NOT NULL,
  `periodo` varchar(255) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `valor_administracion` decimal(10,2) NOT NULL,
  `valor_aseo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_vigilancia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_mantenimiento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `otros_conceptos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_recibo` decimal(10,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `estado` enum('activo','vencido','anulado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recibo_gasto_comuns`
--

INSERT INTO `recibo_gasto_comuns` (`id`, `numero_recibo`, `periodo`, `fecha_emision`, `fecha_vencimiento`, `valor_administracion`, `valor_aseo`, `valor_vigilancia`, `valor_mantenimiento`, `otros_conceptos`, `total_recibo`, `observaciones`, `archivo_adjunto`, `estado`, `created_at`, `updated_at`) VALUES
(234, 'REC-0922', '2022-09', '2022-10-10', '2022-10-10', 0.00, 0.00, 0.00, 0.00, 46.78, 46.78, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(235, 'REC-1022', '2022-10', '2022-11-02', '2022-11-02', 0.00, 0.00, 0.00, 0.00, 96.00, 96.00, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(236, 'REC-0423', '2023-04', '2023-05-01', '2023-05-01', 0.00, 0.00, 0.00, 0.00, 40.92, 40.92, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(237, 'REC-0523', '2023-05', '2023-06-01', '2023-06-01', 0.00, 0.00, 0.00, 0.00, 37.04, 37.04, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(238, 'REC-0623', '2023-06', '2023-07-01', '2023-07-01', 0.00, 0.00, 0.00, 0.00, 45.13, 45.13, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(239, 'REC-0723', '2023-07', '2023-08-01', '2023-08-01', 0.00, 0.00, 0.00, 0.00, 37.43, 37.43, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(240, 'REC-0823', '2023-08', '2023-09-01', '2023-09-01', 0.00, 0.00, 0.00, 0.00, 11.22, 11.22, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(241, 'REC-0923', '2023-09', '2023-10-01', '2023-10-01', 0.00, 0.00, 0.00, 0.00, 13.03, 13.03, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(242, 'REC-1023', '2023-10', '2023-11-01', '2023-11-01', 0.00, 0.00, 0.00, 0.00, 14.02, 14.02, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(243, 'REC-1023Abg', '2023-10', '2023-10-01', '2023-10-01', 0.00, 0.00, 0.00, 0.00, 96.00, 96.00, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(244, 'REC-1123', '2023-11', '2023-12-01', '2023-12-01', 0.00, 0.00, 0.00, 0.00, 16.20, 16.20, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(245, 'REC-1223', '2023-12', '2023-12-30', '2023-12-30', 0.00, 0.00, 0.00, 0.00, 16.12, 16.12, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(246, 'REC-0124', '2024-01', '2024-01-31', '2024-01-31', 0.00, 0.00, 0.00, 0.00, 16.65, 16.65, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(247, 'REC-0224', '2024-02', '2024-02-29', '2024-02-29', 0.00, 0.00, 0.00, 0.00, 14.15, 14.15, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(248, 'REC-0324', '2024-03', '2024-04-03', '2024-04-03', 0.00, 0.00, 0.00, 0.00, 12.20, 12.20, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(249, 'REC-0424', '2024-04', '2024-05-07', '2024-05-07', 0.00, 0.00, 0.00, 0.00, 13.18, 13.18, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(250, 'REC-0524', '2024-05', '2024-06-10', '2024-06-10', 0.00, 0.00, 0.00, 0.00, 18.61, 18.61, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(251, 'REC-0624', '2024-06', '2024-07-09', '2024-07-09', 0.00, 0.00, 0.00, 0.00, 18.87, 18.87, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(252, 'REC-0724', '2024-07', '2024-08-09', '2024-08-09', 0.00, 0.00, 0.00, 0.00, 19.46, 19.46, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(253, 'REC-0824', '2024-08', '2024-09-05', '2024-09-05', 0.00, 0.00, 0.00, 0.00, 13.20, 13.20, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(254, 'REC-0924', '2024-09', '2024-09-30', '2024-09-30', 0.00, 0.00, 0.00, 0.00, 38.51, 38.51, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(255, 'REC-1024', '2024-10', '2024-11-01', '2024-11-01', 0.00, 0.00, 0.00, 0.00, 24.82, 24.82, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(256, 'REC-1124', '2024-11', '2024-12-10', '2024-12-10', 0.00, 0.00, 0.00, 0.00, 25.65, 25.65, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(257, 'REC-1224', '2024-12', '2025-01-03', '2025-01-03', 0.00, 0.00, 0.00, 0.00, 16.06, 16.06, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(258, 'REC-0125', '2025-01', '2025-02-14', '2025-02-14', 0.00, 0.00, 0.00, 0.00, 30.10, 30.10, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(259, 'REC-0225', '2025-02', '2025-03-12', '2025-03-12', 0.00, 0.00, 0.00, 0.00, 26.88, 26.88, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(260, 'REC-0325', '2025-03', '2025-04-07', '2025-04-07', 0.00, 0.00, 0.00, 0.00, 27.47, 27.47, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(261, 'REC-0425', '2025-04', '2025-04-30', '2025-04-30', 0.00, 0.00, 0.00, 0.00, 38.36, 38.36, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(262, 'REC-0525', '2025-05', '2025-06-04', '2025-06-04', 0.00, 0.00, 0.00, 0.00, 18.98, 18.98, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(263, 'REC-0625', '2025-06', '2025-07-08', '2025-07-08', 0.00, 0.00, 0.00, 0.00, 24.64, 24.64, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(264, 'REC-0625-E', '2025-06', '2025-07-01', '2025-07-01', 0.00, 0.00, 0.00, 0.00, 10.00, 10.00, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(265, 'REC-0725', '2025-07', '2025-08-08', '2025-08-08', 0.00, 0.00, 0.00, 0.00, 20.90, 20.90, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(266, 'REC-0725-E', '2025-07', '2025-08-01', '2025-08-01', 0.00, 0.00, 0.00, 0.00, 10.00, 10.00, NULL, NULL, 'vencido', '2025-08-30 02:43:00', '2025-08-30 02:43:00'),
(267, 'REC-0725-INUN', '2025-07', '2025-10-08', '2025-11-08', 0.00, 0.00, 0.00, 0.00, 115.00, 115.00, NULL, NULL, 'vencido', '2025-09-01 17:04:33', '2025-09-01 17:04:33'),
(278, 'REC-0825', '2025-08', '2025-08-31', '2025-09-30', 0.00, 0.00, 0.00, 0.00, 21.23, 21.23, NULL, 'recibos/1756856397_REC-0825.pdf', 'vencido', '2025-09-02 23:39:57', '2025-10-02 13:38:31'),
(279, 'REC-0825-E', '2025-08', '2025-08-31', '2025-09-30', 0.00, 0.00, 0.00, 0.00, 10.00, 10.00, NULL, NULL, 'vencido', '2025-09-03 00:20:10', '2025-09-03 00:20:10'),
(288, 'REC-0925', '2025-09', '2025-10-01', '2025-10-31', 0.00, 0.00, 0.00, 0.00, 23.16, 23.16, NULL, 'recibos/1759412074_REC-0925.pdf', 'activo', '2025-10-02 13:34:34', '2025-10-02 13:34:34'),
(289, 'REC-0925-E', '2025-09', '2025-10-02', '2025-10-31', 0.00, 0.00, 0.00, 0.00, 10.00, 10.00, NULL, NULL, 'vencido', '2025-10-03 02:29:06', '2025-10-03 02:29:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservations`
--

CREATE TABLE `reservations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `apartamento_id` bigint(20) UNSIGNED NOT NULL,
  `space_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_reserva` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `recibo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('4xl3hUdtm6s9J4441SDGYSX8JJmQyAUVkGMwTxlj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiME1iZVVZNUtPQk9SeENkSnk2QzZnQW1yZ1J0bUZGSGpXZ01PT0E5QSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NjoiaHR0cDovL2xvY2FsaG9zdC9zbWFydC1jb211bml0eS9nZXN0aW9uL3B1YmxpYyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1759798764),
('FGgBazNFMT2X1HBKadMkIRV8pOeUYhaSxcZdc0SL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNVJxZ1ZvdE9kdHRTMzBwZ3YwaGtwbmlUQkxFSjdoaHNOeGwyWlJ5ZyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2MDoiaHR0cDovL2xvY2FsaG9zdC9zbWFydC1jb211bml0eS9nZXN0aW9uL3B1YmxpYy9kYXNoYm9hcmQvcGRmIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly9sb2NhbGhvc3Qvc21hcnQtY29tdW5pdHkvZ2VzdGlvbi9wdWJsaWMvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1759799105),
('jIgaWhyc1hlxg7gRpOQy52BWzPInJKIxpN2kbkHx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiaW12d1dMNFVyNVhLSjV0RlJYdlpNZXNoYVR1Smk1WjlteVE3cXB0bSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1759798752),
('pYpmtxLkkRTNSUnmtVHF40QlfbZxCFkqh5wBXL59', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRDhmd0ZWMHdQREw3RkxTb2hvUTVpTkxaOFFmVEdBS0lkUDdNc1ZVWSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1NjoiaHR0cDovL2xvY2FsaG9zdC9zbWFydC1jb211bml0eS9nZXN0aW9uL3B1YmxpYy9kYXNoYm9hcmQiO319', 1759811355),
('SRCOeLkLbkF1qpYDUixHOrYGTH9ykpmKnVrBIkFq', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYWp5aFJIZFlMenZiWDRCemt5eEx3c1VKblBUN3hMa0RNQnhJV3pCZCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo3MToiaHR0cDovL2xvY2FsaG9zdC9zbWFydC1jb211bml0eS9nZXN0aW9uL3B1YmxpYy9jb25jaWxpYWNpb24vcmVjYXVkYWNpb24iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1759800551),
('utMfvnxUuacRBgCBA2LN12uMZb9xbtlkdTMg7SSI', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Trae/1.100.3 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZG9XSElHNG9oUUNKODBUYUg1bUphaU5tekVFVG1HQkJZR2FtYW1oUiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjk2OiJodHRwOi8vbG9jYWxob3N0L3NtYXJ0LWNvbXVuaXR5L2dlc3Rpb24vcHVibGljL2ludmVudGFyaW8/aWRlX3dlYnZpZXdfcmVxdWVzdF90aW1lPTE3NTk3OTkzNTA3OTQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1759799352);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `spaces`
--

CREATE TABLE `spaces` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_por_dia` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `spaces`
--

INSERT INTO `spaces` (`id`, `nombre`, `descripcion`, `precio_por_dia`, `activo`, `created_at`, `updated_at`) VALUES
(6, 'Salón de fiesta', '24 hrs de alquiler', 10.00, 1, '2025-10-05 05:07:13', '2025-10-05 05:07:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `status`) VALUES
(1, 'Admin', 'admin@sc.com', NULL, '$2y$12$U8hdPAZj2/p.zeUUvaVO.uPaHHowCP8T8TLQLV.nll0jg1zP/npvC', 'ZpIs6nYji57FsDgAXyUDczwF5PlGTrXxpJlpTz2hneFB3nWB8fIZVBB7IDWe', NULL, NULL, 'admin', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actas`
--
ALTER TABLE `actas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `actas_nro_acta_unique` (`nro_doc`);

--
-- Indices de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apartamentos_numero_unique` (`numero`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `egresos_new`
--
ALTER TABLE `egresos_new`
  ADD PRIMARY KEY (`id`),
  ADD KEY `egresos_temp_1759253507_proveedor_id_foreign` (`proveedor_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `inquilinos`
--
ALTER TABLE `inquilinos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inquilinos_nro_apartamento_unique` (`nro_apartamento`);

--
-- Indices de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pagos_apartamento_id_fecha_pago_index` (`apartamento_id`,`fecha_pago`),
  ADD KEY `pagos_recibo_gasto_comun_id_estado_index` (`recibo_gasto_comun_id`,`estado`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `proveedors`
--
ALTER TABLE `proveedors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `proveedors_rif_unique` (`rif`);

--
-- Indices de la tabla `recibo_gasto_comuns`
--
ALTER TABLE `recibo_gasto_comuns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recibo_gasto_comuns_numero_recibo_unique` (`numero_recibo`);

--
-- Indices de la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservations_space_id_fecha_reserva_unique` (`space_id`,`fecha_reserva`),
  ADD KEY `reservations_apartamento_id_foreign` (`apartamento_id`),
  ADD KEY `reservations_recibo_id_foreign` (`recibo_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `spaces`
--
ALTER TABLE `spaces`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actas`
--
ALTER TABLE `actas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `apartamentos`
--
ALTER TABLE `apartamentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de la tabla `egresos_new`
--
ALTER TABLE `egresos_new`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inquilinos`
--
ALTER TABLE `inquilinos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1262;

--
-- AUTO_INCREMENT de la tabla `proveedors`
--
ALTER TABLE `proveedors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `recibo_gasto_comuns`
--
ALTER TABLE `recibo_gasto_comuns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

--
-- AUTO_INCREMENT de la tabla `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `spaces`
--
ALTER TABLE `spaces`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `egresos_new`
--
ALTER TABLE `egresos_new`
  ADD CONSTRAINT `egresos_temp_1759253507_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedors` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_apartamento_id_foreign` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_recibo_gasto_comun_id_foreign` FOREIGN KEY (`recibo_gasto_comun_id`) REFERENCES `recibo_gasto_comuns` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_apartamento_id_foreign` FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_recibo_id_foreign` FOREIGN KEY (`recibo_id`) REFERENCES `recibo_gasto_comuns` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_space_id_foreign` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
