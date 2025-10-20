-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2025 at 05:16 PM
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
-- Table structure for table `app_admin` 
--

CREATE TABLE IF NOT EXISTS `app_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `message_email` varchar(150) DEFAULT NULL,
  `merchant_id` varchar(150) DEFAULT NULL,
  `store_id` varchar(150) DEFAULT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `role` enum('super_admin') DEFAULT 'super_admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verification_code` varchar(10) DEFAULT NULL,
  `code_expires_at` datetime DEFAULT NULL,
   PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `app_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE IF NOT EXISTS `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `available` int(11) DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `books`
--

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE IF NOT EXISTS `buses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `buses`
--

-- --------------------------------------------------------

--
-- Table structure for table `bus_problems`
--

CREATE TABLE IF NOT EXISTS `bus_problems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `problem` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('Open','Resolved') DEFAULT 'Open',
   PRIMARY KEY (`id`),
   KEY `bus_id` (`bus_id`),
KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bus_problems`
--

-- --------------------------------------------------------

--
-- Table structure for table `class_fee_types`
--

CREATE TABLE IF NOT EXISTS `class_fee_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_structure_id` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `class_grade` varchar(50) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`),
KEY `fee_type_id` (`fee_type_id`),
KEY `fk_fee_structure` (`fee_structure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_fee_types`
--

-- --------------------------------------------------------

--
-- Table structure for table `class_timetable_details`
--

CREATE TABLE IF NOT EXISTS `class_timetable_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timing_meta_id` int(11) NOT NULL,
  `period_number` int(11) NOT NULL,
  `period_name` varchar(50) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `teacher_id` int(11) DEFAULT NULL,
  `is_break` tinyint(1) DEFAULT 0,
  `period_type` enum('Normal','Lab','Break','Sports','Library') DEFAULT 'Normal',
  `created_by` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_timetable_details`
--

-- --------------------------------------------------------

--
-- Table structure for table `class_timetable_meta`
--

CREATE TABLE IF NOT EXISTS `class_timetable_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `timing_table_id` int(255) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `section` varchar(10) NOT NULL,
  `total_periods` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_finalized` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_timetable_meta`
--

-- --------------------------------------------------------

--
-- Table structure for table `class_timetable_weekdays`
--

CREATE TABLE IF NOT EXISTS `class_timetable_weekdays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `weekday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `assembly_time` time NOT NULL,
  `leave_time` time NOT NULL,
  `total_periods` int(11) NOT NULL,
  `is_half_day` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `fk_weekdays_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_timetable_weekdays`
--

-- --------------------------------------------------------

--
-- Table structure for table `diary_entries`
--

CREATE TABLE IF NOT EXISTS `diary_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `diary_entries`
--


-- --------------------------------------------------------

--
-- Table structure for table `diary_students`
--

CREATE TABLE IF NOT EXISTS `diary_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approve_parent` varchar(255) NOT NULL,
  `diary_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
   PRIMARY KEY (`id`),
   KEY `diary_id` (`diary_id`),
   KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `diary_students`
--

-- --------------------------------------------------------

--
-- Table structure for table `digital_notices`
--

CREATE TABLE IF NOT EXISTS `digital_notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `notice_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `notice_type` varchar(100) DEFAULT NULL,
  `audience` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `digital_notices`
--

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `bus_id` (`bus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `drivers`
--

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE IF NOT EXISTS `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `fk_exams_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exams`
--

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `exam_schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `marks_obtained` int(11) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exam_results`
--

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE IF NOT EXISTS `exam_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `total_marks` int(11) DEFAULT 0,
  `exam_date` date NOT NULL,
  `exam_time` time NOT NULL,
  `day` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `exam_schedule`
--

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE IF NOT EXISTS `faculty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `verification_attempts` int(11) NOT NULL DEFAULT 0,
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faculty`
--

--
-- Triggers `faculty`
--

-- --------------------------------------------------------

--
-- Table structure for table `faculty_attendance`
--

CREATE TABLE IF NOT EXISTS `faculty_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Leave') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `faculty_id` (`faculty_id`),
KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_leaves`
--

CREATE TABLE IF NOT EXISTS `faculty_leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `faculty_id` (`faculty_id`),
KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faculty_leaves`
--

-- --------------------------------------------------------

--
-- Table structure for table `fee_installments`
--

CREATE TABLE IF NOT EXISTS `fee_installments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_period_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Pending','Paid') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_installments`
--

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE IF NOT EXISTS `fee_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `fee_slip_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `status` enum('PENDING','CLEARED','FAILED') DEFAULT 'CLEARED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `fee_slip_id` (`fee_slip_id`),
KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_payments`
--

-- --------------------------------------------------------

--
-- Table structure for table `fee_periods`
--

CREATE TABLE IF NOT EXISTS `fee_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `period_type` enum('monthly','quarterly','term','custom') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_periods`
--


-- --------------------------------------------------------

--
-- Table structure for table `fee_refunds`
--

CREATE TABLE IF NOT EXISTS `fee_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `slip_id` int(11) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_reason` text DEFAULT NULL,
  `refund_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `slip_id` (`slip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_refunds`
--

-- --------------------------------------------------------

--
-- Table structure for table `fee_slip_details`
--

CREATE TABLE IF NOT EXISTS `fee_slip_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`),
KEY `student_id` (`student_id`),
KEY `fee_period_id` (`fee_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_slip_details`
--

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE IF NOT EXISTS `fee_structures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `class_grade` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `frequency` enum('monthly','yearly','one_time') DEFAULT 'monthly',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_structures`
--

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE IF NOT EXISTS `fee_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_types`
--


-- --------------------------------------------------------

--
-- Table structure for table `library_fines`
--

CREATE TABLE IF NOT EXISTS `library_fines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `paid_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
   PRIMARY KEY (`id`),
   KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `library_fines`
--

-- --------------------------------------------------------

--
-- Table structure for table `library_transactions`
--

CREATE TABLE IF NOT EXISTS `library_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Issued','Returned','Overdue') DEFAULT 'Issued',
   PRIMARY KEY (`id`),
   KEY `book_id` (`book_id`),
KEY `student_id` (`student_id`),
KEY `faculty_id` (`faculty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `library_transactions`
--

-- --------------------------------------------------------

--
-- Table structure for table `meeting_announcements`
--

CREATE TABLE IF NOT EXISTS `meeting_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `meeting_announcements`
--

-- --------------------------------------------------------

--
-- Table structure for table `meeting_requests`
--

CREATE TABLE IF NOT EXISTS `meeting_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `requested_by` enum('admin','teacher','parent') NOT NULL,
  `requester_id` int(11) NOT NULL,
  `with_meeting` enum('admin','teacher','parent') NOT NULL,
  `id_meeter` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `agenda` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `meeting_requests`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `sender_designation` enum('admin','teacher','student') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_designation` enum('admin','teacher','student') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `file_attachment` varchar(255) DEFAULT NULL,
  `voice_note` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread',
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--


-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `school_id` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `user_type` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--
-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE IF NOT EXISTS `parents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `parent_cnic` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `verification_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `code_expires_at` datetime DEFAULT NULL,
  `verification_attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `parent_cnic` (`parent_cnic`),
UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `parents`
--

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slip_id` int(11) NOT NULL,
  `payment_date` date NOT NULL, 
  `amount_paid` decimal(10,2) NOT NULL,
  `method` enum('cash','card','online','bank_transfer') DEFAULT 'cash',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_name` varchar(100) NOT NULL,
  `start_point` varchar(150) NOT NULL,
  `end_point` varchar(150) NOT NULL,
  `stops` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE IF NOT EXISTS `scholarships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('percentage','fixed') DEFAULT 'fixed',
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('approved','pending','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `fk_scholarships_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE IF NOT EXISTS `schools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `username` varchar(8) DEFAULT NULL,
  `admin_email` varchar(150) DEFAULT NULL,
  `admin_phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `code_expires_at` datetime DEFAULT NULL,
  `verification_attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
  `num_students` int(11) DEFAULT 0,
   PRIMARY KEY (`id`),
   UNIQUE KEY `registration_number` (`registration_number`),
UNIQUE KEY `school_email` (`school_email`),
UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schools`
--
-- Triggers `schools`
--



-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE IF NOT EXISTS `school_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person` enum('app_admin','admin','facility','student') NOT NULL,
  `person_id` int(11) NOT NULL,
  `layout` tinyint(1) NOT NULL COMMENT '1=Light, 2=Dark',
  `sidebar_color` tinyint(1) NOT NULL COMMENT '1=Light Sidebar, 2=Dark Sidebar',
  `color_theme` varchar(50) DEFAULT NULL,
  `mini_sidebar` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Off, 1=On',
  `sticky_header` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Off, 1=On',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `attendance_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `behavior_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `chat_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `dairy_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `exam_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `fee_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `library_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `meeting_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `notice_board_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `assign_task_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `tests_assignments_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `timetable_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `transport_enabled` tinyint(1) NOT NULL DEFAULT 1,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `school_settings`
--

-- --------------------------------------------------------

--
-- Table structure for table `school_tasks`
--

CREATE TABLE IF NOT EXISTS `school_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `task_description` text NOT NULL,
  `due_date` date NOT NULL,
  `task_completed_percent` decimal(5,2) DEFAULT 0.00,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `fk_school_tasks_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `school_tasks`
--

-- --------------------------------------------------------

--
-- Table structure for table `school_task_assignees`
--

CREATE TABLE IF NOT EXISTS `school_task_assignees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `assigned_to_type` enum('teacher','student') NOT NULL,
  `assigned_to_id` int(11) NOT NULL,
  `status` enum('Active','Not Active') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `school_task_assignees`
--

-- --------------------------------------------------------

--
-- Table structure for table `school_timings`
--

CREATE TABLE IF NOT EXISTS `school_timings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `assembly_time` time NOT NULL,
  `leave_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `half_day_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`half_day_config`)),
  `is_finalized` tinyint(1) DEFAULT 0,
  `is_preview` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `school_timings`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) DEFAULT NULL,
  `parent_name` varchar(100) DEFAULT NULL,
  `parent_cnic` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `username` varchar(8) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `cnic_formb` varchar(20) DEFAULT NULL,
  `class_grade` varchar(50) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `code_expires_at` datetime DEFAULT NULL,
  `verification_attempts` int(11) NOT NULL DEFAULT 0,
  `status` enum('Approved','Inactive','Pending') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `email` (`email`),
