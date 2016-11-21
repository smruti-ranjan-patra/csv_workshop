-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 22, 2016 at 12:06 AM
-- Server version: 5.5.53-0ubuntu0.14.04.1
-- PHP Version: 5.6.28-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `csv_workshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `created_by`, `updated_by`) VALUES
(1, 'E2121', 'John', 'Smith', 1, 2),
(2, 'E3443', 'Manjeet', 'Singh', 2, 3),
(3, 'E1234', 'FrÃ©dÃ©ric', 'Abecassis', 2, 2),
(4, 'E5678', 'Peter', 'Kali', 2, 1),
(5, 'E2099', 'Don', 'O''Donnell', 2, 3),
(6, 'E4397', 'John', 'Cooper', 3, 1),
(7, 'E6789', 'Hailey', 'Bailey', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_skill`
--

CREATE TABLE IF NOT EXISTS `employee_skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_emp_id_skill_id` (`emp_id`,`skill_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=49 ;

--
-- Dumping data for table `employee_skill`
--

INSERT INTO `employee_skill` (`id`, `emp_id`, `skill_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 2, 1),
(9, 2, 2),
(7, 2, 6),
(8, 2, 7),
(10, 3, 4),
(11, 3, 7),
(12, 3, 8),
(13, 3, 9),
(14, 4, 10),
(15, 4, 11),
(16, 4, 12),
(17, 4, 13),
(19, 5, 12),
(18, 5, 13),
(21, 6, 5),
(20, 6, 14),
(22, 6, 15),
(23, 7, 16),
(24, 7, 17);

-- --------------------------------------------------------

--
-- Table structure for table `hr`
--

CREATE TABLE IF NOT EXISTS `hr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `hr`
--

INSERT INTO `hr` (`id`, `name`) VALUES
(1, 'AS'),
(2, 'MC'),
(3, 'SL');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE IF NOT EXISTS `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=35 ;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`) VALUES
(14, 'agile'),
(16, 'html5'),
(17, 'javascript'),
(5, 'jira'),
(4, 'jquery'),
(3, 'laravel'),
(9, 'memcached'),
(11, 'mongodb'),
(2, 'mysql'),
(8, 'nginx'),
(13, 'node.js'),
(10, 'nosql'),
(7, 'perl'),
(1, 'php'),
(6, 'python'),
(12, 'redis'),
(15, 'scrum');

-- --------------------------------------------------------

--
-- Table structure for table `stackoverflow`
--

CREATE TABLE IF NOT EXISTS `stackoverflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `stack_id` int(11) NOT NULL,
  `nick_name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stack_id` (`stack_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `stackoverflow`
--

INSERT INTO `stackoverflow` (`id`, `emp_id`, `stack_id`, `nick_name`) VALUES
(1, 1, 2795324, 'Tap'),
(2, 2, 187144, 'Ismael'),
(3, 3, 5406008, 'Storm'),
(4, 4, 3767564, 'Demon'),
(5, 5, 4465062, 'AÃ§Ä±kgÃ¶z'),
(6, 6, 1857053, 'Rich'),
(7, 7, 445686, 'Music');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `hr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `hr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stackoverflow`
--
ALTER TABLE `stackoverflow`
  ADD CONSTRAINT `stackoverflow_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
