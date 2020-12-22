<?php

namespace App\Models;

use CodeIgniter\Model;

class DomainModel extends Model
{
    protected $table         = 'domains';
    protected $allowedFields = [
        'login_id', 'name', 'scheme_id', 'status'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Domain';
    protected $useTimestamps = true;

    public function atLogin($id)
    {
        if ($id !== 1)
            $this->builder()->where('login_id', $id); // @codeCoverageIgnore
        return $this;
    }

    public function atDomain($id)
    {
        $this->builder()->where('name', $id);
        return $this;
    }
}
