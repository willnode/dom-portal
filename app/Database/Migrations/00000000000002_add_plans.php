<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlans extends Migration
{
    public function up()
    {
        $this->db->simpleQuery("CREATE TABLE `plans` (
            `id` int(11) NOT NULL,
            `alias` varchar(16) NOT NULL,
            `price_idr` int(11) NOT NULL DEFAULT 0,
            `price_usd` int(11) NOT NULL DEFAULT 0,
            `disk` int(11) NOT NULL DEFAULT 0,
            `net` int(11) NOT NULL DEFAULT 0,
            `dbs` int(11) NOT NULL DEFAULT 0,
            `subservs` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `plan_alias` (`alias`)
          )");

        $this->db->simpleQuery("INSERT INTO `plans` VALUES
            (1, 'Freedom', 0, 0, 256, 18, 1, 0),
            (2, 'Lite', 50000, 5, 1024, 60, 6, 5),
            (3, 'Pro', 200000, 20, 3072, 180, 12, 10),
            (4, 'Business', 600000, 60, 8192, 600, 25, 20)
        ");
    }

    public function down()
    {
        $this->forge->dropTable('plans');
    }
}
