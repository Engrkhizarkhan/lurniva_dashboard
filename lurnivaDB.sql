-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2025 at 08:00 PM
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
-- Database: `lurniva`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_fee_types`
--

CREATE TABLE `class_fee_types` (
  `id` int(11) NOT NULL,
  `fee_structure_id` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `class_grade` varchar(50) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_fee_types`
--

INSERT INTO `class_fee_types` (`id`, `fee_structure_id`, `school_id`, `class_grade`, `fee_type_id`, `rate`) VALUES
(13, 5, 4, '1', 11, 2000.00),
(14, 5, 4, '1', 12, 200.00),
(15, 5, 4, '1', 13, 1500.00);

-- --------------------------------------------------------

--
-- Table structure for table `class_timetable_details`
--

CREATE TABLE `class_timetable_details` (
  `id` int(11) NOT NULL,
  `timing_meta_id` int(11) NOT NULL,
  `period_number` int(11) NOT NULL,
  `period_name` varchar(50) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `teacher_id` int(11) DEFAULT NULL,
  `is_break` tinyint(1) DEFAULT 0,
  `period_type` enum('Normal','Lab','Break','Sports','Library') DEFAULT 'Normal',
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_timetable_details`
--

INSERT INTO `class_timetable_details` (`id`, `timing_meta_id`, `period_number`, `period_name`, `start_time`, `end_time`, `created_at`, `teacher_id`, `is_break`, `period_type`, `created_by`) VALUES
(3, 6, 1, 'islamyat', '08:00:00', '08:30:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(4, 6, 2, 'urdu', '08:30:00', '09:00:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(5, 6, 3, 'english', '09:00:00', '09:30:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(6, 6, 4, 'math', '09:30:00', '10:00:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(7, 6, 5, 'science', '10:00:00', '10:30:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(8, 6, 6, 'Brack', '10:30:00', '11:00:00', '0000-00-00 00:00:00', 10, 0, 'Break', 4),
(9, 6, 7, 'Pak Study', '11:00:00', '11:30:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(10, 6, 8, 'Play', '11:30:00', '12:00:00', '0000-00-00 00:00:00', 0, 0, 'Normal', 4),
(11, 7, 1, 'Islamyat', '08:00:00', '08:30:00', '0000-00-00 00:00:00', 10, 0, 'Normal', 4),
(12, 7, 2, 'Urdu', '08:30:00', '09:00:00', '0000-00-00 00:00:00', 10, 0, 'Normal', 4),
(13, 7, 3, 'English', '09:00:00', '09:30:00', '0000-00-00 00:00:00', 10, 0, 'Normal', 4),
(14, 7, 4, 'math', '09:30:00', '10:00:00', '0000-00-00 00:00:00', 10, 0, 'Normal', 4),
(15, 7, 5, 'Science', '10:00:00', '10:30:00', '0000-00-00 00:00:00', 10, 0, 'Normal', 4),
(16, 7, 6, 'Brack', '10:30:00', '11:00:00', '0000-00-00 00:00:00', 9, 0, 'Normal', 4),
(17, 7, 7, 'pakstudy', '11:00:00', '11:30:00', '0000-00-00 00:00:00', 10, 0, 'Normal', 4),
(18, 7, 8, 'Science', '11:30:00', '12:00:00', '0000-00-00 00:00:00', 10, 0, 'Lab', 4),
(19, 8, 1, 'English', '07:25:00', '08:00:00', '0000-00-00 00:00:00', 11, 0, 'Normal', 5),
(20, 8, 2, 'Biology', '08:05:00', '08:55:00', '0000-00-00 00:00:00', 12, 0, 'Normal', 5),
(21, 8, 3, 'Physical', '09:00:00', '21:25:00', '0000-00-00 00:00:00', 0, 0, 'Normal', 5),
(22, 8, 4, 'Chemistry ', '10:19:00', '11:02:00', '0000-00-00 00:00:00', 0, 0, 'Normal', 5);

-- --------------------------------------------------------

--
-- Table structure for table `class_timetable_meta`
--

CREATE TABLE `class_timetable_meta` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `timing_table_id` int(255) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `section` varchar(10) NOT NULL,
  `total_periods` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_finalized` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_timetable_meta`
--

INSERT INTO `class_timetable_meta` (`id`, `school_id`, `timing_table_id`, `class_name`, `section`, `total_periods`, `created_at`, `is_finalized`, `created_by`) VALUES
(6, 4, 1, '1', 'A', 8, '2025-08-22 12:10:09', 1, 4),
(7, 4, 1, '2', 'A', 8, '2025-08-22 12:10:09', 1, 4),
(8, 5, 3, '5', 'B', 5, '2025-08-22 16:27:03', 1, 5),
(9, 5, 3, '6', 'b', 5, '2025-08-22 16:30:00', 0, 5);

-- --------------------------------------------------------

--
-- Table structure for table `class_timetable_weekdays`
--

CREATE TABLE `class_timetable_weekdays` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `weekday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `assembly_time` time NOT NULL,
  `leave_time` time NOT NULL,
  `total_periods` int(11) NOT NULL,
  `is_half_day` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_timetable_weekdays`
--

INSERT INTO `class_timetable_weekdays` (`id`, `school_id`, `weekday`, `assembly_time`, `leave_time`, `total_periods`, `is_half_day`, `created_at`) VALUES
(2, 4, 'Friday', '08:00:00', '11:00:00', 5, 1, '2025-08-22 12:10:09'),
(3, 4, 'Friday', '08:00:00', '11:00:00', 5, 1, '2025-08-22 12:10:09'),
(4, 5, 'Saturday', '07:10:00', '10:22:00', 4, 1, '2025-08-22 16:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `diary_entries`
--

CREATE TABLE `diary_entries` (
  `id` int(11) NOT NULL,
  `school_id` varchar(255) NOT NULL,
  `class_meta_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `deadline` date NOT NULL,
  `parent_approval_required` enum('yes','no') DEFAULT 'no',
  `student_option` enum('all','specific') DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diary_students`
--

CREATE TABLE `diary_students` (
  `id` int(11) NOT NULL,
  `approve_parent` varchar(255) NOT NULL,
  `diary_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_notices`
--

CREATE TABLE `digital_notices` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `notice_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `notice_type` varchar(100) DEFAULT NULL,
  `audience` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `school_id`, `exam_name`, `total_marks`, `created_at`) VALUES
