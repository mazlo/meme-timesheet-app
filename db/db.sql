-- MySQL dump 10.13  Distrib 5.5.33, for osx10.6 (i386)
--
-- Host: localhost    Database: tisheets
-- ------------------------------------------------------
-- Server version	5.5.33

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
-- Table structure for table `contexts`
--

DROP TABLE IF EXISTS `contexts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contexts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefLabel` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contexts`
--

LOCK TABLES `contexts` WRITE;
/*!40000 ALTER TABLE `contexts` DISABLE KEYS */;
INSERT INTO `contexts` VALUES (3,'#missy','2014-09-19 11:03:07','2014-09-19 11:03:07'),(4,'#private','2014-09-19 11:43:14','2014-09-19 11:43:14'),(5,'#privata','2014-09-19 12:35:34','2014-09-19 12:35:34'),(6,'#ttcbenrath','2014-09-21 19:11:03','2014-09-21 19:11:03'),(7,'#gesis','2014-09-23 10:46:07','2014-09-23 10:46:07'),(8,'#daytoday','2014-09-25 06:35:10','2014-09-25 06:35:10'),(9,'#day2day','2014-09-25 06:49:15','2014-09-25 06:49:15'),(10,'#timesheet','2014-09-25 07:58:57','2014-09-25 07:58:57'),(11,'#missyy','2014-09-25 09:38:37','2014-09-25 09:38:37'),(12,'#administration','2014-09-26 14:39:49','2014-09-26 14:39:49'),(13,'#diss','2014-09-26 14:40:16','2014-09-26 14:40:16'),(14,'#wts','2014-09-30 12:54:39','2014-09-30 12:54:39'),(15,'#timesheet','2014-10-14 05:34:41','2014-10-14 05:34:41');
/*!40000 ALTER TABLE `contexts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tisheets`
--

DROP TABLE IF EXISTS `tisheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tisheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text,
  `planned` tinyint(1) DEFAULT '0',
  `start_time` varchar(8) DEFAULT NULL,
  `time_spent` int(4) DEFAULT '0',
  `context_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tisheets`
--

