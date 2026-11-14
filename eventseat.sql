-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 13, 2026 at 05:22 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eventseat`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(24) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `visitor_key` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `checkout_token` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `promo_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` int(10) UNSIGNED NOT NULL,
  `discount` int(10) UNSIGNED NOT NULL,
  `booking_fee` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_amount` int(10) UNSIGNED NOT NULL,
  `booking_status` varchar(20) NOT NULL DEFAULT 'confirmed',
  `user_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `reference`, `visitor_key`, `checkout_token`, `customer_id`, `session_id`, `promo_id`, `booking_date`, `subtotal`, `discount`, `booking_fee`, `total_amount`, `booking_status`, `user_id`) VALUES
(1, 'ES-189A42173B6D', 'b95ad9af1ac8e28b28951a7314c0c96ccc551d8de67cc5bf391513f8673b9edd', 'f14e11d53bdfd40e08b4f7446b9067b3', 1, 5, NULL, '2026-10-31 06:19:17', 18000, 0, 0, 18000, 'confirmed', NULL),
(2, 'ES-A9EB0FFBA31D', 'b95ad9af1ac8e28b28951a7314c0c96ccc551d8de67cc5bf391513f8673b9edd', '07ad86e175b49f6e2a9e2181ea2e119d', 3, 1, 1, '2026-10-31 09:03:07', 26000, 5200, 0, 20800, 'confirmed', NULL),
(4, 'ES-DA628D0C067A', 'bfd5288afd2e47058745be366475fb1c356c78e07147be1b411c44c66cf76cca', '3f05873a8c14137c268691bf8bc06e34', 7, 1, 6, '2026-11-13 03:15:08', 18000, 3600, 0, 14400, 'confirmed', 5);

-- --------------------------------------------------------

--
-- Table structure for table `booking_seats`
--

CREATE TABLE `booking_seats` (
  `booking_seat_id` int(11) UNSIGNED NOT NULL,
  `booking_id` int(11) UNSIGNED NOT NULL,
  `session_id` int(11) UNSIGNED NOT NULL,
  `seat_id` int(11) UNSIGNED NOT NULL,
  `ticket_price` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_seats`
--

