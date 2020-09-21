<?php namespace App\Models;

use CodeIgniter\Model;

class HostStatModel extends Model
{
    protected $table         = 'hosts__stat';
    protected $allowedFields = [
        'host_id', 'domain', 'identifier', 'password', 'quota_server', 'quota_user', 'quota_db', 'quota_net', 'features', 'disabled', 'bandwidths', 'updated_at'
    ];
    protected $primaryKey = 'host_id';
    protected $returnType = 'App\Entities\HostStat';
    protected $useTimestamps = true;
}