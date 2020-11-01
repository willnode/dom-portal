<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHosts extends Migration
{
    public function up()
    {
        $this->db->simpleQuery("CREATE TABLE `hosts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `login_id` int(11) NOT NULL,
            `username` varchar(255) DEFAULT NULL,
            `domain` varchar(255) DEFAULT NULL,
            `password` varchar(255) DEFAULT NULL,
            `status` enum('active','pending','starting','suspended','expired','removed') NOT NULL DEFAULT 'active',
            `liquid_id` int(11) DEFAULT NULL,
            `server_id` int(11) NOT NULL,
            `plan_id` int(11) NOT NULL,
            `addons` int(11) NOT NULL DEFAULT 0,
            `notification` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `expiry_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY (`username`),
            UNIQUE KEY (`domain`),
            KEY (`login_id`),
            KEY (`server_id`),
            CONSTRAINT FOREIGN KEY (`login_id`) REFERENCES `login` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
        )");

        $this->db->simpleQuery("CREATE TABLE `hosts__deploy` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `host_id` int(11) NOT NULL DEFAULT 0,
            `domain` varchar(255) NOT NULL DEFAULT '',
            `template` text NOT NULL DEFAULT '',
            `result` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY (`host_id`),
            CONSTRAINT FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
        )");

        $this->db->simpleQuery("CREATE TABLE `hosts__stat` (
            `host_id` int(11) NOT NULL,
            `domain` varchar(255) DEFAULT NULL,
            `identifier` varchar(255) DEFAULT NULL,
            `password` varchar(255) DEFAULT NULL,
            `quota_server` bigint(20) DEFAULT NULL,
            `quota_user` bigint(20) DEFAULT NULL,
            `quota_db` bigint(20) DEFAULT NULL,
            `quota_net` bigint(20) DEFAULT NULL,
            `features` varchar(255) DEFAULT NULL,
            `disabled` varchar(255) DEFAULT NULL,
            `bandwidths` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL,
            PRIMARY KEY (`host_id`),
            CONSTRAINT FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
        )");

        $this->db->simpleQuery("CREATE TABLE `hosts__coupon` (
            `code` VARCHAR(50) NOT NULL,
            `redeems` INT(11) NOT NULL,
            `currency` VARCHAR(50) NULL DEFAULT NULL,
            `min` FLOAT(12) NULL DEFAULT NULL,
            `max` FLOAT(12) NULL DEFAULT NULL,
            `discount` FLOAT(12) NULL DEFAULT NULL,
            `default_plan_id` INT(11) NOT NULL DEFAULT '1',
            `expiry_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`code`) USING BTREE
        )");
    }

    public function down()
    {
        $this->forge->dropTable('hosts');
        $this->forge->dropTable('hosts__deploy');
        $this->forge->dropTable('hosts__stat');
        $this->forge->dropTable('hosts__coupon');
    }
}
