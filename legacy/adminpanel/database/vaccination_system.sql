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
(27, '', 'Abdullah ul', 10, 7, 62, 'amd@gmail.com', '2025-02-06', '18:14:00', 'approved', NULL, '2025-02-05 06:11:09', 2);

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
(7, 2, 'jawad', ' ul-bahar', '2002-01-08', 'male', 'A+', 'Asthma ', '2025-01-18 03:09:50'),
(8, 6, 'Mustafa', 'Tasir', '2005-01-12', 'male', 'O+', 'Nothing', '2025-01-20 15:35:41'),
(9, 4, 'jawad babu', ' ul-bahar', '2025-01-21', 'male', 'c++', 'corona', '2025-01-21 03:06:33'),
(10, 2, 'faradics dadu', 'khan', '2024-04-04', 'female', 'c++', 'corona', '2025-01-21 03:12:36'),
(11, 7, 'jawad babu', ' ul-bahar', '2003-02-05', 'other', 'c++', 'corona', '2025-01-23 03:28:16'),
(12, 2, 'bilal', 'khan', '2021-02-02', 'male', 'a+', 'good', '2025-02-03 07:43:35'),
(13, 2, 'Mustafa', 'Tasir', '2025-02-03', 'female', 'O+', 'Nothing', '2025-02-05 05:07:14');

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
(21, 'wdwd', 'dadawd@gmail.com', 'dwadad', 'dadawda'),
(22, 'wdwd', 'dadawd@gmail.com', 'dwadad', 'dadawda');

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
(4, 'Abdullah ul', 'abdullahmurshid931@gmail.com', 'hi here is feed back', '4'),
(5, 'Abdullah ul', 'abdullahmurshid931@gmail.com', 'dgfgdfg', '1'),
(6, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '', ''),
(7, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '', ''),
(8, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '', ''),
(9, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '', ''),
(10, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '', ''),
(11, 'Abdullah ul', 'abdullahmurshid931@gmail.com', 'wdwd', '2'),
(12, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '', ''),
(13, 'Abdullah ul', 'abdullahmurshid931@gmail.com', 'dwdwd', '3'),
(14, 'Abdullah ul', 'abdullahmurshid931@gmail.com', '555', '3');

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
(2, NULL, 'Jinnah Hospital 01C', 'Karachi01C', '12687465435701', 'jinnah01C@gmail.com', '321', 'hospital', 'active', '2025-01-07 15:32:10'),
(5, NULL, 'Ziauddin', 'Karachi', '030202166151', 'zia@gmail.com', '12345', 'hospital', 'active', '2025-01-28 08:01:37'),
(6, NULL, 'Civil Hospital', 'Saddar karachi', '+021558741441', 'civilhos@gmail.com', '123', 'hospital', 'active', '2025-02-02 11:13:11'),
(7, NULL, 'Civil Hospital', 'Saddar karachi', '+021558741441', 'civilhos@gmail.com', '123', 'hospital', 'active', '2025-02-02 11:13:41'),
(8, NULL, 'City Medical Center', 'Korangi Road, Karachi', '+021558742255', 'citymedcenter@gmail.com', '456', 'hospital', 'active', '2025-02-02 11:16:10'),
(9, NULL, 'Sunrise Hospital', 'DHA, Karachi', '+02156789432', 'sunrisehospital@gmail.com', '789', 'hospital', 'active', '2025-02-02 11:16:10'),
(10, NULL, 'Metro Hospital', 'Karachi Cantt.', '+021123456789', 'metrohospital@gmail.com', 'abc123', 'hospital', 'active', '2025-02-02 11:16:10'),
(11, NULL, 'Shifa International Hospital', 'Islamabad', '+05123456789', 'shifainternational@gmail.com', 'qwerty', 'hospital', 'active', '2025-02-02 11:16:10'),
(12, NULL, 'Lahore General Hospital', 'Gulberg, Lahore', '+0429876543', 'lahoregenhosp@gmail.com', 'password123', 'hospital', 'active', '2025-02-02 11:16:10'),
(13, NULL, 'Karachi Medical Center', 'Clifton Karachi', '+02133221144', 'karachimedcenter@gmail.com', 'mypassword', 'hospital', 'active', '2025-02-02 11:16:10'),
(14, NULL, 'Agha Khan Hospital', 'Shahra-e-Faisal, Karachi', '+021556667788', 'aghakhanhosp@gmail.com', 'securepass', 'hospital', 'active', '2025-02-02 11:16:10'),
(15, NULL, 'Medicare Hospital', 'Rawalpindi', '+05122334455', 'medicarehosp@gmail.com', 'pass1234', 'hospital', 'active', '2025-02-02 11:16:10'),
(16, NULL, 'Peshawar Health Center', 'University Road, Peshawar', '+0912323456', 'peshawarhealthcenter@gmail.com', 'admin1234', 'hospital', 'inactive', '2025-02-02 11:16:10'),
(17, NULL, 'jiinn', 'sshdiugfibv', '972493874634', 'jinn@gmail.com', '123', 'hospital', 'active', '2025-02-03 12:53:47'),
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
(2, 'Abdullah Murshid', 'abdullahmurshid931@gmail.com', '0000', '0333-2615042', 'parent', 'House no. 55-A BL-2 karachi, Pakistan.', '2025-02-05 09:54:54'),
(3, 'Mustafa', 'm@gmail.com', ' 123 ', '0334-5679086', 'parent', ' House no.632-C Tariq road BL-2, Karachi, Pakistan', '2025-01-21 08:14:14'),
(4, 'abdul rehman', 'rsjutt7@gmail.com', 'ab12', '03273346757', 'parent', ' shah latif town karachi', '2025-01-21 08:22:40'),
(5, 'aqsa noor', 'a@gmail.com', 'aa12', '0334-5679086', 'parent', ' korangi no # 2 1/2 house no # L-497 sector 48-B', '2025-01-21 08:33:39'),
(6, 'jv', 'jv@gmail.com', ' jv123 ', '03315454', 'parent', ' sadsdwd', '2025-01-21 08:40:34'),
(7, 'Mmk', 'mmk@gmail.com', '1234', '0333336554', 'parent', 'mmmk-BL-2', '2025-01-23 08:21:10');

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
(2, 'jawad', '123', 'jawadulbahar@gmail.com', 'admin', '', '03056456544', NULL, '2025-01-07 14:48:05', 'active'),
(3, 'Abdul Rehman', '321', 'rsjutt@gmail.com', 'admin', '', '03133998235', NULL, '2025-01-07 14:51:38', 'active'),
(4, 'Muslim', 'mus', 'muslim@gmail.com', 'admin', '', '03056456544', NULL, '2025-01-08 10:59:25', 'active'),
(16, 'abdullah', '1234567', 'abdullah@gmail.com', '', '[value-6]', '03002110215', '[value-8]', '0000-00-00 00:00:00', ''),
(17, 'ali', '223344', 'ali@gmail.com', '', '', '03192556707', '[value-8]', '0000-00-00 00:00:00', ''),
(19, 'ayub', '$2y$10$5wktSi3V8M2RkDzU3tsqLewdfqGIFKJEtGWb4dDesQ.niW2.OPz8a', 'ayub@gmail.com', '', 'ayub', '0332456432', '', '2025-01-13 05:17:11', 'active'),
(20, 'nawab', '$2y$10$WAUd8TfQdaMKooxcMfSMyOIez9FjA8a5.delPa09ucpbQjgqJxOw2', 'nawab@gmail.com', '', 'nawab', '123', '', '2025-01-13 05:20:02', 'active'),
(21, 'ahmed', '12345', 'ahmed@gmail.com', '', '', '123456789', '[value-8]', '0000-00-00 00:00:00', '');

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
