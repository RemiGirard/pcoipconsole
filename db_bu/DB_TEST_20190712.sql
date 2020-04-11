-- MySQL dump 10.13  Distrib 5.7.26, for Linux (x86_64)
--
-- Host: localhost    Database: pcoipconsole
-- ------------------------------------------------------
-- Server version	5.7.26-0ubuntu0.18.04.1

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
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20190607103734','2019-06-10 10:35:11'),('20190607152023','2019-06-10 10:35:11'),('20190625102613','2019-06-25 10:26:34'),('20190625174820','2019-06-25 17:48:45'),('20190625203849','2019-06-25 20:39:33'),('20190626060324','2019-06-26 06:03:35'),('20190626082110','2019-06-26 08:21:21'),('20190705140925','2019-07-05 14:09:34');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terminal`
--

DROP TABLE IF EXISTS `terminal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terminal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `connected_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `connection_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ping` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logged` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `connected_to_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terminal`
--

LOCK TABLES `terminal` WRITE;
/*!40000 ALTER TABLE `terminal` DISABLE KEYS */;
INSERT INTO `terminal` VALUES (2,'','','10.86.18.143','client','','disconnected','false','Can\'t login in terminal',''),(3,'pcoip-r108-mik002.mikros.int','FRAPCEDIT4003','10.86.220.60','host','45','connected','true','logged','S6.41'),(4,'pcoip-p-mik1003.mikros.int','S5.43','10.86.220.234','client','41','connected','true','logged','FRAPCEDIT4006'),(6,'pcoip-r108-mik001.mikros.int','FRAPCEDIT4004','10.86.221.86','host','','disconnected','true','logged',''),(7,'pcoip-p-mik015','','10.86.220.86','client','','disconnected','false','Can\'t login in terminal',''),(8,'','','10.86.221.154','client','','disconnected','true','Can\'t login in terminal',NULL),(9,'pcoip-p-mik1010.mikros.int','S5.18-1','10.86.221.171','client','43','disconnected','true','logged','FRAMACVFX1004'),(10,'pcoip-p-mik1016.mikros.int','S5.35-1','10.86.220.141','client','12','connected','true','logged','FRAPCFINISH005'),(11,'pcoip-p-mik054.mikros.int','','10.86.220.94','client','26','disconnected','true','logged',NULL),(12,'pcoip-r209-mik004.mikros.int','FRAPCFINISH005','10.86.221.13','host','10','connected','true','logged','S5.35-1'),(20,'testterminal03',NULL,NULL,NULL,'','disconnected','false',NULL,NULL),(21,'pcoip-r109-mik006.mikros.int','FRAMACDIO3','10.86.220.116','host','10.86.19.233','connected','true','logged','10.86.19.233'),(22,NULL,NULL,'10.86.220.69','host','','disconnected','true','Can\'t login in terminal',NULL),(23,'pcoip-r111-mik002.mikros.int','PAD001','10.86.220.182','host','10.86.220.84','connected','true','logged','10.86.220.84'),(24,'pcoip-r108-mik003.mikros.int','FRAMACDI02','10.86.221.37','host','10.86.220.113','connected','true','logged','10.86.220.113'),(25,'pcoip-r108-mik004.mikros.int','FRAMACDI010','10.86.220.82','host','','disconnected','true','logged',''),(26,NULL,NULL,'10.86.220.67','host','','disconnected','true','Can\'t login in terminal',NULL),(28,'pcoip-r109-mik003.mikros.int','FRAMACDI014','10.86.221.152','host','10.86.221.19','connected','true','logged','10.86.221.19'),(29,'pcoip-r109-mik005.mikros.int','FRAPCREVIEW05','10.86.221.21','host','','disconnected','true','logged',''),(30,'pcoip-h-frarmt2022.mikros.int','FRARMT2022','10.86.220.90','host','10.86.19.90','connected','true','logged','10.86.19.90'),(31,'pcoip-r108-mik005.mikros.int','FRAMACVFX1001','10.86.220.78','host','','disconnected','true','logged',''),(32,'pcoip-r108-mik006.mikros.int','FRAMACVFX1002','10.86.221.138','host','47','connected','true','logged','S6.38'),(33,'pcoip-r210-mik005.mikros.int','FRAPCFINISH003','10.86.220.80','host','49','connected','true','logged','S6.39'),(34,'pcoip-r208-mik004.mikros.int','FRAPCFINISH004','10.86.221.124','host','54','connected','true','logged','S6.17-1'),(35,'pcoip-r208-mik001.mikros.int','FINALCUT X','10.86.221.45','host','','disconnected','true','logged',''),(36,'pcoip-r208-mik005.mikros.int','FRAPCFINISH001','10.86.221.121','host','50','connected','true','logged','S5.35-2'),(37,'pcoip-r209-mik005.mikros.int','FRAPCFINISH002','10.86.220.191','host','10.86.220.151','connected','true','logged','10.86.220.151'),(38,'pcoip-r210-mik004.mikros.int','FRAPCFINISH006','10.86.220.110','host','55','connected','true','logged','S5.40'),(39,'pcoip-r209-mik002.mikros.int','FRAPCEDIT4002','10.86.220.112','host','','disconnected','true','logged',''),(40,'pcoip-r210-mik001.mikros.int','FRAPCEDIT4005','10.86.220.39','host','','disconnected','true','logged',''),(41,'pcoip-r210-mik002.mikros.int','FRAPCEDIT4006','10.86.220.253','host','4','connected','true','logged','S5.43'),(42,'pcoip-r209-mik001.mikros.int','FRAPCEDIT4001','10.86.220.107','host','','disconnected','true','logged',''),(43,'pcoip-r109-mik002.mikros.int','FRAMACVFX1004','10.86.220.73','host','10.86.220.208','connected','true','logged','10.86.220.208'),(44,'pcoip-p-mik1018.mikros.int','S5.19-1','10.86.18.124','client','','disconnected','false','Can\'t login in terminal',''),(45,'pcoip-p-mik1020.mikros.int','S6.41','10.86.220.48','client','3','connected','true','logged','FRAPCEDIT4003'),(46,'pcoip-p-mik1006.mikros.int','S6.17-2','10.86.220.50','client','','disconnected','false','Can\'t login in terminal',''),(47,'pcoip-p-mik1008.mikros.int','S6.38','10.86.220.52','client','32','connected','true','logged','FRAMACVFX1002'),(49,'pcoip-p-mik1002.mikros.int','S6.39','10.86.220.54','client','33','connected','true','logged','FRAPCFINISH003'),(50,'pcoip-p-mik1011.mikros.int','S5.35-2','10.86.220.56','client','36','connected','true','logged','FRAPCFINISH001'),(51,'pcoip-p-mik1013.mikros.int','S5.18-2','10.86.220.130','client','31','disconnected','true','logged','FRAMACVFX1001'),(52,'pcoip-p-mik1007.mikros.int','S5.19-2','10.86.220.231','client','','disconnected','false','Can\'t login in terminal',''),(54,'pcoip-p-mik1012.mikros.int','S6.17-1','10.86.221.96','client','34','connected','true','logged','FRAPCFINISH004'),(55,'pcoip-p-mik1001.mikros.int','S5.40','10.86.221.131','client','38','connected','true','logged','FRAPCFINISH006'),(56,'pcoip-p-mik1004.mikros.int','S5.44','10.86.221.168','client','34','disconnected','true','logged','FRAPCFINISH004'),(57,'pcoip-p-mik1005.mikros.int','S5.15','10.86.220.241','client','35','disconnected','true','logged','FINALCUT X');
/*!40000 ALTER TABLE `terminal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'assistantavid','[\"ROLE_OPERATOR\"]','$argon2id$v=19$m=65536,t=6,p=1$ZPAUfi2PSwgMXc8FU1CliA$Og1nfVwimiZk8b9Vuw8bdPqwu9FoN2rnErrxmZ63NLs'),(2,'remi','[\"ROLE_ADMIN\"]','$argon2id$v=19$m=65536,t=6,p=1$GpEt/DCsnDPvO1iOQ76OXQ$0E3oVdzp2g2Nqg2Nq6bn0UU+GPvb9BrFR0EUWoDY3sI');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-12  9:52:55
