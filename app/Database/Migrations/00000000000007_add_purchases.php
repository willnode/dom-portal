<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurchases extends Migration
{
    public function up()
    {
        $this->db->simpleQuery("CREATE TABLE `purchases` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `host_id` INT(11) NULL DEFAULT NULL,
            `domain_id` INT(11) NULL DEFAULT NULL,
            `status` ENUM('pending','active') NOT NULL DEFAULT 'pending',
            `metadata` TEXT(65535) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`),
            INDEX (`host_id`),
            INDEX (`domain_id`),
            CONSTRAINT FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL,
            CONSTRAINT FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON UPDATE RESTRICT ON DELETE SET NULL
        )");
    }

    public function down()
    {
        $this->forge->dropTable('purchases');
    }
}
