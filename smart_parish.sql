-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 08:39 PM
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
-- Database: `smart_parish`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(190) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'Admin login', '', '::1', '2026-09-30 16:41:25'),
(2, 2, 'Parishioner registered', '', '::1', '2026-09-30 16:43:12'),
(3, 2, 'Parishioner login', '', '::1', '2026-09-30 16:44:01'),
(4, 2, 'Appointment booked', 'SP-20260930-EEFF02', '::1', '2026-09-30 16:45:10'),
(5, 1, 'Admin login', '', '::1', '2026-09-30 16:46:10'),
(6, 1, 'Appointment approved', 'SP-20260930-EEFF02', '::1', '2026-09-30 16:47:01'),
(7, 2, 'Parishioner login', '', '::1', '2026-09-30 16:47:42'),
(8, 1, 'Admin login', '', '::1', '2026-09-30 16:54:11'),
(9, 1, 'Updated service', 'Wedding', '::1', '2026-09-30 16:55:21'),
(10, 3, 'Parishioner registered', '', '::1', '2026-09-30 17:00:41'),
(11, 3, 'Parishioner login', '', '::1', '2026-09-30 17:00:50'),
(12, 4, 'Parishioner registered', '', '::1', '2026-09-30 17:12:07'),
(13, 4, 'Parishioner login', '', '::1', '2026-09-30 17:12:30'),
(14, 4, 'Profile updated', '', '::1', '2026-09-30 17:12:55'),
(15, 5, 'Parishioner registered', '', '::1', '2026-09-30 17:18:20'),
(16, 1, 'Admin login', '', '::1', '2026-09-30 17:25:08'),
(17, 6, 'Parishioner registered', '', '192.168.1.101', '2026-09-30 17:27:07'),
(18, 6, 'Parishioner login', '', '192.168.1.101', '2026-09-30 17:27:31'),
(19, 1, 'Admin login', '', '::1', '2026-09-30 17:30:20'),
(20, 4, 'Parishioner login', '', '::1', '2026-09-30 17:30:48'),
(21, 4, 'Parishioner login', '', '192.168.1.101', '2026-09-30 17:38:25'),
(22, 4, 'Appointment booked', 'SP-20260930-E7C9DD', '192.168.1.101', '2026-09-30 17:39:06'),
(23, 1, 'Admin login', '', '::1', '2026-09-30 17:39:22'),
(24, 1, 'Appointment rejected', 'SP-20260930-E7C9DD', '::1', '2026-09-30 17:40:42'),
(25, 1, 'Appointment completed', 'SP-20260930-EEFF02', '::1', '2026-09-30 17:40:45'),
(26, 4, 'Parishioner login', '', '::1', '2026-09-30 17:42:00'),
(27, 4, 'Appointment booked', 'SP-20260930-617921', '::1', '2026-09-30 18:03:47'),
(28, 1, 'Admin login', '', '::1', '2026-09-30 18:03:57'),
(29, 1, 'Appointment approved', 'SP-20260930-617921', '::1', '2026-09-30 18:04:05'),
(30, 6, 'Parishioner login', '', '192.168.1.51', '2026-09-30 18:04:57'),
(31, 4, 'Parishioner login', '', '::1', '2026-09-30 18:06:06'),
(32, 4, 'Appointment booked', 'SP-20260930-72D5A8', '::1', '2026-09-30 18:06:20'),
(33, 1, 'Admin login', '', '::1', '2026-09-30 18:06:43'),
(34, 1, 'Appointment rejected', 'SP-20260930-72D5A8', '::1', '2026-09-30 18:07:05'),
(35, 4, 'Parishioner login', '', '::1', '2026-09-30 18:07:39'),
(36, 6, 'Appointment booked', 'SP-20260930-6F0B2B', '192.168.1.51', '2026-09-30 18:18:53'),
(37, 1, 'Admin login', '', '::1', '2026-09-30 18:54:08'),
(38, 4, 'Parishioner login', '', '::1', '2026-09-30 18:55:45'),
(39, 1, 'Admin login', '', '::1', '2026-09-30 19:09:57'),
(40, 1, 'Appointment rejected', 'SP-20260930-6F0B2B — Reason: WAH KA NI BAYAD BOSS HAHAHHAHA', '::1', '2026-09-30 19:17:47'),
(41, 1, 'Admin login', '', '::1', '2026-09-30 19:21:45'),
(42, 4, 'Parishioner login', '', '::1', '2026-09-30 19:21:57'),
(43, 1, 'Admin login', '', '::1', '2026-09-30 19:45:26'),
(44, 1, 'Payment settings updated', 'GCash settings changed', '::1', '2026-09-30 19:46:44'),
(45, 4, 'Parishioner login', '', '::1', '2026-09-30 19:47:06'),
(46, 4, 'Appointment booked', 'SP-20260930-E1A6ED', '::1', '2026-09-30 19:48:15'),
(47, 1, 'Admin login', '', '::1', '2026-09-30 19:48:33'),
(48, 1, 'Online payment verified', 'SP-20260930-E1A6ED', '::1', '2026-09-30 19:49:40'),
(49, 1, 'Appointment approved', 'SP-20260930-E1A6ED', '::1', '2026-09-30 19:50:05'),
(50, 4, 'Parishioner login', '', '192.168.1.51', '2026-09-30 19:50:35'),
(51, 4, 'Appointment booked', 'SP-20260930-CD473B', '192.168.1.51', '2026-09-30 19:52:25'),
(52, 1, 'Appointment rejected', 'SP-20260930-CD473B — Reason: no payment', '::1', '2026-09-30 19:53:10'),
(53, 4, 'Parishioner login', '', '::1', '2026-09-30 19:54:38'),
(54, 1, 'Admin login', '', '::1', '2026-09-30 19:55:45'),
(55, 1, 'Appointment completed', 'SP-20260930-E1A6ED', '::1', '2026-09-30 19:55:54'),
(56, 4, 'Parishioner login', '', '::1', '2026-09-30 19:56:43'),
(57, 1, 'Admin login', '', '::1', '2026-09-30 20:02:12'),
(58, 1, 'Payment settings updated', 'GCash settings changed', '::1', '2026-09-30 20:05:35'),
(59, 4, 'Parishioner login', '', '::1', '2026-09-30 20:05:46'),
(60, 1, 'Admin login', '', '::1', '2026-09-30 20:10:45'),
(61, 1, 'Payment settings updated', 'GCash settings changed', '::1', '2026-09-30 20:13:16'),
(62, 4, 'Parishioner login', '', '::1', '2026-09-30 20:13:27'),
(63, 4, 'Parishioner login', '', '::1', '2026-09-30 20:20:12'),
(64, 4, 'Profile updated', '', '::1', '2026-09-30 20:20:56'),
(65, 1, 'Admin login', '', '::1', '2026-09-30 20:21:13'),
(66, 1, 'Published announcement', 'BAKASYON', '::1', '2026-09-30 20:22:38'),
(67, 4, 'Parishioner login', '', '::1', '2026-09-30 20:22:51'),
(68, 4, 'Parishioner login', '', '::1', '2026-09-30 20:24:05'),
(69, 1, 'Admin login', '', '::1', '2026-09-30 20:32:51'),
(70, 1, 'Toggled parishioner status', 'User #4', '::1', '2026-09-30 20:34:16'),
(71, 1, 'Toggled parishioner status', 'User #4', '::1', '2026-09-30 20:35:09'),
(72, 4, 'Parishioner login', '', '192.168.1.51', '2026-09-30 20:35:18'),
(73, 1, 'Appointment completed', 'SP-20260930-617921', '::1', '2026-09-30 20:48:01'),
(74, 4, 'Appointment booked', 'SP-20260930-5AEA9E', '192.168.1.51', '2026-09-30 20:48:53'),
(75, 1, 'Appointment approved', 'SP-20260930-5AEA9E', '::1', '2026-09-30 20:49:33'),
(76, 1, 'Online payment verified', 'SP-20260930-CD473B', '::1', '2026-09-30 20:54:12'),
(77, 4, 'Parishioner login', '', '::1', '2026-09-30 21:01:27'),
(78, 1, 'Admin login', '', '192.168.1.51', '2026-09-30 21:07:08'),
(79, 4, 'Profile updated', '', '::1', '2026-09-30 21:12:08'),
(80, 1, 'Admin login', '', '::1', '2026-09-30 21:12:23'),
(81, 1, 'Payment settings updated', 'GCash settings changed', '::1', '2026-09-30 21:12:33'),
(82, 1, 'Admin login', '', '::1', '2026-10-01 10:42:36'),
(83, 1, 'Admin login', '', '::1', '2026-10-01 15:14:58'),
(84, 1, 'Created administrator account', 'reynan allan (reynan@gmail.com)', '::1', '2026-10-01 15:31:46'),
(85, 4, 'Parishioner login', '', '::1', '2026-10-01 15:38:26'),
(86, 1, 'Admin login', '', '::1', '2026-10-01 15:45:47'),
(87, 1, 'Added event', 'MASS', '::1', '2026-10-01 15:47:02'),
(88, 4, 'Parishioner login', '', '::1', '2026-10-01 15:47:15'),
(89, 1, 'Admin login', '', '::1', '2026-10-01 15:47:34'),
(90, 1, 'Added event', 'Free wedding', '::1', '2026-10-01 15:48:33'),
(91, 4, 'Parishioner login', '', '::1', '2026-10-01 15:48:55'),
(92, 1, 'Admin login', '', '::1', '2026-10-01 15:49:28'),
(93, 1, 'Appointment completed', 'SP-20260930-5AEA9E', '::1', '2026-10-01 16:14:47'),
(94, 4, 'Parishioner login', '', '::1', '2026-10-01 16:19:50'),
(95, 1, 'Admin login', '', '::1', '2026-10-01 16:35:23'),
(96, 4, 'Parishioner login', '', '::1', '2026-10-01 16:47:41'),
(97, 1, 'Admin login', '', '::1', '2026-10-01 16:51:37'),
(98, 4, 'Parishioner login', '', '192.168.1.51', '2026-10-01 16:53:36'),
(99, 4, 'Appointment booked', 'SP-20261001-9208A9', '192.168.1.51', '2026-10-01 16:54:05'),
(100, 1, 'Appointment approved', 'SP-20261001-9208A9', '::1', '2026-10-01 16:54:38'),
(101, 1, 'Appointment completed', 'SP-20261001-9208A9', '::1', '2026-10-01 16:55:05'),
(102, 4, 'Appointment booked', 'SP-20261002-5F165C', '192.168.1.51', '2026-10-01 17:00:45'),
(103, 1, 'Unlocked administrator management', 'Super password verified', '::1', '2026-10-01 17:09:52'),
(104, 1, 'Admin login', '', '::1', '2026-10-01 17:10:34'),
(105, 1, 'Unlocked administrator management', 'Super password verified', '::1', '2026-10-01 17:10:48'),
(106, 1, 'Admin login', '', '::1', '2026-10-01 17:16:29'),
(107, 1, 'Admin login', '', '::1', '2026-10-01 17:20:48'),
(108, 1, 'Unlocked administrator management', 'Super password verified', '::1', '2026-10-01 17:25:46'),
(109, 1, 'Unlocked administrator management', 'One-page super password authorization verified', '::1', '2026-10-01 17:33:58'),
(110, 1, 'Unlocked administrator management', 'One-page super password authorization verified', '::1', '2026-10-01 17:36:31'),
(111, 1, 'Unlocked administrator management', 'One-page super password authorization verified', '::1', '2026-10-01 17:40:47'),
(112, 1, 'Appointment rejected', 'SP-20261002-5F165C — Reason: bhoanggg', '::1', '2026-10-01 17:41:23'),
(113, 4, 'Parishioner login', '', '::1', '2026-10-01 17:42:00'),
(114, 1, 'Admin login', '', '::1', '2026-10-01 17:44:02'),
(115, 1, 'Unlocked administrator management', 'One-page super password authorization verified', '::1', '2026-10-01 17:46:57'),
(116, 4, 'Parishioner login', '', '::1', '2026-10-01 17:47:39'),
(117, 1, 'Admin login', '', '::1', '2026-10-01 17:50:02'),
(118, 1, 'Admin login', '', '::1', '2026-10-05 04:06:35'),
(119, 1, 'Unlocked administrator management', 'One-page super password authorization verified', '::1', '2026-10-05 04:06:45'),
(120, 4, 'Parishioner login', '', '::1', '2026-10-05 17:42:03'),
(121, 1, 'Admin login', '', '::1', '2026-10-05 17:43:22'),
(122, 1, 'Unlocked administrator management', 'One-page super password authorization verified', '::1', '2026-10-05 17:44:25'),
(123, 4, 'Parishioner login', '', '::1', '2026-10-05 17:49:59'),
(124, 4, 'Appointment booked', 'SP-20261006-F6B053', '::1', '2026-10-05 18:22:26'),
(125, 1, 'Admin login', '', '::1', '2026-10-05 18:22:40'),
(126, 1, 'Online payment verified', 'SP-20261006-F6B053', '::1', '2026-10-05 18:23:32'),
(127, 1, 'Appointment approved', 'SP-20261006-F6B053', '::1', '2026-10-05 18:23:43'),
(128, 4, 'Parishioner login', '', '::1', '2026-10-05 18:23:59'),
(129, 1, 'Admin login', '', '::1', '2026-10-05 18:49:50'),
(130, 1, 'Appointment completed', 'SP-20261006-F6B053', '::1', '2026-10-05 18:50:04'),
(131, 4, 'Parishioner login', '', '::1', '2026-10-05 18:50:58'),
(132, 1, 'Admin login', '', '::1', '2026-10-05 19:00:14'),
(133, 4, 'Parishioner login', '', '::1', '2026-10-05 19:00:31'),
(134, 4, 'Appointment booked', 'SP-20261006-2F3A05', '::1', '2026-10-05 19:04:45'),
(135, 4, 'Appointment booked', 'SP-20261006-982071', '::1', '2026-10-05 19:06:20'),
(136, 4, 'Appointment booked', 'SP-20261006-366DA6', '::1', '2026-10-05 19:11:23'),
(137, 4, 'Appointment booked', 'SP-20261006-EA96C0', '::1', '2026-10-05 19:15:31'),
(138, 4, 'Appointment booked', 'SP-20261006-7C02FB', '::1', '2026-10-05 19:18:24'),
(139, 4, 'Appointment booked', 'SP-20261006-C77251', '::1', '2026-10-05 19:19:07'),
(140, 4, 'Appointment booked', 'SP-20261006-7A4041', '::1', '2026-10-05 19:20:18'),
(141, 4, 'Appointment booked', 'SP-20261006-65FD4D', '::1', '2026-10-05 19:23:58'),
(142, 1, 'Admin login', '', '::1', '2026-10-05 19:24:15'),
(143, 1, 'Online payment verified', 'SP-20261006-65FD4D', '::1', '2026-10-05 19:24:28'),
(144, 1, 'Appointment approved', 'SP-20261006-65FD4D', '::1', '2026-10-05 19:24:47'),
(145, 4, 'Parishioner login', '', '::1', '2026-10-05 19:25:03'),
(146, 4, 'Parishioner login', '', '::1', '2026-10-06 16:40:57'),
(147, 4, 'Parishioner login', '', '::1', '2026-10-06 17:48:14'),
(148, 4, 'Parishioner login', '', '::1', '2026-10-06 17:51:34'),
(149, 4, 'Appointment booked', 'SP-20261007-43B72B', '::1', '2026-10-06 17:59:08'),
(150, 4, 'Parishioner login', '', '::1', '2026-10-06 18:04:11'),
(151, 4, 'Parishioner login', '', '::1', '2026-10-06 18:09:43'),
(152, 4, 'Parishioner login', '', '::1', '2026-10-06 18:21:23'),
(153, 8, 'Parishioner registered', '', '::1', '2026-10-06 18:26:31'),
(154, 8, 'Parishioner login', '', '::1', '2026-10-06 18:26:40'),
(155, 8, 'Appointment booked', 'SP-20261007-FD72E3', '::1', '2026-10-06 18:27:54'),
(156, 8, 'Appointment booked', 'SP-20261007-537D21', '::1', '2026-10-06 18:29:19'),
(157, 8, 'Appointment booked', 'SP-20261007-D722CF', '::1', '2026-10-06 18:35:01'),
(158, 8, 'Appointment booked', 'SP-20261007-08DA58', '::1', '2026-10-06 18:35:57'),
(159, 1, 'Admin login', '', '::1', '2026-10-06 18:36:11'),
(160, 1, 'Online payment verified', 'SP-20261007-D722CF', '::1', '2026-10-06 18:37:15'),
(161, 8, 'Parishioner login', '', '::1', '2026-10-06 18:37:35'),
(162, 1, 'Admin login', '', '::1', '2026-10-06 18:38:23'),
(163, 1, 'Appointment approved', 'SP-20261007-D722CF', '::1', '2026-10-06 18:38:31'),
(164, 8, 'Parishioner login', '', '::1', '2026-10-06 18:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `published_by` int(10) UNSIGNED DEFAULT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `image`, `published_by`, `published_at`) VALUES
(1, 'BAKASYON', 'BAKASYON SA ANG PARI KAY GI KAPOY', NULL, 1, '2026-09-30 20:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(30) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `service_id` int(10) UNSIGNED NOT NULL,
  `resource_id` int(10) UNSIGNED DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `payment_method` enum('cash','online') NOT NULL DEFAULT 'cash',
  `receipt_path` varchar(255) DEFAULT NULL,
  `payment_status` enum('not_required','pending','verified','rejected') NOT NULL DEFAULT 'not_required',
  `payment_verified_at` datetime DEFAULT NULL,
  `payment_verified_by` int(10) UNSIGNED DEFAULT NULL,
  `qr_confirmed_at` datetime DEFAULT NULL,
  `qr_confirmed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `reference_no`, `user_id`, `service_id`, `resource_id`, `appointment_date`, `start_time`, `end_time`, `notes`, `status`, `admin_note`, `payment_method`, `receipt_path`, `payment_status`, `payment_verified_at`, `payment_verified_by`, `qr_confirmed_at`, `qr_confirmed_by`, `created_at`, `updated_at`) VALUES
(1, 'SP-20260930-EEFF02', 2, 3, NULL, '2026-10-08', '02:47:00', '03:47:00', 'hahahahaha', 'completed', '', 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-09-30 16:45:10', '2026-09-30 17:40:45'),
(2, 'SP-20260930-E7C9DD', 4, 1, 1, '2026-10-31', '02:55:00', '03:55:00', 'Boang', 'rejected', '', 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-09-30 17:39:06', '2026-09-30 17:40:42'),
(3, 'SP-20260930-617921', 4, 1, NULL, '2026-10-01', '08:00:00', '09:00:00', '', 'completed', NULL, 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-09-30 18:03:47', '2026-09-30 20:48:01'),
(4, 'SP-20260930-72D5A8', 4, 1, NULL, '2026-10-01', '09:00:00', '10:00:00', '', 'rejected', '', 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-09-30 18:06:20', '2026-09-30 18:07:05'),
(5, 'SP-20260930-6F0B2B', 6, 1, NULL, '2026-10-01', '09:00:00', '10:00:00', '', 'rejected', 'WAH KA NI BAYAD BOSS HAHAHHAHA', 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-09-30 18:18:53', '2026-09-30 19:17:46'),
(6, 'SP-20260930-E1A6ED', 4, 1, NULL, '2026-10-01', '09:00:00', '10:00:00', 'gwapo ko', 'completed', NULL, 'online', 'uploads/payment_receipts/receipt-e9943e50835ceaca69e7051a.png', 'verified', '2026-10-01 03:49:40', 1, NULL, NULL, '2026-09-30 19:48:15', '2026-09-30 19:55:54'),
(7, 'SP-20260930-CD473B', 4, 1, NULL, '2026-10-01', '10:00:00', '11:00:00', 'Ambot imo', 'rejected', 'no payment', 'online', 'uploads/payment_receipts/receipt-adba8f7d11ea5426657e325e.jpg', 'verified', '2026-10-01 04:54:12', 1, NULL, NULL, '2026-09-30 19:52:25', '2026-09-30 20:54:12'),
(8, 'SP-20260930-5AEA9E', 4, 1, NULL, '2026-10-01', '08:00:00', '09:00:00', '', 'completed', NULL, 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-09-30 20:48:53', '2026-10-01 16:14:47'),
(9, 'SP-20261001-9208A9', 4, 1, NULL, '2026-10-02', '09:00:00', '10:00:00', '', 'completed', NULL, 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-10-01 16:54:05', '2026-10-01 16:55:05'),
(10, 'SP-20261002-5F165C', 4, 1, NULL, '2026-10-02', '12:30:00', '13:30:00', '', 'rejected', 'bhoanggg', 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-10-01 17:00:45', '2026-10-01 17:41:23'),
(11, 'SP-20261006-F6B053', 4, 2, 1, '2026-11-20', '11:00:00', '13:00:00', 'SDASDSA', 'completed', NULL, 'online', 'uploads/payment_receipts/receipt-f1a1c8aeab2a43145f1b20cb.jpg', 'verified', '2026-10-06 02:23:32', 1, NULL, NULL, '2026-10-05 18:22:26', '2026-10-05 18:50:04'),
(12, 'SP-20261006-2F3A05', 4, 2, 1, '2026-11-13', '09:00:00', '11:00:00', '', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-7774ba3485ba800f958c9a8c.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-05 19:04:45', '2026-10-05 19:04:45'),
(13, 'SP-20261006-982071', 4, 2, 2, '2026-11-10', '12:30:00', '14:30:00', '', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-fc84b77a286cc2a85b79b060.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-05 19:06:20', '2026-10-05 19:06:20'),
(14, 'SP-20261006-366DA6', 4, 2, 2, '2026-11-14', '11:30:00', '13:30:00', '', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-5b87c7508fe26385164d2007.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-05 19:11:23', '2026-10-05 19:11:23'),
(15, 'SP-20261006-EA96C0', 4, 2, 1, '2026-11-14', '11:00:00', '13:00:00', '', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-12843d554db56c7340b9ed33.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-05 19:15:31', '2026-10-05 19:15:31'),
(16, 'SP-20261006-7C02FB', 4, 2, 3, '2026-11-14', '08:30:00', '10:30:00', '', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-748c687a7f0d729f526854ce.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-05 19:18:24', '2026-10-05 19:18:24'),
(17, 'SP-20261006-C77251', 4, 1, NULL, '2026-10-14', '13:00:00', '14:00:00', '', 'pending', NULL, 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-10-05 19:19:07', '2026-10-05 19:19:07'),
(18, 'SP-20261006-7A4041', 4, 1, NULL, '2026-10-26', '16:00:00', '17:00:00', '', 'pending', NULL, 'cash', NULL, 'not_required', NULL, NULL, NULL, NULL, '2026-10-05 19:20:18', '2026-10-05 19:20:18'),
(19, 'SP-20261006-65FD4D', 4, 2, 1, '2026-12-16', '11:30:00', '13:30:00', '', 'approved', NULL, 'online', 'uploads/payment_receipts/receipt-1dd6819119603699250e1b86.png', 'verified', '2026-10-06 03:24:28', 1, NULL, NULL, '2026-10-05 19:23:58', '2026-10-05 19:24:47'),
(20, 'SP-20261007-43B72B', 4, 1, 1, '2026-10-09', '13:00:00', '14:00:00', 'asdasd', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-486ba709558b1916c650cd8b.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-06 17:59:08', '2026-10-06 17:59:08'),
(21, 'SP-20261007-FD72E3', 8, 1, NULL, '2026-10-08', '08:00:00', '09:00:00', 'asdasdsa', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-539130355087534bc6b57167.png', 'pending', NULL, NULL, NULL, NULL, '2026-10-06 18:27:54', '2026-10-06 18:27:54'),
(22, 'SP-20261007-537D21', 8, 1, 2, '2026-10-08', '08:30:00', '09:30:00', 'sadsa', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-c150d166eb8eff9af860eea5.jpg', 'pending', NULL, NULL, NULL, NULL, '2026-10-06 18:29:19', '2026-10-06 18:29:19'),
(23, 'SP-20261007-D722CF', 8, 2, 2, '2026-11-21', '08:30:00', '10:30:00', 'asdswad', 'approved', NULL, 'online', 'uploads/payment_receipts/receipt-f654c66b3bbc9774a6560a9f.png', 'verified', '2026-10-07 02:37:15', 1, NULL, NULL, '2026-10-06 18:35:01', '2026-10-06 18:38:31'),
(24, 'SP-20261007-08DA58', 8, 1, 2, '2026-10-09', '11:00:00', '12:00:00', 'asdsa', 'pending', NULL, 'online', 'uploads/payment_receipts/receipt-515234f6c299b57c4ae1640c.jpg', 'pending', NULL, NULL, NULL, NULL, '2026-10-06 18:35:57', '2026-10-06 18:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_history`
--

CREATE TABLE `appointment_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointment_history`
--

INSERT INTO `appointment_history` (`id`, `appointment_id`, `status`, `note`, `changed_by`, `created_at`) VALUES
(1, 1, 'pending', 'Booking submitted', 2, '2026-09-30 16:45:10'),
(2, 1, 'approved', '', 1, '2026-09-30 16:47:01'),
(3, 2, 'pending', 'Booking submitted', 4, '2026-09-30 17:39:06'),
(4, 2, 'rejected', '', 1, '2026-09-30 17:40:42'),
(5, 1, 'completed', '', 1, '2026-09-30 17:40:45'),
(6, 3, 'pending', 'Booking submitted', 4, '2026-09-30 18:03:47'),
(7, 3, 'approved', '', 1, '2026-09-30 18:04:05'),
(8, 4, 'pending', 'Booking submitted', 4, '2026-09-30 18:06:20'),
(9, 4, 'rejected', '', 1, '2026-09-30 18:07:05'),
(10, 5, 'pending', 'Booking submitted', 6, '2026-09-30 18:18:53'),
(11, 5, 'rejected', 'WAH KA NI BAYAD BOSS HAHAHHAHA', 1, '2026-09-30 19:17:46'),
(12, 6, 'pending', 'Booking submitted', 4, '2026-09-30 19:48:15'),
(13, 6, 'approved', NULL, 1, '2026-09-30 19:50:05'),
(14, 7, 'pending', 'Booking submitted', 4, '2026-09-30 19:52:25'),
(15, 7, 'rejected', 'no payment', 1, '2026-09-30 19:53:10'),
(16, 6, 'completed', NULL, 1, '2026-09-30 19:55:54'),
(17, 3, 'completed', NULL, 1, '2026-09-30 20:48:01'),
(18, 8, 'pending', 'Booking submitted', 4, '2026-09-30 20:48:53'),
(19, 8, 'approved', NULL, 1, '2026-09-30 20:49:33'),
(20, 8, 'completed', NULL, 1, '2026-10-01 16:14:47'),
(21, 9, 'pending', 'Booking submitted', 4, '2026-10-01 16:54:05'),
(22, 9, 'approved', NULL, 1, '2026-10-01 16:54:38'),
(23, 9, 'completed', NULL, 1, '2026-10-01 16:55:05'),
(24, 10, 'pending', 'Booking submitted', 4, '2026-10-01 17:00:45'),
(25, 10, 'rejected', 'bhoanggg', 1, '2026-10-01 17:41:23'),
(26, 11, 'pending', 'Booking submitted', 4, '2026-10-05 18:22:26'),
(27, 11, 'approved', NULL, 1, '2026-10-05 18:23:43'),
(28, 11, 'completed', NULL, 1, '2026-10-05 18:50:04'),
(29, 12, 'pending', 'Booking submitted', 4, '2026-10-05 19:04:45'),
(30, 13, 'pending', 'Booking submitted', 4, '2026-10-05 19:06:20'),
(31, 14, 'pending', 'Booking submitted', 4, '2026-10-05 19:11:23'),
(32, 15, 'pending', 'Booking submitted', 4, '2026-10-05 19:15:31'),
(33, 16, 'pending', 'Booking submitted', 4, '2026-10-05 19:18:24'),
(34, 17, 'pending', 'Booking submitted', 4, '2026-10-05 19:19:07'),
(35, 18, 'pending', 'Booking submitted', 4, '2026-10-05 19:20:18'),
(36, 19, 'pending', 'Booking submitted', 4, '2026-10-05 19:23:58'),
(37, 19, 'approved', NULL, 1, '2026-10-05 19:24:47'),
(38, 20, 'pending', 'Booking submitted', 4, '2026-10-06 17:59:08'),
(39, 21, 'pending', 'Booking submitted', 8, '2026-10-06 18:27:54'),
(40, 22, 'pending', 'Booking submitted', 8, '2026-10-06 18:29:19'),
(41, 23, 'pending', 'Booking submitted', 8, '2026-10-06 18:35:01'),
(42, 24, 'pending', 'Booking submitted', 8, '2026-10-06 18:35:57'),
(43, 23, 'approved', NULL, 1, '2026-10-06 18:38:31');

-- --------------------------------------------------------

--
-- Table structure for table `baptism_booking_details`
--

CREATE TABLE `baptism_booking_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `child_full_name` varchar(245) NOT NULL,
  `child_dob` date NOT NULL,
  `child_birth_place` varchar(255) NOT NULL,
  `father_full_name` varchar(245) NOT NULL,
  `mother_maiden_name` varchar(245) NOT NULL,
  `parents_marriage_status` varchar(60) NOT NULL,
  `contact_number` varchar(30) NOT NULL,
  `contact_email` varchar(190) NOT NULL,
  `baptism_type` varchar(40) NOT NULL,
  `principal_male_godparent` varchar(245) NOT NULL,
  `principal_female_godparent` varchar(245) NOT NULL,
  `additional_godparents` text DEFAULT NULL,
  `extra_godparent_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `extra_godparent_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `psa_birth_certificate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `baptism_booking_details`
--

INSERT INTO `baptism_booking_details` (`id`, `appointment_id`, `child_full_name`, `child_dob`, `child_birth_place`, `father_full_name`, `mother_maiden_name`, `parents_marriage_status`, `contact_number`, `contact_email`, `baptism_type`, `principal_male_godparent`, `principal_female_godparent`, `additional_godparents`, `extra_godparent_count`, `extra_godparent_fee`, `psa_birth_certificate`, `created_at`) VALUES
(1, 20, 'dfgdfgfd', '2026-10-01', 'sdfsdfdsf', 'sdfdsf', 'sdfds', 'Civilly Married', '09765676575', 'juswa@gmail.com', 'Mass Baptism', 'asdas', 'asdas', '[\"asdasd\"]', 1, 50.00, 'uploads/baptism_documents/psa-birth-99f66642afb2415ebca27710.jpg', '2026-10-06 17:59:08'),
(2, 21, 'Juuj Fghgf Fghgf', '2024-06-05', 'Santa Rosa I, Marilao, Bulacan', 'Dasd Asd Asdas', 'Asd Asd Asdsa', 'Married in Catholic Church', '09867867867', 'lala123@gmail.com', 'Mass Baptism', 'Asd', 'Asdsa', '[\"Asdasdsa\",\"Asdas\"]', 2, 100.00, 'uploads/baptism_documents/psa-birth-b272b76922f53284e80485e0.jpg', '2026-10-06 18:27:54'),
(3, 22, 'Asd Asdas Asdasd', '2026-10-01', 'Danahao, Clarin, Bohol', 'Asdasd Asd Asdasd', 'Asd Asd Asdasd', 'Civilly Married', '09876678768', 'lala123@gmail.com', 'Mass Baptism', 'Sdas', 'Asdasd', '[\"Asdsad\"]', 1, 50.00, 'uploads/baptism_documents/psa-birth-43a683e5da4f93c87df6b421.jpg', '2026-10-06 18:29:19'),
(4, 24, 'Sads Sadsad Asdsa', '1996-05-14', 'San Luis, Malitbog, Bukidnon', 'Asdsa Asdsa Asdsad', 'Asds Asdsa Asdsa', 'Married in Catholic Church', '09567576567', 'lala123@gmail.com', 'Mass Baptism', 'Asdas', 'Asdsa', '[\"Asdsadsad\"]', 1, 50.00, 'uploads/baptism_documents/psa-birth-cfbdc1d90501c9395a03b899.png', '2026-10-06 18:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `category` varchar(60) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `category`, `created_at`) VALUES
(1, 'MASS', 'ATTEND NAMO MGA BOANGGG', '2026-10-02', '06:50:00', '07:50:00', 'MASS', '2026-10-01 15:47:02'),
(2, 'Free wedding', 'PAKASAL NA MGA BOANG2', '2026-10-02', '09:03:00', '12:06:00', 'Weeding', '2026-10-01 15:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Appointment update', 'Your appointment SP-20260930-EEFF02 is now approved.', 1, '2026-09-30 16:47:01'),
(2, 4, 'Appointment update', 'Your appointment SP-20260930-E7C9DD is now rejected.', 1, '2026-09-30 17:40:42'),
(3, 2, 'Appointment update', 'Your appointment SP-20260930-EEFF02 is now completed.', 0, '2026-09-30 17:40:45'),
(4, 4, 'Appointment update', 'Your appointment SP-20260930-617921 is now approved.', 1, '2026-09-30 18:04:05'),
(5, 4, 'Appointment update', 'Your appointment SP-20260930-72D5A8 is now rejected.', 1, '2026-09-30 18:07:05'),
(6, 6, 'Appointment update', 'Your appointment SP-20260930-6F0B2B is now rejected. Reason: WAH KA NI BAYAD BOSS HAHAHHAHA', 1, '2026-09-30 19:17:47'),
(7, 4, 'Appointment update', 'Your appointment SP-20260930-E1A6ED is now approved.', 1, '2026-09-30 19:50:05'),
(8, 4, 'Appointment update', 'Your appointment SP-20260930-CD473B is now rejected. Reason: no payment', 1, '2026-09-30 19:53:10'),
(9, 4, 'Appointment update', 'Your appointment SP-20260930-E1A6ED is now completed.', 1, '2026-09-30 19:55:54'),
(10, 4, 'Appointment update', 'Your appointment SP-20260930-617921 is now completed.', 1, '2026-09-30 20:48:01'),
(11, 4, 'Appointment update', 'Your appointment SP-20260930-5AEA9E is now approved.', 1, '2026-09-30 20:49:33'),
(12, 4, 'Appointment update', 'Your appointment SP-20260930-5AEA9E is now completed.', 1, '2026-10-01 16:14:47'),
(13, 4, 'Appointment update', 'Your appointment SP-20261001-9208A9 is now approved.', 1, '2026-10-01 16:54:38'),
(14, 4, 'Appointment update', 'Your appointment SP-20261001-9208A9 is now completed.', 1, '2026-10-01 16:55:05'),
(15, 4, 'Appointment update', 'Your appointment SP-20261002-5F165C is now rejected. Reason: bhoanggg', 1, '2026-10-01 17:41:23'),
(16, 4, 'Appointment update', 'Your appointment SP-20261006-F6B053 is now approved.', 1, '2026-10-05 18:23:43'),
(17, 4, 'Appointment update', 'Your appointment SP-20261006-F6B053 is now completed.', 1, '2026-10-05 18:50:04'),
(18, 4, 'Appointment update', 'Your appointment SP-20261006-65FD4D is now approved.', 1, '2026-10-05 19:24:47'),
(19, 8, 'Appointment update', 'Your appointment SP-20261007-D722CF is now approved.', 1, '2026-10-06 18:38:31');

-- --------------------------------------------------------

--
-- Table structure for table `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `gcash_account_name` varchar(160) DEFAULT NULL,
  `gcash_number` varchar(30) DEFAULT NULL,
  `gcash_qr_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_settings`
--

INSERT INTO `payment_settings` (`id`, `gcash_account_name`, `gcash_number`, `gcash_qr_path`, `updated_at`) VALUES
(1, 'WANGZHI WANG', '0912345678900', 'uploads/payment_qr/gcash-8f4b956a997d14a785183afc.png', '2026-09-30 21:12:33');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`, `description`, `capacity`, `status`, `created_at`) VALUES
(1, 'Parish Hall', 'Main parish function hall', 150, 'available', '2026-09-30 16:38:26'),
(2, 'Room A', 'Meeting and formation room', 35, 'available', '2026-09-30 16:38:26'),
(3, 'Room B', 'Small meeting room', 20, 'available', '2026-09-30 16:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 60,
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `duration_minutes`, `fee`, `image`, `status`, `created_at`) VALUES
(1, 'Baptism', 'Schedule a baptism and submit the required details.', 60, 0.00, NULL, 'active', '2026-09-30 16:38:26'),
(2, 'Wedding', 'Reserve a church wedding schedule.', 120, 500.00, NULL, 'active', '2026-09-30 16:38:26'),
(3, 'Blessing', 'Request a home, business, or object blessing.', 60, 0.00, NULL, 'active', '2026-09-30 16:38:26'),
(4, 'Mass Intention', 'Request a Mass intention.', 60, 0.00, NULL, 'active', '2026-09-30 16:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(80) DEFAULT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `contact_number` varchar(30) NOT NULL,
  `province_code` varchar(20) DEFAULT NULL,
  `province` varchar(120) DEFAULT NULL,
  `city_municipality_code` varchar(20) DEFAULT NULL,
  `city_municipality` varchar(160) DEFAULT NULL,
  `barangay_code` varchar(20) DEFAULT NULL,
  `barangay` varchar(160) DEFAULT NULL,
  `street_purok` varchar(255) DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `birthday` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('parishioner','admin') NOT NULL DEFAULT 'parishioner',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `full_name`, `email`, `contact_number`, `province_code`, `province`, `city_municipality_code`, `city_municipality`, `barangay_code`, `barangay`, `street_purok`, `address`, `birthday`, `gender`, `password`, `profile_image`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, 'Parish Administrator', 'admin@smartparish.com', 'N/A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Parish Office', '2000-01-01', 'Other', '$2y$10$d8s.k/in/aS.fD691g3PSeThI0nFGLZX.91sR1ii8aVcWXga/WC2O', NULL, 'admin', 'active', '2026-09-30 16:41:06', '2026-09-30 16:41:06'),
(2, NULL, NULL, NULL, 'jayson parsing', 'jayson123@gmail.com', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'purok 1 pitogo consolacion cebu', '2000-08-16', 'Male', '$2y$10$Al9QZ2imWoVDddSC55J0pOsOEgFiI4BYaTQsry0/Sh9CqxwppA4vG', NULL, 'parishioner', 'active', '2026-09-30 16:43:12', '2026-09-30 16:43:12'),
(3, NULL, NULL, NULL, 'allan cayatano', 'allan123@gmail.com', '093243543456456456546', '0702200000', 'Cebu', '0702219000', 'Consolacion', '0702219016', 'Pulpogan', 'purok 2', 'purok 2, Pulpogan, Consolacion, Cebu', '2026-09-04', 'Male', '$2y$10$jNnQjrmO0jMzP/DrUP5wDOzdQ5hbaZxnIrVFZuW1sEdxP8jJmkfTW', NULL, 'parishioner', 'active', '2026-09-30 17:00:41', '2026-09-30 17:00:41'),
(4, 'juswa', NULL, 'boang', 'juswa boang', 'juswa@gmail.com', '09432534534', '0702200000', 'Cebu', '0702213000', 'Borbon', '0702213015', 'Poblacion', 'purok 2', 'purok 2, Poblacion, Borbon, Cebu', '2023-04-30', 'Male', '$2y$10$2MzuRVxeCxtPy4WKbxamhu5S4iCM.IXdfN1trzrfyeXIbm1cfKYKm', 'assets/uploads/profiles/profile_4_2024ac5e8959966b.png', 'parishioner', 'active', '2026-09-30 17:12:07', '2026-09-30 21:12:08'),
(5, 'lala', NULL, 'hjashahs', 'lala hjashahs', 'lala@gmail.com', '09234234534', '0201500000', 'Cagayan', '0201516000', 'Lal-Lo', '0201516011', 'Catayauan', 'araw 2', 'araw 2, Catayauan, Lal-Lo, Cagayan', '2008-09-11', 'Male', '$2y$10$nT6Eq54B08RdzwO5qheNEOSxbXwckDiTsQT8GT8eQqC9Or21Uj7Za', NULL, 'parishioner', 'active', '2026-09-30 17:18:20', '2026-09-30 17:18:20'),
(6, 'Baho', NULL, 'Tae', 'Baho Tae', 'baho133@gmail.com', '09491881166', '0701200000', 'Bohol', '0701206000', 'Balilihan', '0701206005', 'Boyog Proper', 'Purok 1', 'Purok 1, Boyog Proper, Balilihan, Bohol', '2001-01-12', 'Male', '$2y$10$a2GGBVKMrbJicqHDZ2l7JuwJv7.QRvQEMqhZ4lY11WqcqwbCMsOeC', NULL, 'parishioner', 'active', '2026-09-30 17:27:07', '2026-09-30 17:27:07'),
(7, 'reynan', NULL, 'allan', 'reynan allan', 'reynan@gmail.com', '09234345345', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Parish Administration', '1900-01-01', 'Other', '$2y$10$xCjCbxtl69pNjkQdKPj9ae75r3mdYjRkPWandKXniEjgGAPV9nW.O', NULL, 'admin', 'active', '2026-10-01 15:31:46', '2026-10-01 15:31:46'),
(8, 'Lala', 'Waw', 'Lalares', 'Lala Waw Lalares', 'lala123@gmail.com', '09346453645', '0201500000', 'Cagayan', '0201517000', 'Lasam', '0201517012', 'Gabun', 'purok 22', 'purok 22, Gabun, Lasam, Cagayan', '2004-06-10', 'Male', '$2y$10$KLaDm3D1u1HfSR6Obz59qeCacR0kAwElodheW1OunVjkUh5LWCvmO', NULL, 'parishioner', 'active', '2026-10-06 18:26:31', '2026-10-06 18:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `wedding_booking_details`
--

CREATE TABLE `wedding_booking_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED NOT NULL,
  `groom_full_name` varchar(245) NOT NULL,
  `groom_dob` date NOT NULL,
  `groom_religion_parish` varchar(255) NOT NULL,
  `bride_full_name` varchar(245) NOT NULL,
  `bride_dob` date NOT NULL,
  `bride_religion_parish` varchar(255) NOT NULL,
  `contact_number` varchar(30) NOT NULL,
  `contact_email` varchar(190) NOT NULL,
  `ceremony_type` varchar(80) NOT NULL,
  `preferred_officiant` varchar(160) DEFAULT NULL,
  `civil_requirement` varchar(40) NOT NULL DEFAULT 'license',
  `psa_birth_certificates` text DEFAULT NULL,
  `cenomar` text DEFAULT NULL,
  `baptism_confirmation` text DEFAULT NULL,
  `pmc_certificate` text DEFAULT NULL,
  `marriage_document` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wedding_booking_details`
--

INSERT INTO `wedding_booking_details` (`id`, `appointment_id`, `groom_full_name`, `groom_dob`, `groom_religion_parish`, `bride_full_name`, `bride_dob`, `bride_religion_parish`, `contact_number`, `contact_email`, `ceremony_type`, `preferred_officiant`, `civil_requirement`, `psa_birth_certificates`, `cenomar`, `baptism_confirmation`, `pmc_certificate`, `marriage_document`, `created_at`) VALUES
(1, 11, 'JUNAS MEAS ALOS', '1997-07-23', 'CATHOLIC', 'ANNA LIOS JASA', '1988-12-14', 'CATHOLIC', '09234345345', 'juswa@gmail.com', 'Private Wedding', NULL, 'license', 'uploads/wedding_documents/psa-b4bea1799d9fa24f82fa0f7d.png', 'uploads/wedding_documents/cenomar-825a5f9309b2c48fa1d03cc3.png', 'uploads/wedding_documents/baptism-confirmation-4e1959c2a917d43c58206074.png', 'uploads/wedding_documents/pmc-be19707011986181b36ceff1.jpg', 'uploads/wedding_documents/marriage-bbb50a56be4cfba8bd952fd5.jpg', '2026-10-05 18:22:26'),
(2, 12, 'sdfsdfdsf', '1992-10-14', 'sdf', 'sdfsdfdsfd', '1998-07-02', 'sdfsd', '09867868767', 'juswa@gmail.com', 'Private Wedding', 'asd', 'license', '[\"uploads/wedding_documents/psa-a709ff7edeaeadecb26b4728.png\",\"uploads/wedding_documents/psa-4d0a962d9a4655275fb27d2e.jpg\"]', '[\"uploads/wedding_documents/cenomar-c6fce9144cca267750961a69.jpg\",\"uploads/wedding_documents/cenomar-d6f69d6955410699610725df.png\"]', '[\"uploads/wedding_documents/baptism-confirmation-417ef257a3107adc3df1f692.png\",\"uploads/wedding_documents/baptism-confirmation-a24c7d839181724252ff0f78.jpg\"]', '[\"uploads/wedding_documents/pmc-ae57ea824072c7da80e918b9.jpg\",\"uploads/wedding_documents/pmc-72554eec4bf43e4b065e9a8b.png\"]', '[\"uploads/wedding_documents/marriage-65f0e1557df794478b142a85.jpg\",\"uploads/wedding_documents/marriage-9da21715a5d8ae4eda9933f4.png\"]', '2026-10-05 19:04:45'),
(3, 13, 'fgrhg dfg', '1994-06-10', 'dfgfd', 'dfgdfgfd', '1990-05-16', 'dfgfd', '09876565756', 'juswa@gmail.com', 'Private Wedding', NULL, 'license', '[\"uploads/wedding_documents/psa-65ef4d4072c7279d69ec3287.jpg\",\"uploads/wedding_documents/psa-e8e657d56b5ee1969e2bd6bf.png\"]', '[\"uploads/wedding_documents/cenomar-36af0e3551a22abab1f2f51a.png\"]', '[\"uploads/wedding_documents/baptism-confirmation-e05c03310806c500c646001c.jpg\"]', '[\"uploads/wedding_documents/pmc-52f4ae00484170a44f3fc334.png\"]', '[\"uploads/wedding_documents/marriage-6a32c4af7b629b4fa66f132b.png\"]', '2026-10-05 19:06:20'),
(4, 14, 'fgrhg dfg', '1994-06-10', 'dfgfd', 'dfgdfgfd', '1990-05-16', 'dfgfd', '09876565756', 'juswa@gmail.com', 'Private Wedding', NULL, 'license', '[\"uploads/wedding_documents/psa-bb551c8384e9b2af70ead05e.jpg\"]', '[\"uploads/wedding_documents/cenomar-076f36c7e37a9c32a051d96f.png\"]', '[\"uploads/wedding_documents/baptism-confirmation-3dcb4c9a1b43c47cc95e8e5a.png\"]', '[\"uploads/wedding_documents/pmc-1a5f61410bb21d3a4497105d.jpg\"]', '[\"uploads/wedding_documents/marriage-5522e4773517c2b0e2fb35fe.png\"]', '2026-10-05 19:11:23'),
(5, 15, 'rtrterert', '1994-06-10', 'dfgfd', 'dfgdfgfd', '1990-05-16', 'dfgfd', '09876565756', 'juswa@gmail.com', 'Private Wedding', 'ewrew', 'license', NULL, NULL, NULL, NULL, NULL, '2026-10-05 19:15:31'),
(6, 16, 'dsfd', '1994-06-10', 'dfgfd', 'dfgdfgfd', '1990-05-16', 'dfgfd', '09876565756', 'juswa@gmail.com', 'Private Wedding', 'dsf', 'license', NULL, NULL, NULL, NULL, NULL, '2026-10-05 19:18:24'),
(7, 19, 'sdfsdf sdfds sdfsd', '2007-07-06', 'asddsfsd', 'terer ertert', '1993-06-09', 'qweqw', '09897897897', 'juswa@gmail.com', 'Kasalang Bayan (Mass Wedding)', 'wqwewq', 'license', '[\"uploads/wedding_documents/psa-e03c12319e72c4555db75721.png\",\"uploads/wedding_documents/psa-d9a1023371d58441e2e6c6cf.jpg\"]', '[\"uploads/wedding_documents/cenomar-6f3201c6a3be59a1a803ba52.png\"]', '[\"uploads/wedding_documents/baptism-confirmation-b5d18c91483d5f1bce02a2e2.jpg\"]', '[\"uploads/wedding_documents/pmc-064ff3a02158b53c25ddd4f4.png\"]', '[\"uploads/wedding_documents/marriage-f594238b21ab9e72ff8525a1.png\"]', '2026-10-05 19:23:58'),
(8, 23, 'Sdsd Sfds Sdfds', '1998-06-10', 'dsfsdfsd', 'Sdfds Sdfds Sdfds', '1988-06-08', 'sadsad', '09876657675', 'lala123@gmail.com', 'Private Wedding', 'dsad', 'license', '[\"uploads/wedding_documents/psa-94fba7791a73dcaa93bfaf9a.jpg\"]', '[\"uploads/wedding_documents/cenomar-e25152568c15a25d933294a7.png\"]', '[\"uploads/wedding_documents/baptism-confirmation-5e860898ab244aec29e5422c.jpg\"]', '[\"uploads/wedding_documents/pmc-fab088cbe480c1c533ec3ac6.jpg\"]', '[\"uploads/wedding_documents/marriage-23ddb93cd371a3c0cc052370.png\"]', '2026-10-06 18:35:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `published_by` (`published_by`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `idx_schedule` (`appointment_date`,`start_time`,`end_time`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `baptism_booking_details`
--
ALTER TABLE `baptism_booking_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wedding_booking_details`
--
ALTER TABLE `wedding_booking_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `appointment_history`
--
ALTER TABLE `appointment_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `baptism_booking_details`
--
ALTER TABLE `baptism_booking_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wedding_booking_details`
--
ALTER TABLE `wedding_booking_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD CONSTRAINT `appointment_history_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `baptism_booking_details`
--
ALTER TABLE `baptism_booking_details`
  ADD CONSTRAINT `baptism_booking_details_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wedding_booking_details`
--
ALTER TABLE `wedding_booking_details`
  ADD CONSTRAINT `fk_wedding_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
