<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlans extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE `plans` (
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
    }

    public function down()
    {
        $this->forge->dropTable('plans');
    }
}
