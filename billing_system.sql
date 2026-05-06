-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 03:00 AM
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
-- Database: `billing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing_rates`
--

CREATE TABLE `billing_rates` (
  `id` int(10) UNSIGNED NOT NULL,
  `rate_per_m3` decimal(8,2) NOT NULL DEFAULT 20.00 COMMENT 'Price per cubic metre',
  `min_charge` decimal(8,2) NOT NULL DEFAULT 100.00 COMMENT 'Minimum bill amount',
  `effective_from` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `billing_month` tinyint(3) UNSIGNED NOT NULL COMMENT '1=January … 12=December',
  `billing_year` smallint(5) UNSIGNED NOT NULL,
  `usage_m3` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Cubic metres consumed',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Auto-computed: max(usage*rate, min_charge)',
  `status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `barangay` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `user_id`, `billing_month`, `billing_year`, `usage_m3`, `amount`, `status`, `paid_at`, `created_at`, `updated_at`, `barangay`) VALUES
(1, 1, 1, 2025, 1.20, 100.00, 'unpaid', NULL, '2026-04-20 15:40:17', '2026-04-20 15:40:17', NULL),
(2, 5, 3, 2025, 1.50, 100.00, 'unpaid', NULL, '2026-04-22 09:24:04', '2026-04-29 02:56:56', 'Lawis'),
(3, 11, 2, 2025, 1.70, 100.00, 'unpaid', NULL, '2026-04-22 09:24:41', '2026-04-29 02:56:56', 'lapacan'),
(4, 12, 3, 2025, 2.10, 100.00, 'unpaid', NULL, '2026-04-22 09:25:10', '2026-04-29 02:56:56', 'Ilaya'),
(5, 2, 2, 2025, 2.00, 100.00, 'unpaid', NULL, '2026-04-22 09:30:52', '2026-04-29 03:03:28', 'Badiang'),
(6, 11, 7, 2025, 50.00, 1000.00, 'unpaid', NULL, '2026-04-22 09:32:50', '2026-04-29 02:56:56', 'lapacan'),
(7, 2, 11, 2025, 6.00, 120.00, 'unpaid', NULL, '2026-04-22 09:33:21', '2026-04-29 03:03:28', 'Badiang'),
(8, 14, 4, 2025, 9.20, 184.00, 'unpaid', NULL, '2026-04-27 01:54:12', '2026-04-29 02:56:56', 'Bugang'),
(9, 5, 5, 2025, 7.80, 156.00, 'unpaid', NULL, '2026-04-27 02:03:30', '2026-04-29 02:56:56', 'Lawis'),
(10, 15, 3, 2025, 2.30, 100.00, 'paid', '2026-04-29 02:43:11', '2026-04-27 02:05:04', '2026-04-29 02:56:56', 'Ilaya'),
(11, 14, 7, 2025, 11.00, 220.00, 'paid', '2026-04-29 02:10:24', '2026-04-27 02:09:53', '2026-04-29 02:56:56', 'Bugang');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hashed',
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `address`, `barangay`, `password`, `role`, `is_active`, `created_at`, `updated_at`, `username`) VALUES
(1, 'System Administrator', 'admin@gmail.com', 'AquaBill Office, Main St.', '', '$2y$10$99Iw5SC52FmFVm7H1pwz/Ozf9G4/7yC07sD6zyaBPcFWDNAT83zUi', 'admin', 1, '2026-04-20 15:38:55', '2026-04-20 15:38:55', ''),
(2, 'Trixia Ceniza', 'trixiaceniza@gmail.com', 'purok 1, Badiang, Inabanga', 'Badiang', '$2y$10$Petc88NBOgv5Y1.7zEHpy.SOQgYYU9K6rKJ8zKLHughCDkX48PYTe', 'user', 0, '2026-04-20 16:03:40', '2026-04-29 03:03:28', NULL),
(5, 'Charles Delalamon', 'charles@gmail.com', 'Purok 2, Lawis, Inabanga', 'Lawis', '$2y$10$sM7pnIKwTYtmsQx/Za5GXubEzXtkj2auRz.axH0VSEs13AGlYg1TS', 'user', 1, '2026-04-22 00:51:39', '2026-04-29 03:18:22', NULL),
(11, 'grace baustista', 'grace@gmail.com', 'purok 1, lapacan, Inabanga', 'lapacan', '$2y$10$UbUqp5ZSLk/ieUJoNo3zOO67HvbVND3d4cHEAEAP1CfiImvlNvjNy', 'user', 1, '2026-04-22 01:06:47', '2026-04-29 02:56:56', 'gracefe488a'),
(12, 'jiane napa', 'napa@gmail.com', 'purok 1, Ilaya, Inabanga', 'Ilaya', '$2y$10$eYx5i4N3gTteXSVJipvmdOd3iYW8kGrYGPZQp1JOqzA5cXf0J0KO6', 'user', 0, '2026-04-22 01:14:29', '2026-04-29 02:56:56', 'napa8a624d'),
(13, 'Trixia Amoin', 'trixie@gmail.com', 'SA Skina', 'SA Skina', '$2y$10$IJlOihEefGAeTF5zDO.0feEKabJyRnCN98FAfxsEnkbym4RTgKwEy', 'user', 1, '2026-04-22 13:18:30', '2026-04-29 02:56:56', 'trixie7dd672'),
(14, 'angelyn nangca', 'angelyn@gmail.com', 'Purok 5, Bugang, Inabanga', 'Bugang', '$2y$10$7XSRSDuTc.0URrZ4oVMle.wpkM4e5JVxr6JqHNOf7GFE.uaqCApom', 'user', 1, '2026-04-27 00:40:57', '2026-04-29 02:56:56', 'angelyn516c1d'),
(15, 'jiane napa', 'jiane@gmail.com', 'purok 1, Ilaya, Inabanga', 'Ilaya', '$2y$10$/GTMNyu5FlwPYDAypfx9iezBs.K6kH4Fo6qpALUARzpcUm24RGlzC', 'user', 1, '2026-04-27 02:04:29', '2026-04-29 02:56:56', 'jiane538b31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing_rates`
--
ALTER TABLE `billing_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_period` (`user_id`,`billing_month`,`billing_year`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_period` (`billing_year`,`billing_month`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing_rates`
--
ALTER TABLE `billing_rates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `fk_bills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
