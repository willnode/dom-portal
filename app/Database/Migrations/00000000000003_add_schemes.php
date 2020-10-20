<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchemes extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE `schemes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `alias` varchar(64) DEFAULT NULL,
            `price_idr` int(11) DEFAULT NULL,
            `renew_idr` int(11) DEFAULT NULL,
            `price_usd` int(11) DEFAULT NULL,
            `renew_usd` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `scheme_alias` (`alias`)
          )");

        $this->db->simpleQuery("INSERT INTO `schemes` VALUES
            (1, '.com', 160000, 160000, 11, 11),
            (2, '.org', 155000, 200000, 11, 14)
        ");
    }

    public function down()
    {
        $this->forge->dropTable('schemes');
    }
}
