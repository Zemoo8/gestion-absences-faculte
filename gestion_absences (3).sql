-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2026 at 09:53 AM
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
-- Database: `gestion_absences`
--

-- --------------------------------------------------------

--
-- Table structure for table `absence_thresholds`
--

CREATE TABLE `absence_thresholds` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `max_absences` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_requests`
--

CREATE TABLE `account_requests` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `requested_role` enum('student','professor') DEFAULT 'student',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `account_requests`
--

INSERT INTO `account_requests` (`id`, `nom`, `prenom`, `email`, `photo_path`, `requested_role`, `status`, `created_at`) VALUES
(24, 'Mohamed', 'Ben ali', 'mohamed.ben.ali.6589@gmail.com', NULL, 'student', 'approved', '2026-02-04 15:27:28'),
(25, 'nader', 'belhadj', 'nader.belhadj@ensi-uma.tn', NULL, 'student', 'approved', '2026-02-04 16:08:53'),
(26, 'rania', 'abidi', 'rania26abidi@gmail.com', NULL, 'student', 'approved', '2026-02-04 16:09:09'),
(27, 'Ze', 'moo', 'ahmedbaghoulii@gmail.com', NULL, 'student', 'approved', '2026-02-04 16:10:08');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `module_id`, `date`, `status`, `start_time`, `end_time`) VALUES
(49, 27, 9, '2025-11-06', 'present', '00:00:00', '00:00:00'),
(50, 28, 9, '2025-11-06', 'absent', '00:00:00', '00:00:00'),
(51, 29, 9, '2025-11-06', 'present', '00:00:00', '00:00:00'),
(56, 27, 9, '2025-11-08', 'present', '00:00:00', '00:00:00'),
(57, 28, 9, '2025-11-08', 'present', '00:00:00', '00:00:00'),
(58, 29, 9, '2025-11-08', 'present', '00:00:00', '00:00:00'),
(61, 27, 9, '2025-11-13', 'present', '00:00:00', '00:00:00'),
(62, 28, 9, '2025-11-13', 'absent', '00:00:00', '00:00:00'),
(63, 29, 9, '2025-11-13', 'present', '00:00:00', '00:00:00'),
(68, 27, 9, '2025-11-15', 'present', '00:00:00', '00:00:00'),
(69, 28, 9, '2025-11-15', 'present', '00:00:00', '00:00:00'),
(70, 29, 9, '2025-11-15', 'present', '00:00:00', '00:00:00'),
(74, 27, 9, '2025-11-20', 'present', '00:00:00', '00:00:00'),
(75, 28, 9, '2025-11-20', 'present', '00:00:00', '00:00:00'),
(76, 29, 9, '2025-11-20', 'present', '00:00:00', '00:00:00'),
(80, 27, 9, '2025-11-22', 'present', '00:00:00', '00:00:00'),
(81, 28, 9, '2025-11-22', 'present', '00:00:00', '00:00:00'),
(82, 29, 9, '2025-11-22', 'present', '00:00:00', '00:00:00'),
(86, 27, 9, '2025-11-27', 'present', '00:00:00', '00:00:00'),
(87, 28, 9, '2025-11-27', 'absent', '00:00:00', '00:00:00'),
(88, 29, 9, '2025-11-27', 'present', '00:00:00', '00:00:00'),
(92, 27, 9, '2025-11-29', 'present', '00:00:00', '00:00:00'),
(93, 28, 9, '2025-11-29', 'present', '00:00:00', '00:00:00'),
(94, 29, 9, '2025-11-29', 'present', '00:00:00', '00:00:00'),
(97, 27, 10, '2025-11-07', 'present', '00:00:00', '00:00:00'),
(98, 28, 10, '2025-11-07', 'present', '00:00:00', '00:00:00'),
(99, 29, 10, '2025-11-07', 'present', '00:00:00', '00:00:00'),
(104, 27, 10, '2025-11-09', 'present', '00:00:00', '00:00:00'),
(105, 28, 10, '2025-11-09', 'present', '00:00:00', '00:00:00'),
(106, 29, 10, '2025-11-09', 'present', '00:00:00', '00:00:00'),
(110, 27, 10, '2025-11-14', 'present', '00:00:00', '00:00:00'),
(111, 28, 10, '2025-11-14', 'present', '00:00:00', '00:00:00'),
(112, 29, 10, '2025-11-14', 'present', '00:00:00', '00:00:00'),
(116, 27, 10, '2025-11-16', 'present', '00:00:00', '00:00:00'),
(117, 28, 10, '2025-11-16', 'present', '00:00:00', '00:00:00'),
(118, 29, 10, '2025-11-16', 'present', '00:00:00', '00:00:00'),
(122, 27, 10, '2025-11-21', 'present', '00:00:00', '00:00:00'),
(123, 28, 10, '2025-11-21', 'present', '00:00:00', '00:00:00'),
(124, 29, 10, '2025-11-21', 'present', '00:00:00', '00:00:00'),
(128, 27, 10, '2025-11-23', 'present', '00:00:00', '00:00:00'),
(129, 28, 10, '2025-11-23', 'present', '00:00:00', '00:00:00'),
(130, 29, 10, '2025-11-23', 'present', '00:00:00', '00:00:00'),
(134, 27, 10, '2025-11-28', 'present', '00:00:00', '00:00:00'),
(135, 28, 10, '2025-11-28', 'present', '00:00:00', '00:00:00'),
(136, 29, 10, '2025-11-28', 'present', '00:00:00', '00:00:00'),
(140, 27, 10, '2025-11-30', 'present', '00:00:00', '00:00:00'),
(141, 28, 10, '2025-11-30', 'present', '00:00:00', '00:00:00'),
(142, 29, 10, '2025-11-30', 'present', '00:00:00', '00:00:00'),
(146, 19, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(148, 27, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(149, 28, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(150, 29, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(154, 1, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(155, 3, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(156, 4, 9, '2025-12-05', 'present', '00:00:00', '00:00:00'),
(177, 63, 12, '2025-12-27', 'absent', '00:00:00', '00:00:00'),
(178, 19, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(180, 27, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(181, 28, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(182, 29, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(188, 1, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(189, 3, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(190, 4, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(192, 60, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(193, 62, 12, '2025-12-27', 'present', '00:00:00', '00:00:00'),
(195, 63, 12, '2025-12-27', 'absent', '10:23:00', '10:28:00'),
(196, 19, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(198, 27, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(199, 28, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(200, 29, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(206, 1, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(207, 3, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(208, 4, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(210, 60, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(211, 62, 12, '2025-12-27', 'present', '10:23:00', '10:28:00'),
(213, 63, 12, '2025-12-27', 'absent', '10:29:00', '10:34:00'),
(214, 19, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(216, 27, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(217, 28, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(218, 29, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(224, 1, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(225, 3, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(226, 4, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(228, 60, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(229, 62, 12, '2025-12-27', 'present', '10:29:00', '10:34:00'),
(231, 63, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(232, 19, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(234, 27, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(235, 28, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(236, 29, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(243, 4, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(244, 3, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(245, 1, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(247, 60, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(248, 62, 13, '2025-12-27', 'present', '14:00:00', '14:25:00'),
(250, 63, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(251, 19, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(253, 27, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(254, 28, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(255, 29, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(262, 4, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(263, 3, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(264, 1, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(266, 60, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(267, 62, 13, '2025-12-27', 'present', '14:30:00', '14:32:00'),
(269, 63, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(270, 19, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(272, 27, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(273, 28, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(274, 29, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(281, 4, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(282, 3, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(283, 1, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(285, 60, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(286, 62, 13, '2025-12-27', 'present', '14:31:00', '14:32:00'),
(288, 63, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(289, 19, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(291, 27, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(292, 28, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(293, 29, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(300, 4, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(301, 3, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(302, 1, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(304, 60, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(305, 62, 13, '2025-12-27', 'present', '14:32:00', '14:36:00'),
(307, 63, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(308, 19, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(310, 27, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(311, 28, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(312, 29, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(319, 4, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(320, 3, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(321, 1, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(323, 60, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(324, 62, 13, '2025-12-27', 'present', '14:35:00', '14:38:00'),
(326, 63, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(327, 19, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(329, 27, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(330, 28, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(331, 29, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(338, 4, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(339, 3, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(340, 1, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(342, 60, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(343, 62, 13, '2025-12-27', 'present', '14:39:00', '14:42:00'),
(345, 63, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(346, 19, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(348, 27, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(349, 28, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(350, 29, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(357, 4, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(358, 3, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(359, 1, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(361, 60, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(362, 62, 13, '2025-12-27', 'present', '14:40:00', '14:42:00'),
(386, 63, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(387, 19, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(388, 27, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(389, 28, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(390, 29, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(394, 4, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(395, 3, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(396, 1, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(397, 60, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(398, 62, 12, '2025-12-31', 'present', '14:00:00', '15:00:00'),
(413, 19, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(414, 27, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(415, 28, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(416, 29, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(421, 3, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(423, 60, 13, '2026-01-07', 'absent', '12:00:00', '14:00:00'),
(425, 63, 13, '2026-01-07', 'absent', '12:00:00', '14:00:00'),
(429, 4, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(430, 1, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(431, 62, 13, '2026-01-07', 'present', '12:00:00', '14:00:00'),
(432, 63, 13, '2026-01-09', 'present', '09:00:00', '14:00:00'),
(434, 4, 13, '2026-01-09', 'present', '09:00:00', '14:00:00'),
(435, 1, 13, '2026-01-09', 'present', '09:00:00', '14:00:00'),
(437, 62, 13, '2026-01-09', 'present', '09:00:00', '14:00:00'),
(438, 63, 13, '2026-01-09', 'present', '10:00:00', '14:00:00'),
(440, 4, 13, '2026-01-09', 'present', '10:00:00', '14:00:00'),
(441, 1, 13, '2026-01-09', 'present', '10:00:00', '14:00:00'),
(443, 62, 13, '2026-01-09', 'present', '10:00:00', '14:00:00'),
(444, 63, 13, '2026-01-11', 'present', '17:00:00', '18:00:00'),
(446, 1, 13, '2026-01-11', 'present', '17:00:00', '18:00:00'),
(447, 4, 13, '2026-01-11', 'present', '17:00:00', '18:00:00'),
(449, 62, 13, '2026-01-11', 'absent', '17:00:00', '18:00:00'),
(450, 63, 13, '2026-01-11', 'present', '17:05:00', '18:00:00'),
(452, 1, 13, '2026-01-11', 'present', '17:05:00', '18:00:00'),
(453, 4, 13, '2026-01-11', 'present', '17:05:00', '18:00:00'),
(455, 62, 13, '2026-01-11', 'absent', '17:05:00', '18:00:00'),
(461, 63, 12, '2026-01-31', 'absent', '17:00:00', '18:00:00'),
(463, 1, 12, '2026-01-31', 'present', '17:00:00', '18:00:00'),
(464, 4, 12, '2026-01-31', 'absent', '17:00:00', '18:00:00'),
(465, 62, 12, '2026-01-31', 'present', '17:00:00', '18:00:00'),
(466, 63, 12, '2026-01-31', 'present', '17:00:00', '17:08:00'),
(468, 1, 12, '2026-01-31', 'present', '17:00:00', '17:08:00'),
(469, 4, 12, '2026-01-31', 'absent', '17:00:00', '17:08:00'),
(470, 62, 12, '2026-01-31', 'present', '17:00:00', '17:08:00'),
(471, 63, 13, '2026-02-02', 'present', '12:05:00', '12:15:00'),
(473, 1, 13, '2026-02-02', 'absent', '12:05:00', '12:15:00'),
(474, 4, 13, '2026-02-02', 'absent', '12:05:00', '12:15:00'),
(475, 62, 13, '2026-02-02', 'present', '12:05:00', '12:15:00'),
(491, 63, 13, '2026-02-02', 'absent', '12:05:00', '13:15:00'),
(493, 4, 13, '2026-02-02', 'present', '12:05:00', '13:15:00'),
(494, 1, 13, '2026-02-02', 'present', '12:05:00', '13:15:00'),
(495, 62, 13, '2026-02-02', 'absent', '12:05:00', '13:15:00'),
(496, 63, 13, '2026-02-03', 'present', '09:05:00', '21:15:00'),
(498, 1, 13, '2026-02-03', 'present', '09:05:00', '21:15:00'),
(499, 4, 13, '2026-02-03', 'present', '09:05:00', '21:15:00'),
(500, 62, 13, '2026-02-03', 'present', '09:05:00', '21:15:00'),
(511, 63, 12, '2026-02-03', 'present', '17:00:00', '21:08:00'),
(513, 1, 12, '2026-02-03', 'present', '17:00:00', '21:08:00'),
(514, 4, 12, '2026-02-03', 'present', '17:00:00', '21:08:00'),
(515, 62, 12, '2026-02-03', 'absent', '17:00:00', '21:08:00'),
(516, 63, 12, '2026-02-03', 'present', '17:00:00', '21:02:00'),
(518, 1, 12, '2026-02-03', 'present', '17:00:00', '21:02:00'),
(519, 4, 12, '2026-02-03', 'present', '17:00:00', '21:02:00'),
(520, 62, 12, '2026-02-03', 'absent', '17:00:00', '21:02:00'),
(535, 72, 15, '2026-02-03', 'absent', '21:00:00', '22:00:00'),
(536, 63, 15, '2026-02-03', 'present', '21:00:00', '22:00:00'),
(537, 1, 15, '2026-02-03', 'present', '21:00:00', '22:00:00'),
(538, 4, 15, '2026-02-03', 'present', '21:00:00', '22:00:00'),
(539, 62, 15, '2026-02-03', 'present', '21:00:00', '22:00:00'),
(540, 72, 15, '2026-02-04', 'absent', '12:00:00', '23:00:00'),
(541, 63, 15, '2026-02-04', 'present', '12:00:00', '23:00:00'),
(542, 80, 15, '2026-02-04', 'present', '12:00:00', '23:00:00'),
(543, 1, 15, '2026-02-04', 'present', '12:00:00', '23:00:00'),
(544, 4, 15, '2026-02-04', 'present', '12:00:00', '23:00:00'),
(545, 62, 15, '2026-02-04', 'present', '12:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `created_at`) VALUES
(1, 'First Year CS', '2025-12-27 08:19:17'),
(2, 'Second Year CS', '2025-12-27 08:19:17'),
(3, 'Third Year CS', '2025-12-27 08:19:17'),
(4, 'Master 1 AI', '2025-12-27 08:19:17'),
(5, 'helloo test', '2026-01-06 08:57:22');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `module_name` varchar(150) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `course_start_time` time DEFAULT NULL,
  `total_hours` tinyint(3) UNSIGNED NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_name`, `professor_id`, `course_start_time`, `total_hours`) VALUES
