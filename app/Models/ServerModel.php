<?php namespace App\Models;

use CodeIgniter\Model;

class ServerModel extends Model
{
    protected $table         = 'servers';
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Server';
}