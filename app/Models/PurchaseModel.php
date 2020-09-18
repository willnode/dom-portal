<?php namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table         = 'purchases';
    protected $allowedFields = [
        'id', 'hosting_id', 'status', 'challenge', 'metadata',
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Purchase';
}