-- phpMyAdmin SQL Dump
-- version 5.2.3-1.fc43
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 23, 2026 at 01:49 AM
-- Server version: 8.4.8
-- PHP Version: 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `green_grounds_coffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'User logged in', NULL, '2026-02-22 09:30:01'),
(2, 1, 'login', 'User logged in', NULL, '2026-02-22 09:46:05'),
(3, 1, 'login', 'User logged in', NULL, '2026-02-22 09:46:40'),
(4, 1, 'logout', 'User logged out', NULL, '2026-02-22 09:52:52'),
(5, 1, 'login', 'User logged in', NULL, '2026-02-22 09:53:45'),
(6, 1, 'logout', 'User logged out', NULL, '2026-02-22 09:53:47'),
(7, 1, 'login', 'User logged in', NULL, '2026-02-22 09:54:09'),
(8, 1, 'logout', 'User logged out', NULL, '2026-02-22 09:54:10'),
(9, 1, 'login', 'User logged in', NULL, '2026-02-22 09:54:16'),
(10, 1, 'logout', 'User logged out', NULL, '2026-02-22 09:55:05'),
(11, 1, 'login', 'User logged in', NULL, '2026-02-22 09:55:15'),
(12, 1, 'logout', 'User logged out', NULL, '2026-02-22 09:57:38'),
(13, 1, 'login', 'User logged in', NULL, '2026-02-22 09:57:43'),
(14, 1, 'logout', 'User logged out', NULL, '2026-02-22 10:10:30'),
(15, 2, 'login', 'User logged in', NULL, '2026-02-22 10:12:55'),
(16, 2, 'login', 'User logged in', NULL, '2026-02-22 10:14:07'),
(17, 2, 'create_order', 'Order created: #RCP699AD9A13972E', NULL, '2026-02-22 10:25:37'),
(18, 2, 'create_order', 'Order created: #RCP699AD9E9AD755', NULL, '2026-02-22 10:26:49'),
(19, 2, 'logout', 'User logged out', NULL, '2026-02-22 10:30:59'),
(20, 4, 'login', 'User logged in', NULL, '2026-02-22 10:35:59'),
(21, 4, 'login', 'User logged in', NULL, '2026-02-22 10:36:06'),
(22, 2, 'login', 'User logged in', NULL, '2026-02-22 10:36:10'),
(23, 2, 'logout', 'User logged out', NULL, '2026-02-22 10:36:11'),
(24, 4, 'login', 'User logged in', NULL, '2026-02-22 10:36:14'),
(25, 1, 'login', 'User logged in', NULL, '2026-02-22 10:39:10'),
(26, 1, 'logout', 'User logged out', NULL, '2026-02-22 10:40:53'),
(27, 1, 'login', 'User logged in', NULL, '2026-02-22 10:40:57'),
(28, 1, 'login', 'User logged in', NULL, '2026-02-23 01:33:05'),
(29, 1, 'logout', 'User logged out', NULL, '2026-02-23 01:33:34'),
(30, 2, 'login', 'User logged in', NULL, '2026-02-23 01:33:38'),
(31, 2, 'create_order', 'Order created: #RCP699BAE9D90B27', NULL, '2026-02-23 01:34:21'),
(32, 2, 'create_order', 'Order created: #RCP699BAF6E5C798', NULL, '2026-02-23 01:37:50'),
(33, 2, 'logout', 'User logged out', NULL, '2026-02-23 01:37:53'),
(34, 1, 'login', 'User logged in', NULL, '2026-02-23 01:37:58'),
(35, 1, 'logout', 'User logged out', NULL, '2026-02-23 01:38:44');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50) DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `display_order`, `created_at`) VALUES
(1, 'Coffee', NULL, '☕', 1, '2026-02-22 15:51:15'),
(2, 'Tea', NULL, '🍵', 2, '2026-02-22 15:51:15'),
(3, 'Snacks', NULL, '🍪', 3, '2026-02-22 15:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','digital') DEFAULT 'cash',
  `status` enum('completed','cancelled') DEFAULT 'completed',
  `customer_name` varchar(255) DEFAULT NULL,
  `table_number` varchar(10) DEFAULT NULL,
  `order_type` enum('dine-in','takeaway','delivery') DEFAULT 'dine-in',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `receipt_number`, `subtotal`, `tax`, `discount`, `total`, `payment_method`, `status`, `customer_name`, `table_number`, `order_type`, `notes`, `created_at`) VALUES
(1, 2, '#RCP699AD9A13972E', 8.50, 0.85, 0.00, 9.35, 'digital', 'completed', 'Walk-in Customer', '', 'takeaway', '', '2026-02-22 10:25:37'),
(2, 2, '#RCP699AD9E9AD755', 8.50, 0.85, 0.00, 9.35, 'digital', 'completed', 'Walk-in Customer', '', 'takeaway', '', '2026-02-22 10:26:49'),
(3, 2, '#RCP699BAE9D90B27', 58.10, 5.81, 0.00, 63.91, 'digital', 'completed', 'Walk-in Customer', '', 'delivery', '', '2026-02-23 01:34:21'),
(4, 2, '#RCP699BAF6E5C798', 58.10, 5.81, 0.00, 63.91, 'digital', 'completed', 'Walk-in Customer', '', 'delivery', '', '2026-02-23 01:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `special_instructions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `total`, `special_instructions`, `created_at`) VALUES
(1, 1, 34, 1, 4.50, 4.50, NULL, '2026-02-22 10:25:37'),
(2, 1, 42, 1, 4.00, 4.00, NULL, '2026-02-22 10:25:37'),
(3, 2, 34, 1, 4.50, 4.50, NULL, '2026-02-22 10:26:49'),
(4, 2, 42, 1, 4.00, 4.00, NULL, '2026-02-22 10:26:49'),
(5, 3, 31, 6, 5.30, 31.80, NULL, '2026-02-23 01:34:21'),
(6, 3, 40, 1, 4.00, 4.00, NULL, '2026-02-23 01:34:21'),
(7, 3, 2, 1, 4.00, 4.00, NULL, '2026-02-23 01:34:21'),
(8, 3, 28, 2, 4.00, 8.00, NULL, '2026-02-23 01:34:21'),
(9, 3, 34, 1, 4.50, 4.50, NULL, '2026-02-23 01:34:21'),
(10, 3, 8, 1, 5.80, 5.80, NULL, '2026-02-23 01:34:21'),
(11, 4, 31, 6, 5.30, 31.80, NULL, '2026-02-23 01:37:50'),
(12, 4, 40, 1, 4.00, 4.00, NULL, '2026-02-23 01:37:50'),
(13, 4, 2, 1, 4.00, 4.00, NULL, '2026-02-23 01:37:50'),
(14, 4, 28, 2, 4.00, 8.00, NULL, '2026-02-23 01:37:50'),
(15, 4, 34, 1, 4.50, 4.50, NULL, '2026-02-23 01:37:50'),
(16, 4, 8, 1, 5.80, 5.80, NULL, '2026-02-23 01:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `sku` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `cost`, `quantity`, `sku`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Espresso', NULL, 4.20, 1.50, 50, 'COFFEE-001', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(2, 1, 'Americano', NULL, 4.00, 1.30, 43, 'COFFEE-002', NULL, 'available', '2026-02-22 15:51:19', '2026-02-23 01:37:50'),
(3, 1, 'Cappuccino', NULL, 5.20, 2.00, 40, 'COFFEE-003', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(4, 1, 'Latte', NULL, 5.00, 1.90, 38, 'COFFEE-004', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(5, 1, 'Mocha', NULL, 5.50, 2.20, 35, 'COFFEE-005', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(6, 1, 'Iced Coffee Milk', NULL, 5.30, 1.80, 30, 'COFFEE-006', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(7, 1, 'Cold Brew', NULL, 4.80, 1.60, 25, 'COFFEE-007', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(8, 1, 'Caramel Mac', NULL, 5.80, 2.10, 26, 'COFFEE-008', NULL, 'available', '2026-02-22 15:51:19', '2026-02-23 01:37:50'),
(9, 1, 'Salted Caramel', NULL, 5.40, 2.00, 32, 'COFFEE-009', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(10, 1, 'Hazelnut Latte', NULL, 5.20, 1.90, 29, 'COFFEE-010', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(11, 1, 'Flat White', NULL, 5.10, 1.80, 27, 'COFFEE-011', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(12, 1, 'Pour Over', NULL, 4.50, 1.40, 22, 'COFFEE-012', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(13, 2, 'Green Jasmine Tea', NULL, 4.00, 1.20, 50, 'TEA-001', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(14, 2, 'Earl Grey', NULL, 4.50, 1.30, 45, 'TEA-002', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(15, 2, 'Chamomile', NULL, 3.80, 1.10, 40, 'TEA-003', NULL, 'available', '2026-02-22 15:51:19', '2026-02-22 15:51:19'),
(16, 2, 'Peppermint Tea', NULL, 4.00, 1.20, 38, 'TEA-004', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(17, 2, 'Hibiscus Berry Tea', NULL, 4.20, 1.25, 35, 'TEA-005', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(18, 2, 'Darjeeling', NULL, 4.00, 1.20, 32, 'TEA-006', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(19, 2, 'Genmaicha', NULL, 3.80, 1.10, 28, 'TEA-007', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(20, 2, 'Sencha', NULL, 4.00, 1.20, 30, 'TEA-008', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(21, 2, 'White Peony', NULL, 4.20, 1.25, 26, 'TEA-009', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(22, 2, 'Lemon Ginger', NULL, 3.50, 1.00, 24, 'TEA-010', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(23, 2, 'Moroccan Mint', NULL, 4.00, 1.20, 28, 'TEA-011', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(24, 2, 'Lapsang Souchong', NULL, 4.50, 1.35, 20, 'TEA-012', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(25, 2, 'Dragon Well', NULL, 5.00, 1.50, 18, 'TEA-013', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(26, 2, 'Lemongrass Tea', NULL, 3.50, 1.00, 22, 'TEA-014', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(27, 2, 'Rooibos Chai', NULL, 4.00, 1.20, 25, 'TEA-015', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(28, 3, 'Avocado Toast', NULL, 4.00, 1.50, 16, 'SNACK-001', NULL, 'available', '2026-02-22 15:51:20', '2026-02-23 01:37:50'),
(29, 3, 'Quinoa Salad', NULL, 5.50, 2.20, 18, 'SNACK-002', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(30, 3, 'Hummus Plate', NULL, 3.50, 1.30, 22, 'SNACK-003', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(31, 3, 'Acai Bowl', NULL, 5.30, 2.00, 3, 'SNACK-004', NULL, 'available', '2026-02-22 15:51:20', '2026-02-23 01:37:50'),
(32, 3, 'Vegan Energy', NULL, 5.00, 1.90, 17, 'SNACK-005', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(33, 3, 'Spinach Feta Pastry', NULL, 4.00, 1.50, 19, 'SNACK-006', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(34, 3, 'Banana Bread', NULL, 4.50, 1.60, 16, 'SNACK-007', NULL, 'available', '2026-02-22 15:51:20', '2026-02-23 01:37:50'),
(35, 3, 'Quiche Lorraine', NULL, 3.80, 1.40, 16, 'SNACK-008', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(36, 3, 'Coconut Macaroons', NULL, 4.00, 1.50, 25, 'SNACK-009', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(37, 3, 'Greek Yogurt Parfait', NULL, 4.20, 1.60, 14, 'SNACK-010', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(38, 3, 'Cheese Scones', NULL, 4.00, 1.50, 18, 'SNACK-011', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(39, 3, 'Sweet Potato Fries', NULL, 3.80, 1.40, 17, 'SNACK-012', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(40, 3, 'Almond Butter Toast', NULL, 4.00, 1.50, 17, 'SNACK-013', NULL, 'available', '2026-02-22 15:51:20', '2026-02-23 01:37:50'),
(41, 3, 'Veggie Plate', NULL, 3.80, 1.40, 16, 'SNACK-014', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 15:51:20'),
(42, 3, 'Blueberry Muffin', NULL, 4.00, 1.50, 20, 'SNACK-015', NULL, 'available', '2026-02-22 15:51:20', '2026-02-22 10:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','digital') DEFAULT 'cash',
  `status` enum('completed','pending','failed') DEFAULT 'completed',
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `user_id`, `amount`, `payment_method`, `status`, `reference_number`, `created_at`) VALUES
(1, 1, 2, 9.35, 'digital', 'completed', NULL, '2026-02-22 10:25:37'),
(2, 2, 2, 9.35, 'digital', 'completed', NULL, '2026-02-22 10:26:49'),
(3, 3, 2, 63.91, 'digital', 'completed', NULL, '2026-02-23 01:34:21'),
(4, 4, 2, 63.91, 'digital', 'completed', NULL, '2026-02-23 01:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','manager') DEFAULT 'cashier',
  `status` enum('active','inactive') DEFAULT 'active',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `phone`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'Admin User', 'admin@greengrounds.local', '$2y$12$PUnfqjjyDh50bnOl6pTsD.GIU2D7WN9PmxB7pffKWDOkDSMLx2jum', 'admin', 'active', NULL, '2026-02-22 15:51:16', '2026-02-23 01:37:58', '2026-02-23 01:37:58'),
(2, 'Sarah Johnson', 'sarah@greengrounds.local', '$2y$12$v//rPuB0Gewc9qRRGiM2QO8HTQ2Cyz7/3p7xP/lH6Ajjk0D0b2/dC', 'cashier', 'active', NULL, '2026-02-22 15:51:20', '2026-02-23 01:33:38', '2026-02-23 01:33:38'),
(3, 'Mike Chen', 'mike@greengrounds.local', '$2y$12$IE32N7PaLpm7MFJnfxxXVOrhT5iOYFQktnRKvt1ym7LRy5sIgVK66', 'cashier', 'active', NULL, '2026-02-22 15:51:20', '2026-02-22 15:51:20', NULL),
(4, 'Emma Davis', 'emma@greengrounds.local', '$2y$12$nTtpkwCprMwePe8EB.dYiOnqwRa40xEv0fiC1vG3WOTmAKB.E.5WC', 'manager', 'active', NULL, '2026-02-22 15:51:20', '2026-02-22 10:36:14', '2026-02-22 10:36:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_date` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_receipt` (`receipt_number`),
  ADD KEY `idx_date` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
