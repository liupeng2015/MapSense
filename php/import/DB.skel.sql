-- MySQL dump 10.13  Distrib 5.5.34, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: UEssexS
-- ------------------------------------------------------
-- Server version	5.5.34-0ubuntu0.12.04.1
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aliases`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `qr_id` smallint(6) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`,`lid`),
  KEY `fk_orig_loc` (`lid`),
  CONSTRAINT `fk_orig_loc` FOREIGN KEY (`lid`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` smallint(6) DEFAULT '0',
  `image_name` varchar(255) DEFAULT '',
  `stid` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_pid` (`pid`),
  KEY `fk_cstyle` (`stid`),
  CONSTRAINT `fk_cstyle` FOREIGN KEY (`stid`) REFERENCES `styles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `catmap`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `colour` varchar(7) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`keyword`),
  KEY `fk_orig_loc` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edge_points`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edge_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eid` int(11) NOT NULL,
  `lat` double NOT NULL DEFAULT '0',
  `lon` double NOT NULL DEFAULT '0',
  `alt` double NOT NULL DEFAULT '0',
  `bf` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_edge` (`eid`),
  KEY `fk_epbf` (`bf`),
  CONSTRAINT `fk_edge` FOREIGN KEY (`eid`) REFERENCES `edges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_epbf` FOREIGN KEY (`bf`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edges`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `inclination` int(11) NOT NULL DEFAULT '0',
  `stairs` char(1) NOT NULL DEFAULT 'N',
  `length` double NOT NULL DEFAULT '0',
  `type` smallint(6) DEFAULT '0',
  `status` tinyint(4) DEFAULT '0',
  `status_msg` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_es` (`start`),
  KEY `fk_ee` (`end`),
  CONSTRAINT `fk_ee` FOREIGN KEY (`end`) REFERENCES `vertex` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_es` FOREIGN KEY (`start`) REFERENCES `vertex` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `floors`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `floors` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `room` varchar(20) DEFAULT '',
  `floor` varchar(20) NOT NULL DEFAULT '1',
  `building` varchar(50) NOT NULL DEFAULT '0',
  `tiles` varchar(255) NOT NULL DEFAULT '',
  `width` int(11) NOT NULL DEFAULT '-1',
  `height` int(11) NOT NULL DEFAULT '-1',
  `mode` smallint(6) NOT NULL DEFAULT '2' COMMENT '1:GPS / 2:PiXeLs',
  `maxzoom` smallint(6) NOT NULL DEFAULT '3',
  `minzoom` smallint(6) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '1',
  `tile_type` char(4) NOT NULL DEFAULT 'png',
  `ppm` double NOT NULL DEFAULT '1',
  `oid` int(11) NOT NULL DEFAULT '1',
  `orig_lat_r` double DEFAULT '0',
  `orig_lon_r` double DEFAULT '0',
  `orig_x` int(11) DEFAULT '-1',
  `orig_y` int(11) DEFAULT '-1',
  `rotation_r` double DEFAULT '0',
  `ppd_x` double DEFAULT '0',
  `ppd_y` double DEFAULT '0',
  `lat_scale_factor` double DEFAULT '0',
  `lat_offset_factor` double DEFAULT '0',
  `lon_scale_factor` double DEFAULT '0',
  `lon_offset_factor` double DEFAULT '0',
  `upm` double NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bf_unique` (`room`,`floor`,`building`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `layer_points`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layer_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `lat` double NOT NULL DEFAULT '0',
  `lon` double NOT NULL DEFAULT '0',
  `alt` double NOT NULL DEFAULT '0',
  `bf` smallint(6) NOT NULL,
  `lp_image` varchar(250) DEFAULT '',
  `notes` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_layer` (`lid`),
  KEY `fk_lpbf` (`bf`),
  CONSTRAINT `fk_layer` FOREIGN KEY (`lid`) REFERENCES `layers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lpbf` FOREIGN KEY (`bf`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `layers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `image_name` varchar(250) NOT NULL DEFAULT '',
  `description` varchar(250) NOT NULL DEFAULT '',
  `type` smallint(8) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `lat` double NOT NULL DEFAULT '0',
  `lon` double NOT NULL DEFAULT '0',
  `alt` double NOT NULL DEFAULT '0',
  `bf` smallint(6) NOT NULL,
  `description` varchar(255) NOT NULL,
  `entr` varchar(10) NOT NULL DEFAULT '',
  `cid` int(11) NOT NULL,
  `polygon` varchar(5000) DEFAULT NULL,
  `qr_id` smallint(6) unsigned zerofill DEFAULT NULL,
  `meta` varchar(5000) DEFAULT '',
  `meta_ts` datetime DEFAULT NULL,
  `client_id` varchar(250) DEFAULT '',
  `stid` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `loc_unique` (`name`,`bf`),
  KEY `fk_cat` (`cid`),
  KEY `fk_lbf` (`bf`),
  KEY `fk_ltype` (`stid`),
  CONSTRAINT `fk_ltype` FOREIGN KEY (`stid`) REFERENCES `styles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cat` FOREIGN KEY (`cid`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lbf` FOREIGN KEY (`bf`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yah_image` varchar(250) NOT NULL DEFAULT 'start_marker.png',
  `indoor_pin` varchar(250) NOT NULL DEFAULT 'Obsolete',
  `initial_node_id` int(11) NOT NULL DEFAULT '0',
  `path_thickness` int(11) NOT NULL DEFAULT '4',
  `path_stroke` varchar(5) NOT NULL DEFAULT '.',
  `path_color` varchar(7) NOT NULL DEFAULT '#2ef707',
  `inout_scale_factor` double NOT NULL DEFAULT '1',
  `default_bf` smallint(6) NOT NULL DEFAULT '1',
  `site_type` varchar(255) DEFAULT 'Campus',
  `pstyle` varchar(255) DEFAULT '83AEDD;E9DAAA;0.1',
  `prev_pin` varchar(250) NOT NULL DEFAULT 'previous_marker.png',
  `next_pin` varchar(250) NOT NULL DEFAULT 'next_marker.png',
  `search_pin` varchar(250) NOT NULL DEFAULT 'blank_marker.png',
  `start_pin` varchar(250) NOT NULL DEFAULT 'start_marker.png',
  `end_pin` varchar(250) NOT NULL DEFAULT 'finish_marker.png',
  `broker_url` varchar(500) DEFAULT 'http://127.0.0.1/RL3/Services/broker/broker.php?',
  `meta_to` smallint(6) DEFAULT '600',
  `outpstyle` varchar(255) NOT NULL DEFAULT 'BF101C;none;0.5',
  `outpath_color` varchar(7) NOT NULL DEFAULT '#000000',
  `auth_name` varchar(255) NOT NULL DEFAULT 'Estates',
  `auth_email` varchar(255) NOT NULL DEFAULT 'estates@site.com',
  `dbver` varchar(20) NOT NULL DEFAULT '0.0',
  `font_family` varchar(255) NOT NULL DEFAULT 'Arial',
  `tiles_out` varchar(25) DEFAULT '',
  `tiles_in` varchar(25) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `set_def_bf` (`default_bf`),
  CONSTRAINT `set_def_bf` FOREIGN KEY (`default_bf`) REFERENCES `floors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `styles`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `styles` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `svg_info`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `svg_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bf` smallint(6) NOT NULL,
  `type` varchar(3) NOT NULL DEFAULT '',
  `lid` int(11) NOT NULL,
  `index` int(4) NOT NULL DEFAULT '0',
  `transform` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(1000) NOT NULL DEFAULT '',
  `fontsize` float NOT NULL DEFAULT '14',
  `color` varchar(7) NOT NULL DEFAULT '',
  `fill` varchar(7) NOT NULL DEFAULT '',
  `stroke` float NOT NULL DEFAULT '1',
  `points` varchar(15000) NOT NULL DEFAULT '',
  `txt` mediumtext,
  PRIMARY KEY (`id`),
  KEY `fk_sbf` (`bf`),
  KEY `ilid` (`lid`),
  KEY `itype` (`type`),
  CONSTRAINT `fk_sbf` FOREIGN KEY (`bf`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tiler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bf` smallint(6) NOT NULL,
  `target` char(1) NOT NULL,
  `status` varchar(512) NOT NULL DEFAULT '',
  `lock` tinyint(1) NOT NULL DEFAULT '0',
  `tiler_ver` varchar(100) NOT NULL DEFAULT '0.1',
  `ver` int(11) NOT NULL DEFAULT '1',
  `host` varchar(100) NOT NULL DEFAULT '',
  `screen_id` varchar(100) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bf_target_UNIQUE` (`bf`,`target`),
  CONSTRAINT `fk_t_bf` FOREIGN KEY (`bf`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tour_cat`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `image_name` varchar(255) DEFAULT '',
  `type` smallint(6) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tour_poi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_poi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL,
  `lid` int(11) NOT NULL,
  `order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`lid`,`tid`),
  KEY `fk_tid` (`tid`),
  KEY `fk_lid` (`lid`),
  CONSTRAINT `fk_lid` FOREIGN KEY (`lid`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tid` FOREIGN KEY (`tid`) REFERENCES `tours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tours`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT '0',
  `description` varchar(2000) DEFAULT '',
  `tcid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `fk_tcat` (`tcid`),
  CONSTRAINT `fk_tcat` FOREIGN KEY (`tcid`) REFERENCES `tour_cat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vertex`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vertex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lat` double NOT NULL DEFAULT '0',
  `lon` double NOT NULL DEFAULT '0',
  `alt` double NOT NULL DEFAULT '0',
  `assoc_type` varchar(5) NOT NULL DEFAULT '' COMMENT 'l: location\ne: entrance\n'''': vertex',
  `assoc_id` int(11) DEFAULT NULL,
  `link` varchar(10) NOT NULL,
  `bf` smallint(6) NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `status_msg` varchar(255) DEFAULT '',
  `path_info` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_vbf` (`bf`),
  CONSTRAINT `fk_vbf` FOREIGN KEY (`bf`) REFERENCES `floors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

--
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`id`, `name`, `stroke`, `stroke_width`, `stroke_opacity`, `stroke_dasharray`, `fill`, `fill_opacity`, `zindex`) VALUES
(1, 'NoStyle', '', -1, -1, 'none', '', -1, 0),
(2, 'TestStyle', '#1929FF', 0.1, 1, '0.5, 0.5', 'none', 0, 0),
(8, 'TestStyle2', '#2B24FF', 0.08, 0.4, '1,1', '#5EFF69', 0.4, 0),
(13, 'Cat: White Corridor', '', -1, -1, '', '#FFFFFF', -1, 0),
(14, 'Cat: Meeting Rooms', '', -1, -1, '', '#0065BD', -1, 0),
(18, 'Cat: Cornflower', '', -1, -1, '', '#0065BD', -1, 0),
(19, 'Cat: Powder Blue', '', -1, -1, '', '#9EC3DE', -1, 0),
(20, 'Cat: Turquoise', '', -1, -1, '', '#00AFD8', -1, 0),
(21, 'Cat: Royal Blue', '', -1, -1, '', '#003766', -1, 0),
(22, 'Cat: Dark Purple', '', -1, -1, '', '#4B306A', -1, 0),
(23, 'Cat: Violet', '', -1, -1, '', '#622567', -1, 0),
(24, 'Cat: Bluebell', '', -1, -1, '', '#4c5cc5', -1, 0),
(25, 'Cat: Seagrass', '', -1, -1, '', '#007a87', -1, 0),
(26, 'Cat: Mint', '', -1, -1, '', '#35c4b5', -1, 0),
(27, 'Cat: Aqua', '', -1, -1, '', '#72b5cc', -1, 0),
(28, 'Cat: Sage', '', -1, -1, '', '#BAC696', -1, 0),
(29, 'Cat: Lime Green', '', -1, -1, '', '#BED600', -1, 0),
(31, 'Cat: Apple', '', -1, -1, '', '#58a618', -1, 0),
(32, 'Cat: Leaf', '', -1, -1, '', '#008542', -1, 0),
(33, 'Cat: Forest', '', -1, -1, '', '#275e37', -1, 0),
(34, 'Cat: Yellow', '', -1, -1, '', '#F3D311', -1, 0),
(35, 'Cat: Gold', '', -1, -1, '', '#F2AF00', -1, 0),
(36, 'Cat: Sand', '', -1, -1, '', '#CA9B4A', -1, 0),
(37, 'Cat: Light Orange', '', -1, -1, '', '#e98300', -1, 0),
(38, 'Cat: Dark Orange', '', -1, -1, '', '#D55C19', -1, 0),
(39, 'Cat: Soft Red', '', -1, -1, '', '#A8475A', -1, 0),
(40, 'Cat: Bright Pink', '', -1, -1, '', '#DA3D7E', -1, 0),
(42, 'Cat: Crimson', '', -1, -1, '', '#6e273d', -1, 0),
(43, 'Cat: Scarlet', '', -1, -1, '', '#CD202C', -1, 0),
(44, 'Cat: Light Grey', '', -1, -1, '', '#c6c6bc', -1, 0),
(45, 'Cat: Oatmeal', '', -1, -1, '', '#D3BF96', -1, 0),
(46, 'Cat: Cappuccino', '', -1, -1, '', '#aaa38e', -1, 0),
(47, 'Cat: Olive', '', -1, -1, '', '#aeaa6c', -1, 0),
(48, 'Cat: Mocha', '', -1, -1, '', '#776f65', -1, 0),
(49, 'Cat: Dark Grey', '', -1, -1, '', '#51626f', -1, 0),
(50, 'Cat: Grey', '', -1, -1, '', '#73736D', -1, 0);

-- Dump completed on 2014-02-21 19:46:17
INSERT INTO `categories` (`id`, `pid`, `name`, `type`, `image_name`) VALUES
(1, 1, 'ROOT', 1, ''),
(2, 1, 'Buildings', 1, 'layers/redspot.png'),
(3, 1, 'Lecture Theatres', 10, '/layers/teaching_rooms.png'),
(5, 1, 'Facilities and amenities', 1, ''),
(6, 1, 'Student Residences', 0, '/layers/residences.png'),
(9, 5, 'Food and Drink', 10, '/layers/food.png'),
(13, 5, 'PC Labs/Workstations', 10, '/layers/computer_labs.png'),
(20, 1, 'Transport', 1, ''),
(24, 20, 'Bus stops', 10, '/layers/bus.png'),
(39, 1, 'Recycling', 1, '/layers/recycling.png'),
(53, 20, 'Car Parks', 0, '/layers/parking.png'),
(56, 5, 'Shops', 10, '/layers/shops.png'),
(58, 5, 'Toilets', 1, ''),
(59, 58, 'Toilets (Male)', 20, '/layers/toilet_male.png'),
(60, 58, 'Toilets (Female)', 20, '/layers/toilet_female.png'),
(61, 58, 'Toilets (Accessible)', 20, '/layers/toilet_disabled.png'),
(62, 58, 'Toilets (Unisex)', 20, '/layers/toilet_unisex.png'),
(63, 1, 'Lobby, Atrium or Foyer', 49, ''),
(64, 1, 'Corridors', 49, ''),
(65, 1, 'Outline', 49, ''),
(67, 1, 'Not searchable', 49, ''),
(68, 5, 'Cash Machines (ATM)', 1, '/layers/atm.png'),
(100, 1, 'Stairs', 0, '/layers/stairs.png'),
(101, 1, 'Lifts', 0, '/layers/elev.png');