(9, 'Data Structures & Algorithms', 22, NULL, 30),
(10, 'Web Development with PHP', 22, NULL, 30),
(11, 'web', 22, NULL, 30),
(12, 'cyberpunk', 22, NULL, 30),
(13, 'tahaa', 22, NULL, 30),
(15, 'test taw', 22, NULL, 30),
(16, 'testlyoum11', 22, NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `module_classes`
--

CREATE TABLE `module_classes` (
  `module_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `module_classes`
--

INSERT INTO `module_classes` (`module_id`, `class_id`) VALUES
(12, 1),
(12, 4),
(13, 1),
(15, 1),
(16, 1);

-- --------------------------------------------------------

--
-- Table structure for table `module_schedule`
--

CREATE TABLE `module_schedule` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `module_schedule`
--

INSERT INTO `module_schedule` (`id`, `module_id`, `weekday`, `start_time`, `end_time`) VALUES
(1, 9, 2, '17:00:00', '21:00:00'),
(2, 9, 3, '14:00:00', '16:00:00'),
(3, 10, 2, '09:00:00', '11:00:00'),
(4, 10, 4, '14:00:00', '16:00:00'),
(5, 12, 2, '17:00:00', '21:02:00'),
(6, 13, 2, '21:05:00', '23:15:00'),
(8, 15, 3, '12:00:00', '17:17:00'),
(9, 9, 2, '17:00:00', '21:00:00'),
(10, 16, 3, '17:00:00', '18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `created_at`) VALUES
(1, 4, 'a04fb49de7ae9b1e203b5519bd8914f8dbae045f468be76bf19872bb5dd7b044c2abab617c1e94c230960432b776e3dc9c17', '2025-12-01 20:04:00'),
(2, 8, '95cecefcdf7597aa54a7d7dc7aeca2c40c5197145f3edd45fae397033cc142d2000849c3b23530dd43b54cca81a6561f6137', '2025-12-02 10:12:09'),
(3, 10, '8d95d3bf097bba5b595c7dfb8bbafe2c1e12f2e5dd642bd9cc982655d1e19a13e4e3f80525590b830b6d00c307d055650667', '2025-12-03 15:37:24'),
(5, 10, '04d41de73400bfada77bbdf61e30bc56d0e8f53447a55d4cfa572618e3c81706e11b8db5e0fb0a7b35305afb14b0524fe483', '2026-02-03 17:39:12'),
(6, 72, '306daa806b66943d466139623578ac952adbd6d65e12bf3ec268bc50c013d48b3469d199fa6ca4c1f7012d5afaaac1aed6bc', '2026-02-04 16:10:39');

-- --------------------------------------------------------

--
-- Table structure for table `reminder_log`
--

CREATE TABLE `reminder_log` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `reminder_log`
--

INSERT INTO `reminder_log` (`id`, `student_id`, `professor_id`, `sent_at`) VALUES
(1, 64, 22, '2025-12-27 14:41:52'),
(2, 64, 22, '2025-12-31 14:10:52'),
(3, 63, 22, '2025-12-31 14:23:40'),
(4, 30, 22, '2025-12-31 14:25:02'),
(5, 63, 22, '2026-01-07 12:23:41'),
(6, 30, 22, '2026-01-07 12:23:43'),
(7, 64, 22, '2026-01-07 12:35:38'),
(8, 63, 22, '2026-01-11 17:30:33'),
(9, 64, 22, '2026-01-11 17:30:38'),
(10, 59, 22, '2026-01-11 17:30:43'),
(11, 62, 22, '2026-01-11 17:32:38'),
(12, 63, 22, '2026-01-16 10:08:24'),
(13, 64, 22, '2026-01-16 10:08:26'),
(14, 62, 22, '2026-01-16 10:08:29'),
(15, 63, 22, '2026-01-17 23:11:41'),
(16, 64, 22, '2026-01-17 23:11:51'),
(17, 62, 22, '2026-01-17 23:11:53'),
(18, 64, 22, '2026-01-31 17:10:17'),
(19, 64, 22, '2026-02-03 09:45:16'),
(20, 63, 22, '2026-02-03 20:03:40'),
(21, 4, 22, '2026-02-03 20:03:41'),
(22, 62, 22, '2026-02-03 20:08:42'),
(23, 63, 22, '2026-02-04 10:49:44'),
(24, 4, 22, '2026-02-04 10:49:46'),
(25, 62, 22, '2026-02-04 10:49:48'),
(26, 72, 22, '2026-02-04 17:18:14');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `session_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`student_id`, `class_id`) VALUES
(1, 1),
(4, 1),
(27, 2),
(62, 1),
(63, 1),
(65, 2),
(66, 2),
(72, 1),
(80, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin','professor') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `photo_path`, `password`, `role`) VALUES
(1, 'Test', 'User', 'test.user@zemoo.com', NULL, '$2y$10$CwTycUXWue0Thq9StjUM0uJ8g8hZt5QXfV.P4G5vZ2eF0exH1aP6K', 'student'),
(3, 'Test', 'User', 'test1.user@zemoo.com', NULL, '$2y$10$CwTycUXWue0Thq9StjUM0uJ8g8hZt5QXfV.P4G5vZ2eF0exH1aP6K', 'student'),
(4, 'Test', 'User', 'test2.user@zemoo.com', 'assets/uploads/profiles/profile_4_1770150316.jpg', 'pass123', 'student'),
(8, 'tawfi9', 'll', 'tawfi9.zemoo@gmail.com', NULL, '$2y$10$F0Ph6ZbwCJYF0oG5Iqp/N.JEMXDeRrsSxrZNcBNowDucro7/wbocG', 'professor'),
(10, 'Farouk', 'Zemoo', 'farouk.zemoo@gmail.com', 'assets/uploads/profiles/profile_10_1770141409.png', '$2y$10$5jwTzn9KprmDJYQhydvTxuDs3qPL82OLyXgOmfnCnqowOMJx7Y38m', 'admin'),
(17, 'zemoo', 'ez', 'zemooez22.zemoo@gmail.com', NULL, '$2y$10$QPN1T.c2t/QXhQAO5DP85ukYyQQTrcqFl0wrVMrWRpbWLNmQ.Id1u', 'professor'),
(19, 'ez', 'huge', 'ezhuge38.zemoo@gmail.com', NULL, '$2y$10$3l2OsJ.ao9zNvIELykRif.kSUmZnXbj9vqC8fx3E9xV4QFDo96IsS', 'student'),
(22, 'Smith', 'John', 'prof.smith@macademia.edu', 'assets/uploads/profiles/profile_22_1770221937.webp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor'),
(27, 'Frioui', 'Salma', 'salma.frioui@student.macademia.edu', NULL, '$2y$10$student_hash_2', 'student'),
(28, 'Gharbi', 'Amine', 'amine.gharbi@student.macademia.edu', NULL, '$2y$10$student_hash_3', 'student'),
(29, 'Kchaou', 'Nour', 'nour.kchaou@student.macademia.edu', NULL, '$2y$10$student_hash_4', 'student'),
(60, 'vv', 'dsss', 'vvdsss33.macademia@gmail.com', NULL, '$2y$10$o5UAzPs0XXnzCRpudN/nU.rPATKHMIgn4h4N/2556Yo9OaEDpmdo6', 'student'),
(62, 'yukii', 'ez', 'faroukbenslimen2005@gmail.com', NULL, '$2y$10$Acg.a7m.5yiX4spqHVN3w.fMARlmk.x9D8dn13ZtCJTlgyVnbbdZW', 'student'),
(63, 'cyber', 'punk', 'cyberpunk65.macademia@gmail.com', NULL, '$2y$10$it.N1caz8vFGQUTfuGOPMeSO3IBMVKx3cTtrwg4cCs4iqHGifMCb.', 'student'),
(65, 'Doe', 'John', 'johndoe@student.com', NULL, '$2y$10$wQwQwQwQwQwQwQwQwQwQwOQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQw', 'student'),
(66, 'Doe', 'Jane', 'jane.doe@student.macademia.edu', NULL, '$2y$10$5jwTzn9KprmDJYQhydvTxuDs3qPL82OLyXgOmfnCnqowOMJx7Y38m', 'student'),
(67, 'toki', 'yukii', 'tokiyukii75.macademia@gmail.com', NULL, '$2y$10$IJuJr50WsqL1kNjxVCv5MuHSS/Rh9LpNQwv12rBsX0IM88RsHpd56', 'student'),
(72, 'ahmed', 'baghouli1', 'ahmed.baghouli@sesame.com.tn', NULL, '$2y$10$03s0pwCAdMS/CVTeX8njKOyySjzVttZ97vtE7McsZkUpjSnrLGew2', 'student'),
(74, 'Yves', 'Camara', 'yvescamara25.macademia@gmail.com', NULL, '$2y$10$hOayXaPVqw/Ly8jDFYyb0u3GMllX6DzgpGQ07aB6zQms/utqf4F9G', 'student'),
(75, 'kjhwqkjf', 'fnjkwqnkjf', 'kjhwqkjffnjkwqnkjf20.macademia@gmail.com', NULL, '$2y$10$GoEpbhTUmq/CUtPJL8bVF.fXqGwhMbA2XkznW3ppgawN8IenHUbdy', 'student'),
(78, 'zarbou', 'jaafer', 'zarboujaafer12.macademia@gmail.com', 'assets/uploads/profiles/profile_697e80b4764da_1769898164.jpg', '$2y$10$K04pZmTk7kzzzKLahW18zu5cUj5trmuisIjYE/Lasm/QXGqtfoVdq', 'student'),
(80, 'Mohamed', 'Ben ali', 'mohamedben ali53.macademia@gmail.com', NULL, '$2y$10$n5ny1e9moPMulnQ1bbULhO/3bia9R6u2onc5iYHTr4y0A/uJvnF3a', 'student'),
(81, 'Ze', 'moo', 'zemoo72.macademia@gmail.com', NULL, '$2y$10$yK4bPoYNCX9DvYWmzTPW6.1FfjWFgPTuoHh5AIcMpZOnTVPv0ITM2', 'student'),
(82, 'rania', 'abidi', 'raniaabidi40.macademia@gmail.com', NULL, '$2y$10$AnimWhsWm3drtuEB45qo6OSf5ahyNekWRYUyx8Ibh98wTV87S203a', 'student'),
(83, 'nader', 'belhadj', 'naderbelhadj39.macademia@gmail.com', NULL, '$2y$10$h02jlgSdbnS/tggBZL72M.ISx4gU6RRTByxmfpQJELrRwAdx0MxjS', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absence_thresholds`
--
ALTER TABLE `absence_thresholds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `account_requests`
--
ALTER TABLE `account_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_requests_photo` (`photo_path`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_name` (`class_name`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `module_classes`
--
ALTER TABLE `module_classes`
  ADD PRIMARY KEY (`module_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `module_schedule`
--
ALTER TABLE `module_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reminder_log`
--
ALTER TABLE `reminder_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_prof` (`student_id`,`professor_id`),
  ADD KEY `idx_date` (`sent_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_photo` (`photo_path`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absence_thresholds`
--
ALTER TABLE `absence_thresholds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_requests`
--
ALTER TABLE `account_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=546;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `module_schedule`
--
ALTER TABLE `module_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reminder_log`
--
ALTER TABLE `reminder_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absence_thresholds`
--
ALTER TABLE `absence_thresholds`
  ADD CONSTRAINT `absence_thresholds_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absence_thresholds_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `module_classes`
--
ALTER TABLE `module_classes`
  ADD CONSTRAINT `module_classes_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `module_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `module_schedule`
--
ALTER TABLE `module_schedule`
  ADD CONSTRAINT `module_schedule_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
