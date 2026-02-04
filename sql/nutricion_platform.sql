-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-02-2026 a las 21:28:11
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
-- Base de datos: `nutricion_platform`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assistant_conversations`
--

CREATE TABLE `assistant_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `assistant_conversations`
--

INSERT INTO `assistant_conversations` (`id`, `user_id`, `message`, `response`, `context`, `created_at`) VALUES
(1, 1, '¿Cuántas calorías he consumido hoy?', 'Has consumido 0 kcal (0% de tu objetivo). Te quedan 2,348 kcal para hoy. ¡Vas muy bien! 💪', '{\"calories_today\":\"0\",\"target_calories\":2348}', '2026-02-03 19:37:51'),
(2, 1, '¿Qué puedo comer?', 'Para ganar peso saludablemente: smoothie de proteína con avena, sándwich de pavo con aguacate, o pasta con carne magra. ¿Te animas? 💪', '{\"calories_today\":\"0\",\"target_calories\":2348}', '2026-02-03 19:37:55'),
(3, 1, 'pavo con aguacate', 'Puedo ayudarte con:\n\n📊 Revisar tus calorías del día\n🍽️ Sugerir comidas saludables\n💪 Consejos de nutrición\n📅 Información sobre tu plan\n\n¿Qué te gustaría saber?', '{\"calories_today\":\"0\",\"target_calories\":2348}', '2026-02-03 19:38:03'),
(4, 1, 'Dame motivación', '¡Tú puedes! 💪 Cada día es una oportunidad para mejorar. Recuerda que los pequeños cambios consistentes generan grandes resultados. Ya has dado el primer paso al usar esta app. ¡Sigue adelante! 🌟', '{\"calories_today\":\"0\",\"target_calories\":2348}', '2026-02-03 19:38:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assistant_settings`
--

CREATE TABLE `assistant_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `personality` varchar(50) DEFAULT 'friendly',
  `language` varchar(10) DEFAULT 'es',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calories_log`
--

CREATE TABLE `calories_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_plan_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `calories` int(11) NOT NULL,
  `protein` decimal(5,2) DEFAULT NULL,
  `carbs` decimal(5,2) DEFAULT NULL,
  `fats` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calories_log`
--

INSERT INTO `calories_log` (`id`, `user_id`, `meal_plan_id`, `date`, `calories`, `protein`, `carbs`, `fats`, `notes`, `created_at`) VALUES
(1, 2, NULL, '2026-02-01', 160, 2.00, 8.50, 14.70, '1 pieza de Aguacate', '2026-02-01 17:39:16'),
(2, 2, NULL, '2026-02-01', 23, 3.30, 3.45, 0.30, '1.5 tazas de Champiñones', '2026-02-01 17:39:28'),
(3, 2, NULL, '2026-02-01', 132, 28.00, 0.00, 1.30, '1 g de Atún', '2026-02-01 17:39:32'),
(4, 2, NULL, '2026-02-01', 205, 4.20, 44.50, 0.40, '1 taza cocido de Arroz Blanco', '2026-02-01 17:39:36'),
(5, 2, NULL, '2026-02-01', 105, 1.30, 27.00, 0.40, '1 mediano de Plátano', '2026-02-01 17:39:47'),
(6, 1, NULL, '2026-02-01', 160, 2.00, 8.50, 14.70, '1 pieza de Aguacate', '2026-02-01 17:50:14'),
(7, 1, NULL, '2026-02-01', 550, 37.00, 112.00, 6.00, '10 tazas de Brócoli', '2026-02-01 20:38:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `response` text DEFAULT NULL,
  `context_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_data`)),
  `ai_model` varchar(50) DEFAULT 'huggingface',
  `response_time_ms` int(11) DEFAULT NULL,
  `user_rating` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_user` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foods`
--

CREATE TABLE `foods` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `category` enum('fruits','vegetables','proteins','grains','dairy','snacks','beverages','other') NOT NULL,
  `serving_size` varchar(50) DEFAULT NULL,
  `serving_unit` varchar(20) DEFAULT NULL,
  `calories` int(11) NOT NULL,
  `protein` decimal(5,2) DEFAULT 0.00,
  `carbs` decimal(5,2) DEFAULT 0.00,
  `fats` decimal(5,2) DEFAULT 0.00,
  `fiber` decimal(5,2) DEFAULT 0.00,
  `sugar` decimal(5,2) DEFAULT 0.00,
  `sodium` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `foods`
--

