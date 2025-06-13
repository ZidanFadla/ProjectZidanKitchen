-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 11, 2025 at 02:38 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zidankitchen`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$e8N7U46aYtNyMDqxORTJu.0BefDiPth2m9TvWDzjJNytUkAJampE6', '2025-03-25 16:47:18'),
(2, 'apuila', '$2y$10$yP2jqJSyBdVLgCrdQNqUj.ZO0GCsQjEtlK19/YdMc0F4H8ovbaDG6', '2025-03-25 17:11:04');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `table_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `table_id`, `created_at`) VALUES
(1, 'Putri', '0888-8888-8888', 2, '2025-04-07 08:58:21'),
(2, 'Putra', '0888-8888-8888', 1, '2025-04-07 09:59:25'),
(3, 'Mulyono', '087654321', 1, '2025-04-07 14:10:33'),
(4, 'Putri', '085161481518', 2, '2025-04-08 07:28:14'),
(5, 'Malih', '087654321', 1, '2025-04-09 04:07:07'),
(6, 'Huriyah', '087654321', 1, '2025-04-09 13:32:18'),
(7, 'azzahra', '087654321', 1, '2025-04-09 23:50:51'),
(8, 'Urfav', '087654321', 1, '2025-04-10 01:47:45'),
(9, 'Pak owo', '087654321', 1, '2025-04-10 02:05:07'),
(10, 'Pak owi', '087654321', 1, '2025-04-10 07:17:38'),
(11, 'Bruno Mars', '087654321', 1, '2025-04-10 15:32:47'),
(12, 'Sasuke', '087654321', 1, '2025-04-11 05:44:17'),
(13, 'ngab owi', '087654321', 1, '2025-04-11 08:01:55'),
(14, 'nganis', '087654321', 1, '2025-04-11 09:09:04'),
(15, 'nganjar', '087654321', 2, '2025-04-11 09:21:32'),
(16, 'Mulyadi', '087654321', 2, '2025-04-11 12:08:31'),
(17, 'Chris Evan', '0912834124', 3, '2025-04-11 12:13:16'),
(18, 'Hai', '08123456789', 1, '2025-04-11 12:20:23'),
(19, 'Ajra ganteng', '6969696969', 1, '2025-04-11 12:24:39'),
(20, 'Chris Evan', '0912834124', 2, '2025-04-11 12:30:40'),
(21, 'Mulyono Doang', '087654321', 2, '2025-04-11 12:46:31'),
(22, 'Christ', '087654321', 2, '2025-04-11 12:56:56'),
(23, 'Evan', '313546', 3, '2025-04-11 13:06:16');

-- --------------------------------------------------------

--
-- Table structure for table `meja`
--

