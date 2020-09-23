<?php

namespace App\Models;

use App\Entities\Host;
use CodeIgniter\Model;

class HostDeploysModel extends Model
{
    protected $table         = 'hosts__deploys';
    protected $allowedFields = ['host_id', 'domain', 'template', 'result'];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\HostDeploys';
    protected $useTimestamps = true;

    public function atHost($id)
    {
        $this->builder()->where('host_id', $id);
        return $this;
    }
}