INSERT INTO `foods` (`id`, `name`, `name_en`, `category`, `serving_size`, `serving_unit`, `calories`, `protein`, `carbs`, `fats`, `fiber`, `sugar`, `sodium`, `is_verified`, `created_at`) VALUES
(1, 'Plátano', 'Banana', 'fruits', '1', 'mediano', 105, 1.30, 27.00, 0.40, 3.10, 0.00, 0, 1, '2026-01-30 22:49:05'),
(2, 'Manzana', 'Apple', 'fruits', '1', 'mediana', 95, 0.50, 25.00, 0.30, 4.40, 0.00, 0, 1, '2026-01-30 22:49:05'),
(3, 'Naranja', 'Orange', 'fruits', '1', 'mediana', 62, 1.20, 15.40, 0.20, 3.10, 0.00, 0, 1, '2026-01-30 22:49:05'),
(4, 'Fresa', 'Strawberry', 'fruits', '100', 'g', 32, 0.70, 7.70, 0.30, 2.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(5, 'Sandía', 'Watermelon', 'fruits', '100', 'g', 30, 0.60, 7.60, 0.20, 0.40, 0.00, 0, 1, '2026-01-30 22:49:05'),
(6, 'Mango', 'Mango', 'fruits', '1', 'taza', 99, 1.40, 24.70, 0.60, 2.60, 0.00, 0, 1, '2026-01-30 22:49:05'),
(7, 'Papaya', 'Papaya', 'fruits', '1', 'taza', 62, 0.70, 15.70, 0.40, 2.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(8, 'Piña', 'Pineapple', 'fruits', '1', 'taza', 82, 0.90, 21.60, 0.20, 2.30, 0.00, 0, 1, '2026-01-30 22:49:05'),
(9, 'Aguacate', 'Avocado', 'fruits', '1/2', 'pieza', 160, 2.00, 8.50, 14.70, 6.70, 0.00, 0, 1, '2026-01-30 22:49:05'),
(10, 'Brócoli', 'Broccoli', 'vegetables', '1', 'taza', 55, 3.70, 11.20, 0.60, 2.40, 0.00, 0, 1, '2026-01-30 22:49:05'),
(11, 'Zanahoria', 'Carrot', 'vegetables', '1', 'mediana', 25, 0.60, 6.00, 0.10, 1.70, 0.00, 0, 1, '2026-01-30 22:49:05'),
(12, 'Espinaca', 'Spinach', 'vegetables', '1', 'taza', 7, 0.90, 1.10, 0.10, 0.70, 0.00, 0, 1, '2026-01-30 22:49:05'),
(13, 'Tomate', 'Tomato', 'vegetables', '1', 'mediano', 22, 1.10, 4.80, 0.20, 1.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(14, 'Lechuga', 'Lettuce', 'vegetables', '1', 'taza', 5, 0.50, 1.00, 0.10, 0.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(15, 'Calabaza', 'Zucchini', 'vegetables', '1', 'taza', 20, 1.50, 3.90, 0.40, 1.20, 0.00, 0, 1, '2026-01-30 22:49:05'),
(16, 'Pepino', 'Cucumber', 'vegetables', '1/2', 'taza', 8, 0.30, 1.90, 0.10, 0.30, 0.00, 0, 1, '2026-01-30 22:49:05'),
(17, 'Cebolla', 'Onion', 'vegetables', '1', 'mediana', 44, 1.20, 10.30, 0.10, 1.90, 0.00, 0, 1, '2026-01-30 22:49:05'),
(18, 'Chile', 'Pepper', 'vegetables', '1', 'pieza', 30, 1.00, 7.00, 0.20, 1.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(19, 'Nopales', 'Cactus', 'vegetables', '1', 'taza', 14, 1.10, 2.90, 0.10, 1.90, 0.00, 0, 1, '2026-01-30 22:49:05'),
(20, 'Pechuga de Pollo', 'Chicken Breast', 'proteins', '100', 'g', 165, 31.00, 0.00, 3.60, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(21, 'Carne de Res', 'Beef', 'proteins', '100', 'g', 250, 26.00, 0.00, 15.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(22, 'Salmón', 'Salmon', 'proteins', '100', 'g', 208, 20.00, 0.00, 13.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(23, 'Atún', 'Tuna', 'proteins', '100', 'g', 132, 28.00, 0.00, 1.30, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(24, 'Huevo', 'Egg', 'proteins', '1', 'grande', 78, 6.30, 0.60, 5.30, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(25, 'Frijoles', 'Beans', 'proteins', '1', 'taza', 227, 15.20, 40.40, 0.90, 15.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(26, 'Lentejas', 'Lentils', 'proteins', '1', 'taza', 230, 17.90, 39.90, 0.80, 15.60, 0.00, 0, 1, '2026-01-30 22:49:05'),
(27, 'Tofu', 'Tofu', 'proteins', '100', 'g', 76, 8.00, 1.90, 4.80, 1.20, 0.00, 0, 1, '2026-01-30 22:49:05'),
(28, 'Camarones', 'Shrimp', 'proteins', '100', 'g', 99, 24.00, 0.20, 0.30, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(29, 'Arroz Blanco', 'White Rice', 'grains', '1', 'taza cocido', 205, 4.20, 44.50, 0.40, 0.60, 0.00, 0, 1, '2026-01-30 22:49:05'),
(30, 'Arroz Integral', 'Brown Rice', 'grains', '1', 'taza cocido', 216, 5.00, 44.80, 1.80, 3.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(31, 'Pasta', 'Pasta', 'grains', '1', 'taza cocida', 221, 8.10, 43.20, 1.30, 2.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(32, 'Pan Integral', 'Whole Wheat Bread', 'grains', '1', 'rebanada', 81, 4.00, 13.80, 1.10, 1.90, 0.00, 0, 1, '2026-01-30 22:49:05'),
(33, 'Tortilla de Maíz', 'Corn Tortilla', 'grains', '1', 'pieza', 52, 1.40, 10.70, 0.70, 1.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(34, 'Avena', 'Oatmeal', 'grains', '1', 'taza cocida', 166, 5.90, 28.10, 3.60, 4.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(35, 'Quinoa', 'Quinoa', 'grains', '1', 'taza cocida', 222, 8.10, 39.40, 3.60, 5.20, 0.00, 0, 1, '2026-01-30 22:49:05'),
(36, 'Pan Blanco', 'White Bread', 'grains', '1', 'rebanada', 79, 2.70, 14.70, 1.00, 0.80, 0.00, 0, 1, '2026-01-30 22:49:05'),
(37, 'Leche Entera', 'Whole Milk', 'dairy', '1', 'taza', 149, 7.70, 11.70, 7.90, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(38, 'Leche Descremada', 'Skim Milk', 'dairy', '1', 'taza', 83, 8.30, 12.20, 0.20, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(39, 'Yogurt Natural', 'Plain Yogurt', 'dairy', '1', 'taza', 149, 8.50, 11.40, 8.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(40, 'Yogurt Griego', 'Greek Yogurt', 'dairy', '1', 'taza', 100, 17.00, 6.00, 0.40, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(41, 'Queso Panela', 'Panela Cheese', 'dairy', '100', 'g', 321, 25.00, 3.00, 23.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(42, 'Queso Oaxaca', 'Oaxaca Cheese', 'dairy', '100', 'g', 300, 22.00, 2.00, 23.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(43, 'Almendras', 'Almonds', 'snacks', '28', 'g (23 piezas)', 164, 6.00, 6.10, 14.20, 3.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(44, 'Nueces', 'Walnuts', 'snacks', '28', 'g', 185, 4.30, 3.90, 18.50, 1.90, 0.00, 0, 1, '2026-01-30 22:49:05'),
(45, 'Cacahuates', 'Peanuts', 'snacks', '28', 'g', 161, 7.30, 4.60, 14.00, 2.40, 0.00, 0, 1, '2026-01-30 22:49:05'),
(46, 'Granola', 'Granola', 'snacks', '1/2', 'taza', 260, 7.00, 40.00, 9.00, 5.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(47, 'Agua', 'Water', 'beverages', '1', 'vaso', 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(48, 'Café Negro', 'Black Coffee', 'beverages', '1', 'taza', 2, 0.30, 0.00, 0.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(49, 'Té Verde', 'Green Tea', 'beverages', '1', 'taza', 2, 0.50, 0.00, 0.00, 0.00, 0.00, 0, 1, '2026-01-30 22:49:05'),
(50, 'Jugo de Naranja', 'Orange Juice', 'beverages', '1', 'taza', 112, 1.70, 25.80, 0.50, 0.50, 0.00, 0, 1, '2026-01-30 22:49:05'),
(51, 'Plátano', 'Banana', 'fruits', '1', 'mediano', 105, 1.30, 27.00, 0.40, 3.10, 0.00, 0, 1, '2026-02-01 00:56:15'),
(52, 'Manzana', 'Apple', 'fruits', '1', 'mediana', 95, 0.50, 25.00, 0.30, 4.40, 0.00, 0, 1, '2026-02-01 00:56:15'),
(53, 'Naranja', 'Orange', 'fruits', '1', 'mediana', 62, 1.20, 15.40, 0.20, 3.10, 0.00, 0, 1, '2026-02-01 00:56:15'),
(54, 'Fresa', 'Strawberry', 'fruits', '100', 'g', 32, 0.70, 7.70, 0.30, 2.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(55, 'Sandía', 'Watermelon', 'fruits', '100', 'g', 30, 0.60, 7.60, 0.20, 0.40, 0.00, 0, 1, '2026-02-01 00:56:15'),
(56, 'Mango', 'Mango', 'fruits', '1', 'taza', 99, 1.40, 24.70, 0.60, 2.60, 0.00, 0, 1, '2026-02-01 00:56:15'),
(57, 'Papaya', 'Papaya', 'fruits', '1', 'taza', 62, 0.70, 15.70, 0.40, 2.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(58, 'Piña', 'Pineapple', 'fruits', '1', 'taza', 82, 0.90, 21.60, 0.20, 2.30, 0.00, 0, 1, '2026-02-01 00:56:15'),
(59, 'Aguacate', 'Avocado', 'fruits', '1/2', 'pieza', 160, 2.00, 8.50, 14.70, 6.70, 0.00, 0, 1, '2026-02-01 00:56:15'),
(60, 'Pera', 'Pear', 'fruits', '1', 'mediana', 101, 0.60, 27.10, 0.30, 5.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(61, 'Uvas', 'Grapes', 'fruits', '1', 'taza', 104, 1.10, 27.30, 0.20, 1.40, 0.00, 0, 1, '2026-02-01 00:56:15'),
(62, 'Kiwi', 'Kiwi', 'fruits', '1', 'mediano', 42, 0.80, 10.10, 0.40, 2.10, 0.00, 0, 1, '2026-02-01 00:56:15'),
(63, 'Durazno', 'Peach', 'fruits', '1', 'mediano', 58, 1.40, 14.30, 0.40, 2.30, 0.00, 0, 1, '2026-02-01 00:56:15'),
(64, 'Brócoli', 'Broccoli', 'vegetables', '1', 'taza', 55, 3.70, 11.20, 0.60, 2.40, 0.00, 0, 1, '2026-02-01 00:56:15'),
(65, 'Zanahoria', 'Carrot', 'vegetables', '1', 'mediana', 25, 0.60, 6.00, 0.10, 1.70, 0.00, 0, 1, '2026-02-01 00:56:15'),
(66, 'Espinaca', 'Spinach', 'vegetables', '1', 'taza', 7, 0.90, 1.10, 0.10, 0.70, 0.00, 0, 1, '2026-02-01 00:56:15'),
(67, 'Tomate', 'Tomato', 'vegetables', '1', 'mediano', 22, 1.10, 4.80, 0.20, 1.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(68, 'Lechuga', 'Lettuce', 'vegetables', '1', 'taza', 5, 0.50, 1.00, 0.10, 0.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(69, 'Calabaza', 'Zucchini', 'vegetables', '1', 'taza', 20, 1.50, 3.90, 0.40, 1.20, 0.00, 0, 1, '2026-02-01 00:56:15'),
(70, 'Pepino', 'Cucumber', 'vegetables', '1/2', 'taza', 8, 0.30, 1.90, 0.10, 0.30, 0.00, 0, 1, '2026-02-01 00:56:15'),
(71, 'Cebolla', 'Onion', 'vegetables', '1', 'mediana', 44, 1.20, 10.30, 0.10, 1.90, 0.00, 0, 1, '2026-02-01 00:56:15'),
(72, 'Chile', 'Pepper', 'vegetables', '1', 'pieza', 30, 1.00, 7.00, 0.20, 1.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(73, 'Nopales', 'Cactus', 'vegetables', '1', 'taza', 14, 1.10, 2.90, 0.10, 1.90, 0.00, 0, 1, '2026-02-01 00:56:15'),
(74, 'Coliflor', 'Cauliflower', 'vegetables', '1', 'taza', 25, 2.00, 5.30, 0.10, 2.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(75, 'Pimiento', 'Bell Pepper', 'vegetables', '1', 'mediano', 24, 0.90, 5.80, 0.20, 1.70, 0.00, 0, 1, '2026-02-01 00:56:15'),
(76, 'Ejotes', 'Green Beans', 'vegetables', '1', 'taza', 44, 2.40, 9.90, 0.40, 4.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(77, 'Champiñones', 'Mushrooms', 'vegetables', '1', 'taza', 15, 2.20, 2.30, 0.20, 0.70, 0.00, 0, 1, '2026-02-01 00:56:15'),
(78, 'Pechuga de Pollo', 'Chicken Breast', 'proteins', '100', 'g', 165, 31.00, 0.00, 3.60, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(79, 'Carne de Res', 'Beef', 'proteins', '100', 'g', 250, 26.00, 0.00, 15.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(80, 'Salmón', 'Salmon', 'proteins', '100', 'g', 208, 20.00, 0.00, 13.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(81, 'Atún', 'Tuna', 'proteins', '100', 'g', 132, 28.00, 0.00, 1.30, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(82, 'Huevo', 'Egg', 'proteins', '1', 'grande', 78, 6.30, 0.60, 5.30, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(83, 'Frijoles', 'Beans', 'proteins', '1', 'taza', 227, 15.20, 40.40, 0.90, 15.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(84, 'Lentejas', 'Lentils', 'proteins', '1', 'taza', 230, 17.90, 39.90, 0.80, 15.60, 0.00, 0, 1, '2026-02-01 00:56:15'),
(85, 'Tofu', 'Tofu', 'proteins', '100', 'g', 76, 8.00, 1.90, 4.80, 1.20, 0.00, 0, 1, '2026-02-01 00:56:15'),
(86, 'Camarones', 'Shrimp', 'proteins', '100', 'g', 99, 24.00, 0.20, 0.30, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(87, 'Pavo', 'Turkey', 'proteins', '100', 'g', 135, 30.00, 0.00, 0.70, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(88, 'Cerdo', 'Pork', 'proteins', '100', 'g', 242, 27.00, 0.00, 14.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(89, 'Tilapia', 'Tilapia', 'proteins', '100', 'g', 96, 20.00, 0.00, 1.70, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(90, 'Arroz Blanco', 'White Rice', 'grains', '1', 'taza cocido', 205, 4.20, 44.50, 0.40, 0.60, 0.00, 0, 1, '2026-02-01 00:56:15'),
(91, 'Arroz Integral', 'Brown Rice', 'grains', '1', 'taza cocido', 216, 5.00, 44.80, 1.80, 3.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(92, 'Pasta', 'Pasta', 'grains', '1', 'taza cocida', 221, 8.10, 43.20, 1.30, 2.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(93, 'Pan Integral', 'Whole Wheat Bread', 'grains', '1', 'rebanada', 81, 4.00, 13.80, 1.10, 1.90, 0.00, 0, 1, '2026-02-01 00:56:15'),
(94, 'Tortilla de Maíz', 'Corn Tortilla', 'grains', '1', 'pieza', 52, 1.40, 10.70, 0.70, 1.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(95, 'Avena', 'Oatmeal', 'grains', '1', 'taza cocida', 166, 5.90, 28.10, 3.60, 4.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(96, 'Quinoa', 'Quinoa', 'grains', '1', 'taza cocida', 222, 8.10, 39.40, 3.60, 5.20, 0.00, 0, 1, '2026-02-01 00:56:15'),
(97, 'Pan Blanco', 'White Bread', 'grains', '1', 'rebanada', 79, 2.70, 14.70, 1.00, 0.80, 0.00, 0, 1, '2026-02-01 00:56:15'),
(98, 'Cereal', 'Cereal', 'grains', '1', 'taza', 110, 2.00, 24.00, 1.00, 3.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(99, 'Galletas Saladas', 'Crackers', 'grains', '5', 'piezas', 70, 1.00, 11.00, 2.50, 0.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(100, 'Leche Entera', 'Whole Milk', 'dairy', '1', 'taza', 149, 7.70, 11.70, 7.90, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(101, 'Leche Descremada', 'Skim Milk', 'dairy', '1', 'taza', 83, 8.30, 12.20, 0.20, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(102, 'Yogurt Natural', 'Plain Yogurt', 'dairy', '1', 'taza', 149, 8.50, 11.40, 8.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(103, 'Yogurt Griego', 'Greek Yogurt', 'dairy', '1', 'taza', 100, 17.00, 6.00, 0.40, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(104, 'Queso Panela', 'Panela Cheese', 'dairy', '100', 'g', 321, 25.00, 3.00, 23.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(105, 'Queso Oaxaca', 'Oaxaca Cheese', 'dairy', '100', 'g', 300, 22.00, 2.00, 23.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(106, 'Queso Fresco', 'Fresh Cheese', 'dairy', '100', 'g', 264, 21.00, 4.00, 18.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(107, 'Queso Cheddar', 'Cheddar Cheese', 'dairy', '28', 'g', 113, 7.00, 1.00, 9.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(108, 'Queso Cottage', 'Cottage Cheese', 'dairy', '1', 'taza', 163, 28.00, 6.00, 2.30, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(109, 'Almendras', 'Almonds', 'snacks', '28', 'g', 164, 6.00, 6.10, 14.20, 3.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(110, 'Nueces', 'Walnuts', 'snacks', '28', 'g', 185, 4.30, 3.90, 18.50, 1.90, 0.00, 0, 1, '2026-02-01 00:56:15'),
(111, 'Cacahuates', 'Peanuts', 'snacks', '28', 'g', 161, 7.30, 4.60, 14.00, 2.40, 0.00, 0, 1, '2026-02-01 00:56:15'),
(112, 'Granola', 'Granola', 'snacks', '1/2', 'taza', 260, 7.00, 40.00, 9.00, 5.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(113, 'Palomitas', 'Popcorn', 'snacks', '3', 'tazas', 93, 3.00, 18.60, 1.10, 3.60, 0.00, 0, 1, '2026-02-01 00:56:15'),
(114, 'Barra de Proteína', 'Protein Bar', 'snacks', '1', 'barra', 200, 20.00, 22.00, 7.00, 3.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(115, 'Chips de Plátano', 'Banana Chips', 'snacks', '28', 'g', 147, 0.70, 16.60, 9.50, 2.10, 0.00, 0, 1, '2026-02-01 00:56:15'),
(116, 'Agua', 'Water', 'beverages', '1', 'vaso', 0, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(117, 'Café Negro', 'Black Coffee', 'beverages', '1', 'taza', 2, 0.30, 0.00, 0.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(118, 'Té Verde', 'Green Tea', 'beverages', '1', 'taza', 2, 0.50, 0.00, 0.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(119, 'Jugo de Naranja', 'Orange Juice', 'beverages', '1', 'taza', 112, 1.70, 25.80, 0.50, 0.50, 0.00, 0, 1, '2026-02-01 00:56:15'),
(120, 'Leche de Almendras', 'Almond Milk', 'beverages', '1', 'taza', 30, 1.00, 1.00, 2.50, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(121, 'Batido de Proteína', 'Protein Shake', 'beverages', '1', 'vaso', 120, 20.00, 5.00, 2.00, 0.00, 0.00, 0, 1, '2026-02-01 00:56:15'),
(122, 'Pechuga de Pollo', 'Chicken Breast', 'proteins', '100', 'g', 165, 31.00, 0.00, 3.60, 0.00, 0.00, 0, 1, '2026-02-01 05:12:14'),
(123, 'Arroz Blanco', 'White Rice', 'grains', '1', 'taza', 205, 4.20, 44.50, 0.40, 0.60, 0.00, 0, 1, '2026-02-01 05:12:14'),
(124, 'Brócoli', 'Broccoli', 'vegetables', '1', 'taza', 55, 3.70, 11.20, 0.60, 2.40, 0.00, 0, 1, '2026-02-01 05:12:14'),
(125, 'Plátano', 'Banana', 'fruits', '1', 'mediano', 105, 1.30, 27.00, 0.40, 3.10, 0.00, 0, 1, '2026-02-01 05:12:14'),
(126, 'Huevo', 'Egg', 'proteins', '1', 'grande', 78, 6.30, 0.60, 5.30, 0.00, 0.00, 0, 1, '2026-02-01 05:12:14'),
(127, 'Avena', 'Oatmeal', 'grains', '1', 'taza', 166, 5.90, 28.10, 3.60, 4.00, 0.00, 0, 1, '2026-02-01 05:12:14'),
(128, 'Aguacate', 'Avocado', 'fruits', '1/2', 'pieza', 160, 2.00, 8.50, 14.70, 6.70, 0.00, 0, 1, '2026-02-01 05:12:14'),
(129, 'Yogurt Griego', 'Greek Yogurt', 'dairy', '1', 'taza', 100, 17.00, 6.00, 0.40, 0.00, 0.00, 0, 1, '2026-02-01 05:12:14'),
(130, 'Salmón', 'Salmon', 'proteins', '100', 'g', 208, 20.00, 0.00, 13.00, 0.00, 0.00, 0, 1, '2026-02-01 05:12:14'),
(131, 'Almendras', 'Almonds', 'snacks', '28', 'g', 164, 6.00, 6.10, 14.20, 3.50, 0.00, 0, 1, '2026-02-01 05:12:14'),
(132, 'Pechuga de Pollo', 'Chicken Breast', 'proteins', '100', 'g', 165, 31.00, 0.00, 3.60, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(133, 'Arroz Blanco', 'White Rice', 'grains', '1', 'taza', 205, 4.20, 44.50, 0.40, 0.60, 0.00, 0, 1, '2026-02-01 05:15:35'),
(134, 'Brócoli', 'Broccoli', 'vegetables', '1', 'taza', 55, 3.70, 11.20, 0.60, 2.40, 0.00, 0, 1, '2026-02-01 05:15:35'),
(135, 'Plátano', 'Banana', 'fruits', '1', 'mediano', 105, 1.30, 27.00, 0.40, 3.10, 0.00, 0, 1, '2026-02-01 05:15:35'),
(136, 'Manzana', 'Apple', 'fruits', '1', 'mediana', 95, 0.50, 25.00, 0.30, 4.40, 0.00, 0, 1, '2026-02-01 05:15:35'),
(137, 'Huevo', 'Egg', 'proteins', '1', 'grande', 78, 6.30, 0.60, 5.30, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(138, 'Avena', 'Oatmeal', 'grains', '1', 'taza', 166, 5.90, 28.10, 3.60, 4.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(139, 'Aguacate', 'Avocado', 'fruits', '1/2', 'pieza', 160, 2.00, 8.50, 14.70, 6.70, 0.00, 0, 1, '2026-02-01 05:15:35'),
(140, 'Yogurt Griego', 'Greek Yogurt', 'dairy', '1', 'taza', 100, 17.00, 6.00, 0.40, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(141, 'Salmón', 'Salmon', 'proteins', '100', 'g', 208, 20.00, 0.00, 13.00, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(142, 'Atún', 'Tuna', 'proteins', '100', 'g', 132, 28.00, 0.00, 1.30, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(143, 'Frijoles', 'Beans', 'proteins', '1', 'taza', 227, 15.20, 40.40, 0.90, 15.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(144, 'Lentejas', 'Lentils', 'proteins', '1', 'taza', 230, 17.90, 39.90, 0.80, 15.60, 0.00, 0, 1, '2026-02-01 05:15:35'),
(145, 'Pasta', 'Pasta', 'grains', '1', 'taza', 221, 8.10, 43.20, 1.30, 2.50, 0.00, 0, 1, '2026-02-01 05:15:35'),
(146, 'Pan Integral', 'Whole Wheat Bread', 'grains', '1', 'rebanada', 81, 4.00, 13.80, 1.10, 1.90, 0.00, 0, 1, '2026-02-01 05:15:35'),
(147, 'Tortilla de Maíz', 'Corn Tortilla', 'grains', '1', 'pieza', 52, 1.40, 10.70, 0.70, 1.50, 0.00, 0, 1, '2026-02-01 05:15:35'),
(148, 'Leche', 'Milk', 'dairy', '1', 'taza', 149, 7.70, 11.70, 7.90, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(149, 'Queso Panela', 'Panela Cheese', 'dairy', '100', 'g', 321, 25.00, 3.00, 23.00, 0.00, 0.00, 0, 1, '2026-02-01 05:15:35'),
(150, 'Almendras', 'Almonds', 'snacks', '28', 'g', 164, 6.00, 6.10, 14.20, 3.50, 0.00, 0, 1, '2026-02-01 05:15:35'),
(151, 'Nueces', 'Walnuts', 'snacks', '28', 'g', 185, 4.30, 3.90, 18.50, 1.90, 0.00, 0, 1, '2026-02-01 05:15:35'),
(152, 'Pechuga de Pollo', 'Chicken Breast', 'proteins', '100', 'g', 165, 31.00, 0.00, 3.60, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(153, 'Arroz Blanco', 'White Rice', 'grains', '1', 'taza', 205, 4.20, 44.50, 0.40, 0.60, 0.00, 0, 1, '2026-02-01 05:15:54'),
(154, 'Brócoli', 'Broccoli', 'vegetables', '1', 'taza', 55, 3.70, 11.20, 0.60, 2.40, 0.00, 0, 1, '2026-02-01 05:15:54'),
(155, 'Plátano', 'Banana', 'fruits', '1', 'mediano', 105, 1.30, 27.00, 0.40, 3.10, 0.00, 0, 1, '2026-02-01 05:15:54'),
(156, 'Manzana', 'Apple', 'fruits', '1', 'mediana', 95, 0.50, 25.00, 0.30, 4.40, 0.00, 0, 1, '2026-02-01 05:15:54'),
(157, 'Huevo', 'Egg', 'proteins', '1', 'grande', 78, 6.30, 0.60, 5.30, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(158, 'Avena', 'Oatmeal', 'grains', '1', 'taza', 166, 5.90, 28.10, 3.60, 4.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(159, 'Aguacate', 'Avocado', 'fruits', '1/2', 'pieza', 160, 2.00, 8.50, 14.70, 6.70, 0.00, 0, 1, '2026-02-01 05:15:54'),
(160, 'Yogurt Griego', 'Greek Yogurt', 'dairy', '1', 'taza', 100, 17.00, 6.00, 0.40, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(161, 'Salmón', 'Salmon', 'proteins', '100', 'g', 208, 20.00, 0.00, 13.00, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(162, 'Atún', 'Tuna', 'proteins', '100', 'g', 132, 28.00, 0.00, 1.30, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(163, 'Frijoles', 'Beans', 'proteins', '1', 'taza', 227, 15.20, 40.40, 0.90, 15.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(164, 'Lentejas', 'Lentils', 'proteins', '1', 'taza', 230, 17.90, 39.90, 0.80, 15.60, 0.00, 0, 1, '2026-02-01 05:15:54'),
(165, 'Pasta', 'Pasta', 'grains', '1', 'taza', 221, 8.10, 43.20, 1.30, 2.50, 0.00, 0, 1, '2026-02-01 05:15:54'),
(166, 'Pan Integral', 'Whole Wheat Bread', 'grains', '1', 'rebanada', 81, 4.00, 13.80, 1.10, 1.90, 0.00, 0, 1, '2026-02-01 05:15:54'),
(167, 'Tortilla de Maíz', 'Corn Tortilla', 'grains', '1', 'pieza', 52, 1.40, 10.70, 0.70, 1.50, 0.00, 0, 1, '2026-02-01 05:15:54'),
(168, 'Leche', 'Milk', 'dairy', '1', 'taza', 149, 7.70, 11.70, 7.90, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(169, 'Queso Panela', 'Panela Cheese', 'dairy', '100', 'g', 321, 25.00, 3.00, 23.00, 0.00, 0.00, 0, 1, '2026-02-01 05:15:54'),
(170, 'Almendras', 'Almonds', 'snacks', '28', 'g', 164, 6.00, 6.10, 14.20, 3.50, 0.00, 0, 1, '2026-02-01 05:15:54'),
(171, 'Nueces', 'Walnuts', 'snacks', '28', 'g', 185, 4.30, 3.90, 18.50, 1.90, 0.00, 0, 1, '2026-02-01 05:15:54'),
(172, 'Pechuga de Pollo', 'Chicken Breast', 'proteins', '100', 'g', 165, 31.00, 0.00, 3.60, 0.00, 0.00, 0, 1, '2026-02-02 03:56:48'),
(173, 'Arroz Blanco', 'White Rice', 'grains', '1', 'taza', 205, 4.20, 44.50, 0.40, 0.60, 0.00, 0, 1, '2026-02-02 03:56:48'),
(174, 'Brócoli', 'Broccoli', 'vegetables', '1', 'taza', 55, 3.70, 11.20, 0.60, 2.40, 0.00, 0, 1, '2026-02-02 03:56:48'),
(175, 'Plátano', 'Banana', 'fruits', '1', 'mediano', 105, 1.30, 27.00, 0.40, 3.10, 0.00, 0, 1, '2026-02-02 03:56:48'),
(176, 'Manzana', 'Apple', 'fruits', '1', 'mediana', 95, 0.50, 25.00, 0.30, 4.40, 0.00, 0, 1, '2026-02-02 03:56:48'),
(177, 'Huevo', 'Egg', 'proteins', '1', 'grande', 78, 6.30, 0.60, 5.30, 0.00, 0.00, 0, 1, '2026-02-02 03:56:48'),
(178, 'Avena', 'Oatmeal', 'grains', '1', 'taza', 166, 5.90, 28.10, 3.60, 4.00, 0.00, 0, 1, '2026-02-02 03:56:48'),
(179, 'Aguacate', 'Avocado', 'fruits', '1/2', 'pieza', 160, 2.00, 8.50, 14.70, 6.70, 0.00, 0, 1, '2026-02-02 03:56:48'),
(180, 'Yogurt Griego', 'Greek Yogurt', 'dairy', '1', 'taza', 100, 17.00, 6.00, 0.40, 0.00, 0.00, 0, 1, '2026-02-02 03:56:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foods_database`
--

CREATE TABLE `foods_database` (
  `id` int(11) NOT NULL,
  `external_id` varchar(100) DEFAULT NULL,
  `source` enum('usda','openfoodfacts','manual') NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `serving_size` decimal(8,2) DEFAULT NULL,
  `serving_unit` varchar(50) DEFAULT NULL,
  `calories_per_100g` decimal(8,2) DEFAULT NULL,
  `protein_per_100g` decimal(8,2) DEFAULT NULL,
  `carbs_per_100g` decimal(8,2) DEFAULT NULL,
  `fats_per_100g` decimal(8,2) DEFAULT NULL,
  `fiber_per_100g` decimal(8,2) DEFAULT NULL,
  `sugar_per_100g` decimal(8,2) DEFAULT NULL,
  `sodium_per_100g` decimal(8,2) DEFAULT NULL,
  `additional_nutrients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_nutrients`)),
  `image_url` varchar(500) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `foods_database`
--

INSERT INTO `foods_database` (`id`, `external_id`, `source`, `name`, `brand`, `barcode`, `category`, `serving_size`, `serving_unit`, `calories_per_100g`, `protein_per_100g`, `carbs_per_100g`, `fats_per_100g`, `fiber_per_100g`, `sugar_per_100g`, `sodium_per_100g`, `additional_nutrients`, `image_url`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 'apple_001', 'manual', 'Manzana', NULL, NULL, 'fruits', NULL, NULL, 52.00, 0.30, 14.00, 0.20, 2.40, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(2, 'banana_001', 'manual', 'Plátano', NULL, NULL, 'fruits', NULL, NULL, 89.00, 1.10, 23.00, 0.30, 2.60, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(3, 'chicken_breast_001', 'manual', 'Pechuga de pollo', NULL, NULL, 'proteins', NULL, NULL, 165.00, 31.00, 0.00, 3.60, 0.00, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(4, 'rice_white_001', 'manual', 'Arroz blanco', NULL, NULL, 'grains', NULL, NULL, 130.00, 2.70, 28.00, 0.30, 0.40, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(5, 'broccoli_001', 'manual', 'Brócoli', NULL, NULL, 'vegetables', NULL, NULL, 34.00, 2.80, 7.00, 0.40, 2.60, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(6, 'salmon_001', 'manual', 'Salmón', NULL, NULL, 'proteins', NULL, NULL, 208.00, 25.00, 0.00, 12.00, 0.00, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(7, 'avocado_001', 'manual', 'Aguacate', NULL, NULL, 'fruits', NULL, NULL, 160.00, 2.00, 9.00, 15.00, 7.00, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56'),
(8, 'bread_whole_001', 'manual', 'Pan integral', NULL, NULL, 'grains', NULL, NULL, 247.00, 13.00, 41.00, 4.20, 7.00, NULL, NULL, NULL, NULL, 1, '2026-02-01 21:59:56', '2026-02-01 21:59:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `food_images`
--

CREATE TABLE `food_images` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `analysis_status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `google_vision_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`google_vision_response`)),
  `detected_foods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detected_foods`)),
  `estimated_nutrition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`estimated_nutrition`)),
  `confidence_score` decimal(3,2) DEFAULT NULL,
  `manual_corrections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`manual_corrections`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `analyzed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `food_images`
--

INSERT INTO `food_images` (`id`, `user_id`, `original_filename`, `stored_filename`, `file_path`, `file_size`, `mime_type`, `analysis_status`, `google_vision_response`, `detected_foods`, `estimated_nutrition`, `confidence_score`, `manual_corrections`, `created_at`, `analyzed_at`) VALUES
(1, 1, 'images.jpg', 'food_697fcd236cbfa_1769983267.jpg', 'uploads/food_images/food_697fcd236cbfa_1769983267.jpg', 3908, 'image/jpeg', 'completed', NULL, '[{\"name\":\"Ensalada mixta\",\"confidence\":0.85,\"category\":\"vegetables\",\"estimated_portion\":\"200g\"},{\"name\":\"Pollo a la plancha\",\"confidence\":0.78,\"category\":\"protein\",\"estimated_portion\":\"150g\"}]', '{\"total_calories\":320,\"protein\":28.5,\"carbs\":12,\"fats\":18.2,\"fiber\":4.5}', 0.75, NULL, '2026-02-01 22:01:07', '2026-02-01 22:01:07'),
(2, 1, 'uva.jpg', 'food_697fd4e6b9d90_1769985254.jpg', 'uploads/food_images/food_697fd4e6b9d90_1769985254.jpg', 5409, 'image/jpeg', 'completed', NULL, '[{\"name\":\"Ensalada mixta\",\"confidence\":0.85,\"category\":\"vegetables\",\"estimated_portion\":\"200g\"},{\"name\":\"Pollo a la plancha\",\"confidence\":0.78,\"category\":\"protein\",\"estimated_portion\":\"150g\"}]', '{\"total_calories\":320,\"protein\":28.5,\"carbs\":12,\"fats\":18.2,\"fiber\":4.5}', 0.75, NULL, '2026-02-01 22:34:14', '2026-02-01 22:34:14'),
(3, 2, 'uva.jpg', 'food_6980024861010_1769996872.jpg', 'uploads/food_images/food_6980024861010_1769996872.jpg', 5409, 'image/jpeg', 'completed', NULL, '[{\"name\":\"Ensalada mixta\",\"confidence\":0.85,\"category\":\"vegetables\",\"estimated_portion\":\"200g\"},{\"name\":\"Pollo a la plancha\",\"confidence\":0.78,\"category\":\"protein\",\"estimated_portion\":\"150g\"}]', '{\"total_calories\":320,\"protein\":28.5,\"carbs\":12,\"fats\":18.2,\"fiber\":4.5}', 0.75, NULL, '2026-02-02 01:47:52', '2026-02-02 01:47:52'),
(4, 2, 'camera-photo.jpg', 'food_6980025cc1c02_1769996892.jpg', 'uploads/food_images/food_6980025cc1c02_1769996892.jpg', 39264, 'image/jpeg', 'completed', NULL, '[{\"name\":\"Ensalada mixta\",\"confidence\":0.85,\"category\":\"vegetables\",\"estimated_portion\":\"200g\"},{\"name\":\"Pollo a la plancha\",\"confidence\":0.78,\"category\":\"protein\",\"estimated_portion\":\"150g\"}]', '{\"total_calories\":320,\"protein\":28.5,\"carbs\":12,\"fats\":18.2,\"fiber\":4.5}', 0.75, NULL, '2026-02-02 01:48:12', '2026-02-02 01:48:12'),
(5, 18, 'camera-photo.jpg', 'food_6980040446eba_1769997316.jpg', 'uploads/food_images/food_6980040446eba_1769997316.jpg', 46566, 'image/jpeg', 'completed', NULL, '[{\"name\":\"Ensalada mixta\",\"confidence\":0.85,\"category\":\"vegetables\",\"estimated_portion\":\"200g\"},{\"name\":\"Pollo a la plancha\",\"confidence\":0.78,\"category\":\"protein\",\"estimated_portion\":\"150g\"}]', '{\"total_calories\":320,\"protein\":28.5,\"carbs\":12,\"fats\":18.2,\"fiber\":4.5}', 0.75, NULL, '2026-02-02 01:55:16', '2026-02-02 01:55:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `food_scans`
--

CREATE TABLE `food_scans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `food_name` varchar(255) NOT NULL,
  `calories` int(11) NOT NULL,
  `protein` decimal(5,2) DEFAULT 0.00,
  `carbs` decimal(5,2) DEFAULT 0.00,
  `fats` decimal(5,2) DEFAULT 0.00,
  `fiber` decimal(5,2) DEFAULT 0.00,
  `sugar` decimal(5,2) DEFAULT 0.00,
  `sodium` int(11) DEFAULT 0,
  `vitamins` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vitamins`)),
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `health_profiles`
--

CREATE TABLE `health_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `activity_level` enum('sedentary','light','moderate','active','very_active') DEFAULT 'sedentary',
  `health_conditions` text DEFAULT NULL,
  `dietary_preferences` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `goal` enum('lose_weight','maintain','gain_weight','muscle_gain') DEFAULT 'maintain',
  `target_calories` int(11) DEFAULT NULL,
  `target_protein` decimal(5,2) DEFAULT NULL,
  `target_carbs` decimal(5,2) DEFAULT NULL,
  `target_fats` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `health_profiles`
--

INSERT INTO `health_profiles` (`id`, `user_id`, `weight`, `height`, `age`, `gender`, `activity_level`, `health_conditions`, `dietary_preferences`, `allergies`, `goal`, `target_calories`, `target_protein`, `target_carbs`, `target_fats`, `created_at`, `updated_at`) VALUES
(1, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:47:37', '2026-01-30 22:47:37'),
(2, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:47:38', '2026-01-30 22:47:38'),
(3, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:47:39', '2026-01-30 22:47:39'),
(4, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:47:39', '2026-01-30 22:47:39'),
(5, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:50:38', '2026-01-30 22:50:38'),
(6, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:50:39', '2026-01-30 22:50:39'),
(7, 8, 52.00, 170.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2353, 83.00, 330.00, 78.00, '2026-01-30 22:51:36', '2026-01-30 22:51:36'),
(8, 1, 51.00, 172.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2348, 82.00, 330.00, 78.00, '2026-01-30 23:01:48', '2026-01-30 23:01:48'),
(9, 1, 51.00, 172.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2348, 82.00, 330.00, 78.00, '2026-01-30 23:01:54', '2026-01-30 23:01:54'),
(10, 2, 52.00, 172.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2366, 83.00, 331.00, 79.00, '2026-02-01 00:47:12', '2026-02-01 00:47:12'),
(11, 2, 52.00, 172.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2366, 83.00, 331.00, 79.00, '2026-02-01 00:47:18', '2026-02-01 00:47:18'),
(12, 2, 52.00, 172.00, 19, 'male', 'light', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2366, 83.00, 331.00, 79.00, '2026-02-01 00:47:33', '2026-02-01 00:47:33'),
(13, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:27', '2026-02-01 02:21:27'),
(14, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:28', '2026-02-01 02:21:28'),
(15, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:28', '2026-02-01 02:21:28'),
(16, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:28', '2026-02-01 02:21:28'),
(17, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:29', '2026-02-01 02:21:29'),
(18, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:29', '2026-02-01 02:21:29'),
(19, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:29', '2026-02-01 02:21:29'),
(20, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:29', '2026-02-01 02:21:29'),
(21, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:29', '2026-02-01 02:21:29'),
(22, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 02:21:35', '2026-02-01 02:21:35'),
(23, 18, 52.00, 170.00, 19, 'male', 'moderate', NULL, 'Tipo: normal; Comidas/día: 5', 'lactose', 'gain_weight', 2614, 83.00, 375.00, 87.00, '2026-02-01 03:24:24', '2026-02-01 03:24:24'),
(24, 42, 70.00, 170.00, 30, 'male', 'moderate', 'Ninguna', 'Ninguna restricción especial', 'Ninguna', 'maintain', 2000, 150.00, 250.00, 67.00, '2026-02-01 21:51:27', '2026-02-01 21:51:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `meals`
--

CREATE TABLE `meals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `calories` int(11) NOT NULL,
  `protein` decimal(5,2) NOT NULL,
  `carbs` decimal(5,2) NOT NULL,
  `fats` decimal(5,2) NOT NULL,
  `fiber` decimal(5,2) DEFAULT NULL,
  `preparation_time` int(11) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_vegetarian` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `meals`
--

INSERT INTO `meals` (`id`, `name`, `description`, `meal_type`, `image_url`, `calories`, `protein`, `carbs`, `fats`, `fiber`, `preparation_time`, `ingredients`, `instructions`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `created_at`) VALUES
(1, 'Avena con Frutas', 'Avena integral con plátano, fresas y miel', 'breakfast', 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=800', 320, 12.50, 58.00, 6.50, NULL, 10, 'Avena, leche, plátano, fresas, miel, chía', NULL, 1, 0, 0, '2026-01-29 02:46:26'),
(2, 'Huevos Revueltos con Aguacate', 'Huevos revueltos con aguacate y pan integral', 'breakfast', 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800', 380, 24.00, 28.00, 18.00, NULL, 15, 'Huevos, aguacate, pan integral, aceite de oliva', NULL, 1, 0, 0, '2026-01-29 02:46:26'),
(3, 'Ensalada de Pollo', 'Ensalada verde con pechuga de pollo a la plancha', 'lunch', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800', 420, 38.00, 25.00, 18.00, NULL, 20, 'Lechuga, pollo, tomate, pepino, aceite de oliva', NULL, 0, 0, 0, '2026-01-29 02:46:26'),
(4, 'Salmón con Verduras', 'Salmón al horno con brócoli y zanahoria', 'dinner', 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800', 480, 42.00, 22.00, 24.00, NULL, 30, 'Salmón, brócoli, zanahoria, limón, aceite de oliva', NULL, 0, 0, 0, '2026-01-29 02:46:26'),
(5, 'Avena con Frutas', 'Avena cocida con plátano y fresas', 'breakfast', NULL, 320, 12.00, 52.00, 8.00, NULL, 10, 'Avena, leche, plátano, fresas, miel, canela', '1. Cocinar avena con leche. 2. Agregar frutas. 3. Endulzar con miel.', 1, 0, 1, '2026-02-01 01:06:34'),
(6, 'Huevos Revueltos con Aguacate', 'Huevos revueltos con aguacate y tostadas', 'breakfast', NULL, 380, 18.00, 28.00, 22.00, NULL, 15, '2 huevos, 1/2 aguacate, 2 tostadas integrales, tomate', '1. Batir huevos. 2. Cocinar a fuego medio. 3. Servir con aguacate y tostadas.', 1, 0, 1, '2026-02-01 01:06:34'),
(7, 'Smoothie Verde', 'Smoothie de espinaca, plátano y proteína', 'breakfast', NULL, 280, 25.00, 35.00, 5.00, NULL, 5, 'Espinaca, plátano, proteína en polvo, leche de almendras', '1. Licuar todos los ingredientes. 2. Servir frío.', 1, 1, 1, '2026-02-01 01:06:34'),
(8, 'Yogurt con Granola', 'Yogurt griego con granola y miel', 'breakfast', NULL, 340, 20.00, 45.00, 10.00, NULL, 5, 'Yogurt griego, granola, miel, arándanos', '1. Servir yogurt. 2. Agregar granola y frutas. 3. Endulzar.', 1, 0, 0, '2026-02-01 01:06:34'),
(9, 'Tostadas de Aguacate', 'Pan integral con aguacate y huevo', 'breakfast', NULL, 410, 16.00, 38.00, 24.00, NULL, 12, 'Pan integral, aguacate, huevo, tomate, cilantro', '1. Tostar pan. 2. Machacar aguacate. 3. Cocinar huevo. 4. Ensamblar.', 1, 0, 1, '2026-02-01 01:06:34'),
(10, 'Pechuga de Pollo con Arroz', 'Pechuga a la plancha con arroz integral y brócoli', 'lunch', NULL, 520, 45.00, 58.00, 12.00, NULL, 25, 'Pechuga de pollo, arroz integral, brócoli, aceite de oliva', '1. Cocinar pollo a la plancha. 2. Hervir arroz. 3. Cocer brócoli al vapor.', 0, 0, 1, '2026-02-01 01:06:34'),
(11, 'Ensalada de Atún', 'Ensalada fresca con atún, lechuga y vegetales', 'lunch', NULL, 380, 32.00, 28.00, 16.00, NULL, 15, 'Atún, lechuga, tomate, pepino, maíz, aceite de oliva', '1. Lavar vegetales. 2. Mezclar con atún. 3. Aderezar.', 0, 0, 1, '2026-02-01 01:06:34'),
(12, 'Bowl de Quinoa', 'Quinoa con vegetales asados y aguacate', 'lunch', NULL, 450, 18.00, 62.00, 18.00, NULL, 30, 'Quinoa, pimiento, calabaza, aguacate, limón', '1. Cocinar quinoa. 2. Asar vegetales. 3. Ensamblar bowl.', 1, 1, 1, '2026-02-01 01:06:34'),
(13, 'Salmón con Papas', 'Salmón al horno con papas asadas', 'lunch', NULL, 580, 42.00, 48.00, 24.00, NULL, 35, 'Salmón, papas, limón, eneldo, aceite de oliva', '1. Hornear salmón. 2. Asar papas. 3. Servir con limón.', 0, 0, 1, '2026-02-01 01:06:34'),
(14, 'Pasta con Verduras', 'Pasta integral con verduras salteadas', 'lunch', NULL, 480, 16.00, 72.00, 14.00, NULL, 20, 'Pasta integral, brócoli, zanahoria, calabaza, ajo', '1. Cocer pasta. 2. Saltear verduras. 3. Mezclar.', 1, 1, 0, '2026-02-01 01:06:34'),
(15, 'Tacos de Pescado', 'Tacos con pescado, repollo y salsa', 'dinner', NULL, 420, 28.00, 45.00, 16.00, NULL, 20, 'Pescado blanco, tortillas de maíz, repollo, limón, cilantro', '1. Cocinar pescado. 2. Preparar salsa. 3. Ensamblar tacos.', 0, 0, 1, '2026-02-01 01:06:34'),
(16, 'Sopa de Lentejas', 'Sopa nutritiva de lentejas con vegetales', 'dinner', NULL, 350, 18.00, 54.00, 6.00, NULL, 40, 'Lentejas, zanahoria, apio, tomate, cebolla', '1. Hervir lentejas. 2. Agregar vegetales. 3. Cocinar hasta suavizar.', 1, 1, 1, '2026-02-01 01:06:34'),
(17, 'Pechuga a la Mostaza', 'Pechuga de pollo con salsa de mostaza', 'dinner', NULL, 380, 42.00, 22.00, 14.00, NULL, 25, 'Pechuga, mostaza dijon, miel, arroz, espárragos', '1. Preparar salsa. 2. Cocinar pollo. 3. Servir con arroz.', 0, 0, 1, '2026-02-01 01:06:34'),
(18, 'Omelette de Verduras', 'Omelette con espinaca, champiñones y queso', 'dinner', NULL, 320, 24.00, 12.00, 20.00, NULL, 15, '3 huevos, espinaca, champiñones, queso, cebolla', '1. Batir huevos. 2. Saltear verduras. 3. Cocinar omelette.', 1, 0, 1, '2026-02-01 01:06:34'),
(19, 'Tofu Salteado', 'Tofu con vegetales al estilo asiático', 'dinner', NULL, 380, 22.00, 38.00, 16.00, NULL, 20, 'Tofu, brócoli, pimiento, salsa soya, jengibre', '1. Marinar tofu. 2. Saltear con vegetales. 3. Servir con arroz.', 1, 1, 0, '2026-02-01 01:06:34'),
(20, 'Hummus con Vegetales', 'Hummus casero con zanahorias y pepino', 'snack', NULL, 180, 6.00, 22.00, 8.00, NULL, 10, 'Garbanzos, tahini, limón, zanahoria, pepino', '1. Licuar garbanzos con tahini. 2. Cortar vegetales. 3. Servir.', 1, 1, 1, '2026-02-01 01:06:34'),
(21, 'Smoothie de Proteína', 'Batido de proteína con frutas', 'snack', NULL, 220, 20.00, 28.00, 4.00, NULL, 5, 'Proteína en polvo, plátano, fresas, leche', '1. Licuar ingredientes. 2. Servir frío.', 1, 0, 1, '2026-02-01 01:06:34'),
(22, 'Manzana con Mantequilla de Almendra', 'Manzana rebanada con mantequilla de almendra', 'snack', NULL, 240, 8.00, 32.00, 12.00, NULL, 5, 'Manzana, mantequilla de almendra', '1. Rebanar manzana. 2. Untar mantequilla.', 1, 1, 1, '2026-02-01 01:06:34'),
(23, 'Yogurt con Frutos Secos', 'Yogurt natural con nueces y miel', 'snack', NULL, 260, 12.00, 28.00, 14.00, NULL, 5, 'Yogurt, nueces, almendras, miel', '1. Servir yogurt. 2. Agregar frutos secos y miel.', 1, 0, 1, '2026-02-01 01:06:34'),
(24, 'Avena con Frutas', 'Avena cocida con plátano y fresas', 'breakfast', NULL, 320, 12.00, 52.00, 8.00, NULL, 10, 'Avena, leche, plátano, fresas, miel, canela', '1. Cocinar avena con leche. 2. Agregar frutas. 3. Endulzar con miel.', 1, 0, 1, '2026-02-01 05:02:00'),
(25, 'Huevos Revueltos con Aguacate', 'Huevos revueltos con aguacate y tostadas', 'breakfast', NULL, 380, 18.00, 28.00, 22.00, NULL, 15, '2 huevos, 1/2 aguacate, 2 tostadas integrales, tomate', '1. Batir huevos. 2. Cocinar a fuego medio. 3. Servir con aguacate y tostadas.', 1, 0, 1, '2026-02-01 05:02:00'),
(26, 'Smoothie Verde', 'Smoothie de espinaca, plátano y proteína', 'breakfast', NULL, 280, 25.00, 35.00, 5.00, NULL, 5, 'Espinaca, plátano, proteína en polvo, leche de almendras', '1. Licuar todos los ingredientes. 2. Servir frío.', 1, 1, 1, '2026-02-01 05:02:00'),
(27, 'Yogurt con Granola', 'Yogurt griego con granola y miel', 'breakfast', NULL, 340, 20.00, 45.00, 10.00, NULL, 5, 'Yogurt griego, granola, miel, arándanos', '1. Servir yogurt. 2. Agregar granola y frutas. 3. Endulzar.', 1, 0, 0, '2026-02-01 05:02:00'),
(28, 'Tostadas de Aguacate', 'Pan integral con aguacate y huevo', 'breakfast', NULL, 410, 16.00, 38.00, 24.00, NULL, 12, 'Pan integral, aguacate, huevo, tomate, cilantro', '1. Tostar pan. 2. Machacar aguacate. 3. Cocinar huevo. 4. Ensamblar.', 1, 0, 1, '2026-02-01 05:02:00'),
(29, 'Pechuga de Pollo con Arroz', 'Pechuga a la plancha con arroz integral y brócoli', 'lunch', NULL, 520, 45.00, 58.00, 12.00, NULL, 25, 'Pechuga de pollo, arroz integral, brócoli, aceite de oliva', '1. Cocinar pollo a la plancha. 2. Hervir arroz. 3. Cocer brócoli al vapor.', 0, 0, 1, '2026-02-01 05:02:00'),
(30, 'Ensalada de Atún', 'Ensalada fresca con atún, lechuga y vegetales', 'lunch', NULL, 380, 32.00, 28.00, 16.00, NULL, 15, 'Atún, lechuga, tomate, pepino, maíz, aceite de oliva', '1. Lavar vegetales. 2. Mezclar con atún. 3. Aderezar.', 0, 0, 1, '2026-02-01 05:02:00'),
(31, 'Bowl de Quinoa', 'Quinoa con vegetales asados y aguacate', 'lunch', NULL, 450, 18.00, 62.00, 18.00, NULL, 30, 'Quinoa, pimiento, calabaza, aguacate, limón', '1. Cocinar quinoa. 2. Asar vegetales. 3. Ensamblar bowl.', 1, 1, 1, '2026-02-01 05:02:00'),
(32, 'Salmón con Papas', 'Salmón al horno con papas asadas', 'lunch', NULL, 580, 42.00, 48.00, 24.00, NULL, 35, 'Salmón, papas, limón, eneldo, aceite de oliva', '1. Hornear salmón. 2. Asar papas. 3. Servir con limón.', 0, 0, 1, '2026-02-01 05:02:00'),
(33, 'Pasta con Verduras', 'Pasta integral con verduras salteadas', 'lunch', NULL, 480, 16.00, 72.00, 14.00, NULL, 20, 'Pasta integral, brócoli, zanahoria, calabaza, ajo', '1. Cocer pasta. 2. Saltear verduras. 3. Mezclar.', 1, 1, 0, '2026-02-01 05:02:00'),
(34, 'Tacos de Pescado', 'Tacos con pescado, repollo y salsa', 'dinner', NULL, 420, 28.00, 45.00, 16.00, NULL, 20, 'Pescado blanco, tortillas de maíz, repollo, limón, cilantro', '1. Cocinar pescado. 2. Preparar salsa. 3. Ensamblar tacos.', 0, 0, 1, '2026-02-01 05:02:00'),
(35, 'Sopa de Lentejas', 'Sopa nutritiva de lentejas con vegetales', 'dinner', NULL, 350, 18.00, 54.00, 6.00, NULL, 40, 'Lentejas, zanahoria, apio, tomate, cebolla', '1. Hervir lentejas. 2. Agregar vegetales. 3. Cocinar hasta suavizar.', 1, 1, 1, '2026-02-01 05:02:00'),
(36, 'Pechuga a la Mostaza', 'Pechuga de pollo con salsa de mostaza', 'dinner', NULL, 380, 42.00, 22.00, 14.00, NULL, 25, 'Pechuga, mostaza dijon, miel, arroz, espárragos', '1. Preparar salsa. 2. Cocinar pollo. 3. Servir con arroz.', 0, 0, 1, '2026-02-01 05:02:00'),
(37, 'Omelette de Verduras', 'Omelette con espinaca, champiñones y queso', 'dinner', NULL, 320, 24.00, 12.00, 20.00, NULL, 15, '3 huevos, espinaca, champiñones, queso, cebolla', '1. Batir huevos. 2. Saltear verduras. 3. Cocinar omelette.', 1, 0, 1, '2026-02-01 05:02:00'),
(38, 'Tofu Salteado', 'Tofu con vegetales al estilo asiático', 'dinner', NULL, 380, 22.00, 38.00, 16.00, NULL, 20, 'Tofu, brócoli, pimiento, salsa soya, jengibre', '1. Marinar tofu. 2. Saltear con vegetales. 3. Servir con arroz.', 1, 1, 0, '2026-02-01 05:02:00'),
(39, 'Hummus con Vegetales', 'Hummus casero con zanahorias y pepino', 'snack', NULL, 180, 6.00, 22.00, 8.00, NULL, 10, 'Garbanzos, tahini, limón, zanahoria, pepino', '1. Licuar garbanzos con tahini. 2. Cortar vegetales. 3. Servir.', 1, 1, 1, '2026-02-01 05:02:00'),
(40, 'Smoothie de Proteína', 'Batido de proteína con frutas', 'snack', NULL, 220, 20.00, 28.00, 4.00, NULL, 5, 'Proteína en polvo, plátano, fresas, leche', '1. Licuar ingredientes. 2. Servir frío.', 1, 0, 1, '2026-02-01 05:02:00'),
(41, 'Manzana con Mantequilla de Almendra', 'Manzana rebanada con mantequilla de almendra', 'snack', NULL, 240, 8.00, 32.00, 12.00, NULL, 5, 'Manzana, mantequilla de almendra', '1. Rebanar manzana. 2. Untar mantequilla.', 1, 1, 1, '2026-02-01 05:02:00'),
(42, 'Yogurt con Frutos Secos', 'Yogurt natural con nueces y miel', 'snack', NULL, 260, 12.00, 28.00, 14.00, NULL, 5, 'Yogurt, nueces, almendras, miel', '1. Servir yogurt. 2. Agregar frutos secos y miel.', 1, 0, 1, '2026-02-01 05:02:00'),
(43, 'Avena con Frutas', 'Avena cocida con plátano y fresas', 'breakfast', NULL, 320, 12.00, 52.00, 8.00, NULL, 10, 'Avena, leche, plátano, fresas, miel', '1. Cocinar avena con leche. 2. Agregar frutas. 3. Endulzar con miel.', 1, 0, 1, '2026-02-01 14:45:10'),
(44, 'Huevos Revueltos', 'Huevos revueltos con aguacate', 'breakfast', NULL, 380, 18.00, 28.00, 22.00, NULL, 15, '2 huevos, aguacate, tostadas', '1. Batir huevos. 2. Cocinar. 3. Servir con aguacate.', 1, 0, 1, '2026-02-01 14:45:10'),
(45, 'Smoothie Verde', 'Smoothie de espinaca y plátano', 'breakfast', NULL, 280, 25.00, 35.00, 5.00, NULL, 5, 'Espinaca, plátano, proteína', '1. Licuar todo. 2. Servir frío.', 1, 1, 1, '2026-02-01 14:45:10'),
(46, 'Yogurt con Granola', 'Yogurt griego con granola', 'breakfast', NULL, 340, 20.00, 45.00, 10.00, NULL, 5, 'Yogurt, granola, miel', '1. Servir yogurt. 2. Agregar granola.', 1, 0, 0, '2026-02-01 14:45:10'),
(47, 'Tostadas de Aguacate', 'Pan integral con aguacate', 'breakfast', NULL, 410, 16.00, 38.00, 24.00, NULL, 12, 'Pan, aguacate, huevo', '1. Tostar pan. 2. Machacar aguacate.', 1, 0, 1, '2026-02-01 14:45:10'),
(48, 'Pechuga con Arroz', 'Pechuga a la plancha con arroz', 'lunch', NULL, 520, 45.00, 58.00, 12.00, NULL, 25, 'Pechuga, arroz, brócoli', '1. Cocinar pollo. 2. Hervir arroz.', 0, 0, 1, '2026-02-01 14:45:10'),
(49, 'Ensalada de Atún', 'Ensalada fresca con atún', 'lunch', NULL, 380, 32.00, 28.00, 16.00, NULL, 15, 'Atún, lechuga, tomate', '1. Lavar vegetales. 2. Mezclar.', 0, 0, 1, '2026-02-01 14:45:10'),
(50, 'Bowl de Quinoa', 'Quinoa con vegetales', 'lunch', NULL, 450, 18.00, 62.00, 18.00, NULL, 30, 'Quinoa, pimiento, aguacate', '1. Cocinar quinoa. 2. Asar vegetales.', 1, 1, 1, '2026-02-01 14:45:10'),
(51, 'Salmón con Papas', 'Salmón al horno', 'lunch', NULL, 580, 42.00, 48.00, 24.00, NULL, 35, 'Salmón, papas, limón', '1. Hornear salmón. 2. Asar papas.', 0, 0, 1, '2026-02-01 14:45:10'),
(52, 'Pasta con Verduras', 'Pasta integral con verduras', 'lunch', NULL, 480, 16.00, 72.00, 14.00, NULL, 20, 'Pasta, brócoli, zanahoria', '1. Cocer pasta. 2. Saltear verduras.', 1, 1, 0, '2026-02-01 14:45:10'),
(53, 'Tacos de Pescado', 'Tacos con pescado blanco', 'dinner', NULL, 420, 28.00, 45.00, 16.00, NULL, 20, 'Pescado, tortillas, repollo', '1. Cocinar pescado. 2. Ensamblar tacos.', 0, 0, 1, '2026-02-01 14:45:10'),
(54, 'Sopa de Lentejas', 'Sopa nutritiva de lentejas', 'dinner', NULL, 350, 18.00, 54.00, 6.00, NULL, 40, 'Lentejas, zanahoria, apio', '1. Hervir lentejas. 2. Agregar vegetales.', 1, 1, 1, '2026-02-01 14:45:10'),
(55, 'Pechuga a la Mostaza', 'Pechuga con salsa de mostaza', 'dinner', NULL, 380, 42.00, 22.00, 14.00, NULL, 25, 'Pechuga, mostaza, arroz', '1. Preparar salsa. 2. Cocinar pollo.', 0, 0, 1, '2026-02-01 14:45:10'),
(56, 'Omelette de Verduras', 'Omelette con espinaca', 'dinner', NULL, 320, 24.00, 12.00, 20.00, NULL, 15, '3 huevos, espinaca, queso', '1. Batir huevos. 2. Cocinar.', 1, 0, 1, '2026-02-01 14:45:10'),
(57, 'Tofu Salteado', 'Tofu con vegetales', 'dinner', NULL, 380, 22.00, 38.00, 16.00, NULL, 20, 'Tofu, brócoli, salsa soya', '1. Marinar tofu. 2. Saltear.', 1, 1, 0, '2026-02-01 14:45:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `meal_id` int(11) NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `meal_plans`
--

INSERT INTO `meal_plans` (`id`, `user_id`, `date`, `meal_type`, `meal_id`, `scheduled_time`, `is_completed`, `created_at`) VALUES
(229, 2, '2026-01-26', 'breakfast', 44, '08:00:00', 0, '2026-02-01 19:59:58'),
(230, 2, '2026-01-26', 'lunch', 49, '13:00:00', 0, '2026-02-01 19:59:58'),
(231, 2, '2026-01-26', 'dinner', 37, '19:00:00', 0, '2026-02-01 19:59:58'),
(232, 2, '2026-01-27', 'breakfast', 45, '08:00:00', 0, '2026-02-01 19:59:58'),
(233, 2, '2026-01-27', 'lunch', 30, '13:00:00', 0, '2026-02-01 19:59:58'),
(234, 2, '2026-01-27', 'dinner', 4, '19:00:00', 0, '2026-02-01 19:59:58'),
(235, 2, '2026-01-28', 'breakfast', 45, '08:00:00', 0, '2026-02-01 19:59:58'),
(236, 2, '2026-01-28', 'lunch', 10, '13:00:00', 0, '2026-02-01 19:59:58'),
(237, 2, '2026-01-28', 'dinner', 53, '19:00:00', 0, '2026-02-01 19:59:58'),
(238, 2, '2026-01-29', 'breakfast', 43, '08:00:00', 0, '2026-02-01 19:59:58'),
(239, 2, '2026-01-29', 'lunch', 51, '13:00:00', 0, '2026-02-01 19:59:58'),
(240, 2, '2026-01-29', 'dinner', 15, '19:00:00', 0, '2026-02-01 19:59:58'),
(241, 2, '2026-01-30', 'breakfast', 6, '08:00:00', 0, '2026-02-01 19:59:58'),
(242, 2, '2026-01-30', 'lunch', 14, '13:00:00', 0, '2026-02-01 19:59:58'),
(243, 2, '2026-01-30', 'dinner', 55, '19:00:00', 0, '2026-02-01 19:59:58'),
(244, 2, '2026-01-31', 'breakfast', 24, '08:00:00', 0, '2026-02-01 19:59:58'),
(245, 2, '2026-01-31', 'lunch', 11, '13:00:00', 0, '2026-02-01 19:59:58'),
(246, 2, '2026-01-31', 'dinner', 38, '19:00:00', 0, '2026-02-01 19:59:58'),
(247, 2, '2026-02-01', 'breakfast', 2, '08:00:00', 0, '2026-02-01 19:59:58'),
(248, 2, '2026-02-01', 'lunch', 33, '13:00:00', 0, '2026-02-01 19:59:58'),
(249, 2, '2026-02-01', 'dinner', 18, '19:00:00', 0, '2026-02-01 19:59:58'),
(250, 1, '2026-01-26', 'breakfast', 44, '08:00:00', 0, '2026-02-01 20:39:07'),
(251, 1, '2026-01-26', 'lunch', 29, '13:00:00', 0, '2026-02-01 20:39:07'),
(252, 1, '2026-01-26', 'dinner', 15, '19:00:00', 0, '2026-02-01 20:39:07'),
(253, 1, '2026-01-27', 'breakfast', 28, '08:00:00', 0, '2026-02-01 20:39:07'),
(254, 1, '2026-01-27', 'lunch', 10, '13:00:00', 0, '2026-02-01 20:39:07'),
(255, 1, '2026-01-27', 'dinner', 53, '19:00:00', 0, '2026-02-01 20:39:07'),
(256, 1, '2026-01-28', 'breakfast', 27, '08:00:00', 0, '2026-02-01 20:39:07'),
(257, 1, '2026-01-28', 'lunch', 11, '13:00:00', 0, '2026-02-01 20:39:07'),
(258, 1, '2026-01-28', 'dinner', 56, '19:00:00', 0, '2026-02-01 20:39:07'),
(259, 1, '2026-01-29', 'breakfast', 6, '08:00:00', 0, '2026-02-01 20:39:07'),
(260, 1, '2026-01-29', 'lunch', 10, '13:00:00', 0, '2026-02-01 20:39:07'),
(261, 1, '2026-01-29', 'dinner', 54, '19:00:00', 0, '2026-02-01 20:39:07'),
(262, 1, '2026-01-30', 'breakfast', 7, '08:00:00', 0, '2026-02-01 20:39:07'),
(263, 1, '2026-01-30', 'lunch', 51, '13:00:00', 0, '2026-02-01 20:39:07'),
(264, 1, '2026-01-30', 'dinner', 55, '19:00:00', 0, '2026-02-01 20:39:07'),
(265, 1, '2026-01-31', 'breakfast', 9, '08:00:00', 0, '2026-02-01 20:39:07'),
(266, 1, '2026-01-31', 'lunch', 29, '13:00:00', 0, '2026-02-01 20:39:07'),
(267, 1, '2026-01-31', 'dinner', 19, '19:00:00', 0, '2026-02-01 20:39:07'),
(268, 1, '2026-02-01', 'breakfast', 27, '08:00:00', 0, '2026-02-01 20:39:07'),
(269, 1, '2026-02-01', 'lunch', 12, '13:00:00', 0, '2026-02-01 20:39:07'),
(270, 1, '2026-02-01', 'dinner', 34, '19:00:00', 0, '2026-02-01 20:39:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `food_id` int(11) DEFAULT NULL,
  `ingredient_name` varchar(255) NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `order_index` int(11) DEFAULT 0,
  `is_optional` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `shopping_lists`
--

CREATE TABLE `shopping_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` enum('fruits','vegetables','proteins','grains','dairy','other') DEFAULT 'other',
  `quantity` varchar(50) DEFAULT NULL,
  `is_checked` tinyint(1) DEFAULT 0,
  `week_start` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `shopping_lists`
--

INSERT INTO `shopping_lists` (`id`, `user_id`, `item_name`, `category`, `quantity`, `is_checked`, `week_start`, `created_at`) VALUES
(1, 2, 'Pan integral', '', '2', 0, '2026-01-26', '2026-02-01 19:18:00'),
(2, 2, 'Aguacate', '', '5', 0, '2026-01-26', '2026-02-01 19:18:00'),
(3, 2, 'Huevo', '', '2', 0, '2026-01-26', '2026-02-01 19:18:00'),
(4, 2, 'Tomate', '', '5', 0, '2026-01-26', '2026-02-01 19:18:01'),
(5, 2, 'Cilantro', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(6, 2, 'Pasta integral', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(7, 2, 'Brócoli', '', '4', 0, '2026-01-26', '2026-02-01 19:18:01'),
(8, 2, 'Zanahoria', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(9, 2, 'Calabaza', '', '2', 0, '2026-01-26', '2026-02-01 19:18:01'),
(10, 2, 'Ajo', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(11, 2, 'Lentejas', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(12, 2, 'Apio', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(13, 2, 'Cebolla', '', '2', 0, '2026-01-26', '2026-02-01 19:18:01'),
(14, 2, '2 huevos', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(15, 2, '1/2 aguacate', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(16, 2, '2 tostadas integrales', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(17, 2, 'Quinoa', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(18, 2, 'Pimiento', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(19, 2, 'Salmón', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(20, 2, 'Limón', '', '5', 0, '2026-01-26', '2026-02-01 19:18:01'),
(21, 2, 'Aceite de oliva', '', '2', 0, '2026-01-26', '2026-02-01 19:18:01'),
(22, 2, 'Avena', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(23, 2, 'Leche', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(24, 2, 'Plátano', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(25, 2, 'Fresas', '', '3', 0, '2026-01-26', '2026-02-01 19:18:01'),
(26, 2, 'Miel', '', '4', 0, '2026-01-26', '2026-02-01 19:18:01'),
(27, 2, 'Chía', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(28, 2, 'Pechuga', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(29, 2, 'Mostaza', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(30, 2, 'Arroz', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(31, 2, 'Yogurt', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(32, 2, 'Granola', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(33, 2, 'Tofu', '', '2', 0, '2026-01-26', '2026-02-01 19:18:01'),
(34, 2, 'Salsa soya', '', '2', 0, '2026-01-26', '2026-02-01 19:18:01'),
(35, 2, 'Papas', '', '2', 0, '2026-01-26', '2026-02-01 19:18:01'),
(36, 2, 'Canela', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(37, 2, 'Eneldo', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(38, 2, '3 huevos', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(39, 2, 'Espinaca', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(40, 2, 'Champiñones', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(41, 2, 'Queso', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(42, 2, 'Atún', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(43, 2, 'Lechuga', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(44, 2, 'Pescado blanco', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(45, 2, 'Tortillas de maíz', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(46, 2, 'Repollo', '', '1', 0, '2026-01-26', '2026-02-01 19:18:01'),
(47, 1, 'huevos', 'proteins', '7 ', 0, '2026-01-26', '2026-02-01 22:23:39'),
(48, 1, 'aguacate', 'fruits', '4 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(49, 1, 'tostadas', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(50, 1, 'Pechuga de pollo', 'proteins', '4 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(51, 1, 'arroz integral', 'grains', '4 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(52, 1, 'brócoli', 'vegetables', '5 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(53, 1, 'aceite de oliva', 'other', '6 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(54, 1, 'Pescado blanco', 'proteins', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(55, 1, 'tortillas de maíz', 'other', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(56, 1, 'repollo', 'proteins', '3 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(57, 1, 'limón', 'fruits', '4 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(58, 1, 'cilantro', 'other', '4 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(59, 1, 'Pan integral', 'grains', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(60, 1, 'huevo', 'proteins', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(61, 1, 'tomate', 'fruits', '4 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(62, 1, 'Pescado', 'proteins', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(63, 1, 'tortillas', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(64, 1, 'Yogurt griego', 'dairy', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(65, 1, 'granola', 'other', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(66, 1, 'miel', 'other', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(67, 1, 'arándanos', 'other', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(68, 1, 'Atún', 'proteins', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(69, 1, 'lechuga', 'vegetables', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(70, 1, 'pepino', 'vegetables', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(71, 1, 'maíz', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(72, 1, 'espinaca', 'vegetables', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(73, 1, 'queso', 'dairy', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(74, 1, '1/2 aguacate', 'fruits', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(75, 1, 'integrales', 'other', '2 tostadas', 0, '2026-01-26', '2026-02-01 22:23:39'),
(76, 1, 'Lentejas', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(77, 1, 'zanahoria', 'vegetables', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(78, 1, 'apio', 'vegetables', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(79, 1, 'plátano', 'fruits', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(80, 1, 'proteína en polvo', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(81, 1, 'leche de almendras', 'dairy', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(82, 1, 'Salmón', 'proteins', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(83, 1, 'papas', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(84, 1, 'Pechuga', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(85, 1, 'mostaza', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(86, 1, 'arroz', 'grains', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(87, 1, 'Tofu', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(88, 1, 'pimiento', 'other', '2 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(89, 1, 'salsa soya', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(90, 1, 'jengibre', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(91, 1, 'Quinoa', 'grains', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(92, 1, 'calabaza', 'other', '1 unidad', 0, '2026-01-26', '2026-02-01 22:23:39'),
(93, 1, 'Sal', 'other', '1 paquete', 0, '2026-01-26', '2026-02-01 22:23:39'),
(94, 1, 'Pimienta negra', 'other', '1 frasco', 0, '2026-01-26', '2026-02-01 22:23:39'),
(95, 1, 'Limones', 'fruits', '4 unidades', 0, '2026-01-26', '2026-02-01 22:23:39'),
(96, 1, 'Vinagre', 'other', '1 botella', 0, '2026-01-26', '2026-02-01 22:23:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `picture` varchar(500) DEFAULT NULL,
  `locale` varchar(10) DEFAULT 'es',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `google_id`, `email`, `name`, `picture`, `locale`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, '115979574504308393100', 'gandy3707@gmail.com', 'Andy Flores', 'https://lh3.googleusercontent.com/a/ACg8ocIidkp6NXTOCdieNJWdtQ2yMZVoTFfV5kASdmPOXKh7TcY-Cbr30A=s96-c', 'es', '2026-01-29 03:23:35', '2026-02-03 19:11:04', '2026-02-03 19:11:04', 1),
(2, '107762902449464493376', 'andygf3xyz@gmail.com', 'Andy GF', 'https://lh3.googleusercontent.com/a/ACg8ocJZCqWfkecvIpsstAh93YFemTMQ1rLu-XBDmxqTOcBn5jX49g=s96-c', 'es', '2026-01-29 03:28:36', '2026-02-02 01:55:53', '2026-02-02 01:55:53', 1),
(3, '101599929825463744376', 'user.wwwtecno@gmail.com', 'user', 'https://lh3.googleusercontent.com/a/ACg8ocLSJfBWf9sGuCST29bLp2YlhpLNkRb9PxpDbC0c0ZNTqoYm_w=s96-c', 'es', '2026-01-29 03:33:14', '2026-02-03 19:21:40', '2026-02-03 19:21:40', 1),
(8, '103019938598108543176', 'garciafloresandygael@gmail.com', 'Andy Gael Garcia Flores', 'https://lh3.googleusercontent.com/a/ACg8ocJM2fDeFBgsbxIeLNIMU2SgD0dERBpJrhxoB23qFrPSK6acWg=s96-c', 'es', '2026-01-30 22:31:40', '2026-02-01 02:05:02', '2026-02-01 02:05:02', 1),
(18, '107780669468721199726', 'superpancho952@gmail.com', 'super_pancho1', 'https://lh3.googleusercontent.com/a/ACg8ocJ6G-Kclm3IVXdLauB_lnNU8qZ7MwDVKwZH4GN8V1e-YMZo0A=s96-c', 'es', '2026-02-01 02:20:27', '2026-02-01 06:23:09', '2026-02-01 06:23:09', 1),
(42, 'demo_697fcadf7a092', 'demo@nutricion.com', 'Usuario Demo', 'https://ui-avatars.com/api/?name=Usuario+Demo&background=10B981&color=fff', 'es', '2026-02-01 21:51:27', '2026-02-01 21:51:27', '2026-02-01 21:51:27', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('meal_reminder','goal_achieved','streak_milestone','general') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `region` varchar(50) DEFAULT 'MX',
  `language` varchar(10) DEFAULT 'es',
  `weight_unit` enum('kg','lb') DEFAULT 'kg',
  `height_unit` enum('cm','ft') DEFAULT 'cm',
  `calorie_unit` enum('kcal','kj') DEFAULT 'kcal',
  `theme` enum('light','dark','auto') DEFAULT 'light',
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `region`, `language`, `weight_unit`, `height_unit`, `calorie_unit`, `theme`, `notifications_enabled`, `created_at`, `updated_at`) VALUES
(1, 8, 'MX', 'es', 'kg', 'cm', 'kcal', 'light', 1, '2026-01-30 22:47:37', '2026-01-30 22:47:37'),
(8, 1, 'MX', 'es', 'kg', 'cm', 'kcal', 'light', 1, '2026-01-30 23:01:48', '2026-01-30 23:01:48'),
(10, 2, 'MX', 'es', 'kg', 'cm', 'kcal', 'light', 1, '2026-02-01 00:47:12', '2026-02-01 00:47:12'),
(13, 18, 'MX', 'es', 'kg', 'cm', 'kcal', 'light', 1, '2026-02-01 02:21:27', '2026-02-01 02:21:27'),
(24, 42, 'MX', 'es', 'kg', 'cm', 'kcal', 'light', 1, '2026-02-01 21:51:27', '2026-02-01 21:51:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_streaks`
--

CREATE TABLE `user_streaks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_streak` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `last_activity_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_streaks`
--

INSERT INTO `user_streaks` (`id`, `user_id`, `current_streak`, `longest_streak`, `last_activity_date`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 1, '2026-01-30', '2026-01-30 22:47:37', '2026-01-30 22:47:37'),
(8, 1, 1, 1, '2026-01-30', '2026-01-30 23:01:48', '2026-01-30 23:01:48'),
(10, 2, 1, 1, '2026-01-31', '2026-02-01 00:47:12', '2026-02-01 00:47:12'),
(13, 18, 1, 1, '2026-01-31', '2026-02-01 02:21:27', '2026-02-01 02:21:27');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `assistant_conversations`
--
ALTER TABLE `assistant_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`);

--
-- Indices de la tabla `assistant_settings`
--
ALTER TABLE `assistant_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `calories_log`
--
ALTER TABLE `calories_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_plan_id` (`meal_plan_id`),
  ADD KEY `idx_user_date` (`user_id`,`date`);

--
-- Indices de la tabla `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_session` (`user_id`,`session_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indices de la tabla `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_calories` (`calories`);

--
-- Indices de la tabla `foods_database`
--
ALTER TABLE `foods_database`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_source` (`source`);

--
-- Indices de la tabla `food_images`
--
ALTER TABLE `food_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`analysis_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `food_scans`
--
ALTER TABLE `food_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`);

--
-- Indices de la tabla `health_profiles`
--
ALTER TABLE `health_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indices de la tabla `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_meal_type` (`meal_type`);

--
-- Indices de la tabla `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_meal_plan` (`user_id`,`date`,`meal_type`),
  ADD KEY `meal_id` (`meal_id`),
  ADD KEY `idx_user_date` (`user_id`,`date`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`);

--
-- Indices de la tabla `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_id` (`food_id`),
  ADD KEY `idx_meal_id` (`meal_id`);

--
-- Indices de la tabla `shopping_lists`
--
ALTER TABLE `shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_week` (`user_id`,`week_start`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- Indices de la tabla `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_scheduled` (`scheduled_for`);

--
-- Indices de la tabla `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `user_streaks`
--
ALTER TABLE `user_streaks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `assistant_conversations`
--
ALTER TABLE `assistant_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `assistant_settings`
--
ALTER TABLE `assistant_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `calories_log`
--
ALTER TABLE `calories_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT de la tabla `foods_database`
--
ALTER TABLE `foods_database`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `food_images`
--
ALTER TABLE `food_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `food_scans`
--
ALTER TABLE `food_scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `health_profiles`
--
ALTER TABLE `health_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=271;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `shopping_lists`
--
ALTER TABLE `shopping_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `user_streaks`
--
ALTER TABLE `user_streaks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `assistant_conversations`
--
ALTER TABLE `assistant_conversations`
  ADD CONSTRAINT `assistant_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `assistant_settings`
--
ALTER TABLE `assistant_settings`
  ADD CONSTRAINT `assistant_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `calories_log`
--
ALTER TABLE `calories_log`
  ADD CONSTRAINT `calories_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `calories_log_ibfk_2` FOREIGN KEY (`meal_plan_id`) REFERENCES `meal_plans` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `food_images`
--
ALTER TABLE `food_images`
  ADD CONSTRAINT `food_images_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `food_scans`
--
ALTER TABLE `food_scans`
  ADD CONSTRAINT `food_scans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `health_profiles`
--
ALTER TABLE `health_profiles`
  ADD CONSTRAINT `health_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meal_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plans_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `foods_database` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `shopping_lists`
--
ALTER TABLE `shopping_lists`
  ADD CONSTRAINT `shopping_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_streaks`
--
ALTER TABLE `user_streaks`
  ADD CONSTRAINT `user_streaks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