INSERT INTO `booking_seats` (`booking_seat_id`, `booking_id`, `session_id`, `seat_id`, `ticket_price`) VALUES
(1, 1, 5, 33, 9000),
(2, 1, 5, 34, 9000),
(3, 2, 1, 9, 13000),
(4, 2, 1, 10, 13000),
(8, 4, 1, 1, 18000);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `register_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `full_name`, `email`, `phone`, `register_date`) VALUES
(1, 'Test Customer', 'bookingtest@example.com', '0123456789', '2026-10-31 06:19:17'),
(2, 'Dashboard Test', 'dashboardtest@example.com', '0123456789', '2026-10-31 07:33:44'),
(3, 'Demo Customer', 'demo@example.com', '0123456789', '2026-10-31 09:03:07'),
(7, 'Nor Hanina Binti Zainal Abidin', 'hanina@gmail.com', '0123456789', '2026-11-13 03:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `venue_id` int(10) UNSIGNED NOT NULL,
  `event_name` varchar(120) NOT NULL,
  `category` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `venue_id`, `event_name`, `category`, `description`, `image`, `status`) VALUES
(1, 1, 'Neon Nights', 'Music', 'A live concert with colourful visuals and energetic performances.', 'event-0.svg', 'active'),
(2, 2, 'Sunset Sessions', 'Music', 'An intimate evening of live music by the waterfront.', 'event-1.svg', 'active'),
(3, 3, 'Laugh Out Loud', 'Comedy', 'An evening of local stand-up comedy. Recommended for ages 18 and above.', 'event-2.svg', 'active'),
(4, 1, 'City Lights Live', 'Concert', 'An evening of live performances and stage visuals.', 'event-0.svg', 'active'),
(5, 1, 'Starlight Concert', 'Concert', 'An evening of live performances and stage visuals.', 'event-0.svg', 'active'),
(6, 1, 'Stories on Stage', 'Theatre', 'A theatre performance featuring local storytelling and drama.', 'event-1.svg', 'active'),
(7, 1, 'The Last Curtain', 'Theatre', 'A theatre performance featuring local storytelling and drama.', 'event-1.svg', 'active'),
(8, 1, 'Weekend Cinema', 'Movie', 'A reserved-seat movie screening for film lovers.', 'event-2.svg', 'active'),
(9, 1, 'Cinema Classics Night', 'Movie', 'A reserved-seat movie screening for film lovers.', 'event-2.svg', 'active'),
(10, 1, 'Creative Technology Forum', 'Seminar', 'A practical seminar about creativity and digital technology.', 'event-0.svg', 'active'),
(11, 1, 'Digital Ideas Summit', 'Seminar', 'A practical seminar about creativity and digital technology.', 'event-0.svg', 'active'),
(12, 1, 'Community Sports Festival', 'Sports', 'A seated spectator event featuring local sports demonstrations.', 'event-1.svg', 'active'),
(13, 1, 'Champions Showcase', 'Sports', 'A seated spectator event featuring local sports demonstrations.', 'event-1.svg', 'active'),
(14, 1, 'Acoustic Evenings', 'Music', 'A live acoustic music performance in an intimate venue.', 'event-2.svg', 'active'),
(15, 1, 'Sunset Melodies', 'Music', 'A live acoustic music performance in an intimate venue.', 'event-2.svg', 'active'),
(16, 1, 'Stand-up Spotlight', 'Comedy', 'An evening of stand-up performances and comedy.', 'event-0.svg', 'active'),
(17, 1, 'The Comedy Lounge', 'Comedy', 'An evening of stand-up performances and comedy.', 'event-0.svg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `event_sessions`
--

CREATE TABLE `event_sessions` (
  `session_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `session_date` date NOT NULL,
  `session_time` time NOT NULL,
  `session_status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_sessions`
--

