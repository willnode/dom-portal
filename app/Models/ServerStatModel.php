<?php namespace App\Models;

use CodeIgniter\Model;

class ServerStatModel extends Model
{
    protected $table         = 'servers__stat';
    protected $allowedFields = [
        'server_id', 'metadata', 'updated_at'
    ];
    protected $primaryKey = 'server_id';
    protected $returnType = 'App\Entities\ServerStat';
    protected $useTimestamps = true;
}