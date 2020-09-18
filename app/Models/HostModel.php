<?php namespace App\Models;

use CodeIgniter\Model;

class HostingModel extends Model
{
    protected $table         = 'hosts';
    protected $allowedFields = [
        'login_id', 'username', 'domain', 'password', 'liquid_id', 'scheme_id', 'slave_id', 'plan_id', 'backup_bw', 'notification', 'expiry_at'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Host';
    protected $useTimestamps = true;
}