INSERT INTO `event_sessions` (`session_id`, `event_id`, `session_date`, `session_time`, `session_status`) VALUES
(1, 1, '2026-12-12', '20:00:00', 'active'),
(2, 1, '2026-12-13', '20:00:00', 'active'),
(3, 2, '2026-12-18', '18:30:00', 'active'),
(4, 2, '2026-12-19', '18:30:00', 'active'),
(5, 3, '2026-12-20', '20:30:00', 'active'),
(6, 3, '2026-12-21', '20:30:00', 'active'),
(7, 4, '2026-12-13', '19:30:00', 'active'),
(8, 5, '2026-10-14', '19:30:00', 'active'),
(9, 6, '2026-12-16', '19:30:00', 'active'),
(10, 7, '2026-10-11', '19:30:00', 'active'),
(11, 8, '2026-12-19', '19:30:00', 'active'),
(12, 9, '2026-10-08', '19:30:00', 'active'),
(13, 10, '2026-12-22', '19:30:00', 'active'),
(14, 11, '2026-10-05', '19:30:00', 'active'),
(15, 12, '2026-12-25', '19:30:00', 'active'),
(16, 13, '2026-10-02', '19:30:00', 'active'),
(17, 14, '2026-12-28', '19:30:00', 'active'),
(18, 15, '2026-09-29', '19:30:00', 'active'),
(19, 16, '2026-12-31', '19:30:00', 'active'),
(20, 17, '2026-09-26', '19:30:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 1, '1b91346ac80897e4b608e4f2767726015e7515a762b65511b8b94bcc13dead97', '2026-11-04 00:58:31', NULL, '2026-11-03 16:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `promo_id` int(10) UNSIGNED NOT NULL,
  `promo_code` varchar(30) NOT NULL,
  `discount_percent` tinyint(3) UNSIGNED NOT NULL,
  `expiry_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`promo_id`, `promo_code`, `discount_percent`, `expiry_date`, `status`, `user_id`, `used_at`) VALUES
(1, 'EVENT20', 20, '2027-12-31', 'inactive', NULL, NULL),
(2, 'ES-3BDC925704FFD1CF', 20, '2027-02-02', 'active', 1, NULL),
(3, 'ES-52CC73BD410FC0BF', 20, '2027-02-02', 'active', 2, NULL),
(4, 'ES-3EB017497FA612B3', 20, '2027-02-02', 'active', 3, NULL),
(5, 'ES-3DB3A5F8AED2B20A', 20, '2027-02-02', 'active', 4, NULL),
(6, 'ES-C987D0B8791478A8', 20, '2027-02-11', 'active', 5, '2026-11-13 11:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `seat_id` int(10) UNSIGNED NOT NULL,
  `venue_id` int(10) UNSIGNED NOT NULL,
  `seat_number` varchar(5) NOT NULL,
  `category` varchar(20) NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`seat_id`, `venue_id`, `seat_number`, `category`, `price`, `status`) VALUES
(1, 1, 'A1', 'VIP', 18000, 'available'),
(2, 1, 'A2', 'VIP', 18000, 'available'),
(3, 1, 'A3', 'VIP', 18000, 'available'),
(4, 1, 'A4', 'VIP', 18000, 'available'),
(5, 1, 'B1', 'Premium', 15000, 'available'),
(6, 1, 'B2', 'Premium', 15000, 'available'),
(7, 1, 'B3', 'Premium', 15000, 'available'),
(8, 1, 'B4', 'Premium', 15000, 'available'),
(9, 1, 'C1', 'Standard', 13000, 'available'),
(10, 1, 'C2', 'Standard', 13000, 'available'),
(11, 1, 'C3', 'Standard', 13000, 'available'),
(12, 1, 'C4', 'Standard', 13000, 'unavailable'),
(13, 2, 'A1', 'VIP', 18000, 'available'),
(14, 2, 'A2', 'VIP', 18000, 'available'),
(15, 2, 'A3', 'VIP', 18000, 'available'),
(16, 2, 'A4', 'VIP', 18000, 'available'),
(17, 2, 'B1', 'Premium', 15000, 'available'),
(18, 2, 'B2', 'Premium', 15000, 'available'),
(19, 2, 'B3', 'Premium', 15000, 'available'),
(20, 2, 'B4', 'Premium', 15000, 'available'),
(21, 2, 'C1', 'Standard', 13000, 'available'),
(22, 2, 'C2', 'Standard', 13000, 'available'),
(23, 2, 'C3', 'Standard', 13000, 'available'),
(24, 2, 'C4', 'Standard', 13000, 'unavailable'),
(25, 3, 'A1', 'VIP', 14000, 'available'),
(26, 3, 'A2', 'VIP', 14000, 'available'),
(27, 3, 'A3', 'VIP', 14000, 'available'),
(28, 3, 'A4', 'VIP', 14000, 'available'),
(29, 3, 'B1', 'Premium', 11000, 'available'),
(30, 3, 'B2', 'Premium', 11000, 'available'),
(31, 3, 'B3', 'Premium', 11000, 'available'),
(32, 3, 'B4', 'Premium', 11000, 'available'),
(33, 3, 'C1', 'Standard', 9000, 'available'),
(34, 3, 'C2', 'Standard', 9000, 'available'),
(35, 3, 'C3', 'Standard', 9000, 'available'),
(36, 3, 'C4', 'Standard', 9000, 'unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('contact', 'support@eventseat.example'),
('intro', 'Discover live music and comedy. Choose your moment, pick your seat.'),
('promo_percent', '20'),
('title', 'Your next great night starts here.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `login_failures` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `auth_version` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `status`, `login_failures`, `locked_until`, `created_at`, `auth_version`) VALUES
(1, 'Yvonne Yeoh Yi Wen', 'p25017466@student.newinti.edu.my', '0127909851', '$2y$10$n9gX3i4fqD9ByxD0D4z6fuvEXL0.4W/GACrtFlMkj39DQXZ24w6pe', 'user', 'active', 0, NULL, '2026-11-03 16:21:41', 0),
(2, 'Leong Yu Qian', 'p25017141@student.newinti.edu.my', '01155055525', '$2y$10$GqaG1WKFmXD6Lyqf2o4QeOiblZEUAaHOwIQnxL.pEwgTLfy9PQlYy', 'admin', 'active', 0, NULL, '2026-11-03 16:24:02', 0),
(3, 'Ching Jia Xiyn', 'p25017274@student.newinti.edu.my', '0107710688', '$2y$10$cUnvq5jrslZCFmRw/j/GgO.eSG8CzqjlxqxL4zRbIm/n3GJyEiq9S', 'user', 'active', 0, NULL, '2026-11-03 16:31:44', 0),
(4, 'Tong Zhi Xuen', 'p25017142@student.newinti.edu.my', '0195915590', '$2y$10$00t2GPXCjaN9N8RnuonX7uPCGlTuMLBN2sInIWgyKvBpmGGs4u7Ny', 'user', 'active', 0, NULL, '2026-11-03 16:34:59', 0),
(5, 'Nor Hanina Binti Zainal Abidin', 'hanina@gmail.com', '0123456789', '$2y$10$Wwe8Q3P6Isd.qLOoRAftXOiYaye6kN3AD5sGLGxeudzb4q3LZs68O', 'user', 'active', 0, NULL, '2026-11-13 03:08:17', 0);

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `venue_id` int(10) UNSIGNED NOT NULL,
  `venue_name` varchar(120) NOT NULL,
  `location` varchar(150) NOT NULL,
  `capacity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`venue_id`, `venue_name`, `location`, `capacity`) VALUES
