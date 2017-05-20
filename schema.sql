-- MySQL dump 10.14  Distrib 5.5.54-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: 127.0.0.1
-- ------------------------------------------------------
-- Server version	5.5.54-MariaDB-1ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Temporary table structure for view `DVDAuswahl`
--

DROP TABLE IF EXISTS `DVDAuswahl`;
/*!50001 DROP VIEW IF EXISTS `DVDAuswahl`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `DVDAuswahl` (
  `ID` tinyint NOT NULL,
  `Titel` tinyint NOT NULL,
  `Länge` tinyint NOT NULL,
  `Dateiname` tinyint NOT NULL,
  `DVD` tinyint NOT NULL,
  `Kategorie` tinyint NOT NULL,
  `Sprachen` tinyint NOT NULL,
  `Reihe` tinyint NOT NULL,
  `disc` tinyint NOT NULL,
  `category` tinyint NOT NULL,
  `series_id` tinyint NOT NULL,
  `dur_sec` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(4) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `disc`
--

DROP TABLE IF EXISTS `disc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disc` (
  `ID` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `vdvd` bit(1) NOT NULL,
  `regular` bit(1) NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `id_name` (`ID`,`name`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `episode_series`
--

DROP TABLE IF EXISTS `episode_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `episode_series` (
  `id` int(4) NOT NULL,
  `series_id` int(4) NOT NULL,
  `movie_id` int(10) NOT NULL,
  `episode` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`series_id`,`movie_id`,`id`),
  UNIQUE KEY `movie_id` (`movie_id`),
  KEY `series_id` (`series_id`),
  CONSTRAINT `episode_series_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` char(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movie_languages`
--

DROP TABLE IF EXISTS `movie_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movie_languages` (
  `id` int(5) NOT NULL,
  `movie_id` int(10) NOT NULL,
  `lang_id` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lang_id` (`lang_id`),
  KEY `movie_id` (`movie_id`),
  KEY `la_mo_id` (`movie_id`,`lang_id`),
  CONSTRAINT `movie_languages_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movies` (
  `ID` int(6) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration` int(8) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `disc` int(10) NOT NULL DEFAULT '263',
  `skey` varchar(100) DEFAULT NULL,
  `category` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `id_skey` (`skey`),
  KEY `id_disc` (`disc`),
  KEY `id_category` (`category`),
  KEY `id_title` (`title`),
  CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`disc`) REFERENCES `disc` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `movies_ibfk_2` FOREIGN KEY (`category`) REFERENCES `categories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `series`
--

DROP TABLE IF EXISTS `series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series` (
  `id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `prepend` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `admin` bit(1) NOT NULL DEFAULT b'0',
  `last_login` timestamp NULL DEFAULT NULL,
  `style` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'schrottfilme'
--
/*!50003 DROP FUNCTION IF EXISTS `DURATION_STRING` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE  FUNCTION `DURATION_STRING`(dur_sec int) RETURNS varchar(12) CHARSET utf8
return CONCAT( IF( FLOOR( dur_sec / 3600 ) <= 99, RIGHT( CONCAT( '00', FLOOR( dur_sec / 3600 ) ), 2 ), FLOOR( dur_sec / 3600 ) ), ':', RIGHT( CONCAT( '00', FLOOR( MOD( dur_sec, 3600 ) / 60 ) ), 2 ), ':', RIGHT( CONCAT( '00', MOD( dur_sec, 60 ) ), 2 ) ) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `MAKE_MOVIE_SORTKEY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE  FUNCTION `MAKE_MOVIE_SORTKEY`(title varchar(255), skey varchar(255)) RETURNS varchar(255) CHARSET utf8
RETURN IF( skey IS NOT NULL, skey, IF( LEFT( title, 4 ) IN ('Der ', 'Die ', 'Das ', 'The '), MID( title, 5 ), title ) ) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `MAKE_MOVIE_TITLE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE  FUNCTION `MAKE_MOVIE_TITLE`(movie_title varchar(255), comment varchar(255), series_name varchar(255), episode varchar(100), prepend bool) RETURNS varchar(255) CHARSET utf8
return CONCAT(IF(prepend,
                    CONCAT(IF((series_name IS NOT NULL),
                                CONCAT(series_name,
                                        IF((episode IS NOT NULL),
                                            CONCAT(' ', episode),
                                            ''),
                                        ': '),
                                '')),
                    ''),
                movie_title,
                IF((comment IS NOT NULL),
                    CONCAT(' (', comment, ')'),
                    '')) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `DVDAuswahl`
--

/*!50001 DROP TABLE IF EXISTS `DVDAuswahl`*/;
/*!50001 DROP VIEW IF EXISTS `DVDAuswahl`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013  SQL SECURITY DEFINER */
/*!50001 VIEW `DVDAuswahl` AS select `movies`.`ID` AS `ID`,`MAKE_MOVIE_TITLE`(`movies`.`title`,`movies`.`comment`,`series`.`name`,`episode_series`.`episode`,`series`.`prepend`) AS `Titel`,`DURATION_STRING`(`movies`.`duration`) AS `Länge`,if((`movies`.`filename` is not null),`movies`.`filename`,'n.V.') AS `Dateiname`,`disc`.`name` AS `DVD`,`categories`.`name` AS `Kategorie`,group_concat(`languages`.`name` separator ', ') AS `Sprachen`,`series`.`name` AS `Reihe`,`movies`.`disc` AS `disc`,`movies`.`category` AS `category`,`episode_series`.`series_id` AS `series_id`,`movies`.`duration` AS `dur_sec` from ((((((`movies` left join `disc` on((`movies`.`disc` = `disc`.`ID`))) left join `movie_languages` on((`movies`.`ID` = `movie_languages`.`movie_id`))) left join `languages` on((`movie_languages`.`lang_id` = `languages`.`id`))) left join `categories` on((`movies`.`category` = `categories`.`id`))) left join `episode_series` on((`episode_series`.`movie_id` = `movies`.`ID`))) left join `series` on((`episode_series`.`series_id` = `series`.`id`))) group by `movies`.`ID` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;


-- Dump completed on 2017-05-20  6:40:20
