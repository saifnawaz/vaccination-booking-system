-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2025 at 12:30 PM
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
-- Database: `vaccination_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL,
  `parent_name` varchar(50) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `vaccine_id` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `name`, `parent_name`, `child_id`, `hospital_id`, `vaccine_id`, `email`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `parent_id`) VALUES
(27, '', 'Parent One', 10, 7, 62, 'user27@example.com', '2025-02-06', '18:14:00', 'approved', NULL, '2025-02-05 06:11:09', 2);

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `parent_id`, `first_name`, `last_name`, `dob`, `gender`, `blood_group`, `medical_conditions`, `created_at`) VALUES
(7, 2, 'child1', ' lastname1', '2002-01-08', 'male', 'A+', 'Asthma ', '2025-01-18 03:09:50'),
(8, 6, 'Child2', 'Lastname2', '2005-01-12', 'male', 'O+', 'Nothing', '2025-01-20 15:35:41'),
(9, 4, 'child3 test', ' lastname1', '2025-01-21', 'male', 'c++', 'corona', '2025-01-21 03:06:33'),
(10, 2, 'child4 test', 'lastname4', '2024-04-04', 'female', 'c++', 'corona', '2025-01-21 03:12:36'),
(11, 7, 'child3 test', ' lastname1', '2003-02-05', 'other', 'c++', 'corona', '2025-01-23 03:28:16'),
(12, 2, 'child5', 'lastname4', '2021-02-02', 'male', 'a+', 'good', '2025-02-03 07:43:35'),
(13, 2, 'Child2', 'Lastname2', '2025-02-03', 'female', 'O+', 'Nothing', '2025-02-05 05:07:14');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL,
  `email` varchar(225) NOT NULL,
  `subject` varchar(225) NOT NULL,
  `message` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `subject`, `message`) VALUES
(21, 'wdwd', 'contact1@example.com', 'dwadad', 'dadawda'),
(22, 'wdwd', 'contact1@example.com', 'dwadad', 'dadawda');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_feedback` varchar(580) NOT NULL,
  `user_exp` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_name`, `user_email`, `user_feedback`, `user_exp`) VALUES
(4, 'Parent One', 'parent1@example.com', 'hi here is feed back', '4'),
(5, 'Parent One', 'parent1@example.com', 'dgfgdfg', '1'),
(6, 'Parent One', 'parent1@example.com', '', ''),
(7, 'Parent One', 'parent1@example.com', '', ''),
(8, 'Parent One', 'parent1@example.com', '', ''),
(9, 'Parent One', 'parent1@example.com', '', ''),
(10, 'Parent One', 'parent1@example.com', '', ''),
(11, 'Parent One', 'parent1@example.com', 'wdwd', '2'),
(12, 'Parent One', 'parent1@example.com', '', ''),
(13, 'Parent One', 'parent1@example.com', 'dwdwd', '3'),
(14, 'Parent One', 'parent1@example.com', '555', '3');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `hospital_name` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` enum('hospital') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `user_id`, `hospital_name`, `address`, `phone`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(2, NULL, 'Sample Hospital 01C', 'City01C', '10000000000002', 'hospital2@example.com', 'pass2', 'hospital', 'active', '2025-01-07 15:32:10'),
(5, NULL, 'Sample Hospital 5', 'City A', '030000000005', 'hospital5@example.com', 'pass5', 'hospital', 'active', '2025-01-28 08:01:37'),
(6, NULL, 'Sample Hospital 6', 'Example Road, City A', '+020000000006', 'hospital6@example.com', 'pass6', 'hospital', 'active', '2025-02-02 11:13:11'),
(7, NULL, 'Sample Hospital 6', 'Example Road, City A', '+020000000006', 'hospital6@example.com', 'pass6', 'hospital', 'active', '2025-02-02 11:13:41'),
(8, NULL, 'Sample Hospital 8', 'Example Road, City A', '+020000000008', 'hospital8@example.com', 'pass8', 'hospital', 'active', '2025-02-02 11:16:10'),
(9, NULL, 'Sample Hospital 9', 'Example Road, City A', '+02000000009', 'hospital9@example.com', 'pass9', 'hospital', 'active', '2025-02-02 11:16:10'),
(10, NULL, 'Sample Hospital 10', 'City A', '+020000000010', 'hospital10@example.com', 'pass10', 'hospital', 'active', '2025-02-02 11:16:10'),
(11, NULL, 'Sample Hospital 11', 'City B', '+05000000011', 'hospital11@example.com', 'pass11', 'hospital', 'active', '2025-02-02 11:16:10'),
(12, NULL, 'Sample Hospital 12', 'Example Road, City C', '+0400000012', 'hospital12@example.com', 'pass12', 'hospital', 'active', '2025-02-02 11:16:10'),
(13, NULL, 'Sample Hospital 13', 'Example Road City A', '+02000000013', 'hospital13@example.com', 'pass13', 'hospital', 'active', '2025-02-02 11:16:10'),
(14, NULL, 'Sample Hospital 14', 'Example Road, City A', '+020000000014', 'hospital14@example.com', 'pass14', 'hospital', 'active', '2025-02-02 11:16:10'),
(15, NULL, 'Sample Hospital 15', 'City D', '+05000000015', 'hospital15@example.com', 'pass15', 'hospital', 'active', '2025-02-02 11:16:10'),
(16, NULL, 'Sample Hospital 16', 'Example Road, City E', '+0900000016', 'hospital16@example.com', 'pass16', 'hospital', 'inactive', '2025-02-02 11:16:10'),
(17, NULL, 'testhosp', 'sshdiugfibv', '000000000017', 'hospital17@example.com', 'pass17', 'hospital', 'active', '2025-02-03 12:53:47'),
(18, NULL, '', '', '', '', '', 'hospital', 'active', '2025-02-05 10:18:54'),
(19, NULL, '', '', '', '', '', 'hospital', 'active', '2025-02-05 10:29:53');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_vaccines`
--

CREATE TABLE `hospital_vaccines` (
  `id` int(11) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `vaccine_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(60) NOT NULL,
  `phone_no` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `address` varchar(225) NOT NULL,
  `created_at` varchar(150) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `name`, `email`, `password`, `phone_no`, `role`, `address`, `created_at`) VALUES
