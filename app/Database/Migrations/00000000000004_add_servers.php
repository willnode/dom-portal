<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServers extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE `servers` (
            `id` int(11) NOT NULL,
            `alias` varchar(255) NOT NULL,
            `ip` varchar(255) NOT NULL,
            `domain` varchar(255) NOT NULL,
            `description` varchar(50) DEFAULT NULL,
            `scheme_id` int(11) NOT NULL,
            `capacity` int(11) NOT NULL DEFAULT 1,
            `public` int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`) USING BTREE,
            UNIQUE KEY `slave_alias` (`alias`) USING BTREE
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS `servers__stat` (
            `server_id` int(11) NOT NULL,
            `metadata` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`server_id`) USING BTREE,
            CONSTRAINT `FK_slaves__stat_slaves` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
        )");
    }

    public function down()
    {
        $this->forge->dropTable('servers');
        $this->forge->dropTable('servers__stat');
    }
}