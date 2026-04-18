-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2026 at 07:04 AM
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
-- Database: `cleanbook_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `address` text NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `customer_id`, `service_id`, `booking_date`, `booking_time`, `address`, `special_instructions`, `total_price`, `status`, `payment_status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 2, 6, '2026-04-17', '09:00:00', 'Katoloni,Machakos Town', 'NONE', 3000.00, 'pending', 'unpaid', NULL, '2026-04-11 05:54:28', '2026-04-11 05:54:28'),
(2, 4, 3, '2026-04-23', '09:00:00', 'Nairobi,Kenya', 'Be Panctual', 5000.00, 'completed', 'unpaid', NULL, '2026-04-14 09:17:41', '2026-04-14 09:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `category` enum('automotive','residential','furniture') NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `duration_hours` decimal(3,1) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `category`, `description`, `base_price`, `duration_hours`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Car Exterior Wash & Wax', 'automotive', 'Complete exterior wash with premium wax protection', 1500.00, 1.5, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36'),
(2, 'Car Interior Deep Clean', 'automotive', 'Thorough interior vacuuming, shampooing, and detailing', 2000.00, 2.0, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36'),
(3, 'House Deep Cleaning', 'residential', 'Complete house cleaning including all rooms, kitchen, and bathrooms', 5000.00, 4.0, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36'),
(4, 'Office Cleaning', 'residential', 'Professional office space cleaning and sanitization', 3500.00, 3.0, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36'),
(5, 'Sofa Cleaning', 'furniture', 'Professional upholstery cleaning for sofas and couches', 2500.00, 1.5, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36'),
(6, 'Carpet Cleaning', 'furniture', 'Deep carpet cleaning and stain removal', 3000.00, 2.0, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36'),
(7, 'Mattress Cleaning', 'furniture', 'Deep mattress sanitization and dust mite removal', 2000.00, 1.0, NULL, 'active', '2026-04-11 05:50:36', '2026-04-11 05:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `staff_assignments`
--

CREATE TABLE `staff_assignments` (
  `assignment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` enum('assigned','in_progress','completed','cancelled') DEFAULT 'assigned',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_assignments`
--

INSERT INTO `staff_assignments` (`assignment_id`, `booking_id`, `staff_id`, `assigned_at`, `completed_at`, `status`, `notes`) VALUES
(1, 2, 3, '2026-04-14 09:20:36', '2026-04-14 09:20:43', 'completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('customer','admin','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `user_type`, `created_at`, `status`) VALUES
(1, 'Admin User', 'admin@cleanbook.com', '+254712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-10 07:09:28', 'active'),
(2, 'John Doe', 'john@example.com', '+254723456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2026-04-10 07:09:28', 'active'),
(3, 'Jane Smith', 'jane@cleanbook.com', '+254734567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '2026-04-10 07:09:28', 'active'),
(4, 'Rama Kazungu', 'iddahmuthoni36@gmail.com', '+254740120402', '$2y$10$5Kz6.H7s6W3wB92qIzox5ul3/1NagYVl1e3vnuaxuL889bd37fBvG', 'customer', '2026-04-14 09:16:19', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_booking_date` (`booking_date`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `staff_assignments`
--
ALTER TABLE `staff_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `idx_staff` (`staff_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff_assignments`
--
ALTER TABLE `staff_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `staff_assignments`
--
ALTER TABLE `staff_assignments`
  ADD CONSTRAINT `staff_assignments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_assignments_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
