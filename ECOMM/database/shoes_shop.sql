-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2026 at 03:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shoes_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Men\'s Shoes', 'Comfortable and stylish shoes for men', '1765735610_Mens Shoes.jpg', '2025-12-14 17:41:48', '2025-12-14 18:07:17'),
(2, 'Women\'s Shoes', 'Elegant and fashionable shoes for women', '1765735661_Womens Shoes.jpg', '2025-12-14 17:41:48', '2025-12-14 18:07:41'),
(3, 'Sports Shoes', 'High-performance athletic footwear', '1765735807_Sports Shoes.jpg', '2025-12-14 17:41:48', '2025-12-14 18:10:07'),
(4, 'Casual Shoes', 'Everyday comfortable casual wear', '1765735407_Casual Shoes.jpg', '2025-12-14 17:41:48', '2025-12-14 18:03:27'),
(5, 'Formal Shoes', 'Professional and elegant formal footwear', '1765735415_Formal Shoes.jpg', '2025-12-14 17:41:48', '2025-12-14 18:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Rahul', 'rahul@example.com', 'test', 'hey there.', 'read', '2026-01-12 20:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'COD',
  `payment_details` text DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `shipping_address`, `phone`, `status`, `created_at`, `updated_at`, `payment_method`, `payment_details`, `transaction_id`) VALUES
(1, 2, 6498.00, '123 Main St, City', '1234567890', 'completed', '2025-12-14 17:41:48', '2025-12-14 17:41:48', 'COD', NULL, NULL),
(2, 3, 4298.00, '456 Oak Ave, Town', '9876543210', 'processing', '2025-12-14 17:41:48', '2025-12-14 17:41:48', 'COD', NULL, NULL),
(3, 2, 2999.00, '123 Main St, City', '1234567890', 'cancelled', '2025-12-14 18:14:09', '2025-12-31 14:07:27', 'COD', NULL, NULL),
(4, 2, 1499.00, 'Main, Patna City', '1234567890', 'processing', '2026-01-12 19:37:26', '2026-01-12 19:38:05', 'UPI', '{\"upi_id\":\"rahul@upi\"}', 'TXN69654D76B1C141768246646');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 'Classic Leather Oxford', 1, 2999.00, 2999.00),
(2, 1, 7, 'Running Shoes Pro', 1, 3499.00, 3499.00),
(3, 2, 4, 'Elegant High Heels', 1, 2799.00, 2799.00),
(4, 2, 10, 'Canvas Sneakers White', 1, 1499.00, 1499.00),
(5, 3, 1, 'Classic Leather Oxford', 1, 2999.00, 2999.00),
(6, 4, 11, 'Slip-On Comfort', 1, 1499.00, 1499.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 1, 'Classic Leather Oxford', 'Premium leather oxford shoes for formal occasions', 2999.00, 24, '1765734496_Classic Leather Oxford.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:14:09'),
(2, 1, 'Casual Loafers Brown', 'Comfortable brown loafers for everyday wear', 1899.00, 5, '1765735833_Casual Loafers Brown.jpg', 0, '2025-12-14 17:41:48', '2025-12-14 18:27:42'),
(3, 1, 'Suede Desert Boots', 'Stylish suede boots with rubber sole', 2499.00, 20, '1765735842_Suede Desert Boots.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:10:42'),
(4, 2, 'Elegant High Heels', 'Classic black high heels for special occasions', 2799.00, 15, '1765735850_Elegant High Heels.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:10:50'),
(5, 2, 'Ballet Flats', 'Comfortable ballet flats in multiple colors', 1599.00, 40, '1765735859_Ballet Flats.jpg', 0, '2025-12-14 17:41:48', '2025-12-14 18:10:59'),
(6, 2, 'Ankle Strap Sandals', 'Summer sandals with adjustable ankle strap', 1899.00, 35, '1765735867_Ankle Strap Sandals.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:11:07'),
(7, 3, 'Running Shoes Pro', 'Professional running shoes with air cushion', 3499.00, 50, '1765735876_Running Shoes Pro.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:11:16'),
(8, 3, 'Basketball High-Tops', 'High-top basketball shoes with ankle support', 3999.00, 20, '1765735885_Basketball High-Tops.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:11:25'),
(9, 3, 'Training Cross-Fit', 'Versatile cross-training shoes', 2999.00, 30, '1765735905_Training Cross-Fit.jpg', 0, '2025-12-14 17:41:48', '2025-12-14 18:11:45'),
(10, 4, 'Canvas Sneakers White', 'Classic white canvas sneakers', 1299.00, 60, '1765735916_Canvas Sneakers White.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:11:56'),
(11, 4, 'Slip-On Comfort', 'Easy slip-on shoes for casual wear', 1499.00, 44, '1765735942_Slip-On Comfort.jpg', 0, '2025-12-14 17:41:48', '2026-01-12 19:37:26'),
(12, 4, 'Denim Style Shoes', 'Trendy denim casual shoes', 1799.00, 35, '1765735952_Denim Style Shoes.jpg', 0, '2025-12-14 17:41:48', '2025-12-14 18:12:32'),
(13, 5, 'Patent Leather Dress Shoes', 'Shiny patent leather for formal events', 3299.00, 15, '1765735965_Patent Leather Dress Shoes.jpg', 0, '2025-12-14 17:41:48', '2025-12-14 18:12:45'),
(14, 5, 'Wingtip Brogues', 'Classic wingtip design with brogue detailing', 2899.00, 20, '1765735976_Wingtip Brogues.jpg', 1, '2025-12-14 17:41:48', '2025-12-14 18:12:56'),
(15, 5, 'Monk Strap Shoes', 'Elegant monk strap formal shoes', 3199.00, 18, '1765735987_Monk Strap Shoes.jpg', 0, '2025-12-14 17:41:48', '2025-12-14 18:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@theshoevault.com', '$2y$10$IRgH5sFXPq1yo7X7E0Um1OHzpkXIC3HO.fH4RtYCCwd8SbsnMvOFK', NULL, NULL, 'admin', '2025-12-14 17:41:48', '2025-12-14 17:41:59'),
(2, 'Rahul', 'rahul@example.com', '$2y$10$Yx3BZLpgfrIX8XPuTgumuu0FTcaz6hmARHYk4M7A0BAY6ujL1rvxO', '1234567890', 'Main, Patna City', 'customer', '2025-12-14 17:41:48', '2025-12-14 18:33:56'),
(3, 'Vivek Sharma', 'vivek@example.com', '$2y$10$Yx3BZLpgfrIX8XPuTgumuu0FTcaz6hmARHYk4M7A0BAY6ujL1rvxO', '9876543210', 'Patna', 'customer', '2025-12-14 17:41:48', '2025-12-14 18:34:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
