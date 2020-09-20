<?php namespace App\Models;

use App\Entities\Liquid;
use CodeIgniter\Model;

class LiquidModel extends Model
{
    protected $table         = 'liquid';
    protected $allowedFields = [
        'id', 'login_id', 'password', 'customer', 'contacts', 'domains', 'pending_transacsions', 'default_contacts',
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Liquid';
    protected $useTimestamps = true;

    /** @return Liquid */
    public function atLogin($id)
    {
        $this->builder()->where('login_id', $id);
        return $this->find()[0] ?? null;
    }
}