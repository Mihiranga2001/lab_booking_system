-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 12:33 PM
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
-- Database: `lab_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `equipment_availability`
--

CREATE TABLE `equipment_availability` (
  `id` int(11) NOT NULL,
  `Equipment_ID` int(11) NOT NULL,
  `availability_date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `Instructor_ID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`Instructor_ID`, `Name`, `Email`, `Password`) VALUES
(201, 'Mr.premuda', 'premuda@instructor.com', '$2y$10$NiDUW9aqBkfvzU7tlyAmQO7a5IpJ19zu8GUuiqd4U1nbZacy/WN7u'),
(202, 'Ms.Nadeesha', 'nadeesha@instructor.com', 'password42'),
(203, 'Mr.nayanajith', 'nayanajith@instructor.com', 'password43');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_book_booking`
--

CREATE TABLE `instructor_book_booking` (
  `Instructor_ID` int(11) NOT NULL,
  `Booking_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_book_booking`
--

INSERT INTO `instructor_book_booking` (`Instructor_ID`, `Booking_ID`) VALUES
(201, 707),
(201, 708),
(201, 709),
(201, 710),
(201, 711),
(201, 712),
(201, 713),
(201, 714),
(201, 715);

-- --------------------------------------------------------

--
-- Table structure for table `lab`
--

CREATE TABLE `lab` (
  `Lab_ID` int(11) NOT NULL,
  `Lab_Name` varchar(100) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `Availability` tinyint(1) DEFAULT NULL,
  `TO_ID` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab`
--

INSERT INTO `lab` (`Lab_ID`, `Lab_Name`, `Capacity`, `Availability`, `TO_ID`, `date`) VALUES
(401, 'Computer Architecture Lab', 30, 0, 301, '2025-06-09'),
(402, 'Control System Lab', 25, 1, 302, '2025-06-12'),
(403, 'construction Lab', 20, 1, 303, '2025-06-13');

-- --------------------------------------------------------

--
-- Table structure for table `lab_availability`
--

CREATE TABLE `lab_availability` (
  `id` int(11) NOT NULL,
  `Lab_ID` int(11) NOT NULL,
  `availability_date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_booking`
--

CREATE TABLE `lab_booking` (
  `Booking_ID` int(11) NOT NULL,
  `Lab_ID` int(11) DEFAULT NULL,
  `Lab_Name` varchar(100) DEFAULT NULL,
  `Request_Date` date DEFAULT NULL,
  `Request_Time_Slot` varchar(50) DEFAULT NULL,
  `Status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_booking`
--

INSERT INTO `lab_booking` (`Booking_ID`, `Lab_ID`, `Lab_Name`, `Request_Date`, `Request_Time_Slot`, `Status`) VALUES
(707, 401, 'Computer Architecture Lab', '2025-06-28', '9.00AM - 12.00PM', 'Rejected'),
(708, 401, 'Computer Architecture Lab', '2025-06-28', '1.00AM - 4.00PM', 'Approved'),
(709, 402, 'Control System Lab', '2025-06-27', '1.00AM - 4.00PM', 'Rejected'),
(710, 402, 'Control System Lab', '2025-06-27', '9.00AM - 12.00PM', 'Approved'),
(711, 401, 'Computer Architecture Lab', '2025-06-26', '9.00AM - 12.00PM', 'Pending'),
(712, 402, 'Control System Lab', '2025-06-28', '1.00AM - 4.00PM', 'Approved'),
(713, 402, 'Control System Lab', '2025-06-30', '9.00AM-12.00PM', 'Approved'),
(714, 403, 'construction Lab', '2025-06-30', '9.00AM - 12.00PM', 'Pending'),
(715, 402, 'Control System Lab', '2025-06-30', '1.00AM - 4.00PM', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `lab_equipment`
--

CREATE TABLE `lab_equipment` (
  `Equipment_ID` int(11) NOT NULL,
  `Equipment_Name` varchar(100) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `Availability` tinyint(1) DEFAULT NULL,
  `TO_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_equipment`
--

INSERT INTO `lab_equipment` (`Equipment_ID`, `Equipment_Name`, `Capacity`, `Availability`, `TO_ID`) VALUES
(501, 'power Supply', 10, 1, 302),
(502, 'Oscilloscope', 5, 1, 302),
(503, 'signal generator', 5, 1, 302),
(504, 'computers', 30, 1, 301),
(505, 'routers', 5, 1, 301),
(506, 'mixer', 1, 1, 303),
(507, 'spade', 5, 1, 303);

-- --------------------------------------------------------

--
-- Table structure for table `lab_schedule`
--

CREATE TABLE `lab_schedule` (
  `Schedule_ID` int(11) NOT NULL,
  `Lab_Name` varchar(100) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Time_slot` varchar(50) DEFAULT NULL,
  `Group_No` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_schedule`
--

INSERT INTO `lab_schedule` (`Schedule_ID`, `Lab_Name`, `Date`, `Time_slot`, `Group_No`) VALUES
(603, 'MIPS Lab', '2025-06-27', '9.00AM - 12.00PM', 2);

-- --------------------------------------------------------

--
-- Table structure for table `lab_to`
--

CREATE TABLE `lab_to` (
  `TO_ID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_to`
--

INSERT INTO `lab_to` (`TO_ID`, `Name`, `Email`, `Password`) VALUES
(301, 'mr.Saman', 'saman@to.com', '$2y$10$EhHBeus/SxmF.8OEKmtzSOx6ylJdvaCZRoEZGiBfz6cyZq8Bydc9K'),
(302, 'Ms.methma', 'methma@to.com', 'password52'),
(303, 'Mr.hasantha', 'hasantha@to.com', 'password53');

-- --------------------------------------------------------

--
-- Table structure for table `lecture_in_charge`
--

CREATE TABLE `lecture_in_charge` (
  `LIC_ID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecture_in_charge`
--

INSERT INTO `lecture_in_charge` (`LIC_ID`, `Name`, `Email`, `Password`) VALUES
(1, 'mr.perera', 'perera@lic.com', '$2y$10$ti1mPZzUhgBVzG15Arf9DO25N5g.cC5acqXaMmeZYNAW21x4TXBEu'),
(2, 'Dr.ranasinghe', 'ranasinghe@lic.com', 'password12'),
(3, 'Dr.silva', 'silva@lic.com', 'password13');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `Student_ID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Student_ID`, `Name`, `Email`, `Password`) VALUES
(101, 'kamal', 'kamal@student.com', '$2y$10$sxsTqoJ2z9hgPwKiGGu80OxiKzDHLTY5w3X1INiA6mXWU.UDwg8Jm'),
(102, 'Nimal', 'nimal@student.com', 'password22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `equipment_availability`
--
ALTER TABLE `equipment_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_equipment_date` (`Equipment_ID`,`availability_date`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`Instructor_ID`);

--
-- Indexes for table `instructor_book_booking`
--
ALTER TABLE `instructor_book_booking`
  ADD PRIMARY KEY (`Instructor_ID`,`Booking_ID`),
  ADD KEY `Booking_ID` (`Booking_ID`);

--
-- Indexes for table `lab`
--
ALTER TABLE `lab`
  ADD PRIMARY KEY (`Lab_ID`);

--
-- Indexes for table `lab_availability`
--
ALTER TABLE `lab_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lab_date` (`Lab_ID`,`availability_date`);

--
-- Indexes for table `lab_booking`
--
ALTER TABLE `lab_booking`
  ADD PRIMARY KEY (`Booking_ID`),
  ADD KEY `Lab_ID` (`Lab_ID`);

--
-- Indexes for table `lab_equipment`
--
ALTER TABLE `lab_equipment`
  ADD PRIMARY KEY (`Equipment_ID`);

--
-- Indexes for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  ADD PRIMARY KEY (`Schedule_ID`);

--
-- Indexes for table `lab_to`
--
ALTER TABLE `lab_to`
  ADD PRIMARY KEY (`TO_ID`);

--
-- Indexes for table `lecture_in_charge`
--
ALTER TABLE `lecture_in_charge`
  ADD PRIMARY KEY (`LIC_ID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Student_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipment_availability`
--
ALTER TABLE `equipment_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `Instructor_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `lab_availability`
--
ALTER TABLE `lab_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_booking`
--
ALTER TABLE `lab_booking`
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=716;

--
-- AUTO_INCREMENT for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  MODIFY `Schedule_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=604;

--
-- AUTO_INCREMENT for table `lab_to`
--
ALTER TABLE `lab_to`
  MODIFY `TO_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `lecture_in_charge`
--
ALTER TABLE `lecture_in_charge`
  MODIFY `LIC_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `Student_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `instructor_book_booking`
--
ALTER TABLE `instructor_book_booking`
  ADD CONSTRAINT `instructor_book_booking_ibfk_1` FOREIGN KEY (`Instructor_ID`) REFERENCES `instructor` (`Instructor_ID`),
  ADD CONSTRAINT `instructor_book_booking_ibfk_2` FOREIGN KEY (`Booking_ID`) REFERENCES `lab_booking` (`Booking_ID`);

--
-- Constraints for table `lab_booking`
--
ALTER TABLE `lab_booking`
  ADD CONSTRAINT `lab_booking_ibfk_1` FOREIGN KEY (`Lab_ID`) REFERENCES `lab` (`Lab_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
