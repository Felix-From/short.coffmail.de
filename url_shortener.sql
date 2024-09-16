-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 16. Sep 2024 um 09:09
-- Server-Version: 10.5.23-MariaDB-0+deb11u1
-- PHP-Version: 8.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `url_shortener`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rate_limits`
--

CREATE TABLE `rate_limits` (
  `ip_address` varchar(45) NOT NULL,
  `last_request` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `request_count` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `urls`
--

CREATE TABLE `urls` (
  `id` int(11) NOT NULL,
  `original_url` text NOT NULL,
  `short_code` varchar(10) NOT NULL,
  `count_used` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`ip_address`);

--
-- Indizes für die Tabelle `urls`
--
ALTER TABLE `urls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_short_code` (`short_code`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `urls`
--
ALTER TABLE `urls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
