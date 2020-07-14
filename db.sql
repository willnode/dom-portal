-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.7-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table dbdom.domain
CREATE TABLE IF NOT EXISTS `domain` (
  `domain_id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_login` int(11) NOT NULL,
  `domain_liquid` int(11) DEFAULT NULL,
  `domain_name` varchar(255) NOT NULL,
  `domain_scheme` int(11) DEFAULT NULL,
  `domain_created` timestamp NULL DEFAULT current_timestamp(),
  `domain_expired` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`domain_id`),
  UNIQUE KEY `domain_name` (`domain_name`),
  UNIQUE KEY `domain_liquid` (`domain_liquid`),
  KEY `FK_domain_login` (`domain_login`),
  KEY `FK_domain_schemes` (`domain_scheme`),
  CONSTRAINT `FK_domain_login` FOREIGN KEY (`domain_login`) REFERENCES `login` (`login_id`),
  CONSTRAINT `FK_domain_schemes` FOREIGN KEY (`domain_scheme`) REFERENCES `schemes` (`scheme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.hosting
CREATE TABLE IF NOT EXISTS `hosting` (
  `hosting_id` int(11) NOT NULL AUTO_INCREMENT,
  `hosting_login` int(11) NOT NULL,
  `hosting_username` varchar(255) DEFAULT NULL,
  `hosting_domain` int(11) DEFAULT NULL,
  `hosting_password` varchar(255) DEFAULT NULL,
  `hosting_slave` int(11) DEFAULT NULL,
  `hosting_created` timestamp NULL DEFAULT current_timestamp(),
  `hosting_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`hosting_id`),
  UNIQUE KEY `hosting_username` (`hosting_username`),
  UNIQUE KEY `hosting_cname` (`hosting_domain`) USING BTREE,
  KEY `FK_hosting_login` (`hosting_login`),
  KEY `FK_hosting_slaves` (`hosting_slave`),
  CONSTRAINT `FK_hosting_login` FOREIGN KEY (`hosting_login`) REFERENCES `login` (`login_id`),
  CONSTRAINT `FK_hosting_slaves` FOREIGN KEY (`hosting_slave`) REFERENCES `slaves` (`slave_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for view dbdom.hosting__display
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `hosting__display` (
	`hosting_id` INT(11) NOT NULL,
	`hosting_login` INT(11) NOT NULL,
	`hosting_username` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`hosting_domain` INT(11) NULL,
	`hosting_password` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`hosting_slave` INT(11) NULL,
	`hosting_created` TIMESTAMP NULL,
	`hosting_updated` TIMESTAMP NULL,
	`domain_id` INT(11) NOT NULL,
	`domain_login` INT(11) NOT NULL,
	`domain_liquid` INT(11) NULL,
	`domain_name` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`domain_scheme` INT(11) NULL,
	`domain_created` TIMESTAMP NULL,
	`domain_expired` TIMESTAMP NULL,
	`purchase_id` INT(11) NOT NULL,
	`purchase_hosting` INT(11) NULL,
	`purchase_liquid` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`purchase_active` INT(11) NOT NULL,
	`purchase_price` INT(11) NOT NULL,
	`purchase_issued` TIMESTAMP NOT NULL,
	`purchase_invoiced` TIMESTAMP NULL,
	`purchase_expired` TIMESTAMP NOT NULL,
	`purchase_plan` INT(11) NOT NULL,
	`purchase_years` INT(11) NULL,
	`purchase_status` ENUM('pending','canceled','active','suspended','expired') NOT NULL COLLATE 'latin1_swedish_ci',
	`purchase_template` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`purchase_challenge` VARCHAR(9) NULL COLLATE 'latin1_swedish_ci',
	`plan_id` INT(11) NOT NULL,
	`plan_alias` VARCHAR(16) NOT NULL COLLATE 'latin1_swedish_ci',
	`plan_price` INT(11) NOT NULL,
	`plan_cpu` INT(11) NOT NULL,
	`plan_disk` INT(11) NOT NULL,
	`plan_net` INT(11) NOT NULL,
	`plan_dbs` INT(11) NOT NULL,
	`plan_subservs` INT(11) NOT NULL,
	`plan_features` INT(11) NOT NULL,
	`slave_id` INT(11) NOT NULL,
	`slave_alias` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`slave_ip` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

-- Dumping structure for table dbdom.liquid
CREATE TABLE IF NOT EXISTS `liquid` (
  `liquid_id` int(11) NOT NULL,
  `liquid_login` int(11) NOT NULL,
  `liquid_password` varchar(255) NOT NULL,
  `liquid_cache_customer` text DEFAULT NULL,
  `liquid_cache_contacts` text DEFAULT NULL,
  `liquid_cache_domains` text DEFAULT NULL,
  `liquid_pending_transactions` text DEFAULT NULL,
  `liquid_default_contacts` text DEFAULT NULL,
  `liquid_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `liquid_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`liquid_id`),
  KEY `FK_liquid_login` (`liquid_login`),
  CONSTRAINT `FK_liquid_login` FOREIGN KEY (`liquid_login`) REFERENCES `login` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.login
CREATE TABLE IF NOT EXISTS `login` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` char(60) DEFAULT NULL,
  `otp` char(6) DEFAULT NULL,
  `lang` enum('id','en') NOT NULL DEFAULT 'id',
  `account_created` timestamp NULL DEFAULT current_timestamp(),
  `email_verified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`login_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.plans
CREATE TABLE IF NOT EXISTS `plans` (
  `plan_id` int(11) NOT NULL,
  `plan_alias` varchar(16) NOT NULL,
  `plan_price` int(11) NOT NULL DEFAULT 0,
  `plan_cpu` int(11) NOT NULL DEFAULT 0,
  `plan_disk` int(11) NOT NULL DEFAULT 0,
  `plan_net` int(11) NOT NULL DEFAULT 0,
  `plan_dbs` int(11) NOT NULL DEFAULT 0,
  `plan_subservs` int(11) NOT NULL DEFAULT 0,
  `plan_features` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`plan_id`) USING BTREE,
  UNIQUE KEY `plan_alias` (`plan_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.purchase
CREATE TABLE IF NOT EXISTS `purchase` (
  `purchase_id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_hosting` int(11) DEFAULT NULL,
  `purchase_liquid` varchar(255) DEFAULT NULL,
  `purchase_active` int(11) NOT NULL DEFAULT 0,
  `purchase_price` int(11) NOT NULL DEFAULT 0,
  `purchase_issued` timestamp NOT NULL DEFAULT current_timestamp(),
  `purchase_invoiced` timestamp NULL DEFAULT NULL,
  `purchase_expired` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `purchase_plan` int(11) NOT NULL,
  `purchase_years` int(11) DEFAULT NULL,
  `purchase_status` enum('pending','canceled','active','suspended','expired') NOT NULL DEFAULT 'pending',
  `purchase_template` varchar(255) DEFAULT NULL,
  `purchase_challenge` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`purchase_id`),
  KEY `FK_purchase_plans` (`purchase_plan`),
  KEY `FK_purchase_hosting` (`purchase_hosting`),
  CONSTRAINT `FK_purchase_hosting` FOREIGN KEY (`purchase_hosting`) REFERENCES `hosting` (`hosting_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_purchase_plans` FOREIGN KEY (`purchase_plan`) REFERENCES `plans` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.schemes
CREATE TABLE IF NOT EXISTS `schemes` (
  `scheme_id` int(11) NOT NULL AUTO_INCREMENT,
  `scheme_alias` varchar(64) DEFAULT NULL,
  `scheme_price` int(11) DEFAULT NULL,
  `scheme_renew` int(11) DEFAULT NULL,
  PRIMARY KEY (`scheme_id`),
  UNIQUE KEY `scheme_alias` (`scheme_alias`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table dbdom.slaves
CREATE TABLE IF NOT EXISTS `slaves` (
  `slave_id` int(11) NOT NULL,
  `slave_alias` varchar(255) NOT NULL,
  `slave_ip` varchar(255) NOT NULL,
  PRIMARY KEY (`slave_id`),
  UNIQUE KEY `slave_alias` (`slave_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for view dbdom.slaves__usage
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `slaves__usage` (
	`slave_id` INT(11) NOT NULL,
	`slave_alias` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`slave_ip` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`utilization` DECIMAL(36,4) NULL
) ENGINE=MyISAM;

-- Dumping structure for table dbdom.templates
CREATE TABLE IF NOT EXISTS `templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) DEFAULT NULL,
  `template_category` varchar(255) DEFAULT NULL,
  `template_alias` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for view dbdom.hosting__display
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `hosting__display`;
CREATE ALGORITHM=MERGE SQL SECURITY DEFINER VIEW `hosting__display` AS SELECT hosting.*, domain.*, purchase.*, plans.*, slaves.* FROM hosting
JOIN purchase ON hosting.hosting_id = purchase.purchase_hosting AND purchase.purchase_active = 1
JOIN plans ON purchase.purchase_plan = plans.plan_id
JOIN slaves ON slaves.slave_id = hosting.hosting_slave
JOIN domain ON hosting.hosting_domain = domain.domain_id
LEFT JOIN schemes ON domain.domain_scheme = schemes.scheme_id ;

-- Dumping structure for view dbdom.slaves__usage
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `slaves__usage`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `slaves__usage` AS SELECT slaves.*, COALESCE(SUM(plans.plan_cpu) / (24 * 30 * 2), 0) utilization
FROM slaves
LEFT JOIN hosting ON hosting.hosting_slave = slaves.slave_id
LEFT JOIN purchase ON purchase.purchase_hosting = hosting.hosting_id AND purchase.purchase_active = 1
LEFT JOIN plans ON purchase.purchase_plan = plans.plan_id
GROUP BY slaves.slave_id ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