(1, 4, 'Unit test 1', 600, '2025-08-25 15:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `exam_schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `marks_obtained` int(11) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `school_id`, `exam_schedule_id`, `student_id`, `subject_id`, `total_marks`, `marks_obtained`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 3, 3, 0, 67, 'abc', '2025-08-26 16:11:02', '2025-08-26 16:11:02'),
(2, 4, 2, 3, 4, 0, 98, 'abc', '2025-08-26 16:11:02', '2025-08-26 16:11:02'),
(3, 4, 3, 3, 5, 0, 76, 'bsdf', '2025-08-26 16:11:02', '2025-08-26 16:11:02'),
(4, 4, 4, 3, 6, 0, 78, 'asf', '2025-08-26 16:11:02', '2025-08-26 16:11:02'),
(5, 4, 5, 3, 7, 0, 56, 'asf', '2025-08-26 16:11:02', '2025-08-26 16:11:02'),
(6, 4, 6, 3, 9, 0, 87, 'asdf', '2025-08-26 16:11:02', '2025-08-26 16:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE `exam_schedule` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `total_marks` int(11) DEFAULT 0,
  `exam_date` date NOT NULL,
  `exam_time` time NOT NULL,
  `day` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exam_schedule`
--

INSERT INTO `exam_schedule` (`id`, `school_id`, `exam_name`, `class_name`, `subject_id`, `total_marks`, `exam_date`, `exam_time`, `day`, `created_at`, `updated_at`) VALUES
(1, 4, '1', '1', 3, 100, '2025-08-25', '10:00:00', 'Monday', '2025-08-25 18:53:38', '2025-08-25 18:53:38'),
(2, 4, '1', '1', 4, 100, '2025-08-26', '10:00:00', 'Tuesday', '2025-08-25 18:53:38', '2025-08-25 18:53:38'),
(3, 4, '1', '1', 5, 100, '2025-08-27', '10:00:00', 'Wednesday', '2025-08-25 18:53:38', '2025-08-25 18:53:38'),
(4, 4, '1', '1', 6, 100, '2025-08-28', '10:00:00', 'Thursday', '2025-08-25 18:53:38', '2025-08-25 18:53:38'),
(5, 4, '1', '1', 7, 100, '2025-08-29', '10:00:00', 'Friday', '2025-08-25 18:53:38', '2025-08-25 18:53:38'),
(6, 4, '1', '1', 9, 100, '2025-08-30', '10:00:00', 'Saturday', '2025-08-25 18:53:38', '2025-08-25 18:53:38');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `cnic` varchar(25) NOT NULL,
  `qualification` varchar(100) NOT NULL,
  `subjects` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `joining_date` date NOT NULL,
  `employment_type` enum('Full-time','Part-time','Contractual') NOT NULL,
  `schedule_preference` enum('Morning','Evening') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `code_expires_at` datetime DEFAULT NULL,
  `verification_attempts` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `campus_id`, `full_name`, `cnic`, `qualification`, `subjects`, `email`, `password`, `phone`, `address`, `joining_date`, `employment_type`, `schedule_preference`, `photo`, `created_at`, `status`, `rating`, `verification_code`, `is_verified`, `code_expires_at`, `verification_attempts`) VALUES
(9, 4, 'Shayan Khan', '3740587639645', 'BS SE', 'CSIT', 'shayanm1215225@gmail.com', '$2y$10$x4e587EPTl3QXIRpr8KHW.B/7JJXK6QMDdzSX5JjBDCfB1hD6pDsG', '03091991002', 'jehangira moh awan Swabi', '2023-06-23', 'Full-time', 'Morning', '1755872433_Untitled.jpg', '2025-08-22 14:20:33', 'pending', NULL, NULL, 1, '2025-08-23 16:52:19', 0),
(10, 4, 'Sana Khan', '3740587946212', 'FSc', 'Math, physic', 'shayans1215225@gmai.com', '$2y$10$f4tVQ2Y3ekH6p3GvXg0vZeMwjpQEhbAEuBvFz0adbm3lMmtCf0lce', '03491916168', 'jehangira moh awan Swabi', '2023-06-23', 'Full-time', 'Morning', '1755872508_dme.jpg', '2025-08-22 14:21:48', 'pending', NULL, NULL, 0, NULL, 0),
(11, 5, 'Ikhtisham wahabi', '1620122617891', 'Metric', 'English', 'ikhtishamakhtar@gmail.com', '$2y$10$XGVmzjJQLumJxbMhkO827Oe2/hwkMtcGRpmC3h1d529slA1zyZ6pi', '03414738901', 'Jahengira swabi', '2025-08-02', 'Full-time', 'Morning', '1755880919_IMG_3698.jpeg', '2025-08-22 16:41:59', 'pending', NULL, NULL, 0, NULL, 0),
(12, 5, 'Munir ahmad', '1620122617891', 'Metric', 'English', 'ikhtishamakhtar@gmail.com', '$2y$10$v3oiUE1zMSD8HM0sf2PFwuLZiwywI03EWQ1yRXW7uCe1GcxGya3IG', '0355627189', 'Jahengira swabi', '2025-08-02', 'Full-time', 'Morning', '', '2025-08-22 16:49:24', 'pending', NULL, NULL, 0, NULL, 0),
(13, 5, 'Junaid ahmed', '1620122617891', 'Metric', 'English', 'ikhtishamakhtar@gmail.com', '$2y$10$0jPhKvUMmeAoFbqsjezkVe7Eqyt7W0zP/G4Z/yvAGKmCIMKo0yuN.', '0355627189', 'Jahengira swabi', '2025-08-15', 'Part-time', 'Evening', '', '2025-08-22 16:49:46', 'pending', NULL, NULL, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `faculty_attendance`
--

CREATE TABLE `faculty_attendance` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Leave') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_leaves`
--

CREATE TABLE `faculty_leaves` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `leave_type` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) GENERATED ALWAYS AS (to_days(`end_date`) - to_days(`start_date`) + 1) STORED,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `fee_slip_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `status` enum('PENDING','CLEARED','FAILED') DEFAULT 'CLEARED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `school_id`, `fee_slip_id`, `student_id`, `amount`, `payment_method`, `payment_date`, `status`, `created_at`) VALUES
(1, 4, 3, 3, 3000.00, 'Cash', '2025-08-28', 'CLEARED', '2025-08-28 18:29:48'),
(2, 4, 3, 3, 700.00, 'Cash', '2025-08-28', 'CLEARED', '2025-08-28 18:29:57'),
(3, 4, 4, 3, 3000.00, 'Cash', '2025-08-30', 'CLEARED', '2025-08-30 16:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `fee_periods`
--

CREATE TABLE `fee_periods` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `period_type` enum('monthly','quarterly','term','custom') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_periods`
--

INSERT INTO `fee_periods` (`id`, `school_id`, `period_name`, `period_type`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(5, 4, 'Aug 2025', 'monthly', '2025-08-01', '2025-08-31', 0, '2025-08-22 19:17:50'),
(6, 4, 'jan 2025', 'monthly', '2025-01-01', '2025-01-31', 0, '2025-08-22 19:19:01');

-- --------------------------------------------------------

--
-- Table structure for table `fee_slip_details`
--

CREATE TABLE `fee_slip_details` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_period_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `scholarship_amount` decimal(10,2) DEFAULT 0.00,
  `net_payable` decimal(10,2) NOT NULL,
  `balance_due` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('UNPAID','PARTIALLY_PAID','PAID') DEFAULT 'UNPAID',
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_slip_details`
--

INSERT INTO `fee_slip_details` (`id`, `school_id`, `student_id`, `fee_period_id`, `total_amount`, `scholarship_amount`, `net_payable`, `balance_due`, `payment_status`, `amount_paid`, `payment_date`, `created_at`) VALUES
(3, 4, 3, 5, 3700.00, 0.00, 3700.00, 0.00, 'PAID', 3700.00, '2025-08-23', '2025-08-23 05:09:09'),
(4, 4, 3, 6, 3700.00, 0.00, 3700.00, 700.00, 'PARTIALLY_PAID', 3000.00, '2025-08-30', '2025-08-30 16:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `class_grade` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `frequency` enum('monthly','yearly','one_time') DEFAULT 'monthly',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `school_id`, `class_grade`, `amount`, `frequency`, `status`, `created_at`) VALUES
(5, 4, '1', 3700.00, 'monthly', 'active', '2025-08-23 05:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_types`
--

INSERT INTO `fee_types` (`id`, `school_id`, `fee_name`, `status`, `created_at`) VALUES
(11, 4, 'Tuition fee', 'active', '2025-08-23 09:55:56'),
(12, 4, 'App charges', 'active', '2025-08-23 10:02:49'),
(13, 4, 'Stationary', 'active', '2025-08-23 10:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_announcements`
--

CREATE TABLE `meeting_announcements` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `meeting_agenda` text DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `meeting_time` time NOT NULL,
  `meeting_person` enum('admin','teacher','parent') NOT NULL,
  `person_id_one` int(11) NOT NULL,
  `meeting_person2` enum('admin','teacher','parent') NOT NULL,
  `person_id_two` int(11) NOT NULL,
  `status` enum('scheduled','cancelled','completed') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meeting_requests`
--

CREATE TABLE `meeting_requests` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `requested_by` enum('admin','teacher','parent') NOT NULL,
  `requester_id` int(11) NOT NULL,
  `with_meeting` enum('admin','teacher','parent') NOT NULL,
  `id_meeter` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `agenda` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `sender_designation` enum('admin','teacher','student') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_designation` enum('admin','teacher','student') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `file_attachment` varchar(255) DEFAULT NULL,
  `voice_note` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `slip_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `method` enum('cash','card','online','bank_transfer') DEFAULT 'cash',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('percentage','fixed') DEFAULT 'fixed',
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('approved','pending','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(11) NOT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `school_type` enum('Public','Private','Charter') DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `affiliation_board` varchar(100) DEFAULT NULL,
  `school_email` varchar(150) DEFAULT NULL,
  `school_phone` varchar(20) DEFAULT NULL,
  `school_website` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `admin_contact_person` varchar(255) DEFAULT NULL,
  `admin_email` varchar(150) DEFAULT NULL,
  `admin_phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `code_expires_at` datetime DEFAULT NULL,
  `verification_attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `school_name`, `school_type`, `registration_number`, `affiliation_board`, `school_email`, `school_phone`, `school_website`, `country`, `state`, `city`, `address`, `logo`, `admin_contact_person`, `admin_email`, `admin_phone`, `password`, `verification_code`, `is_verified`, `code_expires_at`, `verification_attempts`, `created_at`) VALUES
(4, 'Kurtlar Developer', 'Private', '1215225', 'mardan', 'kurtlar1215225@gmail.com', '03091991002', 'https://kurtlardeveloper.com', 'Pakistan', 'KPK', 'Jehangira', 'jehangira moh awan Swabi', '1755841590_apple-touch-icon.png', 'Shayan Khan', 'school12@gmail.com', '03491916168', '$2y$10$2uqBC9ly54skkEagFhNLh.9RSnOgnNVz/GP1IkuFu8hkWhjtL8R7O', NULL, 1, '2025-08-22 18:31:33', 0, '2025-08-22 05:46:30'),
(5, 'Raffey school jahengira', 'Private', '003345671889', 'Mardan board', 'abdullahparkour17@gmail.com', '03466294461', 'http://www.epop.pk/portal/oxford-public-school-swabi/5510', 'Pakustan', 'Peshawar', 'Kpk', 'Jahengira swabi', '1755876579_IMG_3192.png', 'Abdullah Raffey', 'abdullahparkour17@gmail.com', '03499545143', '$2y$10$6r/NkrW5AsXTPqTRcqiL4Od2ZMs2PxbuegavxuBTCCAejp9L9UoIa', NULL, 1, '2025-08-22 17:34:39', 0, '2025-08-22 15:29:39');

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL,
  `person` enum('admin','facility','student') NOT NULL,
  `person_id` int(11) NOT NULL,
  `layout` tinyint(1) NOT NULL COMMENT '1=Light, 2=Dark',
  `sidebar_color` tinyint(1) NOT NULL COMMENT '1=Light Sidebar, 2=Dark Sidebar',
  `color_theme` varchar(50) DEFAULT NULL,
  `mini_sidebar` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Off, 1=On',
  `sticky_header` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Off, 1=On',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`id`, `person`, `person_id`, `layout`, `sidebar_color`, `color_theme`, `mini_sidebar`, `sticky_header`, `created_at`, `updated_at`) VALUES
(1, 'admin', 4, 1, 1, 'white', 0, 0, '2025-08-22 11:17:09', '2025-08-22 16:38:46'),
(2, 'student', 3, 1, 1, 'white', 0, 0, '2025-08-22 17:13:32', '2025-08-22 17:17:14'),
(3, 'admin', 5, 1, 1, 'white', 0, 0, '2025-08-22 17:16:18', '2025-08-22 17:17:17'),
(6, 'facility', 9, 1, 1, 'white', 0, 0, '2025-08-23 12:08:32', '2025-08-23 15:08:34'),
(7, 'student', 4, 1, 1, 'white', 0, 0, '2025-08-27 16:38:54', '2025-08-27 16:38:54'),
(8, 'student', 5, 1, 1, 'white', 0, 0, '2025-08-28 19:08:40', '2025-08-28 19:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `school_tasks`
--

CREATE TABLE `school_tasks` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `task_description` text NOT NULL,
  `due_date` date NOT NULL,
  `task_completed_percent` decimal(5,2) DEFAULT 0.00,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_task_assignees`
--

CREATE TABLE `school_task_assignees` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `assigned_to_type` enum('teacher','student') NOT NULL,
  `assigned_to_id` int(11) NOT NULL,
  `status` enum('Active','Not Active') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_timings`
--

CREATE TABLE `school_timings` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `assembly_time` time NOT NULL,
  `leave_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `half_day_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`half_day_config`)),
  `is_finalized` tinyint(1) DEFAULT 0,
  `is_preview` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `school_timings`
--

INSERT INTO `school_timings` (`id`, `school_id`, `assembly_time`, `leave_time`, `created_at`, `half_day_config`, `is_finalized`, `is_preview`, `created_by`) VALUES
(1, 4, '08:00:00', '12:00:00', '2025-08-22 12:10:09', NULL, 1, 0, 4),
(3, 5, '07:00:00', '13:30:00', '2025-08-22 16:30:00', NULL, 0, 0, 5);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `parent_name` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `cnic_formb` varchar(20) DEFAULT NULL,
  `class_grade` varchar(50) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `code_expires_at` datetime DEFAULT NULL,
  `verification_attempts` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive','Pending Verification') DEFAULT 'Pending Verification',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `school_id`, `parent_name`, `full_name`, `gender`, `dob`, `cnic_formb`, `class_grade`, `section`, `roll_number`, `address`, `email`, `parent_email`, `phone`, `profile_photo`, `password`, `verification_code`, `is_verified`, `code_expires_at`, `verification_attempts`, `status`, `created_at`) VALUES
(3, 4, 'Feheem', 'Ferdeen', 'Male', '2020-02-02', '1023343345553', '1', 'A', '1', 'it is a address', 'ferdeen@gmail.com', 'faheem@gmail.com', '03030204102', '1755882812_user-8.png', '$2y$10$vu1OqO/ZJC/YKARHx0LlRueL/JojWg3AguqTPbOyyXmJl5kcjELeO', NULL, 1, '2025-08-22 19:18:32', 0, '', '2025-08-22 17:13:32'),
(4, 4, 'Khan', 'sun', 'Male', '2017-09-27', '232423423423423', '1', 'A', '12', 'kjdgkdfhgksd', 'awasjanzab1919@gmail.com', 'abc123@gmil.com', '02813823801', NULL, '$2y$10$iP.kySkNejvxSx7r5rFq8edF21Gg71Vc/hlguQmhcCDoQRCW5Imxi', '748976', 1, '2025-08-27 18:44:49', 0, '', '2025-08-27 16:38:54'),
(5, 4, 'Farman Ullah', 'khan khan', 'Male', '2005-12-01', '7837493284923', '1', 'A', '19985', 'Peshware', 'jfksdjf@gmail.com', 'jdkflsdasd123@gmail.com', '03023434233', NULL, '$2y$10$bXk2yqpPTdi4XJFtOp6JK.WvY3d4iBT90IW.p4.75TeF1fV/kZ4k6', '962419', 0, '2025-08-28 21:13:40', 0, '', '2025-08-28 19:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_meta_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Present','Absent','Leave') NOT NULL,
  `date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`id`, `school_id`, `teacher_id`, `class_meta_id`, `student_id`, `status`, `date`, `created_at`) VALUES
(3, 4, 9, 6, 3, 'Absent', '2025-08-24', '2025-08-24 19:17:24'),
(4, 4, 9, 6, 3, 'Present', '2025-08-23', '2025-08-24 19:20:30'),
(5, 4, 9, 6, 3, 'Present', '2025-08-21', '2025-08-24 19:21:50'),
(6, 4, 9, 6, 3, 'Absent', '2025-07-21', '2025-08-24 19:22:45'),
(7, 4, 9, 6, 3, 'Absent', '2025-01-21', '2025-08-24 19:23:11');

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_plans`
--

CREATE TABLE `student_fee_plans` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_component` int(11) NOT NULL,
  `base_amount` decimal(10,2) NOT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_leaves`
--

CREATE TABLE `student_leaves` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `leave_type` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_results`
--

INSERT INTO `student_results` (`id`, `school_id`, `assignment_id`, `student_id`, `marks_obtained`, `remarks`, `attachment`, `created_at`, `updated_at`) VALUES
(3, 4, 3, 3, 50.00, 'kdifdnfi', '', '2025-08-23 22:35:27', NULL),
(4, 4, 2, 3, 90.00, 'sdfsdf', '', '2025-08-23 22:43:27', NULL),
(5, 4, 4, 3, 60.00, 'jfksdjf', '', '2025-08-24 10:14:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_meta_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `type` enum('Assignment','Test') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `due_date` date NOT NULL,
  `total_marks` int(5) NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`id`, `school_id`, `teacher_id`, `class_meta_id`, `subject`, `type`, `title`, `description`, `due_date`, `total_marks`, `attachment`, `created_at`, `updated_at`) VALUES
(2, 4, 9, 6, 'english', 'Assignment', 'abc', 'it is description', '2025-08-26', 100, 'assignment_1755970254.', '2025-08-23 22:30:54', '2025-08-24 10:24:58'),
(3, 4, 9, 6, 'math', 'Test', 'titel', 'abc (../uploads/assignment/assignment_1755970254.', '2025-08-25', 100, 'assignment_1755970490.jpg', '2025-08-23 22:34:50', NULL),
(4, 4, 9, 6, 'english', 'Test', 'titel', 'ksdfjsdkf', '2025-08-29', 80, 'assignment_1756012434.png', '2025-08-24 10:13:54', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_fee_types`
--
ALTER TABLE `class_fee_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `fee_type_id` (`fee_type_id`),
  ADD KEY `fk_fee_structure` (`fee_structure_id`);

--
-- Indexes for table `class_timetable_details`
--
ALTER TABLE `class_timetable_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_timetable_meta`
--
ALTER TABLE `class_timetable_meta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_timetable_weekdays`
--
ALTER TABLE `class_timetable_weekdays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_weekdays_school` (`school_id`);

--
-- Indexes for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diary_students`
--
ALTER TABLE `diary_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diary_id` (`diary_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `digital_notices`
--
ALTER TABLE `digital_notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exams_school` (`school_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty_attendance`
--
ALTER TABLE `faculty_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `faculty_leaves`
--
ALTER TABLE `faculty_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_slip_id` (`fee_slip_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fee_periods`
--
ALTER TABLE `fee_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `fee_slip_details`
--
ALTER TABLE `fee_slip_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_period_id` (`fee_period_id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `meeting_announcements`
--
ALTER TABLE `meeting_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `meeting_requests`
--
ALTER TABLE `meeting_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_scholarships_school` (`school_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD UNIQUE KEY `school_email` (`school_email`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_tasks`
--
ALTER TABLE `school_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_school_tasks_school` (`school_id`);

--
-- Indexes for table `school_task_assignees`
--
ALTER TABLE `school_task_assignees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `school_timings`
--
ALTER TABLE `school_timings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_fee_plans`
--
ALTER TABLE `student_fee_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_fee_component` (`fee_component`);

--
-- Indexes for table `student_leaves`
--
ALTER TABLE `student_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student` (`student_id`),
  ADD KEY `fk_school` (`school_id`),
  ADD KEY `fk_teacher` (`teacher_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_submission` (`assignment_id`,`student_id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_fee_types`
--
ALTER TABLE `class_fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `class_timetable_details`
--
ALTER TABLE `class_timetable_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `class_timetable_meta`
--
ALTER TABLE `class_timetable_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `class_timetable_weekdays`
--
ALTER TABLE `class_timetable_weekdays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `diary_entries`
--
ALTER TABLE `diary_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `diary_students`
--
ALTER TABLE `diary_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_notices`
--
ALTER TABLE `digital_notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `faculty_attendance`
--
ALTER TABLE `faculty_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faculty_leaves`
--
ALTER TABLE `faculty_leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_periods`
--
ALTER TABLE `fee_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fee_slip_details`
--
ALTER TABLE `fee_slip_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fee_types`
--
ALTER TABLE `fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `meeting_announcements`
--
ALTER TABLE `meeting_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meeting_requests`
--
ALTER TABLE `meeting_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `school_tasks`
--
ALTER TABLE `school_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `school_task_assignees`
--
ALTER TABLE `school_task_assignees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `school_timings`
--
ALTER TABLE `school_timings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_fee_plans`
--
ALTER TABLE `student_fee_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_leaves`
--
ALTER TABLE `student_leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_fee_types`
--
ALTER TABLE `class_fee_types`
  ADD CONSTRAINT `class_fee_types_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  ADD CONSTRAINT `class_fee_types_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `fk_fee_structure` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_timetable_weekdays`
--
ALTER TABLE `class_timetable_weekdays`
  ADD CONSTRAINT `fk_weekdays_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `diary_students`
--
ALTER TABLE `diary_students`
  ADD CONSTRAINT `diary_students_ibfk_1` FOREIGN KEY (`diary_id`) REFERENCES `diary_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diary_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `fk_exams_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD CONSTRAINT `exam_schedule_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `class_timetable_details` (`id`);

--
-- Constraints for table `faculty_attendance`
--
ALTER TABLE `faculty_attendance`
  ADD CONSTRAINT `faculty_attendance_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_attendance_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_leaves`
--
ALTER TABLE `faculty_leaves`
  ADD CONSTRAINT `faculty_leaves_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_leaves_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`fee_slip_id`) REFERENCES `fee_slip_details` (`id`),
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `fee_periods`
--
ALTER TABLE `fee_periods`
  ADD CONSTRAINT `fee_periods_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_slip_details`
--
ALTER TABLE `fee_slip_details`
  ADD CONSTRAINT `fee_slip_details_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_slip_details_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_slip_details_ibfk_3` FOREIGN KEY (`fee_period_id`) REFERENCES `fee_periods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD CONSTRAINT `fee_types_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `meeting_announcements`
--
ALTER TABLE `meeting_announcements`
  ADD CONSTRAINT `meeting_announcements_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `meeting_requests`
--
ALTER TABLE `meeting_requests`
  ADD CONSTRAINT `meeting_requests_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD CONSTRAINT `fk_scholarships_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `school_tasks`
--
ALTER TABLE `school_tasks`
  ADD CONSTRAINT `fk_school_tasks_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `school_task_assignees`
--
ALTER TABLE `school_task_assignees`
  ADD CONSTRAINT `school_task_assignees_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `school_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

--
-- Constraints for table `student_fee_plans`
--
ALTER TABLE `student_fee_plans`
  ADD CONSTRAINT `fk_fee_component` FOREIGN KEY (`fee_component`) REFERENCES `fee_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_fee_plans_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  ADD CONSTRAINT `student_fee_plans_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `student_leaves`
--
ALTER TABLE `student_leaves`
  ADD CONSTRAINT `fk_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
