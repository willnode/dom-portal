<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDomains extends Migration
{
    public function up()
    {
        $this->db->simpleQuery("CREATE TABLE `domains` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `login_id` INT(11) NOT NULL,
            `name` VARCHAR(64) NOT NULL,
            `scheme_id` INT(11) NULL DEFAULT NULL,
            `status` ENUM('pending','active','expired') NULL DEFAULT 'pending',
            `created_at` TIMESTAMP NULL DEFAULT NULL,
            `updated_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            INDEX (`login_id`),
            INDEX (`scheme_id`),
            CONSTRAINT FOREIGN KEY (`login_id`) REFERENCES `login` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
            CONSTRAINT FOREIGN KEY (`scheme_id`) REFERENCES `schemes` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
        )");
    }

    public function down()
    {
        $this->forge->dropTable('domains');
    }
}
