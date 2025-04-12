-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 08:03 PM
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
-- Database: `ems`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `adminID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','super_admin') NOT NULL DEFAULT 'admin',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ui_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ui_preferences`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`adminID`, `username`, `password`, `name`, `email`, `role`, `status`, `last_login`, `created_at`, `ui_preferences`) VALUES
(1, 'admin', '$2y$10$8gKZT7k1ssMQQy2x9dM0Lezlbdg/tcQ0GgCoYh0aPFB1IQDTQMyfe', 'System Administrator', 'admin@example.com', 'super_admin', 'Active', NULL, '2025-04-07 00:43:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `activity` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `adminID`, `activity`, `ip_address`, `timestamp`) VALUES
(1, 1, 'System login', '127.0.0.1', '2025-04-09 22:37:15');

-- --------------------------------------------------------

--
-- Table structure for table `ballot_designs`
--

CREATE TABLE `ballot_designs` (
  `designID` int(11) NOT NULL,
  `electionID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `style` varchar(50) DEFAULT 'modern',
  `show_logo` tinyint(1) DEFAULT 1,
  `show_header` tinyint(1) DEFAULT 1,
  `show_footer` tinyint(1) DEFAULT 1,
  `logo_position` enum('left','center','right') DEFAULT 'center',
  `header_color` varchar(20) DEFAULT '#4361ee',
  `font_family` varchar(50) DEFAULT 'Poppins',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ballot_designs`
--

INSERT INTO `ballot_designs` (`designID`, `electionID`, `title`, `description`, `style`, `show_logo`, `show_header`, `show_footer`, `logo_position`, `header_color`, `font_family`, `created_at`) VALUES
(1, 1, '', '', 'modern', 1, 1, 1, '', '#4361ee', 'Poppins', '2025-04-08 10:29:37');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidateID` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
  `manifesto` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `positionID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidateID`, `studentID`, `manifesto`, `photo`, `status`, `positionID`) VALUES
(2372719, 1231231, 'i am zuckerberg', '67f69401ac970.jpg', 'Approved', 10),
(2372720, 10928371, 'i will introduced new cards', '67f6948d0d2b8.jpg', 'Approved', 9),
(2372721, 109911311, 'New Infrastructure', '67f694f6607ad.jpg', 'Approved', 11),
(2372723, 10945821, 'kkjk', '67f6a01b50d83.jpeg', 'Approved', 9),
(2372724, 19218182, 'Automobile', '67f6a0310912f.jpg', 'Approved', 8),
(2372726, 2147483647, 'Technology', '67f6a109b0dee.jpeg', 'Approved', 8),
(2372735, 121312131, 'Test manifesto for position Vice President', NULL, 'Approved', 10);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryID` int(11) NOT NULL,
  `electionID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `addedBy` int(11) DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryID`, `electionID`, `name`, `addedBy`, `updatedBy`, `createdAt`, `updatedAt`) VALUES
(2, 1, 'Test Fixed Category 2025-04-09 23:37:01', 19218182, NULL, '2025-04-09 21:37:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `electionID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `startDate` date NOT NULL,
  `start_time` time DEFAULT '08:00:00',
  `endDate` date NOT NULL,
  `end_time` time DEFAULT '17:00:00',
  `status` enum('Scheduled','Ongoing','Completed') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`electionID`, `name`, `startDate`, `start_time`, `endDate`, `end_time`, `status`) VALUES
(1, 'Student Council Election', '2025-04-11', '08:00:00', '2025-05-01', '17:00:00', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `election_participation`
--

CREATE TABLE `election_participation` (
  `id` int(11) NOT NULL,
  `electionID` int(11) DEFAULT NULL,
  `studentID` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_participation`
--

INSERT INTO `election_participation` (`id`, `electionID`, `studentID`, `timestamp`) VALUES
(1, 1, 10928371, '2025-04-09 20:50:18'),
(2, 1, 109911311, '2025-04-09 20:53:06'),
(3, 1, 1231231, '2025-04-09 20:56:00'),
(4, 1, 19218182, '2025-04-09 21:53:29'),
(5, 1, 121312131, '2025-04-11 09:38:24');

-- --------------------------------------------------------

--
-- Stand-in structure for view `election_votes`
-- (See below for the actual view)
--
CREATE TABLE `election_votes` (
`voteID` int(11)
,`electionID` int(11)
,`candidateID` int(11)
,`studentID` int(11)
,`timestamp` datetime
,`status` enum('pending','verified','rejected')
,`ip_address` varchar(45)
,`election_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Can be studentID or admin ID',
  `user_type` enum('student','admin') NOT NULL DEFAULT 'student',
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('election','vote','result','system','reminder','candidate') NOT NULL DEFAULT 'system',
  `related_election` int(11) DEFAULT NULL,
  `related_candidate` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `user_type`, `title`, `message`, `type`, `related_election`, `related_candidate`, `is_read`, `created_at`) VALUES
(4, 1231231, 'student', 'Vote Confirmation', 'Your vote has been recorded', 'vote', 1, NULL, 1, '2025-04-05 01:40:59'),
(6, 10928371, 'student', 'System Alert', 'Complete your profile information', 'system', NULL, NULL, 1, '2025-04-05 01:40:59'),
(28, 10928371, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 1, '2025-04-06 22:10:01'),
(29, 10928371, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 1, '2025-04-06 22:11:51'),
(32, 10928371, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 1, '2025-04-07 14:53:55'),
(34, 10928371, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 1, '2025-04-09 20:50:18'),
(35, 109911311, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 0, '2025-04-09 20:53:06'),
(36, 1231231, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 1, '2025-04-09 20:56:00'),
(37, 19218182, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 0, '2025-04-09 21:53:29'),
(38, 121312131, 'student', 'Vote Submitted', 'Thank you for voting in the Student Council Election election', 'vote', 1, NULL, 1, '2025-04-11 09:38:24');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`) VALUES
(1, 'bmfhpwww@sharklasers.com', '75829dd9f5c4e7572633c81e4b9e1a4a944de9d0e747f1b0faa10ccecab8561f', '2025-04-05 04:20:13', 0),
(2, 'davidayim01@gmail.com', '8f832eb81198fc1182e662568841e2f1d1a6457cd6a093dc90f2bde3c5c618af', '2025-04-05 04:26:46', 0),
(3, 'simonsetor561@outlook.com', 'b79d191ef32fa7c496785a4f4e78e4955d5b28ed826c420b1920ac29b4ec70a2', '2025-04-05 18:18:24', 0),
(4, 'simonsetor561@outlook.com', '704924dd502fc999e5a44769f492bf3f213a1d174005953b49df351c93400bb4', '2025-04-05 21:47:24', 0),
(5, 'alberteinstern@outlook.com', '41102b15aef7ac5ece66746a7b4cbd85cb6fd281951e8f8e74d73b199cfeaace', '2025-04-11 02:43:46', 0),
(6, 'alberteinstern@outlook.com', '30c7000b3acb3d114e34cc78cb3bc80344300c1b3dad73876e9771a673e9a1b4', '2025-04-11 02:47:33', 0),
(7, 'alberteinstern@outlook.com', 'b306b19e17ae4f51b9bfb2b29995f51455d42ce9458f6558c240da753f767abf', '2025-04-11 02:47:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `positionID` int(11) NOT NULL,
  `electionID` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `maxVotes` int(11) DEFAULT 1,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`positionID`, `electionID`, `title`, `description`, `maxVotes`, `display_order`) VALUES
(8, 1, 'Secretary', 'New Secretary', 1, 3),
(9, 1, 'SRC President', 'New President', 1, 2),
(10, 1, 'Vice President', 'New Vice President', 1, 1),
(11, 1, 'treasurer', 'better management', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `resultID` int(11) NOT NULL,
  `electionID` int(11) DEFAULT NULL,
  `candidateID` int(11) DEFAULT NULL,
  `voteCount` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`resultID`, `electionID`, `candidateID`, `voteCount`, `percentage`) VALUES
(323, 1, 2372719, 2, 100.00),
(324, 1, 2372720, 3, 100.00),
(325, 1, 2372721, 0, 0.00),
(326, 1, 2372723, 0, 0.00),
(327, 1, 2372724, 0, 0.00),
(328, 1, 2372726, 0, 0.00),
(329, 1, 2372735, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `studentID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` char(60) NOT NULL,
  `dateOfBirth` date NOT NULL,
  `department` varchar(100) NOT NULL,
  `contactNumber` varchar(15) DEFAULT NULL,
  `registrationDate` date NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('student','admin') DEFAULT 'student',
  `profilePicture` varchar(255) DEFAULT NULL,
  `two_factor_secret` varchar(16) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`studentID`, `name`, `email`, `password`, `dateOfBirth`, `department`, `contactNumber`, `registrationDate`, `status`, `created_at`, `role`, `profilePicture`, `two_factor_secret`, `two_factor_enabled`) VALUES
(1231231, 'Mark Zuckerberg', 'bmfhpwww@sharklasers.com', '$2y$10$6qE8mXZGvLdFnC0YlY0rLeoFOB1g95BtD4J3BHi8wwYgm3RP1Zj3e', '2000-01-02', 'Computer Science', '233551784926', '2025-04-04', 'Active', '2025-04-04 12:42:19', 'student', '1231231_1743771186.jpg', NULL, 0),
(10928371, 'Simon Setor AB', 'simonsetor561@outlook.com', '$2y$10$gOemaLmRIcVSjwLd2DHTkOOcJndd71HZK58OY1Y2h9CHT6XZN3Loa', '2000-01-02', 'Computer Science', '233551784926', '2025-04-05', 'Active', '2025-04-05 15:16:55', 'student', '10928371_1743868570.jpg', 'ZQU7HY2HFQYCQPU5', 0),
(10945821, 'Aristocratjnr', 'david.obuobi@inkris.ca', '$2y$10$PaQkW9.LAKdG5atPFSosZuPBivPtBZKwl9.ZLJz1p1WAxyqGIzPGq', '2002-09-23', 'Administrator', '0551784926', '2025-04-02', 'Active', '2025-04-02 14:03:42', 'admin', '10945821_1744243647.jpeg', 'EO7CYYN3Q5TZKHUP', 1),
(19218182, 'Aristocrat', 'afa@gmail.ocm', '$2y$10$zIEtnqNq5BWQ5H/agLmRqul93m8fa6GUu/TOncAzqdd0rwzpEPdgm', '2000-01-02', 'Computer Science', '23355184926', '2025-04-08', 'Active', '2025-04-08 19:13:43', 'student', '19218182_1744140047.jpg', NULL, 0),
(109911311, 'Testing', 'daobuobi006@st.ug.edu.gh', '$2y$10$be5XjjUTu7RdH4zFiWI3VeMhMJBB7XIUGnaYcn5ThuET0RaV2wE8O', '2000-01-02', 'Computer Science', '233551784926', '2025-04-05', 'Active', '2025-04-05 18:35:06', 'student', '109911311_1744211665.png', NULL, 0),
(121312131, 'Albert Einstern', 'alberteinstern@outlook.com', '$2y$10$..zgZpk4iWwWbEXdhZmnc.8FyTE2hYN/yIVEP0G4qtWvPZqQE/GHy', '2000-01-02', 'Computer Science', '233551784926', '2025-04-10', 'Active', '2025-04-10 23:37:31', 'student', NULL, NULL, 0),
(2147483647, 'Junior', 'ayimobuob44i@gmail.com', '$2y$10$U7FZFpsgHaYtM7RBUPEQfuvzalTJC2W18OtEJP1wojsPLksB4Mknu', '2000-01-02', 'Chemistry', '233551784926', '2025-04-01', 'Active', '2025-04-01 15:50:04', 'student', '2147483647_1744221192.jpg', 'LM2DSMWNBNXG3H2Z', 0);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL DEFAULT 'Election System',
  `admin_email` varchar(255) DEFAULT NULL,
  `max_candidates` int(11) NOT NULL DEFAULT 10,
  `default_positions` int(11) NOT NULL DEFAULT 5,
  `results_public` enum('after_end','while_active','admin_only') NOT NULL DEFAULT 'after_end',
  `voter_registration` enum('enabled','admin_only','disabled') NOT NULL DEFAULT 'enabled',
  `maintenance_mode` enum('enabled','disabled') NOT NULL DEFAULT 'disabled',
  `email_notifications` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `pagination_limit` int(11) NOT NULL DEFAULT 20,
  `date_format` varchar(20) NOT NULL DEFAULT 'd-m-Y',
  `time_format` varchar(20) NOT NULL DEFAULT 'H:i',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `site_name`, `admin_email`, `max_candidates`, `default_positions`, `results_public`, `voter_registration`, `maintenance_mode`, `email_notifications`, `pagination_limit`, `date_format`, `time_format`, `last_updated`) VALUES
(1, 'Election System', '', 10, 5, 'after_end', 'enabled', 'disabled', 'enabled', 20, 'd-m-Y', 'H:i', '2025-04-10 12:46:51');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `voteID` int(11) NOT NULL,
  `electionID` int(11) DEFAULT NULL,
  `candidateID` int(11) DEFAULT NULL,
  `studentID` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`voteID`, `electionID`, `candidateID`, `studentID`, `timestamp`, `status`, `ip_address`) VALUES
(77, 1, 2372719, 10928371, '2025-04-09 20:50:18', 'pending', NULL),
(78, 1, 2372720, 109911311, '2025-04-09 20:53:06', 'pending', NULL),
(79, 1, 2372720, 1231231, '2025-04-09 20:56:00', 'pending', NULL),
(80, 1, 2372720, 19218182, '2025-04-09 21:53:29', 'pending', NULL),
(81, 1, 2372719, 121312131, '2025-04-11 09:38:24', 'pending', NULL);

-- --------------------------------------------------------

--
-- Structure for view `election_votes`
--
DROP TABLE IF EXISTS `election_votes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `election_votes`  AS SELECT `v`.`voteID` AS `voteID`, `v`.`electionID` AS `electionID`, `v`.`candidateID` AS `candidateID`, `v`.`studentID` AS `studentID`, `v`.`timestamp` AS `timestamp`, `v`.`status` AS `status`, `v`.`ip_address` AS `ip_address`, `e`.`name` AS `election_name` FROM (`votes` `v` join `elections` `e` on(`v`.`electionID` = `e`.`electionID`)) WHERE `v`.`timestamp` >= `e`.`startDate` AND `v`.`timestamp` <= `e`.`endDate` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `ballot_designs`
--
ALTER TABLE `ballot_designs`
  ADD PRIMARY KEY (`designID`),
  ADD KEY `electionID` (`electionID`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidateID`),
  ADD KEY `idx_positionid` (`positionID`),
  ADD KEY `idx_studentid` (`studentID`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryID`),
  ADD KEY `electionID` (`electionID`),
  ADD KEY `addedBy` (`addedBy`),
  ADD KEY `updatedBy` (`updatedBy`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`electionID`);

--
-- Indexes for table `election_participation`
--
ALTER TABLE `election_participation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `electionID` (`electionID`,`studentID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `related_election` (`related_election`),
  ADD KEY `related_candidate` (`related_candidate`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`positionID`),
  ADD KEY `idx_electionid` (`electionID`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`resultID`),
  ADD KEY `electionID` (`electionID`),
  ADD KEY `candidateID` (`candidateID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`studentID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`voteID`),
  ADD UNIQUE KEY `electionID` (`electionID`,`studentID`),
  ADD KEY `candidateID` (`candidateID`),
  ADD KEY `studentID` (`studentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ballot_designs`
--
ALTER TABLE `ballot_designs`
  MODIFY `designID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2372739;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `electionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `election_participation`
--
ALTER TABLE `election_participation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `positionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `resultID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483648;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `voteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `admins` (`adminID`) ON DELETE CASCADE;

--
-- Constraints for table `ballot_designs`
--
ALTER TABLE `ballot_designs`
  ADD CONSTRAINT `ballot_designs_ibfk_1` FOREIGN KEY (`electionID`) REFERENCES `elections` (`electionID`);

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `students` (`studentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidates_position` FOREIGN KEY (`positionID`) REFERENCES `positions` (`positionID`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`electionID`) REFERENCES `elections` (`electionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`addedBy`) REFERENCES `students` (`studentID`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_ibfk_3` FOREIGN KEY (`updatedBy`) REFERENCES `students` (`studentID`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`related_election`) REFERENCES `elections` (`electionID`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_candidate`) REFERENCES `candidates` (`candidateID`) ON DELETE SET NULL;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`electionID`) REFERENCES `elections` (`electionID`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`electionID`) REFERENCES `elections` (`electionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`candidateID`) REFERENCES `candidates` (`candidateID`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`electionID`) REFERENCES `elections` (`electionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidateID`) REFERENCES `candidates` (`candidateID`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`studentID`) REFERENCES `students` (`studentID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
