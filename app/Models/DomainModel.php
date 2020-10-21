<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainModel extends Model
{
    protected $table         = 'domains';
    protected $allowedFields = [
        'login_id', 'name', 'scheme_id'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Domain';
    protected $useTimestamps = true;

    public function atLogin($id)
    {
        $this->builder()->where('login_id', $id);
        return $this;
    }

    public function atDomain($id)
    {
        $this->builder()->where('domain', $id);
        return $this;
    }
}
