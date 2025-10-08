-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 09:51 PM
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
-- Database: `nodbot_finance`
--

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

CREATE TABLE `investments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `plan` varchar(50) NOT NULL,
  `duration_months` int(11) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','active','completed','cancelled') DEFAULT 'active',
  `investment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `maturity_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `investments`
--

INSERT INTO `investments` (`id`, `user_id`, `amount`, `plan`, `duration_months`, `interest_rate`, `notes`, `status`, `investment_date`, `maturity_date`) VALUES
(9, 2, 5000.00, 'Daily Savings', 12, 8.00, NULL, 'active', '2025-10-06 18:53:08', NULL),
(10, 2, 10000.00, 'Monthly Recurring', 24, 8.50, NULL, 'active', '2025-10-06 18:53:08', NULL),
(11, 2, 3000.00, 'Fixed Deposit', 6, 7.50, NULL, 'pending', '2025-10-06 18:53:08', NULL),
(12, 2, 5000.00, 'Daily Savings', 12, 8.00, 'First deposit', 'active', '2025-10-06 18:53:55', NULL),
(14, 2, 3000.00, 'Fixed Deposit', 6, 7.50, 'Short term FD', 'pending', '2025-10-06 18:53:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `duration_months` int(11) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `investment_id` int(11) DEFAULT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'completed',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `payment_type`, `payment_method`, `transaction_id`, `investment_id`, `loan_id`, `status`, `payment_date`) VALUES
(1, 2, 100.00, 'investment', 'UPI', 'TXN17599525697902', 12, NULL, 'completed', '2025-10-08 19:42:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `user_type` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `user_type`, `created_at`) VALUES
(2, 'Dipendra Chaturvedi', 'dipendra@gmail.com', '$2y$10$9lSU.BDf3NZGSH0HXA3hCeBKJICgkHa6kREKlFPtQOIyQe2FQudMq', '07796064374', 'user', '2025-10-06 18:02:35'),
(4, 'Administrator', 'admin@nodbot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 'admin', '2025-10-06 18:27:44'),
(5, 'Andrul', 'andrul@gmail.com', '$2y$10$3SV8MA6mZusOYbN3OPIKouPxzyMmYAhe4b.A/wN4ttg8isd9x16M.', '7796064374', 'admin', '2025-10-06 18:35:14'),
(6, 'Nites', 'nitesh@gmail.com', '$2y$10$Ya888csix/NcpFfmAo.AjecXPWPPea7oNeX3e/wF0D/n/yi3ZblHK', '7796064374', 'admin', '2025-10-08 18:52:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_investment_date` (`investment_date`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_payment_investment` (`investment_id`),
  ADD KEY `fk_payment_loan` (`loan_id`);

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
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `investments`
--
ALTER TABLE `investments`
  ADD CONSTRAINT `investments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payment_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
