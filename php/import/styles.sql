-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 14, 2014 at 01:34 AM
-- Server version: 5.5.32
-- PHP Version: 5.3.10-1ubuntu3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `UEssexCbeta6`
--

-- --------------------------------------------------------

--
-- Table structure for table `styles`
--

CREATE TABLE IF NOT EXISTS `styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `stroke` varchar(10) NOT NULL DEFAULT '',
  `stroke_width` float NOT NULL DEFAULT '-1',
  `stroke_opacity` float NOT NULL DEFAULT '-1',
  `stroke_dasharray` varchar(10) NOT NULL DEFAULT 'none',
  `fill` varchar(10) NOT NULL DEFAULT '',
  `fill_opacity` float NOT NULL DEFAULT '-1',
  `zindex` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `style_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `name`, `stroke`, `stroke_width`, `stroke_opacity`, `stroke_dasharray`, `fill`, `fill_opacity`, `zindex`) VALUES
(1, 'NoStyle', '', -1, -1, 'none', '', -1, 0),
(2, 'Stairs', '', -1, -1, '', '#51626F', -1, 0),
(3, 'Level5', '', -1, -1, '', '#622566', -1, 0),
(4, 'Lifts', '', -1, -1, '', '#007A87', -1, 0),
(5, 'ToiletMale', '', -1, -1, '', '#0065BD', -1, 0),
(6, 'Level5 A', '', -1, -1, '', '#4B306A', -1, 0),
(7, 'ToiletFemale  ', '', -1, -1, '', '#DA3D7E', -1, 0),
(8, 'Level4', '', -1, -1, '', '#ca9b4a', -1, 0),
(9, 'Level4 A', '', -1, -1, '', '#E98300', -1, 0),
(10, 'Disabled', '', -1, -1, '', '#bed600', -1, 0),
(11, 'Level3', '', -1, -1, '', '#AEAA6C', -1, 0),
(12, 'Level3 A', '', -1, -1, '', '#59A532', -1, 0),
(13, 'Level2', '', -1, -1, '', '#A6495F', -1, 0),
(14, 'Level2 A', '', -1, -1, '', '#A91263', -1, 0),
(15, 'GreenFillPerim', '#FFFFFF', 0.4, -1, '', '#59A532', -1, 0),
(16, 'Level1', '', -1, -1, '', '#72B5CC', -1, 0),
(17, 'Level1 A', '', -1, -1, '', '#9EC3DE', -1, 0),
(18, 'constable', '#D3BF96', -1, -1, '', '#C99B42', -1, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
