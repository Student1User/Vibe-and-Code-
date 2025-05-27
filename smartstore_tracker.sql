-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 09:47 PM
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
-- Database: `smartstore_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `icon` varchar(50) DEFAULT 'dollar-sign',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `type`, `icon`, `user_id`) VALUES
(1, 'Sales Revenue', 'income', 'trending-up', NULL),
(2, 'Service Income', 'income', 'briefcase', NULL),
(3, 'Other Income', 'income', 'plus-circle', NULL),
(4, 'Inventory', 'expense', 'package', NULL),
(5, 'Marketing', 'expense', 'megaphone', NULL),
(6, 'Utilities', 'expense', 'zap', NULL),
(7, 'Rent', 'expense', 'home', NULL),
(8, 'Supplies', 'expense', 'shopping-cart', NULL),
(9, 'Transportation', 'expense', 'truck', NULL),
(10, 'Other Expenses', 'expense', 'minus-circle', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `category`, `amount`, `description`, `receipt_path`, `receipt_image`, `transaction_date`, `created_at`, `date`, `category_id`) VALUES
(1, 1, 'income', '', 8524006.00, 'Paid Importation fees and accumulated rent', NULL, NULL, '0000-00-00', '2025-05-26 19:28:32', '2025-04-16 00:00:00', 3),
(2, 1, 'expense', '', 2500000.00, 'Paid rent for 6 months', NULL, NULL, '0000-00-00', '2025-05-26 19:29:37', '2025-05-26 00:00:00', 7),
(3, 1, 'expense', '', 500000.00, 'Paid for marketing', NULL, NULL, '0000-00-00', '2025-05-26 19:30:55', '2025-04-23 00:00:00', 5),
(4, 1, 'expense', '', 28000000.00, 'PAIDCARGO, AND INTERNAL TRANSPORTATION', NULL, NULL, '0000-00-00', '2025-05-26 19:33:33', '2025-01-22 00:00:00', 9),
(5, 1, 'income', '', 40000000.00, 'Sold good worth the amount to Emmanuel', NULL, NULL, '0000-00-00', '2025-05-26 19:34:58', '2025-06-20 00:00:00', 1),
(6, 4, 'expense', '', 25000.00, 'Paid rent for the month of may', NULL, NULL, '0000-00-00', '2025-05-26 19:39:45', '2025-05-26 00:00:00', 7),
(7, 4, 'income', '', 500000.00, 'Created a fully functional E-commerce website for a client including hosting and domain name', NULL, NULL, '0000-00-00', '2025-05-26 19:40:58', '2025-05-26 00:00:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Emmanuel Jesse', 'magachi.emmanuel@students.jkuat.ac.ke', '$2y$10$P25sXKRGV/MyuydpDaEnA.QcFAEUdqpgnitllQwqx09zgu2JkSS6C', '2025-05-24 18:28:38'),
(2, 'Winnie Nyambura', 'mwangiwinnie699@gmail.com', '$2y$10$kE9hyP0W0CFPhc.LcsKqjOttIxWEG8r28LayUJrvb9IsY4lghjnYu', '2025-05-24 19:12:13'),
(3, 'Qaqamba', 'qaqambak123@gmail.com', '$2y$10$zqyApqCewzt4Co6IEeBoaOusb/lDtM9wgFAavoMR62JoSAMfzItYq', '2025-05-24 19:13:19'),
(4, 'Hlawutelo', 'hlawutelo2ntsanwisi@gmail.com', '$2y$10$zEKRDDUA1gNhQo6No7P/wup/Q/5z5OUFgBltYq.COxc0oqrczddFK', '2025-05-26 19:38:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
