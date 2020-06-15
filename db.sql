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

-- Dumping structure for table dbdom.hosting
CREATE TABLE IF NOT EXISTS `hosting` (
  `hosting_id` int(11) NOT NULL AUTO_INCREMENT,
  `hosting_login` int(11) NOT NULL,
  `hosting_username` varchar(255) DEFAULT NULL,
  `hosting_cname` varchar(255) DEFAULT NULL,
  `hosting_slave` int(11) DEFAULT NULL,
  `hosting_created` timestamp NULL DEFAULT current_timestamp(),
  `hosting_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`hosting_id`),
  UNIQUE KEY `hosting_username` (`hosting_username`),
  UNIQUE KEY `hosting_cname` (`hosting_cname`),
  KEY `FK_hosting_login` (`hosting_login`),
  KEY `FK_hosting_slaves` (`hosting_slave`),
  CONSTRAINT `FK_hosting_login` FOREIGN KEY (`hosting_login`) REFERENCES `login` (`login_id`),
  CONSTRAINT `FK_hosting_slaves` FOREIGN KEY (`hosting_slave`) REFERENCES `slaves` (`slave_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dbdom.hosting: ~0 rows (approximately)
DELETE FROM `hosting`;
/*!40000 ALTER TABLE `hosting` DISABLE KEYS */;
/*!40000 ALTER TABLE `hosting` ENABLE KEYS */;

-- Dumping structure for view dbdom.hosting__display
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `hosting__display` (
	`hosting_id` INT(11) NOT NULL,
	`hosting_login` INT(11) NOT NULL,
	`hosting_username` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`hosting_cname` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`hosting_slave` INT(11) NULL,
	`hosting_created` TIMESTAMP NULL,
	`hosting_updated` TIMESTAMP NULL,
	`purchase_id` INT(11) NOT NULL,
	`purchase_hosting` INT(11) NULL,
	`purchase_active` INT(11) NOT NULL,
	`purchase_price` INT(11) NOT NULL,
	`purchase_invoiced` TIMESTAMP NOT NULL,
	`purchase_expired` TIMESTAMP NOT NULL,
	`purchase_plan` INT(11) NOT NULL,
	`purchase_years` INT(11) NULL,
	`purchase_status` ENUM('pending','canceled','active','expired') NOT NULL COLLATE 'latin1_swedish_ci',
	`purchase_template` ENUM('','wordpress','phpbb','opencart') NOT NULL COLLATE 'latin1_swedish_ci',
	`purchase_session` VARCHAR(255) NULL COLLATE 'latin1_swedish_ci',
	`purchase_challenge` VARCHAR(6) NULL COLLATE 'latin1_swedish_ci',
	`plan_id` INT(11) NOT NULL,
	`plan_alias` VARCHAR(16) NOT NULL COLLATE 'latin1_swedish_ci',
	`plan_price` INT(11) NOT NULL,
	`plan_cpu` INT(11) NOT NULL,
	`plan_disk` INT(11) NOT NULL,
	`plan_net` INT(11) NOT NULL,
	`plan_dbs` INT(11) NOT NULL,
	`plan_emails` INT(11) NOT NULL,
	`plan_subservs` INT(11) NOT NULL,
	`plan_features` INT(11) NOT NULL,
	`slave_id` INT(11) NOT NULL,
	`slave_alias` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`slave_ip` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`default_domain` VARCHAR(265) NULL COLLATE 'latin1_swedish_ci'
) ENGINE=MyISAM;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Dumping data for table dbdom.login: ~8 rows (approximately)
DELETE FROM `login`;

-- Dumping structure for table dbdom.plans
CREATE TABLE IF NOT EXISTS `plans` (
  `plan_id` int(11) NOT NULL,
  `plan_alias` varchar(16) NOT NULL,
  `plan_price` int(11) NOT NULL DEFAULT 0,
  `plan_cpu` int(11) NOT NULL DEFAULT 0,
  `plan_disk` int(11) NOT NULL DEFAULT 0,
  `plan_net` int(11) NOT NULL DEFAULT 0,
  `plan_dbs` int(11) NOT NULL DEFAULT 0,
  `plan_emails` int(11) NOT NULL DEFAULT 0,
  `plan_subservs` int(11) NOT NULL DEFAULT 0,
  `plan_features` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`plan_id`) USING BTREE,
  UNIQUE KEY `plan_alias` (`plan_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dbdom.plans: ~7 rows (approximately)
DELETE FROM `plans`;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` (`plan_id`, `plan_alias`, `plan_price`, `plan_cpu`, `plan_disk`, `plan_net`, `plan_dbs`, `plan_emails`, `plan_subservs`, `plan_features`) VALUES
	(1, 'Free', 0, 1, 100, 5, 1, 0, 0, 0),
	(2, 'Bronze', 3, 3, 200, 20, 1, 0, 1, 0),
	(3, 'Metal', 6, 6, 500, 50, 5, 1, 5, 1),
	(4, 'Silver', 8, 8, 800, 80, 5, 2, 8, 1),
	(5, 'Gold', 12, 12, 1500, 150, 10, 5, 10, 2),
	(6, 'Platinum', 20, 20, 3000, 300, 10, 10, 15, 3),
	(7, 'Legend', 30, 30, 5000, 500, 20, 20, 20, 3);
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;

-- Dumping structure for table dbdom.purchase
CREATE TABLE IF NOT EXISTS `purchase` (
  `purchase_id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_hosting` int(11) DEFAULT NULL,
  `purchase_active` int(11) NOT NULL DEFAULT 0,
  `purchase_price` int(11) NOT NULL DEFAULT 0,
  `purchase_invoiced` timestamp NOT NULL DEFAULT current_timestamp(),
  `purchase_expired` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `purchase_plan` int(11) NOT NULL,
  `purchase_years` int(11) DEFAULT NULL,
  `purchase_status` enum('pending','canceled','active','expired') NOT NULL DEFAULT 'pending',
  `purchase_template` enum('','wordpress','phpbb','opencart') NOT NULL DEFAULT '',
  `purchase_session` varchar(255) DEFAULT NULL,
  `purchase_challenge` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`purchase_id`),
  KEY `FK_purchase_plans` (`purchase_plan`),
  KEY `FK_purchase_hosting` (`purchase_hosting`),
  CONSTRAINT `FK_purchase_hosting` FOREIGN KEY (`purchase_hosting`) REFERENCES `hosting` (`hosting_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_purchase_plans` FOREIGN KEY (`purchase_plan`) REFERENCES `plans` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dbdom.purchase: ~0 rows (approximately)
DELETE FROM `purchase`;
/*!40000 ALTER TABLE `purchase` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase` ENABLE KEYS */;

-- Dumping structure for table dbdom.report
CREATE TABLE IF NOT EXISTS `report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_source` varchar(255) DEFAULT NULL,
  `report_domain` varchar(255) DEFAULT NULL,
  `report_reason` text DEFAULT NULL,
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dbdom.report: ~0 rows (approximately)
DELETE FROM `report`;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
/*!40000 ALTER TABLE `report` ENABLE KEYS */;

-- Dumping structure for table dbdom.slaves
CREATE TABLE IF NOT EXISTS `slaves` (
  `slave_id` int(11) NOT NULL,
  `slave_alias` varchar(255) NOT NULL,
  `slave_ip` varchar(255) NOT NULL,
  PRIMARY KEY (`slave_id`),
  UNIQUE KEY `slave_alias` (`slave_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dbdom.slaves: ~1 rows (approximately)
DELETE FROM `slaves`;
/*!40000 ALTER TABLE `slaves` DISABLE KEYS */;
INSERT INTO `slaves` (`slave_id`, `slave_alias`, `slave_ip`) VALUES
	(1, 'sv01', '52.231.195.242');
/*!40000 ALTER TABLE `slaves` ENABLE KEYS */;

-- Dumping structure for view dbdom.slaves__usage
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `slaves__usage` (
	`slave_id` INT(11) NOT NULL,
	`slave_alias` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`slave_ip` VARCHAR(255) NOT NULL COLLATE 'latin1_swedish_ci',
	`utilization` DECIMAL(36,4) NULL
) ENGINE=MyISAM;

-- Dumping structure for view dbdom.hosting__display
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `hosting__display`;
CREATE ALGORITHM=MERGE SQL SECURITY DEFINER VIEW `hosting__display` AS SELECT hosting.*, purchase.*, plans.*, slaves.*, 
CONCAT(hosting.hosting_username, '.dom.my.id') AS default_domain FROM hosting
JOIN purchase ON hosting.hosting_id = purchase.purchase_hosting AND purchase.purchase_active = 1
JOIN plans ON purchase.purchase_plan = plans.plan_id 
JOIN slaves ON slaves.slave_id = hosting.hosting_slave ;

-- Dumping structure for view dbdom.slaves__usage
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `slaves__usage`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `slaves__usage` AS SELECT slaves.*, COALESCE(SUM(plans.plan_cpu) / (24 * 30 * 2), 0) utilization 
FROM slaves 
LEFT JOIN hosting ON hosting.hosting_slave = slaves.slave_id 
LEFT JOIN purchase ON purchase.purchase_hosting = hosting.hosting_id AND purchase.purchase_active = 1
LEFT JOIN plans ON purchase.purchase_plan = plans.plan_id 
WHERE purchase.purchase_status = 'active' OR  purchase.purchase_status IS NULL
GROUP BY slaves.slave_id ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
