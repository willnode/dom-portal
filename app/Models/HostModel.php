<?php

namespace App\Models;

use CodeIgniter\Model;

class HostModel extends Model
{
    protected $table         = 'hosts';
    protected $allowedFields = [
        'login_id', 'username', 'domain', 'password', 'liquid_id', 'scheme_id', 'server_id', 'plan_id', 'addons', 'notification', 'expiry_at', 'status'
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Host';
    protected $useTimestamps = true;

    public function atLogin($id)
    {
        if ($id !== 1)
            $this->builder()->where('login_id', $id); // @codeCoverageIgnore
        else
            $this->builder()->whereNotIn('status', ['removed']);
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function atServer($id)
    {
        $this->builder()->where('server_id', $id);
        return $this;
    }
}
