<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTemplates extends Migration
{
    public function up()
    {
        $this->db->simpleQuery("CREATE TABLE `templates` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NOT NULL DEFAULT '',
            `lang` VARCHAR(2) NOT NULL DEFAULT '',
            `template` MEDIUMTEXT NOT NULL DEFAULT '',
            `logo` TEXT(65535) NULL DEFAULT NULL,
            `color` VARCHAR(50) NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            INDEX `lang` (`lang`) USING BTREE
        )");
    }

    public function down()
    {
        $this->forge->dropTable('templates');
    }
}
