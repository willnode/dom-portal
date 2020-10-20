<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHosts extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE `hosts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `login_id` int(11) NOT NULL,
            `username` varchar(255) DEFAULT NULL,
            `domain` varchar(255) DEFAULT NULL,
            `password` varchar(255) DEFAULT NULL,
            `status` enum('active','pending','starting','suspended','expired','removed') NOT NULL DEFAULT 'active',
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
        )");

        $this->db->query("CREATE TABLE `hosts__deploys` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `host_id` int(11) NOT NULL DEFAULT 0,
            `domain` varchar(255) NOT NULL DEFAULT '',
            `template` text NOT NULL DEFAULT '',
            `result` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `FK_hosts__deploys_hosts` (`host_id`),
            CONSTRAINT `FK_hosts__deploys_hosts` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
        )");

        $this->db->query("CREATE TABLE `hosts__stat` (
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
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`host_id`) USING BTREE,
            CONSTRAINT `FK_hosting__stat_hosting` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
        )");

        $this->db->query("CREATE TABLE `purchases` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `host_id` int(11) DEFAULT NULL,
            `status` enum('pending','canceled','active','suspended','expired') NOT NULL DEFAULT 'pending',
            `metadata` text DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            KEY `FK_purchase_hosting` (`host_id`) USING BTREE,
            CONSTRAINT `FK_purchase_hosting` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE
        )");
    }

    public function down()
    {
        $this->forge->dropTable('hosts');
        $this->forge->dropTable('hosts__deploys');
        $this->forge->dropTable('hosts__stat');
        $this->forge->dropTable('purchases');
    }
}
