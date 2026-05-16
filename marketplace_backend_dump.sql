-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: marketplace_backend
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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `path` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `response_status` smallint(5) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `occurred_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_index` (`user_id`),
  KEY `activity_logs_event_index` (`event`),
  KEY `activity_logs_method_index` (`method`),
  KEY `activity_logs_response_status_index` (`response_status`),
  KEY `activity_logs_occurred_at_index` (`occurred_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'api_request','POST','/api/v1/stores','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-12 10:21:01','2026-05-12 10:21:01','2026-05-12 10:21:01'),(2,1,'api_request','PUT','/api/v1/stores/1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:22:16','2026-05-12 10:22:16','2026-05-12 10:22:16'),(3,1,'api_request','POST','/api/v1/social/facebook/authorize','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:22:52','2026-05-12 10:22:52','2026-05-12 10:22:52'),(4,1,'api_request','POST','/api/v1/social/facebook/authorize','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:26:24','2026-05-12 10:26:24','2026-05-12 10:26:24'),(5,1,'api_request','POST','/api/v1/social/facebook/authorize','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:30:53','2026-05-12 10:30:53','2026-05-12 10:30:53'),(6,1,'api_request','POST','/api/v1/social/accounts','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-12 10:33:27','2026-05-12 10:33:27','2026-05-12 10:33:27'),(7,1,'api_request','POST','/api/v1/stores','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-12 10:34:17','2026-05-12 10:34:17','2026-05-12 10:34:17'),(8,1,'api_request','PUT','/api/v1/stores/2','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:35:07','2026-05-12 10:35:07','2026-05-12 10:35:07'),(9,1,'api_request','POST','/api/v1/stores','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-12 10:46:25','2026-05-12 10:46:25','2026-05-12 10:46:25'),(10,1,'api_request','PUT','/api/v1/stores/3','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:47:26','2026-05-12 10:47:26','2026-05-12 10:47:26'),(11,1,'api_request','POST','/api/v1/products','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-12 10:48:57','2026-05-12 10:48:57','2026-05-12 10:48:57'),(12,1,'api_request','POST','/api/v1/products/1/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:49:50','2026-05-12 10:49:50','2026-05-12 10:49:50'),(13,1,'api_request','POST','/api/v1/products/1/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:50:10','2026-05-12 10:50:10','2026-05-12 10:50:10'),(14,1,'api_request','POST','/api/v1/products/1/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:50:20','2026-05-12 10:50:20','2026-05-12 10:50:20'),(15,1,'api_request','POST','/api/v1/products/1/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:50:30','2026-05-12 10:50:30','2026-05-12 10:50:30'),(16,1,'api_request','PUT','/api/v1/products/1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:50:33','2026-05-12 10:50:33','2026-05-12 10:50:33'),(17,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',422,'{\"route_name\":null}','2026-05-12 10:50:51','2026-05-12 10:50:51','2026-05-12 10:50:51'),(18,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',422,'{\"route_name\":null}','2026-05-12 10:50:52','2026-05-12 10:50:52','2026-05-12 10:50:52'),(19,1,'api_request','POST','/api/v1/social/facebook/authorize','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 10:52:38','2026-05-12 10:52:38','2026-05-12 10:52:38'),(20,1,'api_request','DELETE','/api/v1/social/accounts/1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 11:01:58','2026-05-12 11:01:58','2026-05-12 11:01:58'),(21,1,'api_request','POST','/api/v1/social/facebook/authorize','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 11:01:59','2026-05-12 11:01:59','2026-05-12 11:01:59'),(22,1,'api_request','POST','/api/v1/social/facebook/authorize','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 11:03:15','2026-05-12 11:03:15','2026-05-12 11:03:15'),(23,1,'api_request','POST','/api/v1/social/accounts','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-12 11:05:34','2026-05-12 11:05:34','2026-05-12 11:05:34'),(24,1,'api_request','PUT','/api/v1/products/1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 11:06:06','2026-05-12 11:06:06','2026-05-12 11:06:06'),(25,1,'api_request','PUT','/api/v1/products/1','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-12 11:06:43','2026-05-12 11:06:43','2026-05-12 11:06:43'),(26,1,'api_request','POST','/api/v1/products/1/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 01:22:26','2026-05-13 01:22:26','2026-05-13 01:22:26'),(27,1,'api_request','POST','/api/v1/products/1/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 01:23:57','2026-05-13 01:23:57','2026-05-13 01:23:57'),(28,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 01:29:30','2026-05-13 01:29:30','2026-05-13 01:29:30'),(29,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 01:29:34','2026-05-13 01:29:34','2026-05-13 01:29:34'),(30,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 01:29:39','2026-05-13 01:29:39','2026-05-13 01:29:39'),(31,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 01:29:54','2026-05-13 01:29:54','2026-05-13 01:29:54'),(32,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 07:21:42','2026-05-13 07:21:42','2026-05-13 07:21:42'),(33,1,'api_request','POST','/api/v1/products','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',201,'{\"route_name\":null}','2026-05-13 07:22:39','2026-05-13 07:22:39','2026-05-13 07:22:39'),(34,1,'api_request','POST','/api/v1/products/2/images','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 07:22:39','2026-05-13 07:22:39','2026-05-13 07:22:39'),(35,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 07:22:57','2026-05-13 07:22:57','2026-05-13 07:22:57'),(36,1,'api_request','POST','/api/v1/ai/similarity-search','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',200,'{\"route_name\":null}','2026-05-13 07:23:45','2026-05-13 07:23:45','2026-05-13 07:23:45');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'shipping',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_index` (`user_id`),
  KEY `addresses_type_index` (`type`),
  KEY `addresses_is_default_index` (`is_default`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_search_logs`
--

DROP TABLE IF EXISTS `ai_search_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_search_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_image_id` bigint(20) unsigned DEFAULT NULL,
  `query_image_path` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `embedding_version` varchar(255) DEFAULT NULL,
  `top_k` int(10) unsigned NOT NULL DEFAULT 10,
  `result_count` int(10) unsigned NOT NULL DEFAULT 0,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `error_message` text DEFAULT NULL,
  `searched_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_search_logs_user_id_index` (`user_id`),
  KEY `ai_search_logs_product_id_index` (`product_id`),
  KEY `ai_search_logs_product_image_id_index` (`product_image_id`),
  KEY `ai_search_logs_provider_index` (`provider`),
  KEY `ai_search_logs_status_index` (`status`),
  KEY `ai_search_logs_searched_at_index` (`searched_at`),
  CONSTRAINT `ai_search_logs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_search_logs_product_image_id_foreign` FOREIGN KEY (`product_image_id`) REFERENCES `product_images` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_search_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_search_logs`
--

LOCK TABLES `ai_search_logs` WRITE;
/*!40000 ALTER TABLE `ai_search_logs` DISABLE KEYS */;
INSERT INTO `ai_search_logs` VALUES (1,1,NULL,NULL,'search-queries/jOshDTpPArPNAOa7SFsxL6S1cQy01RQ5L1HddsFu.jpg','fake-image-embedding','completed','v1',10,1,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1],\"scores\":[0]}',NULL,'2026-05-13 01:29:30','2026-05-13 01:29:30','2026-05-13 01:29:30'),(2,1,NULL,NULL,'search-queries/sR7XaxzfPwRNzGQfxKTdpEBB78t9aGTm4owfXTNH.jpg','fake-image-embedding','completed','v1',5,1,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1],\"scores\":[0]}',NULL,'2026-05-13 01:29:34','2026-05-13 01:29:34','2026-05-13 01:29:34'),(3,1,NULL,NULL,'search-queries/laoSo4fJvf6t2lFlPhiZpUMsnMYJ2thapmDcXkfN.jpg','fake-image-embedding','completed','v1',10,1,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1],\"scores\":[0]}',NULL,'2026-05-13 01:29:39','2026-05-13 01:29:39','2026-05-13 01:29:39'),(4,1,NULL,NULL,'search-queries/NhkhDeF5rpe3XtjlUe60qDoxljYYE1mrczyX8Kog.webp','fake-image-embedding','completed','v1',10,1,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1],\"scores\":[0]}',NULL,'2026-05-13 01:29:54','2026-05-13 01:29:54','2026-05-13 01:29:54'),(5,1,NULL,NULL,'search-queries/t5X4qMih11qVIb0QZaD8YFQUrADTxSndMEuj1vTu.jpg','huggingface-clip','completed','v1',10,1,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1],\"scores\":[0.4486]}',NULL,'2026-05-13 07:21:42','2026-05-13 07:21:42','2026-05-13 07:21:42'),(6,1,NULL,NULL,'search-queries/SFNrI8wwkJfj3sVaqtF6uZwg43T7173Z6YWDwflD.jpg','huggingface-clip','completed','v1',5,2,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1,2],\"scores\":[0.4486,0]}',NULL,'2026-05-13 07:22:57','2026-05-13 07:22:57','2026-05-13 07:22:57'),(7,1,NULL,NULL,'search-queries/1lci6qk6tKtcoiIBST5EzSPecJMv1Qd4QcILv0Oc.png','huggingface-clip','completed','v1',10,2,'{\"product_id\":null,\"product_image_id\":null,\"uploaded_image\":true}','{\"product_ids\":[1,2],\"scores\":[0.1163,0]}',NULL,'2026-05-13 07:23:45','2026-05-13 07:23:45','2026-05-13 07:23:45');
/*!40000 ALTER TABLE `ai_search_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-5073a5ebf34dabe8cd41b53103982e2b','i:6;',1778660574),('laravel-cache-5073a5ebf34dabe8cd41b53103982e2b:timer','i:1778660574;',1778660574),('laravel-cache-64fbbeee3c1bb5be9d4f5bd05fe83171','i:1;',1778660564),('laravel-cache-64fbbeee3c1bb5be9d4f5bd05fe83171:timer','i:1778660564;',1778660564),('laravel-cache-88987ecaa04038c9ee92d94a9afb02c8','i:1;',1778580394),('laravel-cache-88987ecaa04038c9ee92d94a9afb02c8:timer','i:1778580394;',1778580394),('laravel-cache-a75f3f172bfb296f2e10cbfc6dfc1883','i:6;',1778660574),('laravel-cache-a75f3f172bfb296f2e10cbfc6dfc1883:timer','i:1778660574;',1778660574),('laravel-cache-ApVh9q2fQSoun7gg','a:1:{s:11:\"valid_until\";i:1778578746;}',1779787326),('laravel-cache-e45444ecc678a271a6330f468a373360','i:1;',1778659245),('laravel-cache-e45444ecc678a271a6330f468a373360:timer','i:1778659245;',1778659245),('laravel-cache-f0932b6c4f8f53ee19f2dd71a58a6279','i:2;',1778653431),('laravel-cache-f0932b6c4f8f53ee19f2dd71a58a6279:timer','i:1778653431;',1778653431),('laravel-cache-f1f70ec40aaa556905d4a030501c0ba4','i:2;',1778660564),('laravel-cache-f1f70ec40aaa556905d4a030501c0ba4:timer','i:1778660564;',1778660564),('laravel-cache-oauth_state:2jkptDRKX0oTvIUnX0HgoVD0n3lbDc9jOycjjIL3','a:2:{s:7:\"user_id\";i:1;s:8:\"provider\";s:8:\"facebook\";}',1778579153),('laravel-cache-oauth_state:eeBhK3lJqOljwyf7JkAM0LenV5UtKszn1oljdKzM','a:2:{s:7:\"user_id\";i:1;s:8:\"provider\";s:8:\"facebook\";}',1778581095),('laravel-cache-oauth_state:ezxg71UKm3e9C9AbC4lO0d9CsTJbVDgqb8aDqp8P','a:2:{s:7:\"user_id\";i:1;s:8:\"provider\";s:8:\"facebook\";}',1778580458),('laravel-cache-oauth_state:NVLAPx8D8cQauiLq6ldPJ2SmjQy5r6VVMD31r4GU','a:2:{s:7:\"user_id\";i:1;s:8:\"provider\";s:8:\"facebook\";}',1778578672),('laravel-cache-oauth_state:QegfC0MoffAalEwaqu2CUyYi3enE7j2R1y7aNOfF','a:2:{s:7:\"user_id\";i:1;s:8:\"provider\";s:8:\"facebook\";}',1778581019),('laravel-cache-oauth_state:rWwliHX5AM9RQZq2pAU7pCucXFkKoMVHili17iiH','a:2:{s:7:\"user_id\";i:1;s:8:\"provider\";s:8:\"facebook\";}',1778578884),('laravel-cache-SK5vNG8NwnglZlC8','a:1:{s:11:\"valid_until\";i:1778660514;}',1779868854);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price_amount` bigint(20) unsigned NOT NULL,
  `line_total_amount` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_id_unique` (`cart_id`,`product_id`),
  KEY `cart_items_product_id_index` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `subtotal_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `discount_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `shipping_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `total_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  KEY `carts_status_index` (`status`),
  KEY `carts_expires_at_index` (`expires_at`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,1,'active','USD',0,0,0,0,NULL,NULL,'2026-05-12 10:20:18','2026-05-12 10:20:18');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_index` (`parent_id`),
  KEY `categories_status_index` (`status`),
  KEY `categories_sort_order_index` (`sort_order`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `body` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_conversation_id_index` (`conversation_id`),
  KEY `chat_messages_sender_id_index` (`sender_id`),
  KEY `chat_messages_sent_at_index` (`sent_at`),
  KEY `chat_messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_participants`
--

DROP TABLE IF EXISTS `conversation_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `last_read_message_id` bigint(20) unsigned DEFAULT NULL,
  `joined_at` timestamp NULL DEFAULT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `is_muted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversation_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `conversation_participants_user_id_index` (`user_id`),
  KEY `conversation_participants_last_read_at_index` (`last_read_at`),
  CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_participants`
--

LOCK TABLES `conversation_participants` WRITE;
/*!40000 ALTER TABLE `conversation_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversation_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'private',
  `last_message_id` bigint(20) unsigned DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_product_id_index` (`product_id`),
  KEY `conversations_created_by_index` (`created_by`),
  KEY `conversations_type_index` (`type`),
  KEY `conversations_last_message_at_index` (`last_message_at`),
  CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'social','{\"uuid\":\"55d1fd6a-e78b-4fc7-a995-5d2b8b22f6aa\",\"displayName\":\"App\\\\Jobs\\\\PublishSocialPostJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\PublishSocialPostJob\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\PublishSocialPostJob\\\":2:{s:43:\\\"\\u0000App\\\\Jobs\\\\PublishSocialPostJob\\u0000socialPostId\\\";i:1;s:5:\\\"queue\\\";s:6:\\\"social\\\";}\",\"batchId\":null},\"createdAt\":1778579337,\"delay\":null}',0,NULL,1778579337,1778579337),(2,'ai','{\"uuid\":\"6d8fdb3d-e51c-4538-81f9-e2d407399404\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:1;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778579390,\"delay\":null}',0,NULL,1778579390,1778579390),(3,'ai','{\"uuid\":\"228749f7-f24a-4383-bc9d-5b70e1f5c8e6\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:2;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778579410,\"delay\":null}',0,NULL,1778579410,1778579410),(4,'ai','{\"uuid\":\"dceeff33-ff52-4abc-b82f-f2d06fec1ce5\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:3;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778579420,\"delay\":null}',0,NULL,1778579420,1778579420),(5,'ai','{\"uuid\":\"91f60ef0-5fdb-4506-9116-9bed7f87c4c6\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:4;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778579430,\"delay\":null}',0,NULL,1778579430,1778579430),(6,'ai','{\"uuid\":\"8c3cff9d-e0e6-4cb6-8842-fde6783fb581\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:5;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778631745,\"delay\":null}',0,NULL,1778631745,1778631745),(7,'ai','{\"uuid\":\"c3b83dac-e7b2-4b88-b849-f11c14e2aed0\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:6;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778631837,\"delay\":null}',0,NULL,1778631837,1778631837),(8,'ai','{\"uuid\":\"effc587d-a5c3-4eef-9c98-0b5aea342431\",\"displayName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\",\"command\":\"O:41:\\\"App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\\":2:{s:57:\\\"\\u0000App\\\\Jobs\\\\GenerateProductImageEmbeddingJob\\u0000productImageId\\\";i:7;s:5:\\\"queue\\\";s:2:\\\"ai\\\";}\",\"batchId\":null},\"createdAt\":1778653359,\"delay\":null}',0,NULL,1778653359,1778653359);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_02_090716_create_products_table',1),(5,'2026_03_02_144056_create_messages_table',1),(6,'2026_03_03_051422_add_schedule_at_to_products_table',1),(7,'2026_03_04_052716_add_auto_post_to_products_table',1),(8,'2026_04_23_230000_enhance_users_table_for_marketplace',1),(9,'2026_04_23_230100_create_stores_table',1),(10,'2026_04_23_230200_create_user_profiles_table',1),(11,'2026_04_23_231000_create_categories_table',1),(12,'2026_04_23_231100_create_product_conditions_table',1),(13,'2026_04_23_231200_enhance_products_table_for_marketplace',1),(14,'2026_04_23_231300_create_product_images_table',1),(15,'2026_04_23_232000_create_addresses_table',1),(16,'2026_04_23_232100_create_wishlists_table',1),(17,'2026_04_23_232200_create_carts_table',1),(18,'2026_04_23_232300_create_cart_items_table',1),(19,'2026_04_23_232400_create_orders_table',1),(20,'2026_04_23_232500_create_order_items_table',1),(21,'2026_04_23_232600_create_payments_table',1),(22,'2026_04_23_233000_create_conversations_table',1),(23,'2026_04_23_233100_create_conversation_participants_table',1),(24,'2026_04_23_233200_create_chat_messages_table',1),(25,'2026_04_23_234000_create_ai_search_logs_table',1),(26,'2026_04_23_234100_create_product_image_embeddings_table',1),(27,'2026_04_23_235000_create_social_accounts_table',1),(28,'2026_04_23_235100_create_social_posts_table',1),(29,'2026_04_23_235200_create_shared_products_table',1),(30,'2026_04_23_236000_create_scheduled_posts_table',1),(31,'2026_04_23_237000_create_activity_logs_table',1),(32,'2026_04_23_237100_make_social_account_tokens_nullable',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `seller_id` bigint(20) unsigned DEFAULT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_slug` varchar(255) DEFAULT NULL,
  `product_image_path` varchar(255) DEFAULT NULL,
  `product_condition_label` varchar(255) DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_amount` bigint(20) unsigned NOT NULL,
  `line_total_amount` bigint(20) unsigned NOT NULL,
  `fulfillment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  KEY `order_items_seller_id_index` (`seller_id`),
  KEY `order_items_fulfillment_status_index` (`fulfillment_status`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `store_id` bigint(20) unsigned DEFAULT NULL,
  `address_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `subtotal_amount` bigint(20) unsigned NOT NULL,
  `discount_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `shipping_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `total_amount` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `placed_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_address_id_foreign` (`address_id`),
  KEY `orders_buyer_id_index` (`buyer_id`),
  KEY `orders_store_id_index` (`store_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_placed_at_index` (`placed_at`),
  CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
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
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(255) NOT NULL,
  `provider_reference` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `amount` bigint(20) unsigned NOT NULL,
  `provider_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_payload`)),
  `failure_code` varchar(255) DEFAULT NULL,
  `failure_message` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_index` (`order_id`),
  KEY `payments_user_id_index` (`user_id`),
  KEY `payments_provider_index` (`provider`),
  KEY `payments_status_index` (`status`),
  KEY `payments_provider_reference_index` (`provider_reference`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_conditions`
--

DROP TABLE IF EXISTS `product_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_conditions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `rank` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_conditions_name_unique` (`name`),
  UNIQUE KEY `product_conditions_slug_unique` (`slug`),
  KEY `product_conditions_rank_index` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_conditions`
--

LOCK TABLES `product_conditions` WRITE;
/*!40000 ALTER TABLE `product_conditions` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_conditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_image_embeddings`
--

DROP TABLE IF EXISTS `product_image_embeddings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_image_embeddings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_image_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(255) NOT NULL,
  `embedding_model` varchar(255) DEFAULT NULL,
  `embedding_vector` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`embedding_vector`)),
  `vector_hash` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'completed',
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_image_embeddings_product_image_id_index` (`product_image_id`),
  KEY `product_image_embeddings_provider_index` (`provider`),
  KEY `product_image_embeddings_status_index` (`status`),
  KEY `product_image_embeddings_vector_hash_index` (`vector_hash`),
  CONSTRAINT `product_image_embeddings_product_image_id_foreign` FOREIGN KEY (`product_image_id`) REFERENCES `product_images` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_image_embeddings`
--

LOCK TABLES `product_image_embeddings` WRITE;
/*!40000 ALTER TABLE `product_image_embeddings` DISABLE KEYS */;
INSERT INTO `product_image_embeddings` VALUES (1,1,'huggingface-clip','facebook/data2vec-vision-base','[0.07160511926090732,0.20810178004035035,-0.10790575853534762,-0.3392562560198195,0.6456213160536162,0.09906338513231965,-0.020178971585503254,0.027751301385274364,-0.2898557470546477,-0.33206001863942536,-0.06287317889208342,-0.09111443924484774,-0.2784855163202333,0.18468710931188187,-0.007778714524421293,-0.5755500996888374,-0.1207251485387959,0.040274346090125174,0.0024120525275546168,1.405144528840399,0.3994805181864649,-0.13196943032727837,-0.3627171801771912,0.09902278770962115,0.3764631966020212,0.5321682563127221,0.23354376563542348,-0.07169786779455803,-0.3179382416939713,-0.22690678022944882,-0.006436570770305766,-0.11158409739001228,-0.06064246362289426,0.21813136882853085,0.3387444991695808,0.05066477297746155,-0.5501703451420676,-1.1531832133831106,-1.9797438703817765,0.6727056833466297,-0.6599941763100285,0.112056495177413,-0.19432602596391219,-0.6958680560925815,0.16954632202381395,0.005248590263769711,-0.23789308833544554,-0.02927429621169334,0.08618047984959888,-0.0636682714817828,-0.05505481412188684,0.6248448948756447,0.1331035270592609,0.10515439291461016,-0.10142849527564146,-0.4993810181217599,-0.013699308538875604,0.041418149446029104,-0.07887431346902359,-0.17878050436762158,0.08093297205449361,-0.1999390129671257,0.0236303171947797,-0.001338727257231033,0.052464927541066034,0.23292331022936094,0.3681177068182603,0.22736060324829394,0.2713332010047784,-0.2168064613526375,-0.42575664244513006,0.006926890557778403,-1.0949972987855752,-0.3532051985656729,2.1575651692254896,-0.26709652202307876,0.03347030267365239,0.10952347482148048,0.2158368296465047,-0.17491345081790374,0.4381401948087144,0.37490051815024494,-0.002478961469983767,-0.320416123651767,-0.04320944007486105,0.010324728326900947,0.027570037696997195,0.7469896303707271,-0.13313329182647943,-0.9321673565265264,-0.0329546407692437,-0.962067317631247,0.0538006725503595,0.36637948103619,0.03982177837492531,0.3983286613894527,-0.08335664174176734,-0.010318218945492494,0.020201812823584332,-0.7547682883846691,0.007619651916453397,0.13238724408113411,-0.043824530084295966,-0.1476526274357634,0.18244348257321505,0.8180086196064571,0.4916435589418193,-1.9356410014714933,-0.08890826973083446,0.19068459410641134,0.24024748516022015,0.0023247105350746268,0.02243322604170381,0.09411018827651169,0.17673149398375282,0.03765069628988279,0.2349192020735942,0.0981702713010984,0.5069386915582691,-1.3873124445462364,0.12675582394027507,0.034226264093699985,0.0349446771891556,-0.2421376701807225,0.17831710874378387,-0.14504681744194367,0.21730408219024916,0.14983204233774508,-0.14107612927850846,-0.08911145687532294,-0.2861260228730067,0.23118866751204495,0.12011449998913884,0.35208525524006656,0.0983899128754791,-0.059700690929631366,-0.3257934513175624,-0.0038965426447155984,0.20204564745614648,0.15866581482581113,-0.4432826556104618,0.00899694526439013,0.1826705877842892,0.7656281150222713,-0.018663904561578747,-0.8484188041727763,-0.15053192218753797,-0.308620920512934,-0.03419203611875549,-0.14515394778735463,-0.0535085020853658,-0.129585606256322,0.5053407993932316,0.08816095335387772,-1.6336223568866588,-0.11779037361510593,0.11139736021750696,0.15100944727785917,0.4582178476751134,0.03209345324820636,0.008273081702409492,0.8797561103291337,0.43432860851697364,-0.0740016254798514,-0.2385504185124584,-0.09734543759510546,0.3769644905486839,-0.19863714312343872,-0.025640561273156046,0.5108283799186557,0.06228068541424576,-0.03235595132493731,-0.09062505873489822,-0.20146465369974154,1.2814588337607191,-0.029448633409370574,-0.13713683283812164,0.14552805285639636,0.09608831462629971,0.7050444006844221,-0.2887198054184428,-0.6558755075950223,-0.6062079425440241,-0.030194719290853652,-0.1354984817612765,0.06303115496955584,0.3736536628634641,-0.24954482050631627,0.39944339093106895,-0.017044326323580924,-0.5037770500964471,-0.4436547830653599,-0.5322623110162378,-0.14342280733753657,-0.6442744644774776,-1.2180659649846366,0.6134420070740475,0.047656533775170366,-0.21262098903422655,0.7549479059818281,0.10094468214644857,-0.19168229034551074,0.15903278470109097,0.047971131582921704,-0.3991086289516637,0.7287437166110967,-0.21624566150460522,0.34839770905578954,-0.20673060007535185,-0.7751084592637753,-0.3740079439266438,0.15187430363987586,-0.2956695339883569,-0.0953917624062485,-0.12497221894897899,-0.6680672675380626,0.09053437892234704,0.06629397045835134,-0.1973893173880459,0.5401568643105816,-0.1815243718792864,0.009649901275007707,-0.0014649212523186249,-0.9878383799772804,-0.13748884253407154,-0.29940702399087665,0.07784620218415068,-0.01191105022033686,0.011783446217792553,-0.2849302641777936,-0.2539293538799112,0.373445126726493,0.12045904518894586,0.1707203089412323,-0.6223968992926899,-0.34617212766942185,-0.16905767258036844,0.2015863306491534,-0.04944403083323676,-0.11788218392078118,0.1328693789886169,-0.0248081519110081,-0.42819218899993233,-0.10236481405666978,-0.23844290358725898,0.13591305374405419,0.32408478764490917,0.24225440484159602,0.24451451256710488,-0.29938963476892294,-0.3469565641164524,0.14431335226523923,0.1068256622346397,0.11946813861938751,-0.21573776071229336,0.06726141686658824,-0.41347256431451807,0.05410809845067939,0.1986505547822051,-0.24392214522866631,0.05081762166972731,0.32145147777320865,0.1397839590179058,0.25185713804738247,0.13853128090773809,0.08261800033890117,-0.7341827986452275,-0.7856593812782753,0.31219993734987556,0.5933896500078327,-0.14196662158294,-0.6702913817226281,0.14610116710167775,-0.036429729227562845,0.16380417198982788,0.11352621051683776,-0.03546191061206332,0.2772871759048838,0.9572049139190446,-0.0044259527715296496,-0.11221553146104012,-0.5312525764637187,-0.1801084417621233,0.22973291478048877,0.16428268871856333,-0.3724219176662982,0.07263813626794097,0.3677061640960046,-1.0422085893486963,-0.022905474273914844,-0.05510690191021688,0.052616665435264706,0.0812490633192477,0.11930545126554806,-0.7993579855009081,0.08649327193840695,-0.13936196704369114,0.11509166340707673,0.140186988123247,-0.12443551200153637,0.03792639580604899,0.06556557165244582,-0.29921780527110786,-0.12290597336867891,0.04132359635382111,0.15427953473334838,0.17491458249922492,-0.21523210736166515,-0.3842764135596678,-0.03623767943135676,0.1090625752183741,0.13241613955062054,0.14984004737172266,0.6819888901598941,-0.11028404699592392,-0.18754400312693714,-0.13509232903916985,-0.1680617949069681,-0.07787839662112878,0.5421111016595727,-0.10153380157792383,0.0030521190545620956,-0.18605610326763955,0.46363506231192253,0.10672438457690364,-0.10367997252940186,-0.26951554140813533,-0.08462155204464866,0.11231284958267877,0.09486627374269863,-0.048389593786666366,-0.10324206318692092,0.014527060103314329,0.39559056490735356,0.0033961308935833243,-0.6075887507971773,0.4895523582504788,0.07579025853221981,-0.16234522361986736,0.11970392675824501,0.33781445970248225,-0.08872909004253993,0.5560500978723849,0.08947398990799252,-0.22358682210590256,0.580276458609263,0.14925092366656412,0.001464102795432667,-0.20610160644870573,-0.015001311444389034,-0.009545581786295571,0.4124456689324287,-0.20926811523661065,0.5246267126545754,0.49196946776594946,-0.5808598231790152,1.3779058888516753,0.21604866440039186,-0.33484543418880497,0.19345883233945457,-0.27922968785538516,0.08171144076773712,0.030940358623328057,-0.24866186951519662,0.4076893898719912,0.05025805805326406,0.010087878591626845,0.2529286515714629,-0.03610174785525335,-0.49714879932638134,0.25366633984338266,-0.572026131832133,-0.0901177014046358,0.0424956096670718,-0.6476575115619956,0.7919942423332539,-0.12396267570110822,-0.003082937882447303,-0.45579592381050893,0.1299015791424276,-0.03107150634342975,0.21700702350454282,0.24777117295209602,-0.25647072512230973,0.17801596498740885,-0.3332497624711129,0.17961541896208477,-0.007273499964488798,-0.14398523176627867,0.11804381835742424,0.13499741981222727,-0.3412201911716859,-0.05022838998049365,0.04568264878694462,0.0865708635638345,0.4039681892922422,-0.12841543999647126,-0.43932304990142207,0.041565303959922334,-0.12408185613443272,0.8684926114286188,-0.6316188962628999,0.7826081468312419,0.04885442515475874,-0.11763427967824949,-0.14624885912644228,-0.25981427786214667,-0.5631220360111615,-2.3324994001405193,-1.0167517553533896,0.8171941138860689,-0.04536503903138768,-0.06098358225323222,-0.2597234151104517,-0.1501542228258683,0.13359756870809847,0.6029569584262312,0.0919134612848922,0.2721769223200201,-0.07482375220400352,0.22963612849190643,-0.017766587464564342,-0.010684934438816107,0.10416885744484831,0.605703197154902,-0.07486844644113562,1.0309325652149728,-0.5220832639346655,-0.185254598788085,0.2525250802455094,-0.05666223819353405,-0.4539352422438297,0.6880454662809063,0.10645021108359684,-0.08480277790470513,-0.26908602999513825,-1.4190117030636158,-0.39485879827687886,0.33400187296863776,-0.09622721124421521,-0.5290686834241892,0.6870572264235334,1.405145011882435,0.23649788982126163,0.5397261778657386,-0.2344832281380639,0.21116761792318772,0.29611184429743703,0.00030003490782139566,0.11440234194387951,0.018079024840023387,-0.5148114528788282,0.1952501425189693,-0.4039836483859802,-0.7205529732057626,0.0930240890513491,0.3951238359337635,0.47890942823023624,-0.18359478187882033,-0.0926427951763351,0.41149459353253376,-0.41160811592120355,0.7861873098805093,-0.03697595358436199,0.47576648923970266,0.0489815534181127,-0.006148959916004637,0.0012869337844150614,0.45417323191815673,0.3234802289147327,-0.14730175491776978,0.2167037711266031,-0.05603285858509258,0.456785372192475,0.10991506914780735,0.10474465083245774,0.09757754613726925,-0.023388965963622374,0.5058682609708781,0.20203259657550754,0.4107455286093359,0.0991901524795153,0.5422192136591613,0.34965404049791204,0.12186012052999441,-0.06147302115137505,-0.1624543208588709,0.5968993676502522,0.13536631235519547,1.1661136903336773,-0.10231938711213903,0.17258344762864036,-0.36573522750212606,0.029650599780394388,0.016587346561163247,-0.05616837018110368,0.14767246464796857,0.05236814442199423,-0.25873558135670116,-0.2568655994916949,0.03885644756057796,0.06508937570961372,0.028403485137118302,-0.07306325783584323,-0.0514606499257332,-0.3512804870722552,0.15547459878660927,0.260862767263812,-0.4986969775751914,-0.019852925580985333,0.18463941104710102,-0.07861913686445654,-0.1960671306177052,-0.049466923066547264,0.0615018609116979,-0.8882323975986824,0.02644116084474295,-0.7042078127526692,0.2995038776738843,-0.015025362513042267,-0.04754313307183176,-0.06311752543865046,0.6963578361195281,-0.6499389776307617,-0.3654524566284215,0.543884804344684,-0.5705293131434372,-0.7337964311724311,0.33674458395657225,0.07121658358413567,-0.35805433886220234,0.3005513781560514,0.09291615793753302,-0.23612902995416432,-0.004945598901982234,0.05007032213524589,0.2532678651104877,0.26136841753682094,-0.07978173110960288,0.2030647500184114,0.8228584451584363,-0.2890213131937104,-0.4950508773693257,0.010927843883737619,-0.050180641064927964,-0.060692287834956865,-0.11758551209087148,-0.015070779895561222,0.8903210522172037,-0.028763938752809057,0.0752523293276081,0.29105057740858215,0.14755121167976087,0.480912969102563,-0.5761978480832528,0.09687415464322655,0.15904859762147144,0.19175097592609938,-0.08126434786475693,0.07984401983667525,-0.565070663003823,-0.04726856091455084,0.853549957062652,-0.057205622862116785,0.26709606535945485,0.1265167217715856,-0.009255758056520227,-0.06267457617048486,0.31120267877506497,-0.01170717733841257,0.19945123697938202,0.07397455735035184,-0.2974709560190502,0.0333048290298864,-0.04580156848444438,-0.0787700159099369,-0.014385175255368355,-0.17696812659795136,-0.1717719896259212,-0.2718753227012629,0.6214589924948364,-0.8182043393856377,-0.3546089549657657,0.01810111117305182,0.8401212548556517,-0.07824750566798343,-0.009424996588303505,-0.1389928344462237,0.27198846304084784,0.6208581772401248,-0.4696581750484136,0.12597971795561727,0.633243554437221,0.06809829091292754,0.3516664462185318,0.012842139262271108,0.5236675570515661,-0.05084743865689991,-0.020970448194828645,-0.15119988277441052,-0.22300285443091566,0.6027038868890158,0.13964924139152682,-0.0009425754687243004,0.051316304460905376,0.25571919603688115,0.14581074186501466,0.06132475513170667,0.14856525233427895,0.17199492398153063,-0.46947299038546947,0.05841909533220008,0.10461139666663512,-0.058227239171312506,-0.13251557682172826,0.9763158871295866,0.20598649394551888,-0.2780036845307698,-0.1727190181178036,-0.04902199021898692,0.04863538682876807,-0.00927471202523548,-0.26641020686649314,0.07640256054893249,0.08753033693288016,0.14415856806956098,-0.12637799298615304,-0.007952942011811709,0.9487490949887537,-0.02412293301564334,0.2901094885528806,0.7240759486434177,0.242663278192433,-0.4084483116775335,-0.09235641929240623,0.663722859383537,0.3129776161740804,0.07435151102106433,0.03918672926064467,-0.06894242460780356,0.0358825240976787,0.8601907957706459,-0.31329639875779147,-0.4210361486801944,-0.3408015387967172,-0.5352825694410178,-0.2900700288337802,0.09184491971323296,0.07710960361903278,0.10038654843279099,0.25936802637786666,0.3035375755682096,-0.8427037670499253,0.07217291343560074,0.016905414746001098,-0.044956857855916306,-0.20413825248703737,-0.5312919719995657,-0.5359789086931248,-0.4378263412733671,-0.04854562911878666,0.07184109152389245,-0.02545735582432752,0.11942250181023778,0.16091255251369038,-0.2672205901467466,0.510403051945206,-0.49034240553013714,0.0014085343502329539,-0.35924350411210415,-0.2049660651642016,-0.4338989988971634,-0.2641274547558201,-0.4281239698494449,0.07235946991778722,-0.33761711213520557,0.20756176898031684,0.20605590961563877,0.15189039302389445,0.4193633290771206,-0.045593299561411824,-0.05426236545769876,-0.1442431697638488,0.09763918252694917,0.34202734097368015,-0.4854047341109135,-0.1864371031470231,-0.5618333334320693,0.02025292630113548,-0.0034053746690952368,-0.022715596137934738,-0.4324681312611048,0.07969073859471261,0.37311764700612443,0.018265990062905146,0.062999458178827,-0.3834011810796269,-0.06574920058099147,-0.174397540087268,-0.2368533075450291,0.5459526846992552,0.042905064296157,0.2728022246474996,-0.057202715228127345,-0.04532093192872634,-0.2517097892723079,-0.39095725111786256,0.03748208475370093,0.10845855481059558,0.5618990223268567,0.16879574471711167,0.0924606586110348,-0.03646276851786244,0.055122656163553885,-0.06234470153402367,0.17223173651967177,0.8114970935896266,0.10000086965767412,-0.02998682422809265,-0.20650260256033692,-0.2185347599732609,0.0796610684285347,0.19045466480972859,-0.34605412750131437,0.510413342316252,0.15668430526594296,0.17964158922807327,-0.08967620819726685,0.4214231757196565,0.08451139324653166,0.6960312267481751,0.2506954510439764,0.19265586376038904,-0.2942845666778704,-0.00046434031615062113,0.34529951457312413,-0.018215839111149844,0.057283116123393214,0.8396103765229284,0.017879320346081595,0.09860010142644943,2.2174767940328812,-0.6088405454727505,-0.14310116395624625,0.6661385813896984,0.9241519188744768,0.0984401424500505,0.4780363321923627,0.036274254866308406,0.041800532998224046,-0.18548170419476057,-0.010868411357538198,0.07307904161368663,-0.05094601960644731,-0.19383628628285232,0.23853726167442174,-0.3668100374613695,-0.13802642783216262,0.37787341030827504,-0.030393359399249444,-0.11062366245929058,0.15448911008478242,0.4252578947647829,0.09037884677331354,0.24433850010663158,-0.0992719279212149,0.40594971367290367,0.3089832621602918]','51fb0b5e36bd7cf81cca2103c38b8246e5f7d7ddaa3daa2f8e35ea973b7cfdeb','completed','2026-05-13 07:19:20','2026-05-13 07:19:20','2026-05-13 07:19:20'),(2,2,'huggingface-clip','facebook/data2vec-vision-base','[-0.032445902278443545,0.11612024380344434,-0.08023713688427572,-0.42456714873235235,0.1266281928815099,0.05425149622830671,-0.048158904112081236,-0.04795425317101275,-0.1344500097232223,-0.0402623949976352,-0.3990775921209455,0.013342792558280496,-0.09499670841622103,0.29847128881108453,0.12629376124439354,0.46287938600245315,-0.07532307084207777,0.19035432372256395,-0.08547149609708166,0.8529897889094001,-0.9884595841471919,-0.09714680696071745,0.04854799645626681,-0.054114019282240196,-0.055182699585654936,0.12666949761445223,0.8325112066775333,-0.08434396143293547,-0.08158566520684092,0.1823080408240333,0.08704641708330196,-0.007669182248746853,-0.04475381910366001,-0.03419140344521097,-0.007317044965445352,0.15286591677804748,-0.4090023639418071,-0.09972084532160937,0.26596264416578835,-0.02763640797084793,-0.09131342711132186,0.09358245963967927,-0.07549381201855157,-0.06768781605985034,-0.21229678637296112,0.03210469666008039,0.14779856625250315,0.1536015874399215,-0.038943631732440434,0.005675183991725181,-0.15039117529668516,0.5276950739186994,-0.09631645692888231,0.014993224068436072,-0.0273235545955778,-0.0051169969652056085,-0.0045902966741436435,-0.04930122057566901,-0.2366510705114106,0.7112382611275774,0.0583730736548828,0.0006156182182517829,0.07992951790530348,-0.14233354876570753,0.014891857982182942,0.0632315309810438,0.6775419019035012,0.5432169667296206,0.16615062115742318,-0.24025514443891907,-0.26631827400477254,-0.003161493837738854,-0.6360171090594025,-0.06362625022228514,-0.3499517687643656,0.2704327446916779,0.2329350541811732,0.013248609733793808,0.0813430988520554,-0.06738654732217228,0.3083159662539383,0.3333489087028819,-0.2725846514102547,0.04983267000847411,-0.042196121952245653,-0.09677493905067694,-0.5471197476534901,-0.22203511826819292,-0.3609970473840379,-0.8913486539102561,0.7558260377647762,0.0987859603542324,0.14734857597219614,-0.1090602351213406,-0.15813389625373714,0.02228766040365935,0.044721471266301,-0.07697201497881759,0.26474252153833,-0.055353546848397724,0.09464282935029038,-0.31023954214861493,0.042892008462003646,0.22967843204349053,0.7497715821045304,0.287353933310174,0.25198018056373606,0.5787411046646971,-0.11320370820653507,0.08981307296243113,-0.06444906828296253,-0.40254894718716894,-0.02218043813978317,-0.0577434578147861,-0.06726002433457823,0.0022862689909531127,0.06160871201398106,0.14303904778039234,0.22034218759857457,-0.8432372365416344,-0.046165972942164936,-0.16513953258420536,0.05425991659270727,0.12256275307562033,-0.09118870732747963,-0.02206226773672326,0.005972165242073878,0.2943167265783523,-0.3983452515582067,0.023495479187866684,-0.2574144757753427,0.027090143878012896,-0.031906400745153086,0.6151636151341617,0.07819887342641774,0.09818833815832126,-0.759756633589933,-0.0663107236791733,-0.01892959917387287,0.04191351433575324,-0.18905882464856533,0.005888393191327601,0.11083269042085837,-0.15796772056604325,0.18109600316038177,0.42429395141032744,-0.08823904184351834,-0.255722998285685,-0.16941491471542056,0.06222851185227371,0.11487603068841044,-0.027899527269283186,0.03752017880407006,0.07386217596361574,-0.6662059150976578,-0.021405843191137208,4.630111338721995e-5,0.02754785250021419,0.08744509453379412,0.003310925964718722,0.26365580627940555,0.01919226484789234,0.5900054256890328,-0.04989149271029516,0.1357511492862087,0.04719311428885094,-0.1256835828083189,-0.4632752014673906,0.03993957153188188,-0.15030790263725455,0.08004235287745327,0.3178858607995612,0.31198527750637156,-0.0039953810646231784,-0.03148124473840755,0.03258409680685485,-0.276304227861382,0.07282366354117728,-0.2465979662915304,-0.6121678534906779,-0.33168692906271324,-0.38890316464990227,-0.49313642069825026,-0.024251220323237576,-0.01111113526125623,-0.1460222519430696,0.45027598415514414,0.06752591415516428,-0.0128427647202817,0.2925382642626324,-0.4285960766914665,0.06864226114245363,-0.722961086240895,0.007989601830143463,-1.0577999887619225,0.23903265632192608,-0.2196487291210194,-0.028079279649383414,-0.10693696668834855,0.711260178452594,-0.03205805222527774,-0.05199664794065195,-0.13822469618029629,0.36226844854935353,0.03150560181300737,0.37565299776677624,0.048286447842808694,-0.028133956037064645,-0.1939111856062191,0.452189877161255,-0.21890566925527669,0.04816296367036169,0.20544645155255262,0.08398866406961009,0.1722081234769631,-0.21338074180406286,0.1784420398531983,-0.01589886530959591,-0.17026027081963288,0.08923771978901973,-0.153391091054634,0.02113493196435774,0.09676159890888608,-0.3114609196025708,-0.17617976310932396,0.016686824642637092,-0.3820228116317335,0.12263442793870487,0.22485640390745368,-0.04921892446319237,0.06256146638091006,0.4641206677409768,0.09149145022425256,0.034295761805301916,-1.0009421945731558,0.11666732635951794,-0.23202652691007794,-0.005621429657750305,-0.07260376996381993,-0.10417527321134848,0.09649939114781307,-0.3248857287572594,-0.2921210392969211,-0.2651818204578958,-0.19351945495348763,0.11139827071438879,-0.1439608568461555,-0.43877021037987646,0.18559920565174362,0.19599715042693852,0.11263570439207561,0.7156607896718314,0.21072892917126304,0.11292619493684783,-0.07569513375383949,0.20142456378594517,0.09106641548312255,0.08151277775526387,-0.02971150316439834,0.1934958858566765,-0.3234377698535525,-0.05576316847083003,0.4938524684568555,-0.08273969437492075,0.07344490051657189,0.030613264793158994,-0.7388754769081846,-0.6652592627643631,0.1629515454163633,0.23624696140886678,-0.021143704521301945,-0.027302057924769054,0.12168507930615251,-0.13399403165496393,-0.012283932937658983,0.36016513268891154,-0.07578123672807714,0.35526500913423253,0.5495613688451763,-0.13317412214689334,-0.002076998358322922,-0.4538040469556593,-0.22591546645135677,-0.02878710707096468,0.07380874560369406,0.0005903781597327611,-0.15434809896691942,0.2137651866243391,-1.3493225153554516,-0.03327245528184511,0.2944288539539474,-0.02913415998097528,0.0640858843421645,-0.08284944909752398,0.22596425871154396,-0.16285607318020412,0.45248235509196666,-0.1269208034438431,0.15667157075107854,0.04488493252748288,-0.3458558015128229,-0.01678372475703904,0.009769641706509619,-0.11407485609663755,0.07718765985535052,-0.00608110793648751,-0.01946407147366459,0.13184250206899295,0.02333070435986717,0.2197587752140359,-0.32442935956432245,-0.06032425808474459,-0.02217795840050283,-0.2571195764295747,-0.1978139848481429,0.03953060264956811,0.24146592442589213,0.04248590527225267,-0.09094248410025647,0.10038107089925236,0.0035565473555308307,-0.2703699741339982,-0.026839560781827736,0.14453702600203633,0.04530421644611743,-0.0763010732359182,-0.2101452033824954,-0.28581303404112285,0.28338236736989375,0.6351016053801691,-0.08804844088260283,-0.04170465639314903,0.23184689688750396,0.16857744102853203,0.13092615900249197,0.36699828072681523,0.3249760621580707,-0.015888925872242013,-0.048914273312022236,-0.16689780112420952,0.1712917034717532,0.26692802245081,0.1043840479194352,-0.0879942325716791,-0.6844290836529835,0.03785268607996121,0.07279515315894969,0.07599238030846481,0.06493647062956287,0.05404432848164443,-0.009975500938454217,0.21789166851496314,0.06431287672396674,0.25724402455811574,0.7492892552583199,0.4613704608060741,-0.2200418503572077,-0.02185445894229552,-0.22990391147795705,-0.03766831639340989,-0.05687635434971092,-0.026318315618676206,0.09883203964886081,0.20485920136378424,0.29296397503617877,0.041401825630139125,0.1104948285135067,0.10716103674810815,-0.03705437396160152,0.4664610846034313,-0.2822088432429727,-0.015000281555266096,0.23886288530406008,0.024033027357152348,-0.23442561565638345,0.5382735380467955,-0.043159323342636155,0.09164300772685292,0.18409987390431373,0.3856575431439288,0.05392945741053888,0.15674195713706335,0.1254643992556916,-0.032274033233398086,0.31431820969094953,-0.052791296217240734,0.034298188395817115,0.06668306904671203,-0.1394370793256209,0.039496948916171344,0.0996424407360787,-0.10365465813585874,0.15189733177359654,-0.036437988969104995,0.17807232303681386,-0.8160817461173395,-0.1544550466871239,-0.32777886474646895,0.07433184702363016,0.025484951183159698,0.012916804158937204,-0.06511627093931367,-0.2317143939624583,-0.1376325517330262,-0.16380691251169116,-0.3176593792572769,-0.3133680168828847,-0.09795902675575563,-1.1150769814281596,0.0601054625408393,-0.2276380763739007,-0.08694813127374172,-0.10854975196714915,-0.030946499883755096,-0.13007058168118488,0.020932273329401077,0.01446689751122128,-0.020552669076821035,0.016563781086227806,-0.057230529585749215,-0.2705072397401623,0.4974625464209691,0.09098798413595496,0.18486055363336612,0.5108296545777886,-0.28273078594545875,0.16227519443863767,-0.11751376935824524,0.11451064840625134,0.0024275259542642963,0.16264093134122035,-0.46908199446285287,-0.18638565993825268,-0.0494688368865719,-0.043314774039964934,0.36887699244000244,-0.02863553076448288,0.3005108649884989,-0.07065048740218219,-0.3009059647556754,-0.571374444346544,1.0377328981182108,-0.6985675620534434,0.13583707627556688,-0.010469019206165133,-0.0639018341511852,0.13660230612037183,-0.21380165386454528,-0.06557939928775502,0.281589186371781,-0.024474422015110266,-0.25236972252280465,0.05555815449153513,-0.18766617725678922,0.40633288045238874,-0.17876987134728126,0.10186145869396901,0.35461832609897487,0.44905280282125254,-0.27169960876454025,-0.0861002822456229,0.20530566647010595,0.9066427847616336,-0.15144349213572558,0.15036456826066735,0.12414452827063999,0.023616169226535498,-0.024396834710050316,0.47899248129670474,-0.07639653307236825,0.1575890225919701,0.2108055718602414,0.013976753698508279,0.1596863145897458,-0.09711118443540491,-0.10267851146488796,0.06448523726977341,0.02368655348454276,0.04410225691065207,0.06917748195922882,0.0207141212431175,0.11681553918861212,-0.006726730824684545,0.0685047406522923,0.12666325389864372,-0.04469737965084121,0.035602155844751876,0.5774450556027613,0.1530713364552839,-0.6947386823757079,-0.16869158301972856,0.16961120160445475,-0.14310066716634398,-0.050191328866728745,0.026718100461607143,0.12228074849024105,-0.027580779018636963,0.05804375127704842,-0.08842578513144757,0.0784190379099112,0.005476435094476124,-0.17353754187372708,0.07409834426823002,-0.12117800055672108,0.7140004100504941,-0.17699531074861527,-0.4710171825198107,-0.005340835214285227,0.0883763504562058,0.16967278647075199,-0.3310902103602054,0.02825712916490901,-0.0857724199824385,0.08035714146893869,-0.08560231890586124,-0.30201315032102544,0.2912500453651954,-0.29103578307942446,0.03555475219819449,0.2519407248323036,-0.23754644327735538,-0.05497446328845953,1.1761909329573514,0.08861452609839551,0.054461404119221124,0.04327402325337611,0.35906445622198324,-1.8340358697067058,-0.2280650163910867,0.08984963122597509,-0.2409573788855102,-0.0049157094114062935,-0.0038603642314214786,0.07993903209912995,-0.11915863912108207,0.029898024016109574,0.21802617042552358,0.11092487744500076,0.2662234918739136,-0.14397695814590278,0.2189241044824886,0.01803708678135958,0.614888697504515,0.004219046249030735,-0.07053262074265232,0.011686118645460562,-0.08832805781822585,-0.2849528904858596,-0.056475362616359506,0.2191718330225366,0.07851732627023011,-0.2810202467640588,0.02045720968239463,0.3409172097022461,-0.5677495289234643,0.048016028427329806,0.3219673737173257,-0.026562754976324415,0.08378522002332026,0.11395584488155197,-0.3095017228320449,0.13365111861798704,0.6547086014161785,0.08496274783389482,0.4483337671150865,0.0448548809878131,0.06828670047160337,0.03172150292897096,-0.1302134999012584,-0.06365512669265806,0.14633894617655102,0.06792214601080163,-0.13763199790711378,-0.13108917304283943,0.02767513932241836,-0.025897849585180203,0.09611397283860874,0.08113949017204476,0.16786427559392517,-0.9024490602881775,0.7585255611442068,0.3763187717697181,0.4804614854759069,-0.04549762604493402,0.6581211664269532,-0.10790176300194852,-0.5393898620206371,-0.13649800671769782,0.09476153580365504,-0.23167739081879615,-0.10194697569013304,0.2828165733723471,0.05005516921912277,-0.0955661037714613,-0.05805768815535806,-0.15733463330156533,0.3627006581837907,-0.043905108326781295,0.6581168353349576,0.023022384594045674,-0.0941638152533433,0.07726175464254799,0.154650233240401,0.5069844792809735,0.04977117821474933,0.08167349624605305,-0.14001867304015952,-0.07562577922776492,-0.0272337016442715,0.056379019422811184,-0.39592692676723606,0.14334554986988057,0.5516408232801835,-0.3494226907564299,-0.18762644192648267,0.07333885811666395,0.1430667541769858,-0.20496995296356738,-0.23170877582639418,-0.056539795419371444,-0.5042905382254175,0.04240259726538895,-0.4674497508552625,0.091309371681501,0.161060545016742,0.05649739488200545,0.08335666506206096,-0.08162846847237584,0.05287444388061327,-0.24319460669173126,0.13360055355026582,0.3401078593569718,-0.1927223496651986,-0.013721890102239884,-0.03297455983146609,0.3342492202471771,-0.10253119115070038,0.1261000043528183,-0.023942541464605383,-0.020763618278412888,0.07034650541219133,0.2164600728535292,0.04694105489392231,0.42686704194957986,-0.264920254365593,-0.2104781079312862,0.15409357301999582,0.039515489624371986,-0.08493994315967984,0.035127715606521344,-0.0396972727775101,0.08573359769750102,-0.12949638573575745,-0.284757969588949,0.01740156738696204,-0.15248417716619894,-0.020587962160955375,-0.39314289242596495,-0.3058127874070022,-0.026864272579554805,0.0573575481904498,-0.09643120050482301,-0.036392111632641146,0.08695271803269418,0.15102732091785176,-0.29434364345539266,-0.0749516096639773,-0.41245536829066004,-0.5266318255562694,-0.03258771579688992,-0.13813737616578808,0.2028037292225674,-0.03135927700612524,-0.2595481540520822,-0.013376092629833383,0.013678204722059559,-0.06226730211738374,-0.10856070800514202,0.15187267949485556,0.04705910381700295,0.03989101246207347,-0.10571192615094646,-0.0006100410234286783,0.23636826619400694,-0.0439939713956514,-0.27723504812811234,0.1630844934530282,0.3837938968583866,-0.006124204918174877,-0.10648308140355715,0.6438742682810809,-0.37592471435283153,0.15215559603367473,0.32732220449092503,-0.12443349718346396,0.11754540567106667,0.00037212772613986946,0.09944480208580755,0.050550854792948435,-0.09210648475986608,-0.35416265324797885,-0.11187005760904571,0.1599518406845459,-0.09894147960984427,0.26536393743410214,-0.17540651892111062,-0.5517261521276181,0.14938974817316544,0.027035042855916378,0.08411296659475281,0.02120866553342622,-0.1503720567242578,0.05817589534654608,-0.10914671434598341,-0.054106889235424255,-0.06284672310126728,0.2887326667917334,0.1314097901163292,0.045415295200917236,-0.3065199768101279,0.13500175124322664,0.015183690855005368,-0.1834197514359532,-0.011093163310885808,0.25917771626452957,-0.047263211649866195,-0.227999370114944,0.41087200192895335,0.05229869836991296,0.048158509408591815,1.3882543181783051,0.20963057870580695,-0.017247246614383146,-0.15353362767562403,-0.06606865850697403,0.10455279401562598,-0.21681179745042423,-0.021329462868019674,0.009976243340235374,0.03197705694403367,1.4258932800311122,1.1485224939353273,0.03415679617201068,-0.10853006616370696,0.504309830370449,0.5131968767169466,0.08230292443942448,0.5397409484508982,-0.0430164339711067,-0.05056566905955959,0.06249745382553883,-0.05337017481475312,0.01531876022998376,-0.09588056296017966,-0.09929695087402284,0.03733700316406427,-0.07093250206411642,-0.09152446370595489,0.1938561274644063,-0.13759200510833067,-0.11735331456042201,-0.07834137653043201,0.23404605225671726,0.015262277266199742,0.13256378608432523,1.5080633246656603,0.32666483898186194,0.1796294532238794]','6bd7af474dedb9799db552e8fae4d82b7245e2d7f27ccb99d943342687a1a6fb','completed','2026-05-13 07:19:22','2026-05-13 07:19:22','2026-05-13 07:19:22'),(3,3,'huggingface-clip','facebook/data2vec-vision-base','[-0.032445902278443545,0.11612024380344434,-0.08023713688427572,-0.42456714873235235,0.1266281928815099,0.05425149622830671,-0.048158904112081236,-0.04795425317101275,-0.1344500097232223,-0.0402623949976352,-0.3990775921209455,0.013342792558280496,-0.09499670841622103,0.29847128881108453,0.12629376124439354,0.46287938600245315,-0.07532307084207777,0.19035432372256395,-0.08547149609708166,0.8529897889094001,-0.9884595841471919,-0.09714680696071745,0.04854799645626681,-0.054114019282240196,-0.055182699585654936,0.12666949761445223,0.8325112066775333,-0.08434396143293547,-0.08158566520684092,0.1823080408240333,0.08704641708330196,-0.007669182248746853,-0.04475381910366001,-0.03419140344521097,-0.007317044965445352,0.15286591677804748,-0.4090023639418071,-0.09972084532160937,0.26596264416578835,-0.02763640797084793,-0.09131342711132186,0.09358245963967927,-0.07549381201855157,-0.06768781605985034,-0.21229678637296112,0.03210469666008039,0.14779856625250315,0.1536015874399215,-0.038943631732440434,0.005675183991725181,-0.15039117529668516,0.5276950739186994,-0.09631645692888231,0.014993224068436072,-0.0273235545955778,-0.0051169969652056085,-0.0045902966741436435,-0.04930122057566901,-0.2366510705114106,0.7112382611275774,0.0583730736548828,0.0006156182182517829,0.07992951790530348,-0.14233354876570753,0.014891857982182942,0.0632315309810438,0.6775419019035012,0.5432169667296206,0.16615062115742318,-0.24025514443891907,-0.26631827400477254,-0.003161493837738854,-0.6360171090594025,-0.06362625022228514,-0.3499517687643656,0.2704327446916779,0.2329350541811732,0.013248609733793808,0.0813430988520554,-0.06738654732217228,0.3083159662539383,0.3333489087028819,-0.2725846514102547,0.04983267000847411,-0.042196121952245653,-0.09677493905067694,-0.5471197476534901,-0.22203511826819292,-0.3609970473840379,-0.8913486539102561,0.7558260377647762,0.0987859603542324,0.14734857597219614,-0.1090602351213406,-0.15813389625373714,0.02228766040365935,0.044721471266301,-0.07697201497881759,0.26474252153833,-0.055353546848397724,0.09464282935029038,-0.31023954214861493,0.042892008462003646,0.22967843204349053,0.7497715821045304,0.287353933310174,0.25198018056373606,0.5787411046646971,-0.11320370820653507,0.08981307296243113,-0.06444906828296253,-0.40254894718716894,-0.02218043813978317,-0.0577434578147861,-0.06726002433457823,0.0022862689909531127,0.06160871201398106,0.14303904778039234,0.22034218759857457,-0.8432372365416344,-0.046165972942164936,-0.16513953258420536,0.05425991659270727,0.12256275307562033,-0.09118870732747963,-0.02206226773672326,0.005972165242073878,0.2943167265783523,-0.3983452515582067,0.023495479187866684,-0.2574144757753427,0.027090143878012896,-0.031906400745153086,0.6151636151341617,0.07819887342641774,0.09818833815832126,-0.759756633589933,-0.0663107236791733,-0.01892959917387287,0.04191351433575324,-0.18905882464856533,0.005888393191327601,0.11083269042085837,-0.15796772056604325,0.18109600316038177,0.42429395141032744,-0.08823904184351834,-0.255722998285685,-0.16941491471542056,0.06222851185227371,0.11487603068841044,-0.027899527269283186,0.03752017880407006,0.07386217596361574,-0.6662059150976578,-0.021405843191137208,4.630111338721995e-5,0.02754785250021419,0.08744509453379412,0.003310925964718722,0.26365580627940555,0.01919226484789234,0.5900054256890328,-0.04989149271029516,0.1357511492862087,0.04719311428885094,-0.1256835828083189,-0.4632752014673906,0.03993957153188188,-0.15030790263725455,0.08004235287745327,0.3178858607995612,0.31198527750637156,-0.0039953810646231784,-0.03148124473840755,0.03258409680685485,-0.276304227861382,0.07282366354117728,-0.2465979662915304,-0.6121678534906779,-0.33168692906271324,-0.38890316464990227,-0.49313642069825026,-0.024251220323237576,-0.01111113526125623,-0.1460222519430696,0.45027598415514414,0.06752591415516428,-0.0128427647202817,0.2925382642626324,-0.4285960766914665,0.06864226114245363,-0.722961086240895,0.007989601830143463,-1.0577999887619225,0.23903265632192608,-0.2196487291210194,-0.028079279649383414,-0.10693696668834855,0.711260178452594,-0.03205805222527774,-0.05199664794065195,-0.13822469618029629,0.36226844854935353,0.03150560181300737,0.37565299776677624,0.048286447842808694,-0.028133956037064645,-0.1939111856062191,0.452189877161255,-0.21890566925527669,0.04816296367036169,0.20544645155255262,0.08398866406961009,0.1722081234769631,-0.21338074180406286,0.1784420398531983,-0.01589886530959591,-0.17026027081963288,0.08923771978901973,-0.153391091054634,0.02113493196435774,0.09676159890888608,-0.3114609196025708,-0.17617976310932396,0.016686824642637092,-0.3820228116317335,0.12263442793870487,0.22485640390745368,-0.04921892446319237,0.06256146638091006,0.4641206677409768,0.09149145022425256,0.034295761805301916,-1.0009421945731558,0.11666732635951794,-0.23202652691007794,-0.005621429657750305,-0.07260376996381993,-0.10417527321134848,0.09649939114781307,-0.3248857287572594,-0.2921210392969211,-0.2651818204578958,-0.19351945495348763,0.11139827071438879,-0.1439608568461555,-0.43877021037987646,0.18559920565174362,0.19599715042693852,0.11263570439207561,0.7156607896718314,0.21072892917126304,0.11292619493684783,-0.07569513375383949,0.20142456378594517,0.09106641548312255,0.08151277775526387,-0.02971150316439834,0.1934958858566765,-0.3234377698535525,-0.05576316847083003,0.4938524684568555,-0.08273969437492075,0.07344490051657189,0.030613264793158994,-0.7388754769081846,-0.6652592627643631,0.1629515454163633,0.23624696140886678,-0.021143704521301945,-0.027302057924769054,0.12168507930615251,-0.13399403165496393,-0.012283932937658983,0.36016513268891154,-0.07578123672807714,0.35526500913423253,0.5495613688451763,-0.13317412214689334,-0.002076998358322922,-0.4538040469556593,-0.22591546645135677,-0.02878710707096468,0.07380874560369406,0.0005903781597327611,-0.15434809896691942,0.2137651866243391,-1.3493225153554516,-0.03327245528184511,0.2944288539539474,-0.02913415998097528,0.0640858843421645,-0.08284944909752398,0.22596425871154396,-0.16285607318020412,0.45248235509196666,-0.1269208034438431,0.15667157075107854,0.04488493252748288,-0.3458558015128229,-0.01678372475703904,0.009769641706509619,-0.11407485609663755,0.07718765985535052,-0.00608110793648751,-0.01946407147366459,0.13184250206899295,0.02333070435986717,0.2197587752140359,-0.32442935956432245,-0.06032425808474459,-0.02217795840050283,-0.2571195764295747,-0.1978139848481429,0.03953060264956811,0.24146592442589213,0.04248590527225267,-0.09094248410025647,0.10038107089925236,0.0035565473555308307,-0.2703699741339982,-0.026839560781827736,0.14453702600203633,0.04530421644611743,-0.0763010732359182,-0.2101452033824954,-0.28581303404112285,0.28338236736989375,0.6351016053801691,-0.08804844088260283,-0.04170465639314903,0.23184689688750396,0.16857744102853203,0.13092615900249197,0.36699828072681523,0.3249760621580707,-0.015888925872242013,-0.048914273312022236,-0.16689780112420952,0.1712917034717532,0.26692802245081,0.1043840479194352,-0.0879942325716791,-0.6844290836529835,0.03785268607996121,0.07279515315894969,0.07599238030846481,0.06493647062956287,0.05404432848164443,-0.009975500938454217,0.21789166851496314,0.06431287672396674,0.25724402455811574,0.7492892552583199,0.4613704608060741,-0.2200418503572077,-0.02185445894229552,-0.22990391147795705,-0.03766831639340989,-0.05687635434971092,-0.026318315618676206,0.09883203964886081,0.20485920136378424,0.29296397503617877,0.041401825630139125,0.1104948285135067,0.10716103674810815,-0.03705437396160152,0.4664610846034313,-0.2822088432429727,-0.015000281555266096,0.23886288530406008,0.024033027357152348,-0.23442561565638345,0.5382735380467955,-0.043159323342636155,0.09164300772685292,0.18409987390431373,0.3856575431439288,0.05392945741053888,0.15674195713706335,0.1254643992556916,-0.032274033233398086,0.31431820969094953,-0.052791296217240734,0.034298188395817115,0.06668306904671203,-0.1394370793256209,0.039496948916171344,0.0996424407360787,-0.10365465813585874,0.15189733177359654,-0.036437988969104995,0.17807232303681386,-0.8160817461173395,-0.1544550466871239,-0.32777886474646895,0.07433184702363016,0.025484951183159698,0.012916804158937204,-0.06511627093931367,-0.2317143939624583,-0.1376325517330262,-0.16380691251169116,-0.3176593792572769,-0.3133680168828847,-0.09795902675575563,-1.1150769814281596,0.0601054625408393,-0.2276380763739007,-0.08694813127374172,-0.10854975196714915,-0.030946499883755096,-0.13007058168118488,0.020932273329401077,0.01446689751122128,-0.020552669076821035,0.016563781086227806,-0.057230529585749215,-0.2705072397401623,0.4974625464209691,0.09098798413595496,0.18486055363336612,0.5108296545777886,-0.28273078594545875,0.16227519443863767,-0.11751376935824524,0.11451064840625134,0.0024275259542642963,0.16264093134122035,-0.46908199446285287,-0.18638565993825268,-0.0494688368865719,-0.043314774039964934,0.36887699244000244,-0.02863553076448288,0.3005108649884989,-0.07065048740218219,-0.3009059647556754,-0.571374444346544,1.0377328981182108,-0.6985675620534434,0.13583707627556688,-0.010469019206165133,-0.0639018341511852,0.13660230612037183,-0.21380165386454528,-0.06557939928775502,0.281589186371781,-0.024474422015110266,-0.25236972252280465,0.05555815449153513,-0.18766617725678922,0.40633288045238874,-0.17876987134728126,0.10186145869396901,0.35461832609897487,0.44905280282125254,-0.27169960876454025,-0.0861002822456229,0.20530566647010595,0.9066427847616336,-0.15144349213572558,0.15036456826066735,0.12414452827063999,0.023616169226535498,-0.024396834710050316,0.47899248129670474,-0.07639653307236825,0.1575890225919701,0.2108055718602414,0.013976753698508279,0.1596863145897458,-0.09711118443540491,-0.10267851146488796,0.06448523726977341,0.02368655348454276,0.04410225691065207,0.06917748195922882,0.0207141212431175,0.11681553918861212,-0.006726730824684545,0.0685047406522923,0.12666325389864372,-0.04469737965084121,0.035602155844751876,0.5774450556027613,0.1530713364552839,-0.6947386823757079,-0.16869158301972856,0.16961120160445475,-0.14310066716634398,-0.050191328866728745,0.026718100461607143,0.12228074849024105,-0.027580779018636963,0.05804375127704842,-0.08842578513144757,0.0784190379099112,0.005476435094476124,-0.17353754187372708,0.07409834426823002,-0.12117800055672108,0.7140004100504941,-0.17699531074861527,-0.4710171825198107,-0.005340835214285227,0.0883763504562058,0.16967278647075199,-0.3310902103602054,0.02825712916490901,-0.0857724199824385,0.08035714146893869,-0.08560231890586124,-0.30201315032102544,0.2912500453651954,-0.29103578307942446,0.03555475219819449,0.2519407248323036,-0.23754644327735538,-0.05497446328845953,1.1761909329573514,0.08861452609839551,0.054461404119221124,0.04327402325337611,0.35906445622198324,-1.8340358697067058,-0.2280650163910867,0.08984963122597509,-0.2409573788855102,-0.0049157094114062935,-0.0038603642314214786,0.07993903209912995,-0.11915863912108207,0.029898024016109574,0.21802617042552358,0.11092487744500076,0.2662234918739136,-0.14397695814590278,0.2189241044824886,0.01803708678135958,0.614888697504515,0.004219046249030735,-0.07053262074265232,0.011686118645460562,-0.08832805781822585,-0.2849528904858596,-0.056475362616359506,0.2191718330225366,0.07851732627023011,-0.2810202467640588,0.02045720968239463,0.3409172097022461,-0.5677495289234643,0.048016028427329806,0.3219673737173257,-0.026562754976324415,0.08378522002332026,0.11395584488155197,-0.3095017228320449,0.13365111861798704,0.6547086014161785,0.08496274783389482,0.4483337671150865,0.0448548809878131,0.06828670047160337,0.03172150292897096,-0.1302134999012584,-0.06365512669265806,0.14633894617655102,0.06792214601080163,-0.13763199790711378,-0.13108917304283943,0.02767513932241836,-0.025897849585180203,0.09611397283860874,0.08113949017204476,0.16786427559392517,-0.9024490602881775,0.7585255611442068,0.3763187717697181,0.4804614854759069,-0.04549762604493402,0.6581211664269532,-0.10790176300194852,-0.5393898620206371,-0.13649800671769782,0.09476153580365504,-0.23167739081879615,-0.10194697569013304,0.2828165733723471,0.05005516921912277,-0.0955661037714613,-0.05805768815535806,-0.15733463330156533,0.3627006581837907,-0.043905108326781295,0.6581168353349576,0.023022384594045674,-0.0941638152533433,0.07726175464254799,0.154650233240401,0.5069844792809735,0.04977117821474933,0.08167349624605305,-0.14001867304015952,-0.07562577922776492,-0.0272337016442715,0.056379019422811184,-0.39592692676723606,0.14334554986988057,0.5516408232801835,-0.3494226907564299,-0.18762644192648267,0.07333885811666395,0.1430667541769858,-0.20496995296356738,-0.23170877582639418,-0.056539795419371444,-0.5042905382254175,0.04240259726538895,-0.4674497508552625,0.091309371681501,0.161060545016742,0.05649739488200545,0.08335666506206096,-0.08162846847237584,0.05287444388061327,-0.24319460669173126,0.13360055355026582,0.3401078593569718,-0.1927223496651986,-0.013721890102239884,-0.03297455983146609,0.3342492202471771,-0.10253119115070038,0.1261000043528183,-0.023942541464605383,-0.020763618278412888,0.07034650541219133,0.2164600728535292,0.04694105489392231,0.42686704194957986,-0.264920254365593,-0.2104781079312862,0.15409357301999582,0.039515489624371986,-0.08493994315967984,0.035127715606521344,-0.0396972727775101,0.08573359769750102,-0.12949638573575745,-0.284757969588949,0.01740156738696204,-0.15248417716619894,-0.020587962160955375,-0.39314289242596495,-0.3058127874070022,-0.026864272579554805,0.0573575481904498,-0.09643120050482301,-0.036392111632641146,0.08695271803269418,0.15102732091785176,-0.29434364345539266,-0.0749516096639773,-0.41245536829066004,-0.5266318255562694,-0.03258771579688992,-0.13813737616578808,0.2028037292225674,-0.03135927700612524,-0.2595481540520822,-0.013376092629833383,0.013678204722059559,-0.06226730211738374,-0.10856070800514202,0.15187267949485556,0.04705910381700295,0.03989101246207347,-0.10571192615094646,-0.0006100410234286783,0.23636826619400694,-0.0439939713956514,-0.27723504812811234,0.1630844934530282,0.3837938968583866,-0.006124204918174877,-0.10648308140355715,0.6438742682810809,-0.37592471435283153,0.15215559603367473,0.32732220449092503,-0.12443349718346396,0.11754540567106667,0.00037212772613986946,0.09944480208580755,0.050550854792948435,-0.09210648475986608,-0.35416265324797885,-0.11187005760904571,0.1599518406845459,-0.09894147960984427,0.26536393743410214,-0.17540651892111062,-0.5517261521276181,0.14938974817316544,0.027035042855916378,0.08411296659475281,0.02120866553342622,-0.1503720567242578,0.05817589534654608,-0.10914671434598341,-0.054106889235424255,-0.06284672310126728,0.2887326667917334,0.1314097901163292,0.045415295200917236,-0.3065199768101279,0.13500175124322664,0.015183690855005368,-0.1834197514359532,-0.011093163310885808,0.25917771626452957,-0.047263211649866195,-0.227999370114944,0.41087200192895335,0.05229869836991296,0.048158509408591815,1.3882543181783051,0.20963057870580695,-0.017247246614383146,-0.15353362767562403,-0.06606865850697403,0.10455279401562598,-0.21681179745042423,-0.021329462868019674,0.009976243340235374,0.03197705694403367,1.4258932800311122,1.1485224939353273,0.03415679617201068,-0.10853006616370696,0.504309830370449,0.5131968767169466,0.08230292443942448,0.5397409484508982,-0.0430164339711067,-0.05056566905955959,0.06249745382553883,-0.05337017481475312,0.01531876022998376,-0.09588056296017966,-0.09929695087402284,0.03733700316406427,-0.07093250206411642,-0.09152446370595489,0.1938561274644063,-0.13759200510833067,-0.11735331456042201,-0.07834137653043201,0.23404605225671726,0.015262277266199742,0.13256378608432523,1.5080633246656603,0.32666483898186194,0.1796294532238794]','6bd7af474dedb9799db552e8fae4d82b7245e2d7f27ccb99d943342687a1a6fb','completed','2026-05-13 07:19:24','2026-05-13 07:19:24','2026-05-13 07:19:24'),(4,4,'huggingface-clip','facebook/data2vec-vision-base','[-0.12463738934756233,0.08520446049340831,0.10119199620230067,-0.23808841977675024,0.071588240728151,0.032428323925321516,-0.06784906464529597,0.05876204007639677,0.0062513113415417106,0.01629532170529255,-0.3381180285776346,-0.090033097237455,-0.44737854751706274,0.14622009829748045,-0.142976308969821,0.4519178118207492,0.16257560452467615,-0.008555253213602426,0.2438278828982765,0.9603920676438186,-0.14544944969528276,-0.09393272264494187,-0.05780096099547082,-0.18778869627885103,-0.0410463978586066,0.24505443916641895,0.8011539599995323,-0.05336246713904338,-0.13372195893922245,0.11313588261547579,0.002437932222820393,0.0242825505679459,0.04967013589427972,0.4499747659680126,-0.0470850860562913,0.07734708485623755,-0.12702731999242442,-0.3735796603407276,-1.5071513220563832,-0.6599114523631926,-0.03960558016628481,0.18022715866797354,0.1333364277692786,-0.11786384283219875,-0.35598174781600006,-0.21183591245790012,0.275425949299396,0.18453037321049282,-0.1001321291539175,-0.10351825391798077,-0.15361165577252656,0.9179180148837681,-0.006839548788046131,-0.08893235776393774,0.14820109434581838,0.002043333722439212,-0.062018777780367185,0.058122254469902324,-0.2662490063245281,0.4501331597992082,-0.04038312504904789,-0.07150036711742165,0.1550592751464414,-0.008772361527644996,-0.0010470473071616995,0.14280347118198783,0.1873830804450031,-0.6440886701102638,0.21418378758835158,-0.33178295278564757,-0.13017530681395212,-0.03331201256629254,-0.1433307523874632,0.0852300624565817,0.300091338696119,-0.23701206171592026,0.4696619715683427,-0.060329315715899626,0.18989460728471616,-0.30417315889803487,0.5946031747887273,-0.11939026941344388,-0.29016354152144447,-0.045421759401764815,-0.03913683430573887,-0.5479887908485335,1.0036565696981352,0.046891980221244474,0.016313422806939194,-0.5386279944016074,0.4372055593849666,0.4610384670476226,0.05480400243889881,0.24695312640148684,-0.21474267196425514,-0.0029986772693925286,-0.1499812511434255,-0.040272997767867774,0.15407941375296505,-0.6124548162120929,0.0355379018154903,-0.3856601113510238,0.13507766105624053,-0.08546559794257551,0.7322405070931589,0.40831868417636696,0.5461057067900745,-0.6156518955521172,-0.03223289091519416,0.055558168026441375,0.1818238073238408,-0.4158768238414665,0.04736163763082373,-0.031155603120238212,-0.4805468728808393,0.04913157142396203,-0.003863994968194647,0.17085249291917282,0.6512500558114672,-0.2915170062593359,-0.06808865057498367,-0.29577512590469895,0.016375724827518527,0.2381709182920374,-0.4688175079296934,0.06943463725995197,0.09540544909393206,0.23276736007218585,-0.24063077623847173,-0.018723741969741352,0.14666559775318563,-0.041194451469656196,-0.08845950123605195,0.012440255167066748,-0.10469352571780646,-0.04492595160071535,-0.9408262703700114,-0.07816019434562227,-0.10837951095049095,-0.11242852282892384,-0.3754189219650798,0.5100925623339975,-0.3246033138354418,0.19000343652678475,-0.03683930141898581,-1.2596385388299445,-0.6657749002960127,-0.17697428986973834,-0.19975779804774574,-0.029910298007620774,0.2345226430730081,0.12069072767788264,0.6458353114902483,-0.3063845491884182,-0.9017701573270435,-0.26155118244791137,-0.04015007189109798,-0.004482887323052258,0.23487137001584554,-0.021927942825613667,0.1523753905268197,0.6912174469479336,-0.32154265956413114,-0.15230745166187945,0.48009107378193266,0.0256165716073802,-0.010860012560553358,0.5003837197433997,0.058857977796413316,0.7980889601864601,0.0969217927372414,0.22723981626520906,-0.1783217524460796,-0.1703583773282867,-0.9975260144380586,0.026347854987217048,-0.3573868096413724,0.13204585465834226,-0.34152297336527804,-0.04861620659845394,-0.33379120570678394,-0.5426518290302466,-0.13332617714490533,0.009090759460063802,0.14608588864123284,-0.0511397341340733,0.4273852867731437,0.04386830113847092,-0.06822331186837804,0.2107580606080695,-0.1690747544327798,-0.3841323713073359,-0.04458593269960771,0.2235663411807893,-1.0358937687907122,0.12704495929753606,-0.43135219697868876,0.030783645200323277,-0.18829710861942187,0.8018237131312111,-0.02346832526441153,-0.07254413182275171,0.09298156927573745,0.13091478301395384,-0.08511470167024231,0.6291175790008177,0.2943011437410366,-0.2954248723569255,-0.20973277285476133,0.30285808524450164,-0.536365994616284,0.45463729762415483,-0.04683022281754204,-0.7272041336855459,0.1499187058253866,-0.01682459979748287,0.10844733464167582,-0.257731984965125,-0.29852237589692765,-0.08825244711414168,-0.009833640483580885,0.2709423078940283,0.03957461641609744,-0.48995390289515106,-0.042833701846884745,-0.18931655965386157,-0.19125918891377805,0.023113016444518405,-0.11109017348751664,-0.13475119996210796,-0.12512870529473896,0.5816076270347168,-0.0345335437958767,0.12341511253560633,-0.8341860181987664,0.06400426230062424,-0.1440594208859342,0.17318568697794895,-0.14044730854929818,-0.09786491300610808,0.0674677657350019,-0.534265095623314,-0.25400541839832474,-0.04271002886769584,-0.131349548813296,-0.134720753848808,0.4587361931784296,0.07485491324890982,0.105835083687337,0.032973920116567426,0.055882966197875854,0.25619511519970023,-0.20452824969619968,0.23175902221692243,-0.10003084877416075,0.21683623864602647,-0.46464829288687803,0.21818970403336177,0.08985152891715918,-0.19541018969629925,-0.5036351597775719,0.5177418706243678,0.273308900138836,0.14767482284861877,0.14321879253765274,0.024097167413450096,-0.30199298388584694,-0.565330548921061,0.18973079380026173,0.058959744581249474,-0.4301997658862794,0.06376624422652125,0.05732212557787132,-0.44873487081515795,-0.03615256844566802,0.3080653607393828,0.004753239244827764,0.7412900596094831,1.005333665243071,-0.16355667236027058,0.09054039529351007,0.12376907904429106,-0.005309230611933783,-0.40564148653260795,0.09228549878287891,-0.08891668227132762,-0.0035368544977337955,0.3279812696057745,-1.459313948520549,-0.13731694613349374,-0.03496965770562402,-0.024556208317118418,-0.11394632718381638,0.7570269423877338,-0.3069028468296726,0.03581974839822384,0.29784947400380796,-0.013995320447555123,0.14861522936516294,-0.10029947314279031,-0.4027155971220756,-0.0893042297341842,-0.02358706112594112,-0.17569554294814085,-0.04362469217999044,-0.03638063898418798,-0.19656007136724982,0.4689517743214208,0.746877117396857,0.21801269855728458,-0.2320328827020783,-0.03724631821727238,0.4359686514740895,-0.022795182399848846,-0.18313371651300092,0.13588417421962573,-0.00266733769856885,0.4578705261429526,-0.021673294747678793,0.36517458263285896,0.07373962784304194,-0.04261847445652655,0.06828002147802015,0.21716555221441206,-0.08921159560163387,0.1156076002454735,-0.1503588447830673,0.2114451828928012,0.5026121605944058,0.06866445954473037,-0.11419631361683327,-0.17009557135371245,-0.3105251881185219,-0.12843761027691328,-0.010044143656856701,-0.027803945079148004,0.2559759945367572,-0.002096651401488952,-0.4878136542469717,-0.08892795406896527,0.19296883714035493,0.313887129654472,0.4353907859427996,-0.0295025236596741,0.3714798010949537,-0.41829095379482517,0.2665625962754948,0.25558960894365007,0.02956147794611752,0.023265039766778187,-0.0344753235109921,0.3448988219916934,-0.03050844600396987,0.32605544282927124,0.025141166416434147,-0.034996186572169595,-0.47692187785745366,0.1405940867681529,-0.1698482612875966,0.1052238546326038,0.08686743720229549,-0.450269687131312,0.06955295020160691,0.01580801527730903,0.3185290595313279,0.18882725352163163,0.3618253915851964,0.6055945971895595,0.013785927683064185,0.7598646072084899,0.13921506401401978,0.16231277725983737,-0.1591727730433538,-0.02991394514246338,-0.35823485466089044,0.2419902335847657,0.024608621349026978,-0.03155968125807925,-0.11150849986878143,-0.053005565438291935,-0.01986756129895761,0.2377821730939464,0.1484015628500182,0.04440151117529323,-0.019297482116682085,-0.4853105084677487,0.07905952245479232,0.09355911818143278,-0.0504258725328872,0.07604963400461451,0.18520190424361468,-0.7194400206917357,-0.05071323411206793,0.031730533325170084,0.2977196561497031,-0.03534817813924008,-0.15133782879592433,0.1554965347484189,-0.04527346324914272,0.18196964676156263,0.19841924837322406,-0.10474223652641823,-0.09981951246813393,-0.11513896681472356,-0.17970404795933573,-0.0011500548405884788,-0.17152910220181466,-0.40935303849465954,-1.3634089245673666,-1.0603095943198024,0.7084594848943959,-0.11515882627998342,-0.13577279814656087,-0.2663356756713468,-0.04676570528692703,-0.030531881873491148,-0.04105043403026033,-0.3795504037593373,0.048432741444770645,0.37465745699473746,0.16801702631353801,0.2615293637769023,-0.34118964579570943,0.16509748790831233,0.48341376937661074,0.05031988811490083,0.1737183824826864,0.10525410404419384,0.12244745199261281,-0.06956763974707668,0.13624036809202605,-0.2789562022086554,-0.06303329689919873,0.4894102839658481,0.13404660209035973,-0.07834122132457944,-0.9694169634589122,-0.1274114374121885,0.007407962135984775,0.197789073862646,0.21757464925344883,1.5459906220587376,0.35637159858296064,0.002684177159802128,0.35510799846479546,-0.10010133479747846,0.12618193039457848,-0.2536293251618984,0.0599950218459752,0.22705257610774832,-0.019632246287517145,-0.1165690226178985,0.14151276956515643,-0.04318607174942086,-0.00428799803600396,-0.17662511610184906,0.4938680397554627,0.43126665944290266,0.48876601042282686,0.22812281041713384,-0.008305969164673674,0.3117631422001207,0.8843939603556912,-0.14763435126334867,0.05783984945793896,0.14540168259359026,-0.016641959944222275,0.028479750995904794,0.6762894553978582,0.28437322560641065,0.19533203037687327,0.1538504198185277,0.056228821030534275,0.6340409409122418,0.0541155300725684,-0.03871887241168714,-0.17496388691694945,0.03424010416720332,-0.06373589818228774,0.006605286321066188,-0.030481572394131632,0.05912834503788378,0.015590432576887159,0.09664562669332107,0.5164961978186839,-0.18113702409995275,-0.15714591152098523,0.16388698481880176,-0.04211072533877169,-0.36381747092115624,-0.2301540214882757,0.2291494166705436,-0.4332300690215478,-0.1354888358441782,0.059039497456240334,0.18038411282279487,-0.05239582156909034,-0.022728876307367956,-0.017265701287348938,0.20179699875915488,0.1914424246808202,0.17079193790406752,-0.28306179857714725,-0.24241485057974402,0.5142924852130301,0.10444196866799221,0.13826065571644217,0.05677396233718332,-0.12730591707782546,-0.03915762279518327,0.028204221888279854,0.12361606360340154,-0.10493558347148367,0.4117698626679033,-0.1506397041284153,0.5968694642034293,-0.06413133075664075,-0.8717312776355952,-0.39656629661325044,0.20875120619229198,-0.23289721207911868,-0.34282808242658824,1.787453046758768,-0.4037947014120743,0.28402958005468176,0.1976776883891916,0.41684600848203457,1.6811515787985118,-0.24193745461023095,0.11747033342005221,0.038063548703862325,-0.07467379136221379,-0.1410329513846268,-0.07777830985904231,-0.017904955817819077,0.11791242026150117,0.10998355657651755,0.44985797157189644,-0.08513321940430209,-0.7683134166155067,0.14007943493389727,-0.03393857157080042,0.05868457141805081,-0.005065118782293184,-0.002913299981660687,0.027068482172603926,-0.14183562135720995,-0.03523163578643222,1.1341464746800172,0.4771903317024563,0.12932278436069675,-0.08198340591468711,-0.21973045119013504,0.5650676301571772,0.10275763912636995,-0.025037166796066813,0.0246102287506284,0.03141450145645871,0.04007291001150134,0.03208961481959243,0.14971741567233454,0.06919839770679698,1.1595683301784816,-0.11864502070386548,0.572127912420458,0.12167380053696558,0.03903410510945389,0.176090339572774,-0.03768974886781673,-0.12248871759346067,0.17182821769097129,0.08116887225462172,-0.39459952800936504,-0.3323491069560322,-0.34944437641584253,-0.04710967273588486,-0.10648836127392532,-0.05423252323647101,0.16650592419614324,-0.3984862860613668,0.657391007261606,0.11619433643658902,-0.9373703543191332,-0.014435721419486902,0.9521769937600582,0.32613219951426015,-0.8245658556254685,-0.195025785862194,0.2034921262825149,-0.33049217664983405,-0.10193788195218002,0.007314828421411147,0.357800086317253,-0.14621420958679793,-0.1020706358626024,0.09061406106276787,0.19209046598977697,-0.0887918180140766,0.2523447500522896,0.05760505221911758,0.023328343451822053,0.33973938934292247,-0.1882308225778551,1.0856831895121313,0.09137419112520719,-0.45717408662179704,-0.7048441053363423,0.08309969496215944,-0.2764383314713968,-0.036602576773821706,-0.41520175806762,0.250292406483504,0.2910437662716421,-0.13736260338653314,-0.23912835424425563,1.218464231695318,0.37612792486703106,-0.21345836283145048,-0.24316696304033705,-0.0801428527874709,-0.39958137518201536,0.0687092806751542,-0.6936730546469039,-0.002063475475501184,-0.265612360521404,-0.6664305868260751,-0.05414649022289263,-0.17905008629836652,0.17974895045934197,-0.1027223061448729,0.14288222276502843,-0.2046905770072912,0.17153852743564674,0.25298871951553553,0.05251766318339514,-0.5993518323012921,0.4212903327646613,0.3086512615516555,0.16271789495219083,-0.08093933555058813,0.006493931396883265,0.8866575396794656,-0.12433327662139224,-0.1693168084862817,-0.29718786080694065,-0.1320563222678747,0.06381339181467494,-0.062046522208575754,-0.0609900762395269,0.09533347724109585,-0.05812648136575285,-0.7904565286556361,-0.19869590619682964,-0.38026886420598643,-0.003157857278666842,0.06346816258731908,0.6017545330250315,-0.05084150090596762,-0.39332215494102785,-0.47709419021130595,0.2914348330849848,0.1684348150646919,-0.05206013413330512,0.11914154046940792,0.20359711923050283,-0.14476118669821525,0.1710121832196459,-0.5487569843904649,1.7406937569464902,-0.21810761448657753,-0.10401890046456072,-0.26729704537854393,0.10549700589818634,-0.5310113776592197,-0.028133634541084432,-0.22128223614805015,0.2892782678071239,-0.5021771696883107,-0.089777040813484,-0.16678729803073292,-0.07515362041900737,-0.01649092832730308,-0.13288089815943224,0.07084564799395612,-0.07667686357600188,-0.40346949626951656,-0.32390865871947505,0.27737219801862834,-0.37415178449231146,-0.05445615432655373,0.07559800422502218,-0.2955957191906453,-0.05193531908097321,-0.21211211915662143,-0.158499076135154,0.17124271988283682,0.1534680246657971,-0.06429862980406553,0.06368890438414401,0.007000131771289909,-0.0748316413636655,0.006342610890910899,0.26442727789656273,-0.046858053926819035,0.31315218274059664,-0.44863565439159797,-0.3906283354608881,-0.07033959716123524,0.12657241906313613,0.46538267431421404,0.15163096517731015,-0.016263158594820706,-0.05760754176845708,-0.023627430431634604,0.006995669157902573,-0.10461912605542746,0.5407233668945087,0.7323061340597227,0.08423192481519796,0.0029246316298483137,-0.0033971354652546004,-0.24674279184132664,0.0007568425711641457,-0.15352697684642325,0.38160241910771747,-0.01421995295110031,-0.3227740616235069,0.08209913694071506,-0.08860391073950051,0.08938226987711782,0.7018775649765875,0.029281749985733917,-0.09302122723240273,-0.03713209583745503,0.0824459436855416,0.10697072731132434,0.09662675303300644,-0.022606268129488783,-0.07655208900412497,0.18128153697237712,1.7509426103933208,1.956322524611493,-0.23375531261299884,0.0050843477838313405,0.393647696122026,-0.5169195985364808,-0.09914203851866692,0.986540659455917,-0.5144222886065201,-0.2603279623199434,0.11063811828976006,0.036445520759434885,-0.040614195480921454,0.07767163106761246,-0.025244545057124333,0.23992679775857986,-0.24508851809961352,-0.20586921579232864,0.09492486076391667,0.1058855298348495,-0.04102444498111886,0.06659962141651159,0.10387105499849263,-0.24257873198437638,0.2664938774686732,0.5253485533914666,0.2536723928663142,-0.0020272879746652725]','ce1010ee19e87dd3c2c5cf2cd648b5880b29dc1ffcdd4d4ff6b0d167cc19ce27','completed','2026-05-13 07:19:25','2026-05-13 07:19:25','2026-05-13 07:19:25'),(5,5,'huggingface-clip','facebook/data2vec-vision-base','[-0.0015814224004551654,0.08012447040468965,0.07084208845366549,-0.2570361915011394,0.08763944380325714,0.20186973439318573,-0.027760810220524252,0.11435396690748935,-0.2917447381742715,0.05157298432086325,-0.4606797768200185,-0.031033370555803973,-0.07811551610466452,0.3629735216105695,-0.10454308083322286,-0.5422811935801144,0.08698544053592469,0.3115313693663569,0.054415846964448386,0.5577659537852624,0.03164671806070178,-0.15466209750774382,-0.07194138949602218,-0.2654621916028865,-0.24965676827223848,0.35440251404339174,0.8149718438762094,-0.060544139567318224,-0.06456385420918699,0.20637815528393247,0.07640179897194675,-0.01964370953081634,0.025042029265503443,0.17093822477281395,-0.019534069073249394,0.10661583862487264,-0.3739680184127035,-0.861993640368133,-0.743641968405224,0.2453848692697241,-0.19336815945494465,-0.036084643023816615,0.02548004893011645,-0.14929091422180385,-0.13370075311673082,-0.318036354852481,0.26787085029140567,0.06808724487318568,-0.008821986490747712,0.008846949690806344,-0.160680842323484,0.3725703288682826,-0.10764680735100307,0.05996288785056168,0.06502946496936422,-0.04147244272233568,-0.06919066323236879,-0.0880906331068647,0.0704127730090209,0.23661308031444092,-0.13502101484345755,0.018095845331025408,-0.03645790835926256,-0.060707332670977006,0.12814849884152354,0.2669910551580662,0.6382143571003704,0.0685988373884241,0.2416491212670781,-0.2521778162696451,0.011215960182637203,0.027052316460096593,-0.152285677499048,0.08525955006432942,-0.3694224584951694,-0.14897865588838224,0.02756539540280243,-0.1305358587429731,0.1474640112746091,-0.06565973258028894,0.34295031955928973,0.042074656985028824,0.12171497507975791,-0.1915474670246565,-0.07178810242974781,-0.14917337085914603,-0.38620669741495534,-0.196945491526985,-0.25305405097923755,-0.4966148494808996,-0.03477246718963391,-0.4020801909494824,-0.05764584930354805,0.11443086871924588,-0.1616443698403046,0.22947142559912076,-0.13866333252841045,0.33707760513563956,0.26403827025781995,-0.3393508945701072,0.14738110753941452,-0.15926741198329303,0.14372022276942803,0.282298137433236,1.3129412297556546,0.19104420887377246,0.5510800349086448,-1.2712456555989793,-0.020156753445806475,0.08283667188623227,0.1520250333735464,-0.3012775833657021,0.06796591325904133,0.07290109052431538,-0.02133245595381004,0.04928380037246622,0.13812280582089148,-0.008099974192168355,0.8906999665317197,-0.7635386091240961,0.0472819575623888,-0.18278666611389743,0.15920265694429536,0.10907031977750115,0.13323967814502227,-0.0533653243759462,-0.029436148383990307,0.3074579547788135,-0.2665610073694515,0.06547665292737523,-0.2878902586171673,0.05288067967087257,-0.07612088812883887,0.41048444478611845,-0.012232767467075004,-0.04505866622081887,-0.43180567530683495,0.16961064060847394,-0.04994083733799002,-0.0571192415918077,-0.18980205417988907,0.3530899747771206,-0.010004048712603597,-0.10342953650417931,0.14496929763561012,0.1451447245920181,-0.4200941479013202,-0.1615062447209833,-0.03879118132107149,0.016255024821157118,0.04320662269406588,0.15529790054888554,-0.002361170838818635,-0.13747800555231457,-1.1900419822993316,-0.2609588191147213,0.022051887451978353,0.09235980952640778,0.18702754735283916,-0.07952599000920858,0.11648665841014082,1.9750367922183636,0.000648287714838386,-0.10632988877670112,0.36559842409086496,0.08689094766700253,0.018646743576997315,0.5699969912396962,0.10892012992609046,0.08967991856158461,0.0935834045813692,-0.17305008857392795,0.09856103189693774,-0.07974868507814703,-0.6371505993423123,-0.03299058150490604,-0.30494129552557914,0.09255627139195502,0.23601079452310117,-0.42411426275254693,-0.143674245091193,-0.6134283278813482,-0.351618486177649,-0.07575922778929906,0.07545940029649963,-0.32596105977257317,0.5507887490661011,0.03836899956328993,-0.1843582735453652,0.3229312170722385,-0.4613368621256751,0.02339513474179498,-0.5922182941564648,0.13163994898698128,-1.0317010598282004,-0.5977531629259968,-0.16335566247090091,0.0044554908580246005,-0.19223311985133754,0.7778388905684053,-0.06091850004655304,-0.040088394552771814,-0.018114968645031832,0.032251814025132754,-0.13612081635232806,0.8469916173602043,0.04016532915671531,-0.12088076971727778,-0.057239518047022035,0.12299540543191248,-0.280994260452007,0.14589496411981184,0.0626107024718525,-0.12367079864601194,0.28847654913268456,-0.2508339714778937,-0.3003424456494336,-0.12060355546359483,-0.1574519908653695,-0.25313017997467596,-0.4241516582722785,0.11537712677925598,-0.14802694333383937,-0.47683103416743805,0.06444726119842913,-0.1476306519731123,-0.2046228716531674,-0.06051532002307739,0.1584272148920995,0.20439165698548012,0.20610093631483803,-0.1466208079742353,0.11763209353708118,-0.08073129484236127,-1.227540629059029,0.16997829176353907,-0.08985420471756969,0.05106893482057822,-0.14998334604859126,-0.004474235177666497,0.1636424290706143,-0.4206473305632318,-0.26874241408263216,-0.06744267213333228,-0.17325749345653252,-0.03999248538922689,-0.0559640687649247,-0.1326966574013725,0.09092845960619486,0.10537999749584312,-0.2915161175530423,0.5145852001677007,-0.044178428605841806,0.07471895586997082,-0.21602141360520635,0.08202953302288318,-0.141255813158247,0.05862484942308537,0.08681183119084648,-0.2050112671133717,-0.2839132817178435,0.32555552989452474,0.1458057291418087,0.1865721978344671,-0.03798187583647101,-0.05880798083472392,-0.34297776383910383,-0.3891666166054151,0.3325552017413798,0.33772648038900444,-0.23734662225611772,-0.21702804608828374,0.01079966416236765,0.11872960992930409,0.07282909273990161,0.41158097240657826,0.008634194274915165,0.6426950904025417,0.9261642302283639,-0.09613719713569235,-0.02183208851909577,-0.4342037157393722,0.06362069959972091,-0.2026438868172032,0.05224568169737745,-0.3646212919960318,0.24226453212245844,0.4732691844201973,-0.28758965756590205,-0.1867700302115556,-0.17063448347164872,-0.12191789905684834,0.06409406542676373,0.23684187560592812,-0.23743008963555703,-0.12342895851423255,0.4941635746052335,-0.06657117101727857,0.051839751346263716,0.032003881237652805,-0.2699223347220967,-0.08867661045482476,0.01350073641175546,-0.17512698665020165,-0.008682246075300168,-0.07082092995577502,0.2134821077588317,0.20057760479713455,0.3953484138586147,0.14466634414957713,-0.31583664861748817,-0.04477975194325888,0.19320993237481052,-0.4636937997133712,-0.2611957770857374,-0.006321145116994488,0.03907615618619519,-0.36863658216020784,-0.06032735626993294,0.1621468468488851,0.009468303682509489,-0.13865389544939324,0.041739862621407296,0.04780704997654834,0.07657138565035188,0.1634504962854352,-0.23391496844862725,-0.16985742221905514,0.4366751433300507,-0.11796916199453307,-0.16894923591115252,-0.0641624433405669,-0.14409093577048368,-0.060917624414231875,0.018267801816239576,-0.15244987413646455,0.4803147279352498,0.017257664185345318,-0.1961131951875682,-0.1835285468383924,0.19029370807483556,0.14796171598266433,0.44066670591716045,-0.011328303987316334,-0.1753856243141974,0.08905931526226545,0.1836989307250156,0.3006014614200532,0.08004978904352869,0.09826279122622915,0.0734269073366561,0.3531940324411628,-0.010390404211759076,0.6253830723388849,0.2359008814272031,-0.03652350424710888,0.11577703008607862,0.029890221561929258,-0.3210043559597833,-0.18793362023091406,-0.2544864664754835,-0.19843253813953457,0.0401541825589549,0.22134966518124802,0.33164436221690047,0.16330613390965681,0.12179006324876249,0.8546675198196154,-0.057218650348806875,0.4942794453806532,0.029480673213634723,0.06846204667995083,-0.13438302236089134,0.07367858004184241,-0.8220568826128989,0.4864047291750079,-0.01362818672627844,0.14540807255969246,-0.06247090431270639,-0.04308323482840989,0.0720260240003729,0.15295369364498515,0.09696668819618898,-0.18791951403508747,-0.07022067279493446,-0.23770897239736202,-0.04915704846802891,0.07131033569283614,-0.11393014521445596,0.09489784243121875,0.13982749973816164,-0.4356446110786454,-0.07332296509870097,0.02178596142090876,0.14931613335777122,0.8605214343949925,-0.23437043647565478,0.22601285875368704,-0.05750006498138983,0.11744978209676192,0.38188347914067056,0.06584807929256695,-0.11055559396943512,-0.14943986569370526,-0.01010390698465372,0.05838686995371363,-0.3019538352970355,-0.13035685241903144,-2.037460293450634,-0.5038343752677633,0.5105477844088381,-0.16767024619594634,-0.08829045861216053,-0.12139830302871377,-0.22958339553274365,0.04219822314754601,-0.11018013552618379,0.007291273742076246,-0.03379137746914087,0.09463968608459826,-0.32242660172553117,0.32992207215919256,-0.18550798328366452,0.1270225663562447,0.4670231333521553,0.025129261294256914,0.3535278975375016,-0.22176143107861918,0.07533923285263697,-0.29437101816966527,0.21566206596491547,-0.628530443702744,0.4235540268219402,-0.004908552680366677,0.06025220693070158,-0.12409599042417208,-0.23864846673515167,0.034680900695795186,0.05358824397491641,-0.09530617007596429,-0.0565223440945711,0.47796076957401135,0.48710944695483305,0.07676836401334199,0.26018222444114597,-0.20662959769892927,0.019203329301642508,-0.6714577399487347,-0.038060833629383825,0.2181033177725583,0.049309169705417204,-0.4389708626916074,0.13823669250324122,0.24471479348441216,0.12327960261195747,-0.14897506393972065,0.26210600341230555,0.45711303797082475,0.3304920988251942,0.014431062683954759,-0.3902915062242173,-0.022587146262548447,0.8257458920538576,-0.06123976180903722,-0.6889210872193751,0.15601413365604763,0.0585462367983516,-0.08645682580069389,0.8413684285212744,0.26336230194848503,0.17114966407389887,0.23607357630540432,0.15615333646280588,-0.1922802648942582,0.019415035482193463,-0.029261348750158615,0.14984892388223078,0.09125813816291124,-0.024046308599733914,0.04229841351291628,0.19277370069294147,0.09371941088935033,0.10533090261461776,0.1510682775553902,0.22270823119747155,-0.02160385214968296,-0.043209791977696005,0.25304384699692895,0.09692870128356627,0.03209605731417515,-0.1649980421662171,0.1475692147112494,-0.16262125491453003,0.3185798801436812,0.08970706801516415,-0.08690007309178789,0.270641176337329,0.012127632579484335,-0.15448708787954474,-0.21065679380048521,-0.015281640143289709,0.0082304174988176,-0.3635087329894304,-0.13377765087094062,0.6610808633929733,-0.10762407056760365,-0.4895942215275889,0.20555209719308837,-0.00952530360714471,0.08981129969588392,-0.39001811284401694,-0.03670852276411494,0.06219933589277458,0.45207073998065467,-0.08743125060809596,-0.33914439435480964,0.21850133629256935,-0.577379466811559,-0.1408654066649456,0.35363787424580434,-0.08618288304004346,-0.04051603145334813,0.8226327974429628,-0.16819130536168814,0.09079447204157294,-0.013893763873333746,0.08244161974156493,0.15397107867089097,0.015504325366564814,0.11042141708129365,0.23313582461176033,-0.07153280526115961,0.08461143680656716,0.17673870760842278,-0.03444293865149245,0.05894012188303715,0.22230560742708652,0.36096801339568296,-0.14075978826920543,0.21997302229017454,-0.0393079680480479,-0.0006450786902971074,0.27148964890245786,-0.0636620146487154,-0.04255602092805394,0.0684500167756176,-0.1300811968604339,0.012882818557147107,1.364676708327181,0.46472739594598356,0.28242896521359984,0.04083229702079508,-0.24147892198701174,-0.09155121671158802,-0.469611220999011,0.016984550840207162,0.6091282592311048,0.1600534101749586,0.12513314945467272,0.07633160128861519,-0.09625306510045027,0.04869334572705447,1.0984230696762614,-0.13038258066446387,0.4894334555551561,0.018607458476890777,0.07331738767638415,0.15276697614977164,-0.28565511493092677,-0.15329677686594584,0.40346289504328886,0.08365572953216588,-0.1727840045826311,-0.1183961285768682,0.02098973277663141,0.10772559571264283,-0.11435680884068955,-0.019878652213888272,0.07570729578143756,-0.5400802580176423,0.9686005681582968,-0.08033771499953597,-0.08119018395610986,-0.119128530835591,0.8065608739900181,0.06854328735693722,-0.7294253700644459,-0.15422028410904404,-0.09760458113124527,0.1469596683574831,-0.15330920434381984,0.24465358489651592,0.12410845411445019,0.007467044636767906,-0.013839393195242333,-0.10542488585700172,0.09885986172830506,0.040233763721937,0.4236460917955501,0.038707929258270254,-0.06132618508237854,-0.04960203427717392,0.03732754266939036,0.8533107706388166,0.19683622783593607,-0.12986081485225237,0.10377541826896104,-0.03310844458185779,-0.13072561252181747,-0.05791312415947013,-0.36450329810726045,0.2852183707876292,0.5837466018868321,-0.16953082457344346,-0.2251576257971243,0.018380864509168614,0.2488565993690887,-0.2455124341505154,-0.5152148575460548,-0.07157351395718312,-0.2962032404384306,0.04861120214487242,-0.3110365696856516,0.08384436354641496,0.1116678153546137,-0.12627890532621217,-0.11864760470562402,-0.14288042805117418,0.08510902178412313,-0.18963278141073636,0.14320158536267008,0.0746873510460166,0.19005479363412572,0.5522452724322223,-0.05619105886842885,0.2608184982673265,0.07867444311575916,0.4671514942192562,0.115144672960771,-0.05085343744801972,0.025813494188484127,0.2093438119769361,-0.05619142165284623,-0.01334620358866786,-0.8263528246980936,-0.17641615443555686,-0.08749311510355301,-0.06817282306007937,0.033493683288812866,0.16171446444376886,-0.09097485499619624,-0.06423773227447435,-0.5208548081537643,0.11569103029164188,0.01819467575727787,-0.013103769439140212,0.2821464234499762,-0.1490438301552047,-0.15429193881011763,-0.26410137886136564,0.3195701249057266,0.06905687930828272,0.02446332999722569,0.08856492423656902,0.18949620682642165,-0.17627803398193415,-0.1742580034946615,-0.9186245530682999,0.8391830931290001,-0.14009679065364872,-0.3214824485833648,-0.5259880110760914,-0.1663383101656806,-0.41304850630133605,-0.02893410360459522,-0.11590669645341634,0.38160638138770037,-0.23125970157024067,0.01331190231934587,-0.07995999123674208,0.09751818616854424,-0.060446795780976365,-0.08124167326989058,0.24650518584885364,-0.046917943356896696,-0.5361334774312256,-0.1357061810186369,0.12403813012648696,-0.2941455540902024,-0.18285559831591333,0.5231035890265694,-0.4895690002143005,0.025820081720251572,0.10092793815050671,-0.07197315014690765,-0.049880546379390024,0.19107283543568426,-0.04436293497225412,0.00881901252536141,-0.1077164167362185,-0.31506989836139687,-0.024875416546861456,0.29021069566780694,-0.10684384667370138,0.23665690797187275,-0.12050492651666028,-0.12514603764367133,-0.03783961977374735,0.2041445386165819,0.36224422377397114,0.1071997238228542,-0.08674129293486948,-0.16459226724455311,-0.04926206603372082,0.0476363096338919,-0.10796236221916113,0.6424784038532324,0.7043425735171298,0.1971934070328468,-0.4707587818912973,-0.09783484287522166,-0.09853273435840904,0.024221279471935375,-0.19122127363124986,0.4889658504865383,0.09157437744176933,-0.37954577680223334,0.08922648139245959,0.3755138637498021,-0.008242393650001076,1.647086996991698,0.011698831849599134,0.0719582289788248,-0.08992081914873566,0.12552427500959126,0.17575528357783096,-0.11808617904780522,-0.03602872809190138,0.35450538521074704,-0.19554155280572472,1.4778724920174797,1.480562234682688,0.011119585767917826,-0.11107513570814125,0.5187414337976641,0.020450642668679925,-0.0192296227829077,0.9613556066707609,-0.27673460287987467,-0.23650178794369697,0.014688211578289567,-0.2210218795211182,0.08712700127711434,-0.05680133862173312,0.008551361800412539,0.2613965698522587,-0.22244546360154235,-0.12617376650480297,0.15599898971158652,0.040956634082027765,-0.3244263089243039,0.08050212007356325,0.36993738847392016,0.007929834158415078,0.0994858292905136,0.7692104753693045,0.19793023696159803,0.20636880255010578]','95a2b4e95f9fecf8643b2f6c80141941cb47c40335684d89e4b9910d22dfb2be','completed','2026-05-13 07:19:28','2026-05-13 07:19:28','2026-05-13 07:19:28'),(6,6,'huggingface-clip','facebook/data2vec-vision-base','[0.1547248536888699,0.06931798946832456,0.21524544734375428,-0.373668464282433,-0.26500381227005937,0.11589339091371613,-0.00394775624668254,0.15434757581592726,-0.3814564477605036,-0.2985397555243678,0.15188418888855668,0.01260868429033757,-0.616586467686114,-0.2823869190438825,0.06347761599159077,0.57273183589527,-0.1564708948429369,0.2641249485408443,0.0892639966199392,0.621069955152606,-0.6718273177024374,-0.058509617064489164,-0.16711539748658988,-0.16682950173520014,0.023018911741508483,0.6377460546176975,0.39803966685897924,-0.1664813009195909,-0.060562548929350306,-0.6735890660869893,-0.15374307287635333,-0.059786438499931906,0.24569741204655185,0.48031457115158555,0.21947185848520473,0.12763426587624396,-0.3538832391120569,-1.0898550004807068,-1.6573341798585683,-0.1687375880248353,-0.018929886470931738,0.23427860748550505,-0.14978457982108223,-0.9144704454592641,0.06865313656918874,0.19826058409859895,0.2701135342278835,-0.01950578420286464,0.13405066705781607,-0.03044217069861408,-0.04514989058657308,0.5155943984042872,0.10019840820938311,-0.17756934099249203,0.0039062129834733033,-0.07371984871735485,-0.07198071574170596,0.22128255400560834,-0.2431584052767529,0.020528804267364257,-0.4272367057579908,0.0076694469201629084,-0.05854462229903506,-0.08032737109753384,0.013557259749939078,0.03185043881557466,1.077287298017347,-0.943632215514382,0.280335610883717,-0.2862331813891527,-0.050970969598906984,-0.003735469302592733,0.3135712781429366,-0.14491227202987308,0.6963739467945498,0.0984815550698582,0.37616927026762736,0.1572040852187627,0.1802688266391757,-0.10433046427569564,0.27202328906434725,0.44956018325404695,-0.18833191215880876,-0.21513408386068145,0.002424610090538916,-0.4642855972997126,1.014310509344648,-0.28307217977430466,-0.21112371614524328,-0.5229113855290458,-0.20563449721776308,0.10639381121671498,-0.0024942047837122963,0.22770927099157645,-0.10017445686552201,0.7190642958308385,-0.18525229168814716,0.9256952244665493,0.6980184135807271,-0.6790271844279948,-0.18058509493204128,-0.07592874443156143,0.22643334582994035,0.19401002141424256,0.6528531208298722,1.18579225626013,0.6907983784455106,-1.0497411802023422,-0.051169032816300024,0.15943719197067888,0.17015829724425138,-0.847320091584461,-0.04188210699532351,-0.025146021743465762,-0.078689462171104,0.020719250927969984,-0.007906393136186194,0.1735266705391087,0.15779965634689416,-0.7462351060552797,0.138032846266834,-0.11658996577152443,0.03553505665516282,0.5968977106938393,0.0224926966383727,0.04298275323711137,0.08207286899403532,0.3070836845539548,0.04514861623103598,0.026064105256848265,-0.15022657864885453,0.06966616296732968,-0.22839419723566395,-0.03504070253844189,0.04723605218471671,-0.06747729199947931,-0.8072813934254163,-0.4900470400503293,0.1843886945673222,0.01915706261587069,-0.5355613897861865,0.4993037725018744,0.2081315949675036,-0.06101064735711528,-0.05180508196017076,-0.48862519943235716,0.277164079948579,-0.36554238036378917,-0.3399157485957754,-0.0829841929977183,0.17571607098777584,0.0313469944539513,0.2119547028294268,0.00201507384278977,-1.2647815183807296,0.32954223840710173,-0.01982577242929876,-0.01983503883126603,0.016622095894333517,0.018791928908345645,0.11666559106741232,-0.807467831038695,0.15423652395199916,-0.2613197094477184,-0.06287797327214842,0.005993390304117988,0.2090692248663435,0.6793326990251065,-0.07438795715198157,0.8389921544951761,-0.02398036769532178,0.07403952515740798,-0.10298527272590696,-0.07520609985219154,0.12518519329067868,-0.03884152136861257,-0.5446870518176992,0.024164705293311863,-0.28630109384702046,-0.31887875004496596,-0.5732988629520867,-0.6179727793950115,0.0738688453301837,0.015474422212774619,0.03310502777679771,-0.14610429576817938,0.48809169396535884,0.20512103351577052,0.1908072911050445,-0.05040705988188345,-0.10989619223200031,0.47555000798275626,-0.5014122438517773,0.19542386004379786,-0.8467410613929257,-0.5393807746323421,-0.13576067652881882,-0.2598958348531541,-0.4827804974921505,0.9423372317200083,-0.09160503229365075,-0.24052160896501518,-0.0686034276012404,0.07828110817498214,-0.11584127509640293,0.8180288369270977,-0.03534796633057386,-0.12485256908239258,-0.02610612082174568,0.37171011418576927,-0.32755211499864756,0.27053961560229395,-0.47580285299348357,-0.9438357425068841,0.4388337814802219,-0.3844202223947735,-0.0867599181223396,0.163398339557296,-0.2756589217688227,0.18287146554995082,0.2492654764359825,0.23360851952371184,0.05760114674803891,-0.3454425470823148,-0.4182298167083923,-0.17749845746994436,-0.054220234701317746,0.06454719975383877,0.0849785634142472,-0.17674895968680568,0.6837838199746034,-0.51128590086163,0.05244254297931409,0.14035472731717016,-1.2128634292840352,0.3365231400289511,-0.09204890764717882,-0.06686736987027644,-0.15005158400399432,-0.0894325443113289,0.08349311360019745,-0.3348290563293917,-0.05954552679541308,-0.2891111866604102,0.004835562151443062,0.2234811340659611,0.4151931732803167,-0.05156134852080554,0.06895700511097114,0.18781948621209882,-0.2598400284644916,0.4510829246850852,-0.3734884530892076,0.18333926533767245,-0.30774518126458744,0.3586165403621035,-0.2128799470510277,0.011648050924233601,-0.033239873252019554,0.04178720550921268,-0.7257801823845124,0.3074597276145649,-0.8436427635949124,0.17343281420789242,0.1253665527057016,-0.14104278205993212,-0.8052882357561951,-0.38804229570382515,0.1448492766121582,0.31096714510473655,-0.2662725863587655,-0.2544604692263009,-0.06217745049658047,0.15622736224910566,-0.0722480861902672,0.4015400128599805,-0.2809296014274324,0.24386698783843347,0.6702893631778619,-0.12750372112974487,0.1284981377858333,0.2749626516165959,-0.10416126989054512,0.44049712142500513,-0.15977138174174182,0.2065917671596641,0.2740450118134167,-0.10851911906280606,-1.074481797815882,-0.04960928393378948,0.0025754248678180754,-0.024580894278003102,-0.21430427498074228,0.39080436000491725,0.08364638226058897,0.48803000750615816,0.33665182516167913,-0.37338656681202126,0.12149611771872579,-0.037385268113664515,-0.6343480963543587,0.024468396729458747,0.0058879650003355955,-0.04008574746484126,0.0531232543203975,0.13748067383935703,-0.17114474264689755,0.7529569704908178,-0.03174060032357873,0.0945718582011322,0.1677114351340546,0.1492328925704144,0.6300806324210415,-0.5797937970459556,-0.12121079883051843,0.10302231474246222,-0.07525774040336243,-0.02712039290903713,-0.09548324138944883,-0.020692044982525904,-0.008540901922674609,-0.3065670921566523,-0.0786392637032089,0.24597135295164843,-0.0704939147182102,-0.2239790250698511,-0.10324675560961974,0.04773232769067423,0.7347438011985027,-0.008809737074614365,-0.22667917410008667,-0.03918703331796424,-0.7996890015692124,0.4323981230338943,0.23113492017299844,-0.07350133097527294,-0.02173547862676152,0.07061223706483444,0.6389207926290441,-0.12387423689990082,0.3819068762459579,0.003976354823119201,0.40584356646020403,-0.1731717733874312,-0.4338347742494744,0.6952830693367774,-0.21715562385956044,0.23320898530782594,0.1939014248162281,-0.18092625046404182,-0.005465029186300018,0.20455364042391935,0.05624937953729996,0.4347055283408177,-0.1206875807702598,-0.02867505437904865,0.9497651868513998,0.09009614481288127,-0.1909537251174762,0.1338193162644594,0.13224004687319468,0.4078603808018781,-0.14700127675713318,0.733547867460148,0.25631893878249534,0.1361209089048727,0.09205136487147,-0.36335614106208936,-0.03693797821379952,0.19316245726106432,-0.1679711046126059,-0.2650164042003831,-0.3503196368627439,0.020696771754835953,-0.4026557849058267,0.39617924068860594,-0.04055472834707285,-0.09320990241464785,0.5302745260033511,0.4557554494911039,-0.013467407040918472,0.015259353681169048,-0.027883322038575485,0.04417944838079272,0.07245617813238966,-0.17752731521022955,-0.1471045068707459,0.020319723137678002,-0.052290299217107934,0.12500899784996355,0.04703104536895295,-0.39986188420884045,0.3069904413396909,0.031171289806521313,0.23097249404479972,0.8452854524910223,-0.26657745468803695,-0.04531443132167956,-0.058851763536340466,0.10335632962506232,2.050742813608217,0.053046183206193,0.8886934588214165,-0.029684396777987516,0.15331020926569736,0.08588510424579461,-0.10289346001971972,0.13684111183031603,-1.8828312519011159,-0.3571585143205998,9.130489501894125e-5,-0.18120172870023943,-0.14712026564884212,0.17537247828978497,-0.20671820835338026,0.36443049070510425,0.36984018112892086,0.013057720830881504,-0.08513410617053736,0.03398958026362952,-0.12261934799548749,0.4178464395291914,-0.20347784419377396,0.2055458809360791,0.6890934426904783,0.1782832229637002,0.025535111450282904,-0.24921523191526457,-0.26871255803309607,-0.06917802063006019,0.08113823718371452,0.25849880401439834,-0.08098104299958511,0.4434636889526687,-0.020423486695251413,0.01745324624409228,-0.26150309119307374,-0.18093831799711635,0.11049496273505431,-0.8633757347080309,-0.20151571009832855,0.7486971472366358,0.6699938071598559,-0.09802182373557675,0.4672843933919194,-0.10950282337274024,0.043093180027435805,-0.7678345658338918,-0.15688687614945288,0.35080655838887626,-0.1341483022658353,0.05634501809564533,0.18250466105031785,-0.1287604559042732,0.041739036522032744,-0.11555984152922984,0.3336580982006292,0.40047431639679304,-1.0013101811468752,0.14343892283640744,0.020833052696201,-0.671352996767456,0.5745676158227707,-0.12429883220022649,-0.22219667220748204,0.07607805882793317,-0.018499837069935285,0.05047004948355976,1.040201747700156,0.13892148484045705,0.3701190215353452,-0.08707348632432407,0.07752238866000857,-0.30760941097363925,0.07293180241497697,-0.4001686223286209,-0.2516903245495396,0.012204972298184721,0.032816583349790214,0.0054477504309033975,0.08539296556169612,-0.09187107830696035,0.5125207970808804,0.04323795165034569,-0.02562307893430937,-0.21189144564801968,-0.11992006482459548,0.4127302742634101,0.05384573378472537,-0.3410966329766747,-0.19705036604247494,0.05344358373014965,-0.14552158055422817,0.03449996985580186,0.08335730465138473,0.14353354912319782,-0.26475484841290464,0.10381037869254603,-0.11378459831440306,0.6819461778865248,0.16559938724781506,-0.01260266973291935,-0.14650768227372932,-0.18019061642616213,-0.30688807040601873,-0.056296603157312664,-0.11106388433009083,0.00877723809884776,-0.2962248774876506,-0.13007605175444317,-0.8824938623622287,-0.06529262928507258,-0.026725068560749445,-0.5544159800442985,-0.29657443469215355,-0.38248107441450435,0.3117953689300885,-0.6793645122821258,0.11119982897455279,-0.1314755082830697,-0.17777984756101775,-0.2240131372292786,0.9567368465353783,-0.4366661479441345,-0.09611586904741805,0.0002996745013494782,-0.47349514053226804,1.7701644671674308,-0.3376131261117416,0.06176022838889861,-0.9307950219980956,0.12190682753840187,-0.08625406385223597,-0.19859873137536085,-0.3490545909407402,0.05929017164091447,0.20028018033803266,0.14829791183976415,0.04700988506470443,-0.2909403266314285,0.10854433715192195,-0.09352122995113661,-0.34345367883667727,0.016086383080960907,0.0018351146127546423,-0.0014577576174663125,-0.0768332755205524,0.14217252999681243,0.6826908416327486,0.47633279994104133,0.1502856281655427,0.07743293336659982,-0.16135836352161628,0.6714920509360769,0.3466098946460102,0.09706079483452976,0.21023460706136884,-0.06826464619326789,0.04527808036560637,0.17218893758930742,-0.5301952651385123,0.14801195318820096,0.3011560829316129,-0.08422261295275205,0.08276658052429947,0.003229382616907434,0.0001568552183441175,0.186158354780096,-0.06593297825529959,0.2875607833455359,0.40512483730571824,0.2544361489451476,-0.41750703213812057,-0.27634019800816395,0.02756219936817232,-0.348052876107962,-0.1304475848555924,0.16352537844973017,0.30891960576592664,-0.10510787365058091,0.36356340449475943,0.3146518552864944,-0.48004706176281503,0.12183605980167643,2.5167375569264903,0.22982310515303675,0.08661484343902713,-0.04330470428659065,-0.19963971709331432,-0.19714028970397499,-0.6312158030604348,0.08598917716556242,0.1770267350803635,-0.13105941768162435,-0.05651544841794713,-0.06283655663534732,0.5213872682028994,0.07886723743995112,-0.0006712560322097867,0.05267144483395942,-0.23036919248900212,0.26599571153493173,-0.7343397208768402,0.362005114245706,-0.03550849882070316,-0.05346202591805232,0.286896927642688,0.1876337303824931,0.037911967361383705,0.5380657032642722,-0.0011324424755778447,0.07815506421254126,0.6247007085156963,-0.12628968741253094,-0.4201588716971343,0.7033494656272044,0.002659559051321848,-0.016105068111815845,-0.1488819456503249,-0.035112954384965235,-0.08238727696296679,0.0654730292020583,-0.17966245990222895,0.2668200583589561,0.007163033922330528,-0.12005727699021738,0.07212446256231063,-0.06256342981705622,0.31164351362246917,-0.10111794591899655,0.24406064966892077,-0.17787161714378608,-0.06472411107902444,0.5603482324516561,-0.22886730118873022,0.33240693792503395,0.4692530506550509,0.46050634062825346,0.023733485151179193,0.12385829590671095,-0.05815130757120069,-0.8295558872467219,-0.18725442279929172,0.16179069068323026,-0.909797259696244,-0.45051261874596554,0.2508957326591847,0.07441024935230282,-0.07086196586490859,0.02502122652943856,0.11737833973516705,-0.6988008343011878,-0.4394087918329663,-0.21988008612161739,-0.05714316268777711,-0.06486625141974747,0.2858892086184782,-0.05353945566915675,-0.39816945725768277,-0.3346852253552829,-0.22043583185771282,-0.17176004250765528,-0.058481121346529406,-0.07460527163859325,0.23701639881069173,-0.4111950760388151,0.5151253002624826,0.3027999456770964,-0.12399968665877953,0.07522221443274053,0.5006112108564922,-0.09459887620216463,0.6035503767002941,-0.40842160335765576,-0.044586630066327515,-0.008729867669317543,0.2867712793436828,-0.14679032721353438,0.10016565438924915,-0.47836226256573716,-0.028632442492805246,-0.2060254933076159,-0.312599999459016,0.25219985086773405,-0.060247477876141535,-0.46311594987864874,0.45275454586732256,-0.039425545815274424,-0.16250159726939092,0.01837864836328072,0.28721338009395575,-0.03960216481966643,0.1545424747616431,0.16274398887000485,-0.2129894098725529,0.32027703591889517,0.30168065107971204,-0.004234991582680191,0.21999986687492518,-0.11241570101615968,0.08071311839577283,0.08589169599865161,0.1388051789005755,-0.007523588491083704,-0.0935292363386639,-0.27432553551093614,0.26156647390831345,0.17728260599979706,0.10343374229014998,0.2330963288390711,-0.1460773291924335,-0.35154318781025934,-0.18537838716617772,-0.1653543001969775,0.16291610621327185,0.11920454288592922,0.2989727148507263,0.5537388586311549,0.17087711639841782,-0.17081700598550392,0.050332863407379705,-0.5469931669552163,0.2140862149160431,0.002157093636185725,0.3993256021158647,-0.1538686791735082,-0.19259249139458962,0.2889114073891344,0.0833558764747169,0.15634241095381532,-0.09975379737576252,-0.21213436523821147,-0.02088138341979327,-0.16650162046056638,0.07571953380962616,0.16794782781996823,-0.14050973740791628,0.04219374066303829,-0.10276729741540316,-0.5826590240396426,1.0883312151001454,1.6432886804326385,-0.08030597705259294,-0.09309710283186949,0.4794482925438752,0.8225874796245002,0.04024408611606069,0.5156288236283004,-0.2507697603816573,0.07571267019035437,-0.21013888690046267,-0.22499850705776572,-0.03461004355210258,0.03506953358032083,0.06952262076881341,0.20579317776453185,-0.3830468391923962,-0.11017146430632113,-0.20496773510358207,0.20246778029026902,0.07000265323053294,-0.1368903238027886,0.33711742415910745,0.03389253302508029,0.17386889795720728,0.9137172757974131,0.20566179629339634,0.16264976556282867]','c36a75b4c6aa511d7d04999469ae76dd25a6afcecac4f564818eda6eab491170','completed','2026-05-13 07:19:30','2026-05-13 07:19:30','2026-05-13 07:19:30');
/*!40000 ALTER TABLE `product_image_embeddings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'product-images',
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `ai_embedding_status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_index` (`product_id`),
  KEY `product_images_is_primary_index` (`is_primary`),
  KEY `product_images_sort_order_index` (`sort_order`),
  KEY `product_images_ai_embedding_status_index` (`ai_embedding_status`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'YaWrKXwcrc4VFd1DGVo6Yb5iOlyUh3HUOkJ7qEVf.jpg','product-images','image/jpeg',69622,1,1,'completed','2026-05-12 10:49:50','2026-05-13 07:19:20'),(2,1,'A3ppxgIUWHMSPJAJ3tkBkRQHeQi3Cmip4AN63O9k.jpg','product-images','image/jpeg',51867,2,0,'completed','2026-05-12 10:50:10','2026-05-13 07:19:22'),(3,1,'th681uRncz7Y7kyheNk6Km1xbV6Vs8BnN05ElBfZ.jpg','product-images','image/jpeg',51867,3,0,'completed','2026-05-12 10:50:20','2026-05-13 07:19:24'),(4,1,'Q5NG6MJgwj0QOXg8JWB9FhENP5UpVOffKrsYXo6K.jpg','product-images','image/jpeg',23565,4,0,'completed','2026-05-12 10:50:30','2026-05-13 07:19:25'),(5,1,'3C2NW2dTjYALqSn2sqC5uBAlwLgVxVNm7RE3dqwl.jpg','product-images','image/jpeg',422067,5,0,'completed','2026-05-13 01:22:25','2026-05-13 07:19:28'),(6,1,'VsHncinpyRneiWdHTah1uCZSd68FYTBydfIvvngr.webp','product-images','image/webp',6492,6,0,'completed','2026-05-13 01:23:57','2026-05-13 07:19:30'),(7,2,'0cLbBXGQ6xdvck7O8uIvuf1tD60xFFq7w6EV3h6t.jpg','product-images','image/jpeg',422067,1,1,'pending','2026-05-13 07:22:39','2026-05-13 07:22:39');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `price_amount` bigint(20) unsigned NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `stock_quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `location_country_code` varchar(2) DEFAULT NULL,
  `location_state` varchar(255) DEFAULT NULL,
  `location_city` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `moderation_status` varchar(255) NOT NULL DEFAULT 'approved',
  `visibility` varchar(255) NOT NULL DEFAULT 'public',
  `allow_offers` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `store_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `product_condition_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `schedule_at` timestamp NULL DEFAULT NULL,
  `auto_post` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_store_id_index` (`store_id`),
  KEY `products_category_id_index` (`category_id`),
  KEY `products_product_condition_id_index` (`product_condition_id`),
  KEY `products_status_index` (`status`),
  KEY `products_moderation_status_index` (`moderation_status`),
  KEY `products_published_at_index` (`published_at`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_product_condition_id_foreign` FOREIGN KEY (`product_condition_id`) REFERENCES `product_conditions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Kon Kmeng','kon-kmeng',NULL,'Lok kon Kmeng Muy',2000,2000,'USD',1,'YaWrKXwcrc4VFd1DGVo6Yb5iOlyUh3HUOkJ7qEVf.jpg','Phnom Penh','CA',NULL,'Phnom Penh','published','approved','public',1,'2026-05-12 10:48:57',1,3,NULL,NULL,'2026-05-12 10:48:57','2026-05-12 11:06:43','2026-05-11 10:07:00',NULL,NULL),(2,'TEst','test',NULL,'s',0,0,'USD',1,'0cLbBXGQ6xdvck7O8uIvuf1tD60xFFq7w6EV3h6t.jpg',NULL,NULL,NULL,NULL,'published','approved','public',1,'2026-05-13 07:22:39',1,3,NULL,NULL,'2026-05-13 07:22:39','2026-05-13 07:22:39',NULL,NULL,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduled_posts`
--

DROP TABLE IF EXISTS `scheduled_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scheduled_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `social_post_id` bigint(20) unsigned NOT NULL,
  `scheduled_for` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduled_posts_social_post_id_index` (`social_post_id`),
  KEY `scheduled_posts_scheduled_for_index` (`scheduled_for`),
  KEY `scheduled_posts_status_index` (`status`),
  KEY `scheduled_posts_status_scheduled_for_index` (`status`,`scheduled_for`),
  CONSTRAINT `scheduled_posts_social_post_id_foreign` FOREIGN KEY (`social_post_id`) REFERENCES `social_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_posts`
--

LOCK TABLES `scheduled_posts` WRITE;
/*!40000 ALTER TABLE `scheduled_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduled_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('mB58dMLNvu1K6EKv4kZKte52aWM6p2wSi0UXWS6s',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUFEMDFlNkxScmdsS3l5cDBtcEhRd1hkSWhOM3o0Z0hnUVFqZHlMRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778660522);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shared_products`
--

DROP TABLE IF EXISTS `shared_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shared_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `platform` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'shared',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `shared_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shared_products_user_id_index` (`user_id`),
  KEY `shared_products_product_id_index` (`product_id`),
  KEY `shared_products_platform_index` (`platform`),
  KEY `shared_products_shared_at_index` (`shared_at`),
  CONSTRAINT `shared_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shared_products_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shared_products`
--

LOCK TABLES `shared_products` WRITE;
/*!40000 ALTER TABLE `shared_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `shared_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_accounts`
--

DROP TABLE IF EXISTS `social_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `platform` varchar(255) NOT NULL,
  `provider_user_id` varchar(255) NOT NULL,
  `provider_account_name` varchar(255) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scopes`)),
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_accounts_platform_provider_user_id_unique` (`platform`,`provider_user_id`),
  KEY `social_accounts_user_id_index` (`user_id`),
  KEY `social_accounts_platform_index` (`platform`),
  KEY `social_accounts_status_index` (`status`),
  CONSTRAINT `social_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_accounts`
--

LOCK TABLES `social_accounts` WRITE;
/*!40000 ALTER TABLE `social_accounts` DISABLE KEYS */;
INSERT INTO `social_accounts` VALUES (1,1,'facebook','1374007090.1773112723',NULL,NULL,NULL,NULL,'[]','disconnected','2026-05-12 10:33:27','2026-05-12 10:33:27','2026-05-12 11:01:58'),(2,1,'facebook','61589094608903',NULL,'eyJpdiI6IlBPWTQwUHhCdjhaMmVSc09lam9RdVE9PSIsInZhbHVlIjoiMnVqcndjdk5nS01Rc1R4QlBOL2V4WlpyTE9FYXREbEpjaGp5cjJrbGpVaz0iLCJtYWMiOiJmMmU4NmE4MTFhZTUwMjhhODEwNzM4Y2I0MDdiNjdmYTg5YjAwMmVmZDRkNGRhYjBiN2M3ZGY3OTJhZGM1NWUzIiwidGFnIjoiIn0=',NULL,NULL,'[]','active','2026-05-12 11:05:34','2026-05-12 11:05:34','2026-05-12 11:05:34');
/*!40000 ALTER TABLE `social_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_posts`
--

DROP TABLE IF EXISTS `social_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `social_account_id` bigint(20) unsigned DEFAULT NULL,
  `platform` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `media_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`media_payload`)),
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `provider_post_id` varchar(255) DEFAULT NULL,
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_posts_user_id_index` (`user_id`),
  KEY `social_posts_product_id_index` (`product_id`),
  KEY `social_posts_social_account_id_index` (`social_account_id`),
  KEY `social_posts_platform_index` (`platform`),
  KEY `social_posts_status_index` (`status`),
  KEY `social_posts_posted_at_index` (`posted_at`),
  CONSTRAINT `social_posts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `social_posts_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `social_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_posts`
--

LOCK TABLES `social_posts` WRITE;
/*!40000 ALTER TABLE `social_posts` DISABLE KEYS */;
INSERT INTO `social_posts` VALUES (1,1,1,1,'facebook','Kon Kmeng - USD 2000','{\"image\":null,\"title\":\"Kon Kmeng\",\"description\":\"Lok kon Kmeng Muy\",\"price_amount\":2000,\"currency\":\"USD\",\"location_city\":\"Phnom Penh\"}','queued',NULL,NULL,NULL,NULL,'2026-05-12 10:48:57','2026-05-12 10:48:57');
/*!40000 ALTER TABLE `social_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `followers_count` int(10) unsigned NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_slug_unique` (`slug`),
  KEY `stores_user_id_index` (`user_id`),
  KEY `stores_status_index` (`status`),
  KEY `stores_is_verified_index` (`is_verified`),
  KEY `stores_city_index` (`city`),
  CONSTRAINT `stores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stores`
--

LOCK TABLES `stores` WRITE;
/*!40000 ALTER TABLE `stores` DISABLE KEYS */;
INSERT INTO `stores` VALUES (3,1,'BOMBOK','bombok',NULL,NULL,'Please support my store','nadrayoky000@gmail.com','0716249197','CA',NULL,'Phnom Penh',NULL,'active',0,0,'2026-05-12 10:46:25','2026-05-12 10:46:25','2026-05-12 10:47:26',NULL);
/*!40000 ALTER TABLE `stores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `cover_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `default_store_id` bigint(20) unsigned DEFAULT NULL,
  `is_seller` tinyint(1) NOT NULL DEFAULT 0,
  `profile_visibility` varchar(255) NOT NULL DEFAULT 'public',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `user_profiles_username_unique` (`username`),
  KEY `user_profiles_default_store_id_foreign` (`default_store_id`),
  KEY `user_profiles_is_seller_index` (`is_seller`),
  KEY `user_profiles_profile_visibility_index` (`profile_visibility`),
  CONSTRAINT `user_profiles_default_store_id_foreign` FOREIGN KEY (`default_store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
INSERT INTO `user_profiles` VALUES (1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,1,'public','2026-05-12 10:20:18','2026-05-12 10:46:25');
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_status_index` (`status`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'mai soklyna','nadrayoky000@gmail.com',NULL,'$2y$12$e.e8JHz/8X2mSUjFXq.wx.hTAVn/iM8soHk.iwZwLSo9vQMTR.h/O','active','admin','2026-05-13 08:59:45',NULL,NULL,'2026-05-12 10:20:18','2026-05-13 09:03:24',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlists_product_id_index` (`product_id`),
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'marketplace_backend'
--

--
-- Dumping routines for database 'marketplace_backend'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-14 13:11:06