CREATE TABLE `meja` (
  `id_meja` int NOT NULL,
  `current_order_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meja`
--

INSERT INTO `meja` (`id_meja`, `current_order_id`) VALUES
(1, NULL),
(2, NULL),
(3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int NOT NULL,
  `nama_menu` varchar(255) NOT NULL,
  `kategori_menu` enum('makanan','minuman','dessert') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `model_3d` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `harga_promo` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `kategori_menu`, `harga`, `gambar`, `model_3d`, `created_at`, `updated_at`, `harga_promo`) VALUES
(1, 'Sushi asli Jepun🍣', 'makanan', '50000.00', 'sushi.png', 'sushi/scene.gltf', '2025-03-22 14:32:46', '2025-04-05 17:47:38', NULL),
(2, 'Birthday Cake', 'dessert', '150000.00', 'birthdayCake.jpeg', 'birthdayCake/scene.gltf', '2025-03-22 17:28:12', '2025-03-22 17:30:46', NULL),
(3, 'Coca Cola', 'minuman', '10000.00', 'cocaCola.jpeg', 'cocaCola/scene.gltf', '2025-03-25 13:03:21', NULL, NULL),
(4, 'Gâteau Basque', 'dessert', '150000.00', 'Gâteau Basque.jpeg', 'gâteau_basque/scene.gltf', '2025-03-26 05:52:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `id_meja` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `metode_pembayaran` varchar(20) DEFAULT NULL,
  `status` enum('pending','paid','failed','done') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `snap_token` varchar(255) DEFAULT NULL,
  `midtrans_order_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `id_meja`, `customer_id`, `total_harga`, `metode_pembayaran`, `status`, `snap_token`, `midtrans_order_id`, `created_at`) VALUES
(1, 1, NULL, '210000.00', 'Cash', 'done', NULL, NULL, '2025-03-25 11:47:54'),
(2, 1, NULL, '210000.00', 'QRIS', 'done', NULL, NULL, '2025-03-25 11:48:16'),
(3, 1, NULL, '100000.00', 'QRIS', 'done', NULL, NULL, '2025-03-25 12:17:46'),
(4, 1, NULL, '40000.00', 'Cash', 'done', NULL, NULL, '2025-03-25 12:20:13'),
(5, 1, NULL, '40000.00', 'QRIS', 'failed', NULL, NULL, '2025-03-25 12:22:21'),
(6, 2, NULL, '40000.00', 'QRIS', 'done', NULL, NULL, '2025-03-25 12:37:55'),
(7, 3, NULL, '200000.00', 'E-Wallet', 'done', NULL, NULL, '2025-03-26 00:42:30'),
(8, 3, NULL, '350000.00', 'QRIS', 'done', NULL, NULL, '2025-03-26 01:51:35'),
(9, 2, NULL, '160000.00', 'QRIS', 'done', NULL, NULL, '2025-04-05 12:54:20'),
(10, 1, NULL, '5000.00', 'QRIS', 'done', NULL, NULL, '2025-04-06 06:01:57'),
(11, 1, NULL, '405000.00', 'QRIS', 'done', NULL, NULL, '2025-04-06 08:16:19'),
(12, 1, NULL, '5000.00', 'QRIS', 'done', NULL, NULL, '2025-04-06 08:39:12'),
(13, 3, NULL, '465000.00', 'QRIS', 'done', NULL, NULL, '2025-04-06 16:46:20'),
(14, 2, NULL, '355000.00', 'QRIS', 'done', NULL, NULL, '2025-04-07 05:29:24'),
(15, 2, NULL, '5000.00', 'QRIS', 'done', NULL, NULL, '2025-04-07 08:58:52'),
(17, 1, 2, '255000.00', 'QRIS', 'done', 'c41a2303-b5fb-487f-aa58-5fa7da703139', NULL, '2025-04-07 10:49:48'),
(18, 1, 3, '150000.00', 'QRIS', 'done', '2e7217f7-ae7e-4cbb-8612-0af3622bd038', NULL, '2025-04-07 14:10:49'),
(19, 1, 5, '207000.00', 'Cash', 'done', '5833d024-40c5-4615-9a3f-0bded7eb68f0', NULL, '2025-04-09 04:08:15'),
(20, 1, 7, '160000.00', 'Cash', 'done', '27ff7915-d1e8-47c8-bf66-c4e2a7d3d9b1', NULL, '2025-04-10 01:21:29'),
(21, 1, 8, '10000.00', 'Cash', 'done', NULL, NULL, '2025-04-10 01:49:16'),
(22, 1, 8, '150000.00', 'Cash', 'done', NULL, NULL, '2025-04-10 01:49:33'),
(23, 1, 9, '150000.00', 'Cash', 'done', NULL, NULL, '2025-04-10 02:05:22'),
(24, 1, 10, '10000.00', 'Cash', 'done', NULL, NULL, '2025-04-10 07:18:14'),
(25, 1, 10, '150000.00', 'Cash', 'pending', NULL, NULL, '2025-04-10 07:19:48'),
(26, 1, 10, '150000.00', 'Cash', 'pending', NULL, NULL, '2025-04-10 07:21:34'),
(27, 1, 10, '300000.00', 'Cash', 'pending', NULL, NULL, '2025-04-10 07:21:55'),
(28, 1, 10, '160000.00', 'Cash', 'pending', NULL, NULL, '2025-04-10 07:48:42'),
(29, 1, 10, '310000.00', 'Cash', 'pending', NULL, NULL, '2025-04-10 07:48:56'),
(30, 1, 11, '150000.00', NULL, 'failed', NULL, NULL, '2025-04-10 15:32:59'),
(31, 1, 11, '300000.00', NULL, 'failed', NULL, NULL, '2025-04-10 16:06:52'),
(32, 1, 11, '200000.00', NULL, 'failed', NULL, NULL, '2025-04-10 16:10:10'),
(33, 1, 12, '160000.00', NULL, 'pending', 'fd47c2a2-e9f7-4597-872d-a985c91eb832', 'ZK-33-1744350501', '2025-04-11 05:44:34'),
(34, 1, 13, '100000.00', NULL, 'paid', '073d6439-8efb-4a2e-ab24-d5cd1e317a32', 'ZK-34-1744359591', '2025-04-11 08:15:14'),
(35, 1, 13, '150000.00', NULL, 'pending', '9a134229-1267-4867-9a90-81dcb24fbd39', 'ZK-35-1744361213', '2025-04-11 08:46:52'),
(36, 1, 14, '10000.00', NULL, 'done', '05b8fba5-df13-4572-81e3-71cc7a3d49a3', 'ZK-36-1744362661', '2025-04-11 09:11:01'),
(37, 2, 15, '50000.00', NULL, 'done', 'ccaf2923-3a17-49d6-a25e-bfbb14204698', 'ZK-37-1744363301', '2025-04-11 09:21:41'),
(38, 2, 15, '150000.00', NULL, 'done', 'a6b9f7aa-447c-498b-9d41-e1f25584a029', 'ZK-38-1744371329', '2025-04-11 11:35:29'),
(39, 2, 16, '150000.00', NULL, 'done', '81e4422c-3beb-447b-9226-af21f9139d8a', 'ZK-39-1744373327', '2025-04-11 12:08:47'),
(40, 3, 17, '460000.00', NULL, 'done', '19208f33-febf-4bba-9df8-849d1fe641a1', 'ZK-40-1744373616', '2025-04-11 12:13:36'),
(41, 1, 18, '150000.00', NULL, 'done', '833154e8-9f50-456e-9b23-97340cd097f2', 'ZK-41-1744374086', '2025-04-11 12:21:26'),
(42, 1, 19, '160000.00', NULL, 'done', '13e59232-3cf8-4fcd-940a-41986e952113', 'ZK-42-1744374421', '2025-04-11 12:27:00'),
(43, 2, 16, '300000.00', NULL, 'done', 'b9b5551b-3411-46d9-9138-9fe059c81f68', 'ZK-43-1744375174', '2025-04-11 12:39:34'),
(44, 2, 21, '500000.00', NULL, 'done', 'dd2a5e18-05cb-42c9-b0b3-63a9c52aee8e', 'ZK-44-1744375628', '2025-04-11 12:47:08'),
(45, 2, 22, '160000.00', NULL, 'pending', '8460551f-459f-49c4-9fcc-9c09807d24a8', 'ZK-45-1744376234', '2025-04-11 12:57:14'),
(46, 2, 22, '160000.00', NULL, 'done', '0b58ccb4-e9e7-4418-9733-9593b80c73ba', 'ZK-46-1744376275', '2025-04-11 12:57:54'),
(47, 3, 23, '150000.00', NULL, 'done', 'f7f0afd2-6afd-4f84-8a8e-5f50ae40d836', 'ZK-47-1744376812', '2025-04-11 13:06:51');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `id_menu` int NOT NULL,
  `nama_menu` varchar(255) NOT NULL,
  `jumlah` int NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `id_menu`, `nama_menu`, `jumlah`, `harga`, `subtotal`) VALUES
(1, 1, 1, 'sushi', 1, '50000.00', '50000.00'),
(2, 1, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(3, 1, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(4, 2, 1, 'sushi', 1, '50000.00', '50000.00'),
(5, 2, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(6, 2, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(7, 3, 1, 'sushi', 2, '50000.00', '100000.00'),
(8, 4, 1, 'sushi', 1, '40000.00', '40000.00'),
(9, 5, 1, 'sushi', 1, '40000.00', '40000.00'),
(10, 6, 1, 'sushi', 1, '40000.00', '40000.00'),
(11, 7, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(12, 7, 1, 'sushi', 1, '40000.00', '40000.00'),
(13, 7, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(14, 8, 1, 'sushi', 1, '40000.00', '40000.00'),
(15, 8, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(16, 8, 4, 'Gâteau Basque', 2, '150000.00', '300000.00'),
(17, 9, 3, 'Coca Cola', 2, '5000.00', '10000.00'),
(18, 9, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(19, 10, 3, 'Coca Cola', 1, '5000.00', '5000.00'),
(20, 11, 1, 'Sushi asli Jepun🍣', 2, '50000.00', '100000.00'),
(21, 11, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(22, 11, 3, 'Coca Cola', 1, '5000.00', '5000.00'),
(23, 11, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(24, 12, 3, 'Coca Cola', 1, '5000.00', '5000.00'),
(25, 13, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(26, 13, 1, 'Sushi asli Jepun🍣', 4, '50000.00', '150000.00'),
(27, 13, 3, 'Coca Cola', 4, '5000.00', '15000.00'),
(28, 13, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(29, 14, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(30, 14, 3, 'Coca Cola', 1, '5000.00', '5000.00'),
(31, 14, 1, 'Sushi asli Jepun🍣', 1, '50000.00', '50000.00'),
(32, 14, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(33, 15, 3, 'Coca Cola', 1, '5000.00', '5000.00'),
(36, 17, 3, 'Coca Cola', 1, '5000.00', '5000.00'),
(37, 17, 1, 'Sushi asli Jepun🍣', 3, '50000.00', '100000.00'),
(38, 17, 1, 'Sushi asli Jepun🍣 (Gratis)', 1, '0.00', '0.00'),
(39, 17, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(40, 18, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(41, 19, 1, 'Sushi asli Jepun🍣', 1, '50000.00', '50000.00'),
(42, 19, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(43, 19, 3, 'Coca Cola', 1, '7000.00', '7000.00'),
(44, 20, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(45, 20, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(46, 21, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(47, 22, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(48, 23, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(49, 24, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(50, 25, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(51, 26, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(52, 27, 2, 'Birthday Cake', 2, '150000.00', '300000.00'),
(53, 28, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(54, 28, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(55, 29, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(56, 29, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(57, 29, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(58, 30, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(59, 31, 4, 'Gâteau Basque', 2, '150000.00', '300000.00'),
(60, 32, 1, 'Sushi asli Jepun🍣', 1, '50000.00', '50000.00'),
(61, 32, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(62, 33, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(63, 33, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(64, 34, 1, 'Sushi asli Jepun🍣', 2, '50000.00', '100000.00'),
(65, 35, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(66, 36, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(67, 37, 1, 'Sushi asli Jepun🍣', 1, '50000.00', '50000.00'),
(68, 38, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(69, 39, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(70, 40, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(71, 40, 4, 'Gâteau Basque', 2, '150000.00', '300000.00'),
(72, 40, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(73, 41, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(74, 42, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(75, 42, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(76, 43, 2, 'Birthday Cake', 2, '150000.00', '300000.00'),
(77, 44, 4, 'Gâteau Basque', 1, '150000.00', '150000.00'),
(78, 44, 2, 'Birthday Cake', 2, '150000.00', '300000.00'),
(79, 44, 1, 'Sushi asli Jepun🍣', 1, '50000.00', '50000.00'),
(80, 45, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(81, 45, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(82, 46, 3, 'Coca Cola', 1, '10000.00', '10000.00'),
(83, 46, 2, 'Birthday Cake', 1, '150000.00', '150000.00'),
(84, 47, 4, 'Gâteau Basque', 1, '150000.00', '150000.00');

-- --------------------------------------------------------

--
-- Table structure for table `promos`
--

CREATE TABLE `promos` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `discount` int DEFAULT NULL,
  `promo_type` enum('bundle','discount') NOT NULL,
  `bundle_price` int DEFAULT NULL,
  `bundle_items` json DEFAULT NULL,
  `bundle_discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `bundle_discount_value` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `menu_target` json DEFAULT NULL COMMENT 'Array of menu IDs that this promo applies to'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `promos`
--

INSERT INTO `promos` (`id`, `title`, `description`, `discount`, `promo_type`, `bundle_price`, `bundle_items`, `bundle_discount_type`, `bundle_discount_value`, `image`, `created_at`, `updated_at`, `start_date`, `end_date`, `menu_target`) VALUES
(3, 'Ramadhan Kareem! 🕌', 'Nikmati promo bukber hingga 70% hanya di ZidanKitchen! 🤑😍', 30, 'discount', 0, NULL, 'percentage', NULL, 'promoBukber.jpeg', '2025-04-05 15:02:49', '2025-04-05 15:03:31', '2025-04-04', '2025-05-09', NULL),
(4, 'Eid Mubarak 2025!', 'blablabla', 50, 'discount', 0, NULL, 'percentage', NULL, 'promoEid.png', '2025-04-05 15:07:32', NULL, '2025-03-31', '2025-04-07', NULL),
(10, 'tes bundle', 'tesbundle', 0, 'bundle', NULL, '[\"1\", \"2\", \"3\"]', 'percentage', '20.00', 'default.png', '2025-04-10 00:22:42', NULL, '2025-04-10', '2025-04-30', NULL),
(11, 'tes diskon', 'tesdiskon', 0, 'discount', NULL, NULL, 'percentage', NULL, 'default.png', '2025-04-10 06:26:50', NULL, '2025-04-10', '2025-04-30', NULL),
(12, 'tes bundle lagi', 'tes', 0, 'bundle', NULL, '[\"3\", \"4\"]', 'percentage', '30.00', 'default.png', '2025-04-10 06:32:26', NULL, '2025-04-10', '2025-04-30', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD KEY `fk_meja_order` (`current_order_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `meja` (`id_meja`);

--
-- Constraints for table `meja`
--
ALTER TABLE `meja`
  ADD CONSTRAINT `fk_meja_order` FOREIGN KEY (`current_order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
