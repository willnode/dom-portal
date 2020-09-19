<?php namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table         = 'purchases';
    protected $allowedFields = [
        'id', 'host_id', 'status', 'challenge', 'metadata',
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Purchase';


    public function atHost($id)
    {
        $this->builder()->where('host_id', $id);
        return $this;
    }

    public function descending()
    {
        $this->builder()->orderBy('id', 'DESC');
        return $this;
    }
}