(1, 'The Grand Arena', 'Kuala Lumpur', 12),
(2, 'Harbor Pavilion', 'Penang', 12),
(3, 'City Comedy Club', 'Kuala Lumpur', 12);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD UNIQUE KEY `checkout_token` (`checkout_token`),
  ADD UNIQUE KEY `booking_id` (`booking_id`,`session_id`),
  ADD KEY `fk_bookings_session` (`session_id`),
  ADD KEY `fk_bookings_promo` (`promo_id`),
  ADD KEY `fk_bookings_customer` (`customer_id`),
  ADD KEY `fk_booking_user` (`user_id`);

--
-- Indexes for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD PRIMARY KEY (`booking_seat_id`),
  ADD UNIQUE KEY `session_id` (`session_id`,`seat_id`),
  ADD KEY `fk_booking_seats_booking` (`booking_id`,`session_id`),
  ADD KEY `fk_booking_seats_seat` (`seat_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_events_venue` (`venue_id`);

--
-- Indexes for table `event_sessions`
--
ALTER TABLE `event_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `fk_sessions_event` (`event_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `reset_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`),
  ADD KEY `fk_promo_user` (`user_id`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `venue_id` (`venue_id`,`seat_number`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `booking_seats`
--
ALTER TABLE `booking_seats`
  MODIFY `booking_seat_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `event_sessions`
--
ALTER TABLE `event_sessions`
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `promo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `venue_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_promo` FOREIGN KEY (`promo_id`) REFERENCES `promo_codes` (`promo_id`),
  ADD CONSTRAINT `fk_bookings_session` FOREIGN KEY (`session_id`) REFERENCES `event_sessions` (`session_id`);

--
-- Constraints for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD CONSTRAINT `fk_booking_seats_booking` FOREIGN KEY (`booking_id`,`session_id`) REFERENCES `bookings` (`booking_id`, `session_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_seats_seat` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`);

--
-- Constraints for table `event_sessions`
--
ALTER TABLE `event_sessions`
  ADD CONSTRAINT `fk_sessions_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD CONSTRAINT `fk_promo_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `fk_seats_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
