<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServers extends Migration
{
    public function up()
    {
        $this->db->simpleQuery("CREATE TABLE `servers` (
            `id` int(11) NOT NULL,
            `alias` varchar(255) NOT NULL,
            `ip` varchar(255) NOT NULL,
            `domain` varchar(255) NOT NULL,
            `description` varchar(50) DEFAULT NULL,
            `scheme_id` int(11) NOT NULL,
            `capacity` int(11) NOT NULL DEFAULT 1,
            `public` int(11) NOT NULL DEFAULT 1,
            `status` varchar(50) NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slave_alias` (`alias`)
        )");

        $this->db->simpleQuery("CREATE TABLE IF NOT EXISTS `servers__stat` (
            `server_id` int(11) NOT NULL,
            `metadata` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`server_id`),
            CONSTRAINT `FK_slaves__stat_slaves` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
        )");

        $this->db->simpleQuery("INSERT INTO `servers`  VALUES
	        (1, 'sga', '0.0.0.0', '.dom.my.id', 'Singapore A', 1, 80, 1, '')
        ");
    }

    public function down()
    {
        $this->forge->dropTable('servers');
        $this->forge->dropTable('servers__stat');
    }
}
