-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 07:48 AM
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
-- Database: `users`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `usertype` varchar(55) NOT NULL DEFAULT '',
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `id_type` varchar(100) DEFAULT NULL,
  `resident_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `usertype`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `barangay`, `id_type`, `resident_type`, `file_path`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', 'Test', 'User', 'admin@gov.ph', 'admin123', 'Barangay 170', 'government-id', 'resident', 'uploads/default.jpg', '2025-01-01 00:00:00', '2025-11-24 06:05:54'),
(2, '', 'John', 'Dela', 'Cruz', 'john@gmail.com', 'password123', 'Barangay 170', 'government-id', 'resident', 'uploads/john_id.jpg', '2025-01-15 00:00:00', '2025-01-15 00:00:00'),
(3, '', 'Shou', 'Nicol', 'Ballesteros', 'shou123@gmail.com', 'pogi', 'Sangandaan', 'passport', 'resident', 'uploads/6918b4726a53c_1763226738.png', '2025-11-15 17:12:18', '2025-11-23 19:50:30'),
(4, '', 'Tyron', 'Pongay', 'Chavez', 'tyron123@gmail.com', 'juday123', 'Sangandaan', 'pwd-id', 'resident', 'uploads/6918b520bef2b_1763226912.png', '2025-11-15 17:15:12', '2025-11-15 17:15:12'),
(5, '', 'Airam', 'Jesse', 'Licero', 'airam@gmail.com', 'airam123', 'Bahay Toro', 'philhealth-id', 'resident', 'uploads/6919f4f7a0458_1763308791.png', '2025-11-16 15:59:51', '2025-11-16 15:59:51'),
(6, '', 'Shou', 'Nicol', 'Ballesteros', 'shouballesteros4@gmail.com', 'testing', 'Sangandaan', 'sss-id', 'non-resident', 'uploads/69234a44e6477_1763920452.png', '2025-11-23 17:54:12', '2025-11-23 19:50:12'),
(7, '', 'Christien', 'Michael', 'Jimenez', 'tien@gmail.com', 'tientien', 'Sangandaan', 'philhealth-id', 'non-resident', 'uploads/6923e772d81ec_1763960690.png', '2025-11-24 05:04:50', '2025-11-24 05:04:50'),
(8, '', 'karl', 'steaven', 'navidad', 'karl@gmail.com', 'karldota', 'Sangandaan', 'government-id', 'non-resident', 'uploads/6923f51ac0300_1763964186.png', '2025-11-24 06:03:06', '2025-11-24 06:03:06'),
(9, '', 'shou', 'nicol', 'ballesteros', 'shouballesteros5@gmail.com', 'nkeroro09', 'Sangandaan', 'sss-id', 'resident', 'uploads/6923fe5103005_1763966545.JPG', '2025-11-24 06:42:25', '2025-11-24 06:42:25');

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `action_type` enum('LOGIN','LOGOUT','REQUEST_UPDATE','REQUEST_DELETE','STATUS_CHANGE','NOTIFICATION_ADD','NOTIFICATION_DELETE','USER_VIEW','PRIORITY_CHANGE') NOT NULL,
  `action_description` text NOT NULL,
  `target_type` varchar(50) DEFAULT NULL COMMENT 'Type of target (request, user, notification, etc.)',
  `target_id` varchar(50) DEFAULT NULL COMMENT 'ID of the affected item',
  `old_value` text DEFAULT NULL COMMENT 'Previous value before change',
  `new_value` text DEFAULT NULL COMMENT 'New value after change',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`id`, `admin_id`, `admin_email`, `action_type`, `action_description`, `target_type`, `target_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000006 from \'\' to \'HIGH\'', 'request', 'BHR-2025-000006', '', 'HIGH', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:20:11'),
