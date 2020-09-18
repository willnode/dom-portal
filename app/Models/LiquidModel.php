<?php namespace App\Models;

use CodeIgniter\Model;

class LiquidModel extends Model
{
    protected $table         = 'liquid';
    protected $allowedFields = [
        'id', 'login_id', 'password', 'cache_customer', 'cache_contacts', 'cache_domains', 'pending_transacsions', 'default_contacts',
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Liquid';
    protected $useTimestamps = true;
}