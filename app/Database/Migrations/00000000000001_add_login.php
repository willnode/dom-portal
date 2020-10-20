<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogin extends Migration
{
    public function up()
    {
       $this->db->simpleQuery("CREATE TABLE `login` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `phone` varchar(255) DEFAULT NULL,
            `password` char(60) DEFAULT NULL,
            `otp` char(9) DEFAULT NULL,
            `lang` enum('id','en') NOT NULL DEFAULT 'id',
            `trustiness` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `email_verified_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `phone` (`phone`)
        )");
    }

    public function down()
    {
        $this->forge->dropTable('login');
    }
}