(2, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000005 status from \'IN PROGRESS\' to \'READY\'', 'request', 'BHR-2025-000005', 'IN PROGRESS', 'READY', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:20:50'),
(3, 1, 'Unknown', 'NOTIFICATION_DELETE', 'Deleted notification: \'Health and Wellness Seminar\'', 'notification', '2', 'Health and Wellness Seminar', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:21:10'),
(4, 1, 'admin@gov.qc.ph', 'LOGIN', 'Admin logged into the system', 'system', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:22:21'),
(5, 1, 'admin@gov.qc.ph', 'LOGIN', 'Admin logged into the system', 'system', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:29:36'),
(6, 1, 'admin@gov.qc.ph', 'LOGIN', 'Admin logged into the system', 'system', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:46:19'),
(7, 1, 'admin@gov.qc.ph', 'LOGIN', 'Admin logged into the system', 'system', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:54:51'),
(8, 1, 'admin@gov.qc.ph', 'NOTIFICATION_ADD', 'Added new NEWS notification: \'domain bukas\'', 'notification', NULL, NULL, 'domain bukas', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 20:58:53'),
(9, 1, 'admin@gov.qc.ph', 'LOGIN', 'Admin logged into the system', 'system', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:16:30'),
(10, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'\' to \'MEDIUM\'', 'request', 'BHR-2025-000007', '', 'MEDIUM', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:20:46'),
(11, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'MEDIUM\' to \'HIGH\'', 'request', 'BHR-2025-000007', 'MEDIUM', 'HIGH', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:21:04'),
(12, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'HIGH\' to \'MEDIUM\'', 'request', 'BHR-2025-000007', 'HIGH', 'MEDIUM', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:23:51'),
(13, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'MEDIUM\' to \'LOW\'', 'request', 'BHR-2025-000007', 'MEDIUM', 'LOW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:24:28'),
(14, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'LOW\' to \'HIGH\'', 'request', 'BHR-2025-000007', 'LOW', 'HIGH', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:24:51'),
(15, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000007 status from \'PENDING\' to \'COMPLETED\'', 'request', 'BHR-2025-000007', 'PENDING', 'COMPLETED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:24:56'),
(16, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000006 from \'HIGH\' to \'MEDIUM\'', 'request', 'BHR-2025-000006', 'HIGH', 'MEDIUM', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:25:03'),
(17, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000006 status from \'COMPLETED\' to \'PENDING\'', 'request', 'BHR-2025-000006', 'COMPLETED', 'PENDING', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:25:08'),
(18, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000006 from \'MEDIUM\' to \'LOW\'', 'request', 'BHR-2025-000006', 'MEDIUM', 'LOW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:25:22'),
(19, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000007 status from \'COMPLETED\' to \'PENDING\'', 'request', 'BHR-2025-000007', 'COMPLETED', 'PENDING', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:35:02'),
(20, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000007 status from \'PENDING\' to \'READY\'', 'request', 'BHR-2025-000007', 'PENDING', 'READY', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:35:14'),
(21, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'HIGH\' to \'MEDIUM\'', 'request', 'BHR-2025-000007', 'HIGH', 'MEDIUM', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:35:24'),
(22, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000005 status from \'READY\' to \'IN PROGRESS\'', 'request', 'BHR-2025-000005', 'READY', 'IN PROGRESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:52:22'),
(23, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000005 from \'\' to \'HIGH\'', 'request', 'BHR-2025-000005', '', 'HIGH', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:52:23'),
(24, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000004 status from \'READY\' to \'PENDING\'', 'request', 'BHR-2025-000004', 'READY', 'PENDING', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:52:38'),
(25, 1, 'Unknown', 'STATUS_CHANGE', 'Updated request BHR-2025-000007 status from \'READY\' to \'COMPLETED\'', 'request', 'BHR-2025-000007', 'READY', 'COMPLETED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:53:04'),
(26, 1, 'Unknown', 'PRIORITY_CHANGE', 'Changed priority for request BHR-2025-000007 from \'MEDIUM\' to \'LOW\'', 'request', 'BHR-2025-000007', 'MEDIUM', 'LOW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:53:04');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` enum('NEWS','EVENT') NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `title`, `date`, `description`, `created_at`, `updated_at`) VALUES
(1, 'NEWS', 'Free Medical Check-up', 'November 18, 2025', 'The barangay health center will conduct free medical check-ups for all residents this coming weekend.', '2025-11-16 15:39:03', '2025-11-16 15:39:03'),
(5, 'NEWS', 'ml', 'November 25, 2025', 'jjjjjaaayy', '2025-11-16 15:45:47', '2025-11-16 15:45:47'),
(6, 'NEWS', 'dota', 'December 29, 2025', 'rarara', '2025-11-16 15:49:08', '2025-11-16 15:49:08'),
(8, 'NEWS', 'test', 'test', 'test', '2025-11-17 20:27:40', '2025-11-17 20:27:40'),
(9, 'NEWS', 'domain bukas', '12132134', 'daws', '2025-11-23 20:58:53', '2025-11-23 20:58:53');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 15 minute),
  `used` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp`, `created_at`, `expires_at`, `used`, `reset_token`) VALUES
(12, 'admin@gov.qc.ph', '639117', '2025-11-23 20:45:24', '2025-11-23 21:45:24', 0, NULL),
(13, 'shouballesteros4@gmail.com', '554369', '2025-11-24 06:36:40', '2025-11-24 07:36:40', 0, NULL),
(14, 'shouballesteros5@gmail.com', '277794', '2025-11-24 06:42:37', '2025-11-24 07:42:37', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `ticket_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `priority` varchar(55) NOT NULL DEFAULT '',
  `contact` varchar(15) NOT NULL,
  `requesttype` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `status` enum('PENDING','UNDER REVIEW','IN PROGRESS','READY','COMPLETED') DEFAULT 'PENDING',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `ticket_id`, `user_id`, `fullname`, `priority`, `contact`, `requesttype`, `description`, `status`, `submitted_at`, `updated_at`) VALUES
(7, 'BHR-2025-000002', 2, 'michael', '', '09167039130', 'Clearance', 'hahaha', 'COMPLETED', '2025-11-16 12:18:55', '2025-11-17 20:25:40'),
(8, 'BHR-2025-000003', 2, 'airam licerio', '', '09167039130', 'indigency', 'dwdsadaw', 'IN PROGRESS', '2025-11-16 12:19:07', '2025-11-17 20:25:24'),
(9, 'BHR-2025-000004', 2, 'john@gmail.com', '', 'N/A', 'Barangay Certificate of Household Membership', 'dwasd', 'PENDING', '2025-11-16 16:40:41', '2025-11-24 05:52:38'),
(13, 'BHR-2025-000005', 2, 'john@gmail.com', 'HIGH', '09167039130', 'Barangay Clearance', 'haha', 'IN PROGRESS', '2025-11-17 20:33:56', '2025-11-24 05:52:21'),
(14, 'BHR-2025-000006', 2, 'john@gmail.com', 'LOW', '09167039130', 'Barangay Construction / Renovation Permit', 'test', 'PENDING', '2025-11-17 20:34:10', '2025-11-23 21:25:22'),
(15, 'BHR-2025-000007', 2, 'john@gmail.com', 'LOW', '09167039130', 'Barangay Certification for PWD', 'wawa', 'COMPLETED', '2025-11-23 20:31:26', '2025-11-24 05:53:02');

-- --------------------------------------------------------

--
-- Table structure for table `request_updates`
--

CREATE TABLE `request_updates` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_updates`
--

INSERT INTO `request_updates` (`id`, `request_id`, `status`, `message`, `updated_by`, `created_at`) VALUES
(2, 15, 'PENDING', '', 'Admin', '2025-11-24 05:35:02'),
(3, 15, 'READY', '', 'Admin', '2025-11-24 05:35:14'),
(4, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:21'),
(5, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:23'),
(6, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:24'),
(7, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:25'),
(8, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:26'),
(9, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:27'),
(10, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:27'),
(11, 13, 'IN PROGRESS', '', 'Admin', '2025-11-24 05:52:28'),
(12, 9, 'PENDING', '', 'Admin', '2025-11-24 05:52:38'),
(13, 15, 'COMPLETED', '', 'Admin', '2025-11-24 05:53:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_usertype` (`usertype`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_target` (`target_type`,`target_id`),
  ADD KEY `idx_admin_action_date` (`admin_id`,`action_type`,`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_reset_token` (`email`),
  ADD KEY `idx_email_used` (`email`,`used`,`expires_at`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `request_updates`
--
ALTER TABLE `request_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_id` (`request_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `request_updates`
--
ALTER TABLE `request_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_updates`
--
ALTER TABLE `request_updates`
  ADD CONSTRAINT `request_updates_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
