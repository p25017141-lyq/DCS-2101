-- Import into a NEW empty database named eventseat.
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 30, 2026 at 05:54 PM
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
  `booking_status` varchar(20) NOT NULL DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 3, 'Laugh Out Loud', 'Comedy', 'An evening of local stand-up comedy. Recommended for ages 18 and above.', 'event-2.svg', 'active');

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
(6, 3, '2026-12-21', '20:30:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `promo_id` int(10) UNSIGNED NOT NULL,
  `promo_code` varchar(30) NOT NULL,
  `discount_percent` tinyint(3) UNSIGNED NOT NULL,
  `expiry_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`promo_id`, `promo_code`, `discount_percent`, `expiry_date`, `status`) VALUES
(1, 'EVENT20', 20, '2027-12-31', 'active');

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
  ADD KEY `fk_bookings_customer` (`customer_id`),
  ADD KEY `fk_bookings_session` (`session_id`),
  ADD KEY `fk_bookings_promo` (`promo_id`);

--
-- Indexes for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD PRIMARY KEY (`booking_seat_id`),
  ADD UNIQUE KEY `session_id` (`session_id`,`seat_id`),
  ADD KEY `fk_booking_seats_seat` (`seat_id`),
  ADD KEY `fk_booking_seats_booking` (`booking_id`,`session_id`);

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
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `venue_id` (`venue_id`,`seat_number`);

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
  MODIFY `booking_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_seats`
--
ALTER TABLE `booking_seats`
  MODIFY `booking_seat_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_sessions`
--
ALTER TABLE `event_sessions`
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `promo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `fk_bookings_promo` FOREIGN KEY (`promo_id`) REFERENCES `promo_codes` (`promo_id`),
  ADD CONSTRAINT `fk_bookings_session` FOREIGN KEY (`session_id`) REFERENCES `event_sessions` (`session_id`);

--
-- Constraints for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD CONSTRAINT `fk_booking_seats_booking` FOREIGN KEY (`booking_id`,`session_id`) REFERENCES `bookings` (`booking_id`, `session_id`),
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
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `fk_seats_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Back up eventseat first. Import ONCE into the existing eventseat database.
CREATE TABLE users (
 user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(80) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE,
 phone VARCHAR(40) NOT NULL, password_hash VARCHAR(255) NOT NULL,
 role ENUM('user','admin') NOT NULL DEFAULT 'user',
 status ENUM('active','blocked') NOT NULL DEFAULT 'active',
 login_failures INT NOT NULL DEFAULT 0, locked_until DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE bookings ADD user_id INT UNSIGNED NULL, ADD CONSTRAINT fk_booking_user FOREIGN KEY(user_id) REFERENCES users(user_id);
ALTER TABLE promo_codes ADD user_id INT UNSIGNED NULL, ADD used_at DATETIME NULL,
 ADD CONSTRAINT fk_promo_user FOREIGN KEY(user_id) REFERENCES users(user_id);
UPDATE promo_codes SET status='inactive' WHERE user_id IS NULL;
CREATE TABLE site_settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO site_settings VALUES ('title','Your next great night starts here.'),('intro','Discover live music and comedy. Choose your moment, pick your seat.'),('contact','support@eventseat.example'),('promo_percent','20');

-- Repair AUTO_INCREMENT while preserving the existing data and restoring foreign keys.
-- Run while the local website is not being used. Back up eventseat first.
ALTER TABLE events DROP FOREIGN KEY fk_events_venue;
ALTER TABLE seats DROP FOREIGN KEY fk_seats_venue;
ALTER TABLE booking_seats DROP FOREIGN KEY fk_booking_seats_seat;

ALTER TABLE venues MODIFY venue_id INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE seats MODIFY seat_id INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE events ADD CONSTRAINT fk_events_venue FOREIGN KEY (venue_id) REFERENCES venues (venue_id);
ALTER TABLE seats ADD CONSTRAINT fk_seats_venue FOREIGN KEY (venue_id) REFERENCES venues (venue_id);
ALTER TABLE booking_seats ADD CONSTRAINT fk_booking_seats_seat FOREIGN KEY (seat_id) REFERENCES seats (seat_id);

-- Back up eventseat. Import ONCE into your upgraded database.
ALTER TABLE users ADD auth_version INT UNSIGNED NOT NULL DEFAULT 0;
CREATE TABLE password_resets (
 reset_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX reset_user_created(user_id,created_at),
 CONSTRAINT fk_password_reset_user FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
