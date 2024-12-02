-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2024 at 01:27 AM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `turniej`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `mecze`
--

CREATE TABLE `mecze` (
  `id` int(11) NOT NULL,
  `runda` int(11) NOT NULL,
  `uczestnik1_id` int(11) NOT NULL,
  `uczestnik2_id` int(11) DEFAULT NULL,
  `zwyciezca_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mecze`
--

INSERT INTO `mecze` (`id`, `runda`, `uczestnik1_id`, `uczestnik2_id`, `zwyciezca_id`) VALUES
(16, 1, 19, 20, 19);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uczestnicy`
--

CREATE TABLE `uczestnicy` (
  `id` int(11) NOT NULL,
  `imie` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uczestnicy`
--

INSERT INTO `uczestnicy` (`id`, `imie`) VALUES
(19, 'r'),
(20, 'e');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','observer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '25e4ee4e9229397b6b17776bfceaf8e7', 'admin'),
(2, 'observer', '550c6a827744c0bfeebb0232f94d75d0', 'observer'),
(3, 'ja', 'a78c5bf69b40d464b954ef76815c6fa0', 'observer');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `mecze`
--
ALTER TABLE `mecze`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uczestnik1_id` (`uczestnik1_id`),
  ADD KEY `uczestnik2_id` (`uczestnik2_id`),
  ADD KEY `zwyciezca_id` (`zwyciezca_id`);

--
-- Indeksy dla tabeli `uczestnicy`
--
ALTER TABLE `uczestnicy`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mecze`
--
ALTER TABLE `mecze`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `uczestnicy`
--
ALTER TABLE `uczestnicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mecze`
--
ALTER TABLE `mecze`
  ADD CONSTRAINT `mecze_ibfk_1` FOREIGN KEY (`uczestnik1_id`) REFERENCES `uczestnicy` (`id`),
  ADD CONSTRAINT `mecze_ibfk_2` FOREIGN KEY (`uczestnik2_id`) REFERENCES `uczestnicy` (`id`),
  ADD CONSTRAINT `mecze_ibfk_3` FOREIGN KEY (`zwyciezca_id`) REFERENCES `uczestnicy` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
