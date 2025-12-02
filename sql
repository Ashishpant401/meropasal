-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 01:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `new`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('home','work','other') DEFAULT 'home',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@meropasal.com', '$2y$10$30rf8QgD6DA7TICo0pfQ1ueUYmoTfADSpSvcbtu0L8y2BETNQl/iW', 'Super Admin', 'super_admin', 1, '2025-11-02 05:54:14', '2025-10-03 15:13:15');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Nike', 'nike', NULL, '2025-10-07 10:13:42', '2025-10-07 10:13:42'),
(2, 'Adidas', 'adidas', NULL, '2025-10-07 10:13:42', '2025-10-07 10:13:42'),
(3, 'Puma', 'puma', NULL, '2025-10-07 10:13:42', '2025-10-07 10:13:42'),
(4, 'H&M', 'h&m', NULL, '2025-10-07 10:13:42', '2025-10-07 10:13:42'),
(5, 'Zara', 'zara', NULL, '2025-10-07 10:13:42', '2025-10-07 10:13:42'),
(6, 'Goldstar', 'goldstar', '', '2025-10-07 13:55:26', '2025-10-07 13:55:26'),
(7, 'Air jorden', 'air-jorden', '', '2025-10-24 03:15:22', '2025-10-24 03:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `selected` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `size`, `color`, `created_at`, `updated_at`, `selected`) VALUES
(40, 3, 16, 1, NULL, NULL, '2025-10-12 07:09:28', '2025-10-12 07:15:05', 0),
(41, 3, 18, 1, NULL, NULL, '2025-10-12 07:10:19', '2025-10-12 07:10:28', 0),
(51, 4, 18, 2, 'L', 'Brown', '2025-10-14 13:59:12', '2025-10-14 13:59:12', 1),
(70, 1, 17, 1, NULL, NULL, '2025-10-23 03:48:47', '2025-10-23 03:50:26', 0),
(73, 1, 13, 1, NULL, NULL, '2025-10-23 03:49:59', '2025-11-02 05:53:36', 0),
(74, 1, 18, 1, NULL, NULL, '2025-11-02 05:53:29', '2025-11-02 05:53:35', 0);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'T-Shirts', 't-shirts', NULL, '2025-10-07 09:32:10', '2025-10-07 09:32:10'),
(2, 'Shirts', 'shirts', NULL, '2025-10-07 09:32:10', '2025-10-07 09:32:10'),
(3, 'Pants', 'pants', NULL, '2025-10-07 09:32:10', '2025-10-07 09:32:10'),
(4, 'Shoes', 'shoes', NULL, '2025-10-07 09:32:10', '2025-10-07 09:32:10'),
(5, 'Dresses', 'dresses', NULL, '2025-10-07 09:32:10', '2025-10-07 09:32:10'),
(6, 'jacket', 'jacket', 'djwheewufeb ewe vefeferferer f', '2025-10-07 09:33:19', '2025-10-07 09:33:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `order_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `shipping_address`, `payment_method`, `first_name`, `last_name`, `email`, `phone`, `order_notes`, `created_at`, `updated_at`) VALUES
(3, 1, 'ORD20251007070357930', 2735.80, 'processing', 'mahendrangr', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-10-07 05:03:57', '2025-10-07 09:18:48'),
(4, 2, 'ORD20251007122215254', 4082.20, 'delivered', 'Harpal', 'cash_on_delivery', 'Manoj ', 'Pant', 'manojpant186@gmail.com', '9767450895', 'Happy ', '2025-10-07 10:22:15', '2025-10-13 07:40:09'),
(8, 3, 'ORD20251012091115445', 1438.90, 'cancelled', 'aithpur', 'bank_transfer', 'Abishek ', 'lighting ', 'allwooddragon@gmail.com', '9844578010', 'I want black colour ', '2025-10-12 07:11:15', '2025-10-13 07:39:35'),
(11, 1, 'ORD20251013094159786', 2867.80, 'pending', 'mahendrangr', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-10-13 07:41:59', '2025-10-13 07:41:59'),
(12, 1, 'ORD20251015084357590', 1438.90, 'pending', 'mahendrangr', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-10-15 06:43:57', '2025-10-15 06:43:57'),
(13, 1, 'ORD20251015084941813', 4082.20, 'pending', 'mahendrangr', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-10-15 06:49:41', '2025-10-15 06:49:41'),
(14, 1, 'ORD20251019043829385', 1990.00, 'pending', 'mahendrangr', 'paypal', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-10-19 02:38:29', '2025-10-19 02:38:29'),
(15, 1, 'ORD20251024033853484', 1990.00, 'pending', 'mahendrangra', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-10-24 01:38:53', '2025-10-24 01:38:53'),
(16, 1, 'ORD20251102001856751', 4960.00, 'pending', 'mahendrangar', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-11-01 23:18:56', '2025-11-01 23:18:56'),
(17, 1, 'ORD20251102001938406', 4960.00, 'pending', 'mahendrangar', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-11-01 23:19:38', '2025-11-01 23:19:38'),
(18, 1, 'ORD20251102065353476', 1438.90, 'pending', 'mahendrangAR', 'cash_on_delivery', 'Ashish', 'pant', 'ashishpant401@gmail.com', '9862467605', '', '2025-11-02 05:53:53', '2025-11-02 05:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `attribute_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `attribute_id`, `quantity`, `price`, `size`, `color`) VALUES
(4, 3, 13, NULL, 1, 1234.00, NULL, NULL),
(5, 3, 11, NULL, 1, 1244.00, NULL, NULL),
(6, 4, 13, NULL, 3, 1234.00, NULL, NULL),
(8, 8, 18, NULL, 1, 1299.00, 'L', NULL),
(11, 11, 18, NULL, 2, 1299.00, NULL, NULL),
(12, 12, 18, NULL, 1, 1299.00, 'L', 'Brown'),
(13, 13, 13, NULL, 3, 1234.00, 'L', 'Blue'),
(14, 14, 17, NULL, 1, 1800.00, NULL, NULL),
(15, 15, 17, NULL, 1, 1800.00, '7', 'Blue'),
(16, 16, 20, NULL, 1, 4500.00, '28', 'White'),
(17, 17, 20, NULL, 1, 4500.00, '28', 'White'),
(18, 18, 14, NULL, 1, 1299.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `changed_by`, `changed_at`, `notes`) VALUES
(1, 4, 'delivered', 1, '2025-10-07 10:22:42', NULL),
(2, 8, 'delivered', NULL, '2025-10-12 07:12:16', NULL),
(4, 8, 'cancelled', 1, '2025-10-13 07:39:35', NULL),
(5, 4, 'delivered', 1, '2025-10-13 07:40:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `gender` enum('men','women','kids') DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_featured` tinyint(1) DEFAULT 0,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `old_price`, `category_id`, `brand_id`, `gender`, `image`, `stock`, `created_at`, `is_featured`, `deleted`) VALUES
(11, 'kid shoe', 'jkbsdkf dbvdfvb erufeferuf rugfirf ', 1244.00, 3456.00, 4, 4, 'kids', '68e486162edd3.jpeg', 11, '2025-10-07 03:16:38', 0, 0),
(12, 't shirt', 'dsfavdv ddvdv efef', 2399.00, 4999.00, 1, 1, 'kids', '68e4888e9e8e1.jpg', 23, '2025-10-07 03:27:10', 0, 1),
(13, 'jacket', 'jdcs cs uyc hwcv', 1234.00, 2453.00, 1, 2, 'men', '68e48d858bd4d.jpeg', 16, '2025-10-07 03:48:21', 0, 0),
(14, 'Kid\'s Tshirt', 'Kid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s TshirtKid\'s Tshirt', 1299.00, 1399.00, 1, 2, 'kids', '68e4fa4a4ccd9.jpeg', 55, '2025-10-07 11:32:26', 0, 0),
(15, 'Top', 'asc', 3999.00, 4999.00, 5, 3, 'women', '68e526accdd21.jpeg', 22, '2025-10-07 14:41:48', 1, 0),
(16, 'Jordan1inNepal', 'Jordan1inNepal', 6999.00, 7999.00, 4, 1, 'men', '68e5d8401aaf4.webp', 54, '2025-10-08 03:19:28', 0, 0),
(17, 'Light Blue Heeled Sandals For Women', 'Light Blue Heeled Sandals For Women', 1800.00, 1900.00, 4, 1, 'women', '68e5e22127a9f.jpg', 10, '2025-10-08 03:21:02', 0, 0),
(18, 'Basic Plain T-Shirt', 'Basic Plain T-Shirt Basic Plain T-Shirt Basic Plain T-Shirt', 1299.00, 1499.00, 1, 5, 'men', '68e613fb7771c.jpg', 41, '2025-10-08 07:34:19', 0, 0),
(19, 'Air jorden ', '', 8999.00, 8499.00, 4, 7, 'men', '68faef9829677.avif', 23, '2025-10-24 03:16:40', 0, 0),
(20, 'Adidas shoe  for women', '', 4500.00, 5000.00, 4, 2, 'women', '68faefe551bc5.avif', 25, '2025-10-24 03:17:57', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_attributes`
--

INSERT INTO `product_attributes` (`id`, `product_id`, `size`, `color`, `stock`) VALUES
(3, 12, 'M', 'Red', 23),
(4, 12, 'L', 'Green', 34),
(5, 12, 'XXL', 'White', 23),
(8, 11, 'S', 'Blue', 12),
(9, 13, 'L', 'Blue', 12),
(10, 13, 'XXL', 'White', 45),
(14, 14, 'S', 'Blue', 12),
(15, 14, 'S', 'Red', 32),
(16, 14, 'XS', 'Black', 5),
(18, 15, 'L', 'Red', 45),
(21, 17, '7', 'Blue', 4),
(22, 16, '38', 'Red', 32),
(23, 18, 'L', 'Brown', 10),
(24, 18, 'M', 'Yellow', 34),
(25, 19, '28', 'Red', 23),
(26, 20, '28', 'White', 27);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `address`, `phone`, `created_at`, `status`, `updated_at`) VALUES
(1, 'ashish', 'ashishpant401@gmail.com', '$2y$10$wuM7LmiTpWjDBKQwYjO8XOm4RwLtFn3b8gxKYufJFWlkNwynAwaBO', 'Ashish', 'pant', NULL, NULL, '2025-10-03 14:32:42', 'active', '2025-10-07 11:00:10'),
(2, 'Munna', 'manojpant186@gmail.com', '$2y$10$JxC3Sj9ua2oulslT3Fg99uksq13fkrQxL1lopZAFpOQPW5sPaFaga', 'Manoj ', 'Pant', NULL, NULL, '2025-10-07 10:18:18', NULL, '2025-10-07 11:00:23'),
(3, 'luxlighting deepahit', 'allwooddragon@gmail.com', '$2y$10$xTdupano4Mh/t7Mp9muLTuEL75tW7OPobwioZ.QxDH1mz5oK.Lx52', 'Abishek ', 'lighting ', NULL, NULL, '2025-10-12 07:08:42', 'active', '2025-10-12 07:15:12'),
(4, 'hi', 'apant6506@gmail.com', '$2y$10$lwgTd.MoBCatAl9gyiwyG.T7aWZwQoAqs9c8lamIgMv57/or5pWzG', 'hello', ' hi', NULL, NULL, '2025-10-14 13:55:04', 'active', '2025-10-14 13:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `user_status_history`
--

CREATE TABLE `user_status_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_status_history`
--

INSERT INTO `user_status_history` (`id`, `user_id`, `status`, `changed_by`, `notes`, `changed_at`) VALUES
(1, 3, 'active', NULL, '', '2025-10-12 07:15:12');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(15, 1, 16, '2025-10-15 06:50:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_default` (`is_default`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `attribute_id` (`attribute_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_status_history`
--
ALTER TABLE `user_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_status_history`
--
ALTER TABLE `user_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`);

--
-- Constraints for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_status_history`
--
ALTER TABLE `user_status_history`
  ADD CONSTRAINT `user_status_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