LOCK TABLES `tisheets` WRITE;
/*!40000 ALTER TABLE `tisheets` DISABLE KEYS */;
INSERT INTO `tisheets` VALUES (1,'2014-09-18 22:00:00','updated sql scripts for productionss #missy',0,NULL,7,3,1,'0000-00-00 00:00:00','2014-10-14 05:55:33'),(2,'2014-09-18 22:00:00','neue aufgabe @ttcbenrath',0,NULL,9,6,1,'2014-09-19 11:28:58','2014-09-21 19:11:03'),(3,'2014-09-21 22:00:00','debug, org.eurostat -> de.gesis for all resources #missy',0,NULL,4,3,1,'2014-09-22 19:32:51','2014-10-14 05:55:22'),(4,'2014-09-21 22:00:00','merge with development-branch and generate rdf xml testlike',0,NULL,2,NULL,1,'2014-09-22 19:34:05','2014-09-22 19:52:09'),(5,'2014-09-21 22:00:00','team meeting frontend',0,NULL,0,NULL,1,'2014-09-22 19:52:35','2014-09-22 19:52:35'),(6,'2014-09-21 22:00:00',NULL,0,NULL,0,NULL,1,'2014-09-22 19:52:35','2014-09-22 19:52:35'),(7,'2014-09-22 22:00:00','Emails checken und beantworten #missy',0,NULL,3,3,1,'2014-09-23 09:59:32','2014-10-14 05:55:07'),(8,'2014-09-22 22:00:00','export missydb studien rdf/xml #missy',0,NULL,1,3,1,'2014-09-23 09:59:32','2014-10-14 05:55:09'),(9,'2014-09-22 22:00:00','Anpassung Gantt-chart #missy',0,NULL,3,3,1,'2014-09-23 10:36:49','2014-10-14 05:55:11'),(10,'2014-09-22 22:00:00','Sozializing #gesis',0,NULL,3,7,1,'2014-09-23 10:45:16','2014-10-14 05:55:15'),(11,'2014-09-24 22:00:00','Weitere Aufgabe für Sigit -> Besprechung, Konzeption #missy',0,NULL,5,3,1,'2014-09-25 06:34:00','2014-10-14 05:54:36'),(23,'2014-09-23 22:00:00','Matrix import -> Anpassung des Codes für alle Typen von Studien #missy',0,NULL,16,3,1,'2014-09-25 06:45:07','2014-10-14 05:54:55'),(24,'2014-09-23 22:00:00','Emails etc. #day2day',0,NULL,4,9,1,'2014-09-25 06:46:33','2014-10-14 05:54:58'),(26,'2014-09-23 22:00:00','Bereinigung bestehende Matrix-Dateien #missy',0,NULL,12,3,1,'2014-09-25 06:46:47','2014-10-14 05:55:01'),(28,'2014-09-24 22:00:00','Debugging timesheet #timesheet',0,NULL,6,10,1,'2014-09-25 07:58:57','2014-10-14 05:54:38'),(61,'2014-09-24 22:00:00','Emails etc. #daytoday',0,NULL,2,8,1,'2014-09-25 09:45:51','2014-10-14 05:54:43'),(64,'2014-09-24 22:00:00','variable statistics computation #missy',0,NULL,4,3,1,'2014-09-25 11:19:47','2014-10-14 05:54:45'),(65,'2014-09-24 22:00:00','productive deployment #missy',0,NULL,2,3,1,'2014-09-25 11:19:56','2014-10-14 05:54:50'),(66,'2014-09-25 22:00:00','Lesen über den Aufbau eines Exposés #diss',0,NULL,4,13,1,'2014-09-26 12:00:59','2014-10-14 05:54:25'),(71,'2014-09-25 22:00:00','Timesheet ausbau #timesheet',0,NULL,2,10,1,'2014-09-26 12:21:47','2014-10-14 05:54:27'),(72,'2014-09-25 22:00:00','debugging gitlab server config -> conflict with nexus app, with alex #administration',0,NULL,4,12,1,'2014-09-26 14:39:49','2014-10-14 05:54:32'),(73,'2014-09-28 22:00:00','weekly team meeting missy frontend 10:00 #missy',0,NULL,2,3,1,'2014-09-29 06:19:46','2014-10-14 05:54:02'),(74,'2014-09-28 22:00:00','debugging import app -> database connection 10:30 #missy',0,NULL,3,3,1,'2014-09-29 07:43:56','2014-10-14 05:54:04'),(75,'2014-09-28 22:00:00','konzept -> extending disco rdf/xml export with spatial 11:15 #missy',0,NULL,1,3,1,'2014-09-29 07:44:19','2014-10-14 05:54:07'),(76,'2014-09-28 22:00:00','Implementation disco rdf/xml eximport with spatial attribute 12:50 #missy',0,NULL,8,3,1,'2014-09-29 08:52:09','2014-10-14 05:54:09'),(77,'2014-09-28 22:00:00','debugging CV for documents area #missy',0,NULL,7,3,1,'2014-09-29 12:31:40','2014-10-14 05:54:12'),(78,'2014-09-28 22:00:00','import them.classification prodDB 16:40 #missy',0,NULL,4,3,1,'2014-09-29 13:36:28','2014-10-14 05:54:15'),(81,'2014-09-29 22:00:00','Besprechung allgemeine Aufgaben und weitere Aufgaben für Sigit 9:45 #missy',0,NULL,4,3,1,'2014-09-30 07:53:45','2014-10-14 05:53:41'),(82,'2014-09-29 22:00:00','Konzeption: get variables for thematic classification mit Alina 10:45 #missy',0,NULL,4,3,1,'2014-09-30 07:54:42','2014-10-14 05:53:44'),(83,'2014-09-29 22:00:00','Klärung \"thematic order\" und laden der Variablen; telco, email 13:00 #missy',0,NULL,5,3,1,'2014-09-30 10:00:12','2014-10-14 05:53:47'),(84,'2014-09-29 22:00:00','Erweiterung ConceptDAO um getVariablesByStudyGroupAndNotation() #missy',0,NULL,4,3,1,'2014-09-30 12:39:40','2014-10-14 05:53:50'),(85,'2014-09-29 22:00:00','ThematicClassificationService.getRelatedVariablesForLeafConcepts() #missy',0,NULL,3,3,1,'2014-09-30 12:40:12','2014-10-14 05:53:54'),(86,'2014-09-29 22:00:00','Forschungsgruppe Semantic Web 15:00 #wts',0,NULL,3,14,1,'2014-09-30 12:40:23','2014-10-14 05:53:57'),(87,'2014-09-30 22:00:00','debugging import frequencies, email Florian 15:00 #missy',0,NULL,2,3,1,'2014-10-01 11:42:45','2014-10-14 05:53:20'),(88,'2014-09-30 22:00:00','Einführung Alina ThematicClassificationService, TemplateService 14:00 #missy',0,NULL,4,3,1,'2014-10-01 11:43:11','2014-10-14 05:53:23'),(89,'2014-09-30 22:00:00','Changes to implementation ConceptDAO and ThematicClassificationService 11:00 #missy',0,NULL,12,3,1,'2014-10-01 11:44:09','2014-10-14 05:53:26'),(90,'2014-09-30 22:00:00','Eigene Organisation und Urlaubsvorbereitung #private',0,NULL,8,4,1,'2014-10-01 13:12:39','2014-10-14 05:53:29'),(91,'2014-10-12 22:00:00','copy generated html files automatically; conceptualisation #missy',0,NULL,6,3,1,'2014-10-13 14:23:53','2014-10-14 05:52:57'),(93,'2014-10-12 22:00:00','make a thing',0,NULL,3,NULL,1,'2014-10-13 17:59:53','2014-10-13 18:10:12'),(94,'2014-10-13 22:00:00','extend timesheet with start time @9:30 #timesheet',0,'@9:30',3,15,1,'2014-10-14 05:34:41','2014-10-14 05:49:41'),(95,'2014-10-13 22:00:00','added basic login/logout; copied from minutes @17:30 #timesheet',0,'@17:30',2,10,1,'2014-10-14 13:58:00','2014-10-14 13:58:00'),(96,'2014-10-13 22:00:00','css optimizations @17:45 #timesheet',0,'@17:45',1,10,1,'2014-10-14 14:06:45','2014-10-14 14:06:45'),(97,'2014-10-14 22:00:00','tested timesheet with different users #timesheet',0,'@9:00',1,10,2,'2014-10-15 05:41:10','2014-10-15 06:00:57'),(99,'2014-10-14 22:00:00','added signup form functionality @9:00 #timesheet',0,'@9:00',3,10,1,'2014-10-15 05:58:55','2014-10-15 12:24:00'),(100,'2014-10-14 22:00:00','updated database and added dependency @9:45 #timesheet',0,'@9:45',1,10,1,'2014-10-15 05:59:05','2014-10-15 12:23:59'),(101,'2014-10-19 22:00:00','Länder für Frequencies View anpassen',1,NULL,0,NULL,1,'2014-10-15 09:23:16','2014-10-15 12:24:11'),(102,'2014-10-14 22:00:00','emails and administration #missy @10:00',0,'@10:00',5,3,1,'2014-10-15 09:36:16','2014-10-15 12:23:58'),(103,'2014-10-14 22:00:00','StatisticsService code push and verification in ME #missy',0,NULL,4,3,1,'2014-10-15 09:36:57','2014-10-15 12:23:55');
/*!40000 ALTER TABLE `tisheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'matthaeus','$2y$08$vZdYSXswakXfiNZ0tgfa6elzkuvRZSX6F9fMOp.xfQebFgFA66gSG','matthaeus@email.com','2013-12-08 16:45:37','2014-10-15 12:06:03'),(2,'john','$2y$08$HeuK5XL5cBJT209U81i3cub4yPIXoJLwNzyfyX.ZZ15TRpCUGkZi2','john@jo.com','2014-10-15 05:31:04','2014-10-15 05:51:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-10-15 16:29:10