UNIQUE KEY `username` (`username`),
KEY `school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

--
-- Triggers `students`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE IF NOT EXISTS `student_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_meta_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Present','Absent','Leave') NOT NULL,
  `date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_behavior`
--

CREATE TABLE IF NOT EXISTS `student_behavior` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `deadline` date NOT NULL,
  `parent_approval` enum('yes','no') DEFAULT 'no',
  `parent_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `class_id` (`class_id`),
KEY `teacher_id` (`teacher_id`),
KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_behavior`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_plans`
--

CREATE TABLE IF NOT EXISTS `student_fee_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_component` int(11) NOT NULL,
  `base_amount` decimal(10,2) NOT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `school_id` (`school_id`),
KEY `student_id` (`student_id`),
KEY `fk_fee_component` (`fee_component`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_leaves`
--

CREATE TABLE IF NOT EXISTS `student_leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `leave_type` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `fk_student` (`student_id`),
KEY `fk_school` (`school_id`),
KEY `fk_teacher` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_payment_plans`
--

CREATE TABLE IF NOT EXISTS `student_payment_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_payment_plans`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_plan_orders`
--

CREATE TABLE IF NOT EXISTS `student_plan_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `num_students` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_plan_orders`
--
-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE IF NOT EXISTS `student_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_results`
--

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE IF NOT EXISTS `teacher_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teacher_assignments`
--

-- --------------------------------------------------------

--
-- Table structure for table `transport_routes`
--

CREATE TABLE IF NOT EXISTS `transport_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `route_name` varchar(100) NOT NULL,
  `stops` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transport_routes`
--

-- --------------------------------------------------------

--
-- Table structure for table `transport_student_routes`
--

CREATE TABLE IF NOT EXISTS `transport_student_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `student_id` (`student_id`),
KEY `route_id` (`route_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- ALTER TABLE `bus_problems`
--   ADD CONSTRAINT `bus_problems_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`);

-- --
-- -- Constraints for table `class_fee_types`
-- --
-- ALTER TABLE `class_fee_types`
--   ADD CONSTRAINT `class_fee_types_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
--   ADD CONSTRAINT `class_fee_types_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
--   ADD CONSTRAINT `fk_fee_structure` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `class_timetable_weekdays`
-- --
-- ALTER TABLE `class_timetable_weekdays`
--   ADD CONSTRAINT `fk_weekdays_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- Constraints for table `diary_students`
-- --
-- ALTER TABLE `diary_students`
--   ADD CONSTRAINT `diary_students_ibfk_1` FOREIGN KEY (`diary_id`) REFERENCES `diary_entries` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `diary_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `drivers`
-- --
-- ALTER TABLE `drivers`
--   ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE SET NULL;

-- --
-- -- Constraints for table `exams`
-- --
-- ALTER TABLE `exams`
--   ADD CONSTRAINT `fk_exams_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- Constraints for table `exam_schedule`
-- --
-- ALTER TABLE `exam_schedule`
--   ADD CONSTRAINT `exam_schedule_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `class_timetable_details` (`id`);

-- --
-- -- Constraints for table `faculty_attendance`
-- --
-- ALTER TABLE `faculty_attendance`
--   ADD CONSTRAINT `faculty_attendance_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `faculty_attendance_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `faculty_leaves`
-- --
-- ALTER TABLE `faculty_leaves`
--   ADD CONSTRAINT `faculty_leaves_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `faculty_leaves_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `fee_payments`
-- --
-- ALTER TABLE `fee_payments`
--   ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`fee_slip_id`) REFERENCES `fee_slip_details` (`id`),
--   ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

-- --
-- -- Constraints for table `fee_periods`
-- --
-- ALTER TABLE `fee_periods`
--   ADD CONSTRAINT `fee_periods_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `fee_refunds`
-- --
-- ALTER TABLE `fee_refunds`
--   ADD CONSTRAINT `fee_refunds_ibfk_1` FOREIGN KEY (`slip_id`) REFERENCES `fee_slip_details` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `fee_slip_details`
-- --
-- ALTER TABLE `fee_slip_details`
--   ADD CONSTRAINT `fee_slip_details_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fee_slip_details_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fee_slip_details_ibfk_3` FOREIGN KEY (`fee_period_id`) REFERENCES `fee_periods` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `fee_types`
-- --
-- ALTER TABLE `fee_types`
--   ADD CONSTRAINT `fee_types_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

-- --
-- -- Constraints for table `library_fines`
-- --
-- ALTER TABLE `library_fines`
--   ADD CONSTRAINT `library_fines_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `library_transactions` (`id`);

-- --
-- -- Constraints for table `library_transactions`
-- --
-- ALTER TABLE `library_transactions`
--   ADD CONSTRAINT `library_transactions_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
--   ADD CONSTRAINT `library_transactions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
--   ADD CONSTRAINT `library_transactions_ibfk_3` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`);

-- --
-- -- Constraints for table `meeting_announcements`
-- --
-- ALTER TABLE `meeting_announcements`
--   ADD CONSTRAINT `meeting_announcements_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

-- --
-- -- Constraints for table `meeting_requests`
-- --
-- ALTER TABLE `meeting_requests`
--   ADD CONSTRAINT `meeting_requests_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `messages`
-- --
-- ALTER TABLE `messages`
--   ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

-- --
-- -- Constraints for table `scholarships`
-- --
-- ALTER TABLE `scholarships`
--   ADD CONSTRAINT `fk_scholarships_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- Constraints for table `school_tasks`
-- --
-- ALTER TABLE `school_tasks`
--   ADD CONSTRAINT `fk_school_tasks_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- Constraints for table `school_task_assignees`
-- --
-- ALTER TABLE `school_task_assignees`
--   ADD CONSTRAINT `school_task_assignees_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `school_tasks` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `students`
-- --
-- ALTER TABLE `students`
--   ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`);

-- --
-- -- Constraints for table `student_behavior`
-- --
-- ALTER TABLE `student_behavior`
--   ADD CONSTRAINT `student_behavior_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_timetable_meta` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `student_behavior_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `student_behavior_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `student_fee_plans`
-- --
-- ALTER TABLE `student_fee_plans`
--   ADD CONSTRAINT `fk_fee_component` FOREIGN KEY (`fee_component`) REFERENCES `fee_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `student_fee_plans_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
--   ADD CONSTRAINT `student_fee_plans_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

-- --
-- -- Constraints for table `student_leaves`
-- --
-- ALTER TABLE `student_leaves`
--   ADD CONSTRAINT `fk_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE;

-- --
-- -- Constraints for table `transport_student_routes`
-- --
-- ALTER TABLE `transport_student_routes`
--   ADD CONSTRAINT `transport_student_routes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `transport_student_routes_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE CASCADE;


-- /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
-- /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
-- /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
