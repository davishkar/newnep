-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 06:00 PM
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
-- Database: `nep_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`) VALUES
(1, 'Main Admin', 'admin@gmail.com', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mobile` varchar(15) DEFAULT NULL,
  `abc_id` varchar(50) DEFAULT NULL,
  `year` varchar(5) DEFAULT NULL,
  `subjects` text DEFAULT NULL,
  `profile_completed` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students` pass:- Student@123 for all
--

INSERT INTO `students` (`id`, `name`, `email`, `password`, `created_at`, `mobile`, `abc_id`, `year`, `subjects`, `profile_completed`) VALUES
(1, 'Aarav Patel', 'aarav@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:00:00', '9876543210', 'ABC123456', 'FY', '{\"Course 1\":\"Mathematics\",\"Course 2\":\"Physics\",\"OE\":\"Yoga & Wellness\",\"KS\":\"Indian Constitution\"}', 1),
(2, 'Vivaan Singh', 'vivaan@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:05:00', '9876543211', 'ABC123457', 'SY', '{\"Major\":\"Computer Science\",\"Minor\":\"Electronics\",\"OE\":\"AI Basics\"}', 1),
(3, 'Aditya Sharma', 'aditya@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:10:00', '9876543212', 'ABC123458', 'TY', '{\"Major 1\":\"Data Science\",\"Major 2\":\"Machine Learning\",\"OJT\":\"Internship\"}', 1),
(4, 'Vihaan Gupta', 'vihaan@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:15:00', '9876543213', 'ABC123459', 'FY', '{\"Course 1\":\"Mathematics\",\"Course 2\":\"Physics\",\"OE\":\"Yoga & Wellness\",\"KS\":\"Indian Constitution\"}', 1),
(5, 'Arjun Kumar', 'arjun@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:20:00', '9876543214', 'ABC123460', 'SY', '{\"Major\":\"Computer Science\",\"Minor\":\"Electronics\",\"OE\":\"AI Basics\"}', 1),
(6, 'Sai Iyer', 'sai@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:25:00', '9876543215', 'ABC123461', 'TY', '{\"Major 1\":\"Data Science\",\"Major 2\":\"Machine Learning\",\"OJT\":\"Internship\"}', 1),
(7, 'Reya Verma', 'reya@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:30:00', '9876543216', 'ABC123462', 'FY', '{\"Course 1\":\"Mathematics\",\"Course 2\":\"Physics\",\"OE\":\"Yoga & Wellness\",\"KS\":\"Indian Constitution\"}', 1),
(8, 'Kiara Shah', 'kiara@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:35:00', '9876543217', 'ABC123463', 'SY', '{\"Major\":\"Computer Science\",\"Minor\":\"Electronics\",\"OE\":\"AI Basics\"}', 1),
(9, 'Myra Reddy', 'myra@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:40:00', '9876543218', 'ABC123464', 'TY', '{\"Major 1\":\"Data Science\",\"Major 2\":\"Machine Learning\",\"OJT\":\"Internship\"}', 1),
(10, 'Ananya Das', 'ananya@example.com', '$2y$10$EfCHBNEjmg9x0oS9sxvKjuLd64fpPuzAq6aoHC5N7snAMTIvPUD/y', '2026-02-07 10:45:00', '9876543219', 'ABC123465', 'FY', '{\"Course 1\":\"Mathematics\",\"Course 2\":\"Physics\",\"OE\":\"Yoga & Wellness\",\"KS\":\"Indian Constitution\"}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_details`
--

DROP TABLE IF EXISTS `student_details`;
CREATE TABLE `student_details` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `year` varchar(10) NOT NULL,
  `abc_id` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_details`
--

INSERT INTO `student_details` (`id`, `student_id`, `mobile`, `year`, `abc_id`, `course`, `dob`, `address`, `created_at`) VALUES
(1, 1, '9876543210', 'FY', 'ABC123456', 'B.Sc. Computer Science', '2005-01-15', 'Hostel Block A, Room 101', '2026-02-07 10:00:00'),
(2, 2, '9876543211', 'SY', 'ABC123457', 'B.Sc. Computer Science', '2004-03-22', 'Hostel Block B, Room 202', '2026-02-07 10:05:00'),
(3, 3, '9876543212', 'TY', 'ABC123458', 'B.Sc. Computer Science', '2003-07-10', 'Hostel Block C, Room 303', '2026-02-07 10:10:00'),
(4, 4, '9876543213', 'FY', 'ABC123459', 'B.Sc. Computer Science', '2005-05-05', 'Hostel Block A, Room 102', '2026-02-07 10:15:00'),
(5, 5, '9876543214', 'SY', 'ABC123460', 'B.Sc. Computer Science', '2004-09-18', 'Hostel Block B, Room 203', '2026-02-07 10:20:00'),
(6, 6, '9876543215', 'TY', 'ABC123461', 'B.Sc. Computer Science', '2003-11-30', 'Hostel Block C, Room 304', '2026-02-07 10:25:00'),
(7, 7, '9876543216', 'FY', 'ABC123462', 'B.Sc. Computer Science', '2005-02-14', 'Hostel Block A, Room 103', '2026-02-07 10:30:00'),
(8, 8, '9876543217', 'SY', 'ABC123463', 'B.Sc. Computer Science', '2004-06-25', 'Hostel Block B, Room 204', '2026-02-07 10:35:00'),
(9, 9, '9876543218', 'TY', 'ABC123464', 'B.Sc. Computer Science', '2003-12-05', 'Hostel Block C, Room 305', '2026-02-07 10:40:00'),
(10, 10, '9876543219', 'FY', 'ABC123465', 'B.Sc. Computer Science', '2005-08-08', 'Hostel Block A, Room 104', '2026-02-07 10:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

DROP TABLE IF EXISTS `student_marks`;
CREATE TABLE `student_marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `exam_name` varchar(50) DEFAULT NULL,
  `marks` varchar(10) NOT NULL,
  `credits` int(11) NOT NULL,
  `session` varchar(20) DEFAULT NULL,
  `category` varchar(30) DEFAULT NULL,
  `entered_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_marks`
--

INSERT INTO `student_marks` (`id`, `student_id`, `semester`, `subject`, `exam_name`, `marks`, `credits`, `session`, `category`, `entered_by`, `created_at`, `locked`) VALUES
(1, 1, 1, 'Mathematics', 'IA1', '18', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:00:00', 1),
(2, 1, 1, 'Mathematics', 'ESE', '85', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:00:00', 1),
(3, 2, 3, 'Computer Science', 'IA1', '19', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:05:00', 1),
(4, 2, 3, 'Computer Science', 'ESE', '90', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:05:00', 1),
(5, 3, 5, 'Data Science', 'IA1', '17', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:10:00', 1),
(6, 3, 5, 'Data Science', 'ESE', '88', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:10:00', 1),
(7, 4, 1, 'Physics', 'IA1', '15', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:15:00', 1),
(8, 4, 1, 'Physics', 'ESE', '75', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:15:00', 1),
(9, 5, 3, 'Electronics', 'IA1', '18', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:20:00', 1),
(10, 5, 3, 'Electronics', 'ESE', '82', 4, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:20:00', 1),
(11, 7, 1, 'Yoga & Wellness', 'IA1', '19', 2, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:30:00', 1),
(12, 7, 1, 'Yoga & Wellness', 'ESE', '45', 2, 'OCT/NOV-2025', 'Regular', 1, '2026-02-07 10:30:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(150) DEFAULT NULL,
  `year` enum('FY','SY','TY') DEFAULT NULL,
  `subject_type` varchar(50) DEFAULT NULL,
  `credits` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `year`, `subject_type`, `credits`) VALUES
(1, 'DSA', 'TY', 'Major', 4),
(2, 'AI', 'TY', 'Major', 4),
(3, 'Mathematics', 'FY', 'Major', 4),
(4, 'Physics', 'FY', 'Major', 4),
(5, 'Computer Science', 'SY', 'Major', 4),
(6, 'Electronics', 'SY', 'Minor', 4);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `email`, `password`, `department`, `created_at`) VALUES
(1, 'Komal', 'komal@gmail.com', '123456', 'Computer Science', '2026-01-09 07:52:15'),
(2, 'amar mane', 'a@gmail.com', '123456', 'cs', '2026-01-12 15:26:40'),
(3, 'smruti kulkarni', 's@gmail.com', '123456', 'cs', '2026-01-23 16:29:46');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_year` enum('All','FY','SY','TY') NOT NULL,
  `posted_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `message`, `target_year`, `posted_by`, `created_at`) VALUES
(1, 'Welcome to New Semester', 'Classes for the new academic year will commence from June 15th. Please check your timetables for details.', 'All', 'Main Admin', '2026-02-01 10:00:00'),
(2, 'Assignment Submission Deadline', 'Last date to submit Assignment 1 for DSA is extended to next Friday.', 'TY', 'Komal', '2026-02-05 14:30:00'),
(3, 'Guest Lecture on AI', 'A guest lecture on \"Future of AI\" is organized on Saturday at 10 AM in the Auditorium. All students are requested to attend.', 'All', 'amar mane', '2026-02-06 09:15:00'),
(4, 'Holiday Notice', 'College will remain closed on Monday due to public holiday.', 'All', 'Main Admin', '2026-02-07 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late') NOT NULL,
  `marked_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `marked_by` (`marked_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `subject`, `date`, `status`, `marked_by`, `created_at`) VALUES
(1, 1, 'Mathematics', '2026-02-01', 'Present', 1, '2026-02-01 10:00:00'),
(2, 1, 'Mathematics', '2026-02-02', 'Present', 1, '2026-02-02 10:00:00'),
(3, 1, 'Mathematics', '2026-02-03', 'Absent', 1, '2026-02-03 10:00:00'),
(4, 1, 'Mathematics', '2026-02-04', 'Present', 1, '2026-02-04 10:00:00'),
(5, 2, 'Computer Science', '2026-02-01', 'Present', 1, '2026-02-01 10:00:00'),
(6, 2, 'Computer Science', '2026-02-02', 'Late', 1, '2026-02-02 10:00:00'),
(7, 3, 'Data Science', '2026-02-01', 'Present', 1, '2026-02-01 10:00:00'),
(8, 3, 'Data Science', '2026-02-02', 'Present', 1, '2026-02-02 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `message`, `submitted_at`) VALUES
(1, 'Rahul Verma', 'rahul@test.com', 'Great portal! Very easy to use and navigate.', '2026-02-02 12:00:00'),
(2, 'Priya Singh', 'priya@test.com', 'Can you please add a dark mode option? It would be really helpful.', '2026-02-05 16:20:00');

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_details`
--
ALTER TABLE `student_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mark` (`student_id`,`semester`,`subject`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_details`
--
ALTER TABLE `student_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for table `student_details`
--
ALTER TABLE `student_details`
  ADD CONSTRAINT `student_details_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
