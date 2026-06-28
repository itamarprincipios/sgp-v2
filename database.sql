-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sgp_v2
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ai_queries`
--

DROP TABLE IF EXISTS `ai_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_queries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `question` text NOT NULL,
  `context_filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_filters`)),
  `response` text DEFAULT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_queries_user_id_foreign` (`user_id`),
  CONSTRAINT `ai_queries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_queries`
--

LOCK TABLES `ai_queries` WRITE;
/*!40000 ALTER TABLE `ai_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_school_id_foreign` (`school_id`),
  CONSTRAINT `classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,1,'1º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(2,1,'1º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(3,1,'1º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(4,1,'1º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(5,1,'1º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(6,1,'1º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(7,1,'2º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(8,1,'2º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(9,1,'2º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(10,1,'2º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(11,1,'2º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(12,1,'2º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(13,1,'3º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(14,1,'3º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(15,1,'3º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(16,1,'3º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(17,1,'3º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(18,1,'3º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(19,1,'4º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(20,1,'4º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(21,1,'4º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(22,1,'4º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(23,1,'4º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(24,1,'4º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(25,1,'5º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(26,1,'5º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(27,1,'5º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(28,1,'5º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(29,1,'5º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(30,1,'5º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(31,2,'1º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(32,2,'1º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(33,2,'1º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(34,2,'1º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(35,2,'1º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(36,2,'1º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(37,2,'2º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(38,2,'2º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(39,2,'2º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(40,2,'2º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(41,2,'2º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(42,2,'2º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(43,2,'3º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(44,2,'3º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(45,2,'3º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(46,2,'3º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(47,2,'3º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(48,2,'3º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(49,2,'4º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(50,2,'4º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(51,2,'4º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(52,2,'4º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(53,2,'4º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(54,2,'4º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32'),(55,2,'5º Ano A','2026-06-28 02:33:32','2026-06-28 02:33:32'),(56,2,'5º Ano B','2026-06-28 02:33:32','2026-06-28 02:33:32'),(57,2,'5º Ano C','2026-06-28 02:33:32','2026-06-28 02:33:32'),(58,2,'5º Ano D','2026-06-28 02:33:32','2026-06-28 02:33:32'),(59,2,'5º Ano E','2026-06-28 02:33:32','2026-06-28 02:33:32'),(60,2,'5º Ano F','2026-06-28 02:33:32','2026-06-28 02:33:32');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `period_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('planejamento','relatorio') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `content_text` longtext DEFAULT NULL,
  `content_extracted_at` timestamp NULL DEFAULT NULL,
  `status` enum('pendente','enviado','atrasado','aprovado','rejeitado','ajustado') NOT NULL DEFAULT 'enviado',
  `feedback` text DEFAULT NULL,
  `score_base` decimal(5,2) NOT NULL DEFAULT 0.00,
  `penalty_delay` decimal(5,2) NOT NULL DEFAULT 0.00,
  `penalty_resubmission` decimal(5,2) NOT NULL DEFAULT 0.00,
  `score_final` decimal(5,2) NOT NULL DEFAULT 0.00,
  `rejection_count` int(11) NOT NULL DEFAULT 0,
  `rejection_reason` text DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_tenant_id_foreign` (`tenant_id`),
  KEY `documents_user_id_foreign` (`user_id`),
  KEY `documents_period_id_foreign` (`period_id`),
  CONSTRAINT `documents_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2012_12_31_000000_create_tenants_table',1),(2,'2013_01_01_000000_create_schools_table',1),(3,'2013_01_01_100000_create_classes_table',1),(4,'2014_10_12_000000_create_users_table',1),(5,'2014_10_12_100000_create_password_reset_tokens_table',1),(6,'2014_10_12_200000_create_user_schools_table',1),(7,'2014_10_12_300000_create_periods_table',1),(8,'2014_10_12_400000_create_documents_table',1),(9,'2014_10_12_500000_create_user_medals_table',1),(10,'2014_10_12_600000_create_ai_queries_table',1),(11,'2014_10_12_700000_create_official_notices_table',1),(12,'2019_08_19_000000_create_failed_jobs_table',1),(13,'2019_12_14_000001_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `official_notices`
--

DROP TABLE IF EXISTS `official_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `official_notices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `recipient_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `severity` varchar(20) NOT NULL DEFAULT 'warning',
  `viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `official_notices_tenant_id_foreign` (`tenant_id`),
  KEY `official_notices_sender_id_foreign` (`sender_id`),
  KEY `official_notices_recipient_id_foreign` (`recipient_id`),
  CONSTRAINT `official_notices_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `official_notices_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `official_notices_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `official_notices`
--

LOCK TABLES `official_notices` WRITE;
/*!40000 ALTER TABLE `official_notices` DISABLE KEYS */;
/*!40000 ALTER TABLE `official_notices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `bimester` int(11) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` date DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `opening_date` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_physical_education` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `periods_tenant_id_foreign` (`tenant_id`),
  KEY `periods_school_id_foreign` (`school_id`),
  CONSTRAINT `periods_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `periods_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periods`
--

LOCK TABLES `periods` WRITE;
/*!40000 ALTER TABLE `periods` DISABLE KEYS */;
INSERT INTO `periods` VALUES (1,1,NULL,'Planejamento Junho 2026','Período regular de planejamento pedagógico da rede',2,'2026-06-01 00:00:00','2026-06-30','2026-06-30 23:59:59','2026-06-01 00:00:00',1,0,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(2,1,1,'Planejamento Julho 2026 (Escola 1)','Período de planejamento pedagógico para a Escola 1',3,'2026-07-01 00:00:00','2026-07-31','2026-06-30 23:59:59','2026-06-24 00:00:00',1,1,'2026-06-28 02:33:33','2026-06-28 02:43:45');
/*!40000 ALTER TABLE `periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `inep_code` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `director_name` varchar(255) DEFAULT NULL,
  `director_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_inep_code_unique` (`inep_code`),
  KEY `schools_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `schools_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,1,'Escola Municipal Exemplo 1','12345678','Rua das Flores, 123','Diretora Maria Silva','95991112222','2026-06-28 02:33:32','2026-06-28 02:33:32'),(2,1,'Escola Municipal Exemplo 2','87654321','Av. Central, 456','Diretor Carlos Souza','95992223333','2026-06-28 02:33:32','2026-06-28 02:33:32');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ai_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `max_schools_limit` int(11) NOT NULL DEFAULT 10,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES (1,'Prefeitura Exemplo','exemplo',1,1,10,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32');
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_medals`
--

DROP TABLE IF EXISTS `user_medals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_medals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `medal_type` varchar(50) NOT NULL,
  `period_type` varchar(20) NOT NULL,
  `reference_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_medals_user_id_medal_type_reference_date_unique` (`user_id`,`medal_type`,`reference_date`),
  CONSTRAINT `user_medals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_medals`
--

LOCK TABLES `user_medals` WRITE;
/*!40000 ALTER TABLE `user_medals` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_medals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_schools`
--

DROP TABLE IF EXISTS `user_schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_schools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `school_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_schools_user_id_school_id_unique` (`user_id`,`school_id`),
  KEY `user_schools_school_id_foreign` (`school_id`),
  CONSTRAINT `user_schools_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_schools_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_schools`
--

LOCK TABLES `user_schools` WRITE;
/*!40000 ALTER TABLE `user_schools` DISABLE KEYS */;
INSERT INTO `user_schools` VALUES (1,3,1,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(2,4,2,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(3,10,1,'2026-06-28 02:33:33','2026-06-28 02:33:33');
/*!40000 ALTER TABLE `user_schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `school_id` bigint(20) unsigned DEFAULT NULL,
  `class_id` bigint(20) unsigned DEFAULT NULL,
  `monitor_class_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','semed','director','coordinator','professor','supervisor_edfis','supervisor_monitor') NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `is_physical_education` tinyint(1) NOT NULL DEFAULT 0,
  `is_monitor` tinyint(1) NOT NULL DEFAULT 0,
  `is_first_grade` tinyint(1) NOT NULL DEFAULT 0,
  `profile_photo` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_tenant_id_foreign` (`tenant_id`),
  KEY `users_school_id_foreign` (`school_id`),
  KEY `users_class_id_foreign` (`class_id`),
  KEY `users_monitor_class_id_foreign` (`monitor_class_id`),
  CONSTRAINT `users_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_monitor_class_id_foreign` FOREIGN KEY (`monitor_class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,NULL,NULL,NULL,'Administrador do Sistema','admin@sgp.com',NULL,'$2y$10$8xx4LY0GudZQGTrRm4xlr.SbtaDz5EBmw3mrzrm7aOGX/VdGoh4mm','admin','5595999999999',0,0,0,NULL,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32'),(2,1,NULL,NULL,NULL,'Equipe SEMED','semed@sgp.com',NULL,'$2y$10$SzJ7FBZmEGXEn9Y.QelBieAHLpTwCo9MbsYB3pBoHCCDVPXsVr0Ue','semed','5595988888888',0,0,0,NULL,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32'),(3,1,1,NULL,NULL,'Coordenadora Milza (Escola 1)','coord1@sgp.com',NULL,'$2y$10$chJSpHQhQ3VFIY04UHEQ0.DWF1yDug0rHy5r8ws38BiflUMB./nCC','coordinator','5595977777777',0,0,0,NULL,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32'),(4,1,2,NULL,NULL,'Coordenador Rosi (Escola 2)','coord2@sgp.com',NULL,'$2y$10$.6vj8GVaxdnf/BiEWKPaUuQJ.kvODV32ZWEPT4aQ/yz.WVpu5aGru','coordinator','5595966666666',0,0,0,NULL,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32'),(5,1,1,1,NULL,'Professor João da Silva','prof1@sgp.com',NULL,'$2y$10$cqXSXrAYScPeOCNew/DL8Okgjk2R/HqwUm/d1N1cRPvT3j3B7gxRq','professor','5595955555555',0,0,0,NULL,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32'),(6,1,1,8,NULL,'Professora Ana Santos','prof2@sgp.com',NULL,'$2y$10$WgGnXf4RY0X4xG3Oxh37zuRdvClDLD8DyAwqHo1vZ2i0i3k/3BrC2','professor','5595944444444',0,0,0,NULL,NULL,'2026-06-28 02:33:32','2026-06-28 02:33:32'),(7,1,1,1,NULL,'Professor Roberto (Ed. Física)','prof.edfis@sgp.com',NULL,'$2y$10$JWHP4kJoJceEvX756SNVKeqCguFANTql9C6DmTdNpAI.8A49f4ICG','professor','5595933333333',1,0,0,NULL,NULL,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(8,1,NULL,NULL,NULL,'Supervisora Sandra (Ed. Física)','supervisor.edfis@sgp.com',NULL,'$2y$10$Bl0z0INBD48cBS4dU.pKNeba//UXNi8cXz4yaIxZsN/NHnnEFgxTG','supervisor_edfis','5595922222222',0,0,0,NULL,NULL,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(9,NULL,NULL,NULL,NULL,'Super Administrador','superadmin@sgp.com',NULL,'$2y$10$RX3WPmaJTmASduQOPjXNQeTSPit8OqBJL9leptygJjrk2s9CpWSBK','superadmin','5595999999999',0,0,0,NULL,NULL,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(10,1,1,NULL,NULL,'Diretor Roberto Silva','diretor1@sgp.com',NULL,'$2y$10$zzHBjmDsuArxp15WsB7ohuc2fo9JUriFJAs/STlEYGy8gpzcZ84MS','director','5595999991234',0,0,0,NULL,NULL,'2026-06-28 02:33:33','2026-06-28 02:33:33'),(11,1,1,NULL,NULL,'Itamar Vieira nunes','itamar@hildemar.com',NULL,'$2y$10$H6hJ1AJOtpidTzRRteYVwuipVV4U8AXpcglw9jZ4TKeBa6giHKYY6','professor','95991248941',1,0,0,NULL,NULL,'2026-06-28 02:36:59','2026-06-28 02:36:59');
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

-- Dump completed on 2026-06-27 20:02:19