(2, 'Parent One', 'parent1@example.com', 'pass1', '0300-0000001', 'parent', 'House no. 1 Example Street, City A.', '2025-02-05 09:54:54'),
(3, 'Parent Two', 'parent2@example.com', ' pass2 ', '0300-0000002', 'parent', ' House no.2 Example Street, City A', '2025-01-21 08:14:14'),
(4, 'parent three', 'parent3@example.com', 'pass3', '03000000003', 'parent', ' example town city a', '2025-01-21 08:22:40'),
(5, 'parent four', 'parent4@example.com', 'pass4', '0300-0000002', 'parent', ' example area # 4 house no # X-4 sector 4', '2025-01-21 08:33:39'),
(6, 'p5', 'parent5@example.com', ' pass5 ', '03000005', 'parent', ' sadsdwd', '2025-01-21 08:40:34'),
(7, 'P6', 'parent6@example.com', 'pass6', '0300000006', 'parent', 'example-6', '2025-01-23 08:21:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `full_name`, `phone`, `address`, `created_at`, `status`) VALUES
(1, '$username', '$pass', '$email', 'admin', '', '$number', NULL, '2025-01-07 14:36:10', 'active'),
(2, 'admin1', 'pass2', 'admin1@example.com', 'admin', '', '03000000102', NULL, '2025-01-07 14:48:05', 'active'),
(3, 'Admin Two', 'pass3', 'admin2@example.com', 'admin', '', '03000000103', NULL, '2025-01-07 14:51:38', 'active'),
(4, 'Admin3', 'pass4', 'admin3@example.com', 'admin', '', '03000000102', NULL, '2025-01-08 10:59:25', 'active'),
(16, 'user16', 'pass16', 'user16@example.com', '', '[value-6]', '03000000116', '[value-8]', '0000-00-00 00:00:00', ''),
(17, 'user17', 'pass17', 'user17@example.com', '', '', '03000000117', '[value-8]', '0000-00-00 00:00:00', ''),
(19, 'user19', '$2y$10$5bcVGdjGXGRQGMeUYPN4r.Bzs97S4ZcbTbGC21.yEzH7ElVhhGtyi', 'user19@example.com', '', 'user19', '0300000119', '', '2025-01-13 05:17:11', 'active'),
(20, 'user20', '$2y$10$.qOTncssLB3SgxjkT3Wyhu6KY8hWmtQHfYi5nJib5SlikrZQpqz/K', 'user20@example.com', '', 'user20', '123', '', '2025-01-13 05:20:02', 'active'),
(21, 'user21', 'pass21', 'user21@example.com', '', '', '123456789', '[value-8]', '0000-00-00 00:00:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_records`
--

CREATE TABLE `vaccination_records` (
  `id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `vaccine_id` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `vaccination_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vaccines`
--

CREATE TABLE `vaccines` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `vaccines_detail` varchar(50) DEFAULT NULL,
  `vaccines_qty` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT 1,
  `status` enum('available','unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccines`
--

INSERT INTO `vaccines` (`id`, `name`, `vaccines_detail`, `vaccines_qty`, `hospital_id`, `status`) VALUES
(20, 'COVID-19 Vaccine', 'This vaccine helps protect against COVID-19.', '20', 5, 'unavailable'),
(21, 'Madorna', 'This vaccine for corona virus', '2', 14, 'available'),
(61, 'Polio Vaccine', 'Protects against poliomyelitis', '100', 14, 'available'),
(62, 'MMR Vaccine', 'Measles, Mumps, and Rubella protection', '150', 7, 'available'),
(63, 'Hepatitis B', 'Prevention of Hepatitis B infection', '75', 2, 'available'),
(64, 'DPT', 'Diphtheria, Pertussis, Tetanus vaccine', '120', 13, 'available'),
(65, 'Influenza', 'Seasonal flu protection', '200', 12, 'available'),
(66, 'COVID-19', 'Coronavirus protection', '500', 15, 'available'),
(67, 'BCG', 'Tuberculosis prevention', '80', 10, 'available'),
(68, 'Pneumonia', 'Pneumococcal vaccine', '90', 16, 'available'),
(69, 'Typhoid', 'Protection against typhoid fever', '110', 11, 'available'),
(71, 'Rotavirus', 'Prevention of severe diarrhea in infants', '0', 5, 'unavailable'),
(72, 'HPV', 'Human Papillomavirus protection', '0', 6, 'unavailable'),
(73, 'Meningitis', 'Protection against meningococcal disease', '0', 7, 'unavailable'),
(74, 'Yellow Fever', 'Protection for international travelers', '0', 2, 'unavailable'),
(75, 'Hepatitis A', 'Prevention of Hepatitis A infection', '0', 13, 'unavailable'),
(76, 'Rabies', 'Post-exposure prevention', '40', 12, 'available'),
(77, 'Tetanus Toxoid', 'Tetanus prevention booster', '180', 15, 'available'),
(78, 'Japanese Encephalitis', 'Protection against JE virus', '55', 10, 'available'),
(79, 'Shingles', 'Herpes zoster prevention', '60', 16, 'available'),
(80, 'Cholera', 'Protection against cholera', '95', 11, 'available'),
(81, 'dfgtdg', 'adaswdfasdf', '234', 16, 'available'),
(86, 'PLV', 'DWDD', '1231', 15, 'available'),
(88, 'PLV', '', '1231', 11, 'available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `vaccine_id` (`vaccine_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `vaccine_id` (`vaccine_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `vaccine_id` (`vaccine_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vaccines_ibfk_1` (`hospital_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vaccines`
--
ALTER TABLE `vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`),
  ADD CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`);

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD CONSTRAINT `hospitals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  ADD CONSTRAINT `hospital_vaccines_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`),
  ADD CONSTRAINT `hospital_vaccines_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`);

--
-- Constraints for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD CONSTRAINT `vaccination_records_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`),
  ADD CONSTRAINT `vaccination_records_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`),
  ADD CONSTRAINT `vaccination_records_ibfk_3` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`),
  ADD CONSTRAINT `vaccination_records_ibfk_4` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);

--
-- Constraints for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD CONSTRAINT `vaccines_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
