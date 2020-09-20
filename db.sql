-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.13-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table dbdom.hosts
CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('active','pending','starting','suspended','expired') NOT NULL DEFAULT 'active',
  `liquid_id` int(11) DEFAULT NULL,
  `scheme_id` int(11) DEFAULT NULL,
  `server_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `addons` int(11) NOT NULL DEFAULT 0,
  `notification` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expiry_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `hosting_username` (`username`) USING BTREE,
  UNIQUE KEY `hosting_cname` (`domain`) USING BTREE,
  KEY `FK_hosting_login` (`login_id`) USING BTREE,
  KEY `FK_hosting_slaves` (`server_id`) USING BTREE,
  CONSTRAINT `FK_hosting_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_hosting_slaves` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.hosts__stat
CREATE TABLE IF NOT EXISTS `hosts__stat` (
  `host_id` int(11) NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quota_server` bigint(20) DEFAULT NULL,
  `quota_user` bigint(20) DEFAULT NULL,
  `quota_db` bigint(20) DEFAULT NULL,
  `quota_net` bigint(20) DEFAULT NULL,
  `features` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bandwidths` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`host_id`) USING BTREE,
  CONSTRAINT `FK_hosting__stat_hosting` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.liquid
CREATE TABLE IF NOT EXISTS `liquid` (
  `id` int(11) NOT NULL,
  `login_id` int(11) NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacts` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domains` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pending_transactions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_contacts` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_liquid_login` (`login_id`) USING BTREE,
  CONSTRAINT `FK_liquid_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.login
CREATE TABLE IF NOT EXISTS `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` char(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` char(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` enum('id','en') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.plans
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int(11) NOT NULL,
  `alias` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_idr` int(11) NOT NULL DEFAULT 0,
  `price_usd` int(11) NOT NULL DEFAULT 0,
  `disk` int(11) NOT NULL DEFAULT 0,
  `net` int(11) NOT NULL DEFAULT 0,
  `dbs` int(11) NOT NULL DEFAULT 0,
  `subservs` int(11) NOT NULL DEFAULT 0,
  `features` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `plan_alias` (`alias`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.purchases
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `status` enum('pending','canceled','active','suspended','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `metadata` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_purchase_hosting` (`host_id`) USING BTREE,
  CONSTRAINT `FK_purchase_hosting` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.schemes
CREATE TABLE IF NOT EXISTS `schemes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_idr` int(11) DEFAULT NULL,
  `renew_idr` int(11) DEFAULT NULL,
  `price_usd` int(11) DEFAULT NULL,
  `renew_usd` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `scheme_alias` (`alias`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.servers
CREATE TABLE IF NOT EXISTS `servers` (
  `id` int(11) NOT NULL,
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheme_id` int(11) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `public` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `slave_alias` (`alias`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.servers__stat
CREATE TABLE IF NOT EXISTS `servers__stat` (
  `server_id` int(11) NOT NULL,
  `cpu_avg` float(12,2) DEFAULT NULL,
  `ram_used` bigint(20) DEFAULT NULL,
  `ram_cache` bigint(20) DEFAULT NULL,
  `ram_total` bigint(20) DEFAULT NULL,
  `swap_used` bigint(20) DEFAULT NULL,
  `swap_total` bigint(20) DEFAULT NULL,
  `disk_free` bigint(20) DEFAULT NULL,
  `disk_total` bigint(20) DEFAULT NULL,
  `httpd` int(11) DEFAULT NULL,
  `fpm` int(11) DEFAULT NULL,
  `bind` int(11) DEFAULT NULL,
  `sshd` int(11) DEFAULT NULL,
  `mysqld` int(11) DEFAULT NULL,
  `postgres` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`server_id`) USING BTREE,
  CONSTRAINT `FK_slaves__stat_slaves` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.templates
CREATE TABLE IF NOT EXISTS `templates` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.templates__index
CREATE TABLE IF NOT EXISTS `templates__index` (
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `match` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '100',
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`domain`,`match`),
  KEY `domain` (`domain`),
  KEY `templates__index_ibfk_1` (`target`),
  CONSTRAINT `templates__index_ibfk_1` FOREIGN KEY (`target`) REFERENCES `templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.templates__repos
CREATE TABLE IF NOT EXISTS `templates__repos` (
  `type` enum('github') COLLATE utf8mb4_unicode_ci NOT NULL,
  `repo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`type`,`repo`),
  KEY `FK_templates__repos_templates` (`target`),
  CONSTRAINT `FK_templates__repos_templates` FOREIGN KEY (`target`) REFERENCES `templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
