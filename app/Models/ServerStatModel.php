<?php namespace App\Models;

use CodeIgniter\Model;

class HostStatModel extends Model
{
    protected $table         = 'servers__stat';
    protected $allowedFields = [
        'server_id', 'cpu_avg', 'ram_used', 'ram_cache', 'ram_total', 'swap_used', 'swap_total', 'disk_free', 'disk_total', 'httpd', 'fpm', 'bind', 'sshd', 'mysqld', 'postgres',
    ];
    protected $primaryKey = 'server_id';
    protected $returnType = 'App\Entities\ServerStat';
    protected $useTimestamps = true;
}