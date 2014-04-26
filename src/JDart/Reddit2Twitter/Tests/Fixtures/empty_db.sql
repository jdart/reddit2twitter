-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 25, 2014 at 08:18 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.11-1~dotdeb.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `reddit_twitter`
--

-- --------------------------------------------------------

--
-- Table structure for table `reddit_post`
--

DROP TABLE IF EXISTS `reddit_post`;
CREATE TABLE IF NOT EXISTS `reddit_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reddit_id` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `score` int(11) NOT NULL,
  `posted` tinyint(1) NOT NULL,
  `queued` tinyint(1) NOT NULL,
  `post_data` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=60 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
