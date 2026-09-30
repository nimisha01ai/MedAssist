-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Sep 24, 2026 at 12:37 PM
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
-- Database: `medassist_db`
--
-- --------------------------------------------------------

--
-- Table structure for table `bed_status`
--

CREATE TABLE `bed_status` (
  `ward_type` varchar(20) NOT NULL,
  `total_beds` int(11) NOT NULL,
  `occupied_beds` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bed_status`
--

INSERT INTO `bed_status` (`ward_type`, `total_beds`, `occupied_beds`) VALUES
('General', 50, 30),
('ICU', 20, 15);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `triage_score` int(11) DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `age`, `phone`, `gender`, `blood_group`, `symptoms`, `created_at`, `triage_score`, `risk_level`) VALUES
(4, 'Adarsh singh', 84, '9685456525', 'male', 'O+', 'Severe Chest Pain, Breathing Difficulty', '2026-09-23 00:42:11', 12, 'High Risk');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bed_status`
--
ALTER TABLE `bed_status`
  ADD PRIMARY KEY (`ward_type`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
