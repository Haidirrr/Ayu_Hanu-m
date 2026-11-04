-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 30, 2026 at 09:46 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ayu hanu'm`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `section` enum('women','bags','shoes','accessories') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `section`, `name`, `slug`) VALUES
(1, 'women', 'Atasan', 'atasan'),
(2, 'women', 'Outerwear', 'outerwear'),
(3, 'women', 'Dress', 'dress'),
(4, 'bags', 'Tas', 'tas'),
(5, 'shoes', 'Sepatu', 'sepatu'),
(6, 'accessories', 'Dompet', 'dompet');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_pct` int NOT NULL,
  `min_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_pct`, `min_order`, `is_active`) VALUES
(1, 'ELIZABETH10', 10, 200000, 1),
(2, 'DISKON20', 20, 500000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `address` text NOT NULL,
  `location_url` varchar(500) DEFAULT NULL,
  `shipping_id` int NOT NULL,
  `payment_id` int NOT NULL,
  `subtotal` int NOT NULL DEFAULT '0',
  `shipping_cost` int NOT NULL DEFAULT '0',
  `total` int NOT NULL DEFAULT '0',
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','paid','shipped','done','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `customer_name`, `phone`, `address`, `location_url`, `shipping_id`, `payment_id`, `subtotal`, `shipping_cost`, `total`, `payment_proof`, `status`, `created_at`) VALUES
(1, 'ELZ261015190130803', 'Takdir Ihwal', '11111111111111', 'makassar', '', 1, 4, 827000, 15000, 842000, 'uploads/payments/bukti_2f3a69fb168e3ddbdf42af7e.webp', '', '2026-10-15 17:01:30'),
(2, 'ELZ261015191713420', 'iwal', '082190933498', 'makassar', '', 1, 3, 249000, 15000, 264000, '', 'shipped', '2026-10-15 17:17:13'),
(3, 'ELZ261015191819714', 'iwal', '082190527576', 'makassar', '', 1, 6, 489000, 15000, 504000, 'uploads/payments/bukti_b1c8d9789bd8d2beb00b815e.webp', 'shipped', '2026-10-15 17:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` int NOT NULL DEFAULT '0',
  `qty` int NOT NULL DEFAULT '1',
  `color` varchar(100) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `qty`, `color`, `size`) VALUES
(1, 1, 4, 'Celine Silk Blouse', 289000, 2, 'Putih', 'S'),
(2, 1, 2, 'Luna Luxury Wallet', 249000, 1, 'Pink', 'One Size'),
(3, 2, 2, 'Luna Luxury Wallet', 249000, 1, 'Pink', 'One Size'),
(4, 3, 1, 'Elsa Shoulder Bag Deluxe', 489000, 1, 'Beige', 'One Size');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `account_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `detail`, `account_name`, `account_number`, `qr_image`, `is_active`) VALUES
(1, 'Transfer BCA', 'Transfer melalui bank BCA', 'Elizabeth Store', '1234567890', NULL, 1),
(2, 'DANA', 'Pembayaran melalui DANA', 'Elizabeth Store', '081234567890', NULL, 1),
(3, 'COD', 'Bayar saat barang diterima', 'Elizabeth Store', '-', NULL, 1),
(4, 'Transfer Bank', 'Transfer ke rekening toko', NULL, NULL, NULL, 1),
(5, 'E-Wallet', 'Pembayaran melalui e-wallet', NULL, NULL, NULL, 1),
(6, 'QRIS', 'Pembayaran menggunakan QRIS', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `specs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` int NOT NULL,
  `old_price` int DEFAULT NULL,
  `colors` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Beige',
  `sizes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'One Size',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '4.8',
  `reviews` int NOT NULL DEFAULT '0',
  `stock` int NOT NULL DEFAULT '10',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `specs`, `price`, `old_price`, `colors`, `sizes`, `image`, `rating`, `reviews`, `stock`, `is_featured`, `created_at`) VALUES
(1, 4, 'Elsa Shoulder Bag Deluxe', 'Tas bahu kulit sintetis kualitas tinggi dengan ornamen emas elegan.', 'Bahan: Premium Synthetic Leather|Ukuran: 28 x 20 cm|Include Box', 489000, 699000, 'Beige,Pink,Hitam', 'One Size', 'uploads/tas_kulit.jpg', 4.9, 142, 15, 1, '2026-10-15 06:18:42'),
(2, 6, 'Luna Luxury Wallet', 'Dompet lipat wanita dengan kompartemen kartu RFID Protection.', 'Bahan: Microfiber Leather|Slot Kartu: 10', 249000, 320000, 'Pink,Hitam,Cokelat', 'One Size', 'uploads/dompet.jpg', 4.8, 98, 25, 1, '2026-10-15 06:18:42'),
(3, 5, 'Grace Stiletto Heels 7cm', 'Sandal heels berdesain ergonomis untuk acara formal.', 'Bahan: Soft Synthetic Suede|Tinggi: 7 cm', 399000, NULL, 'Beige,Hitam', '36,37,38,39', 'uploads/sendal.webp', 4.9, 80, 12, 1, '2026-10-15 06:18:42'),
(4, 1, 'Celine Silk Blouse', 'Atasan bahan sutra sintetis yang jatuh dan adem dipakai seharian.', 'Bahan: Silk Blend|Fit: Regular', 289000, 350000, 'Putih,Beige', 'S,M,L,XL', 'uploads/baju.jpg', 4.7, 53, 30, 1, '2026-10-15 06:18:42');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `eta` varchar(100) DEFAULT NULL,
  `cost` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `eta`, `cost`) VALUES
(1, 'Reguler', '2–5 hari', 15000),
(2, 'Express', '1–2 hari', 25000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@elizabeth.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1g.X/aK4e.9l6PjY5r7.f1P0r7S2Y/S', 'admin', '2026-10-15 06:18:42');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
