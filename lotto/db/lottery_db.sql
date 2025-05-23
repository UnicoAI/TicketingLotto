-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 11:38 AM
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
-- Database: `lottery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `lottery_id`, `price`, `quantity`, `expiry_date`, `added_at`) VALUES
(4, 1, 4, 3.00, 21, '2025-05-31', '2025-05-22 22:01:08');

-- --------------------------------------------------------

--
-- Table structure for table `lotteries`
--

CREATE TABLE `lotteries` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT 0.00,
  `winning_price` decimal(15,2) NOT NULL,
  `money_to_raise` decimal(15,2) NOT NULL,
  `total_raised` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lotteries`
--

INSERT INTO `lotteries` (`id`, `title`, `description`, `photo`, `price`, `winning_price`, `money_to_raise`, `total_raised`, `is_active`, `expiry_date`, `created_at`) VALUES
(4, 'bxbv', 'bvbxvbxcv', '/../uploads/unixbackground.png', 3.00, 32434.00, 342343.00, 66666.00, 1, '2025-05-31', '2025-05-20 17:59:56'),
(10, 'sdgsdgsd', 'sdggsdg', '/../uploads/unixbanner.png', 32.00, 32133213223.00, 9999999999999.99, 99999999999.00, 1, '2025-05-20', '2025-05-20 19:01:42');

-- --------------------------------------------------------

--
-- Table structure for table `lottery_winners`
--

CREATE TABLE `lottery_winners` (
  `id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `winner_user_id` int(11) NOT NULL,
  `winning_amount` decimal(15,2) NOT NULL,
  `drawn_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `payment_status`, `created_at`) VALUES
(1, 1, 30.00, 'completed', '2025-05-01 09:00:00'),
(2, 1, 20.00, 'completed', '2025-05-05 13:00:00'),
(3, 1, 50.00, 'pending', '2025-05-10 17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `lottery_id`, `title`, `price`, `quantity`, `total`) VALUES
(1, 1, 4, 'Champions League Final', 10.00, 1, 10.00),
(2, 1, 4, 'Europa League Final', 20.00, 1, 20.00),
(3, 2, 4, 'Meciul Zilei', 20.00, 1, 20.00),
(4, 3, 4, 'Champions League Final', 10.00, 5, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `user_id`, `request_date`, `status`, `admin_comment`) VALUES
(3, 1, '2025-05-22 22:05:21', 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL,
  `is_winner` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `lottery_id`, `ticket_number`, `user_id`, `purchase_date`, `price`, `is_winner`) VALUES
(1, 4, 'ABC123', 1, '2025-05-01 09:01:00', 10.00, 1),
(2, 4, 'XYZ456', 1, '2025-05-01 09:02:00', 20.00, 2),
(3, 4, 'LMN789', 1, '2025-05-05 13:01:00', 20.00, 0),
(4, 4, 'TKT001', 1, '2025-05-10 17:01:00', 10.00, 0),
(5, 4, 'TKT002', 1, '2025-05-10 17:01:00', 10.00, 0),
(6, 4, 'TKT003', 1, '2025-05-10 17:01:00', 10.00, 0),
(7, 4, 'TKT004', 1, '2025-05-10 17:01:00', 10.00, 0),
(8, 4, 'TKT005', 1, '2025-05-10 17:01:00', 10.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `referral_code` varchar(20) DEFAULT NULL,
  `referred_by_code` varchar(20) DEFAULT NULL,
  `can_refer` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `is_admin`, `created_at`, `referral_code`, `referred_by_code`, `can_refer`) VALUES
(1, 'Marius Daniel Boncica', 'info@unicoais.com', '$2y$10$Vb9z/jJtHTd9i1f9Uijxo.hhs824wBKa3TbbuLSirnjLZQs0Lk6ja', 1, '2025-05-20 16:46:47', 'REF2D1996', 'efgfdgfgg', 1),
(2, 'Marius Daniel Boncica', 'info1@unicoais.com', '$2y$10$FsNQljAKXynAFBYVEKnPZ.HJi6wo16weZ9O92c00qKpilRIg08vvi', 0, '2025-05-22 22:13:31', 'REFEE213B', 'REF2D1996', 1),
(3, 'jbjk.bhjk.k', 'info6@unicoais.com', '$2y$10$8qLo2r1yB8Uvr4PVvM04HORfPFAOuVFGQfWvaK1GIs1XBoVnefHli', 0, '2025-05-22 22:17:13', 'REF4E9C1C', 'REFEE213B', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `lottery_id` (`lottery_id`);

--
-- Indexes for table `lotteries`
--
ALTER TABLE `lotteries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lottery_winners`
--
ALTER TABLE `lottery_winners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lottery_id` (`lottery_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `winner_user_id` (`winner_user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lottery_id` (`lottery_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lotteries`
--
ALTER TABLE `lotteries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `lottery_winners`
--
ALTER TABLE `lottery_winners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries` (`id`);

--
-- Constraints for table `lottery_winners`
--
ALTER TABLE `lottery_winners`
  ADD CONSTRAINT `lottery_winners_ibfk_1` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lottery_winners_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lottery_winners_ibfk_3` FOREIGN KEY (`winner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
