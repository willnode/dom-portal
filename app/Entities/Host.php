<?php

namespace App\Entities;

use App\Models\PlanModel;
use App\Models\SchemeModel;
use CodeIgniter\Entity;

/**
 * @property int login_id
 * @property string username
 * @property string domain
 * @property string password
 * @property int liquid_id
 * @property int scheme_id
 * @property int plan_id
 * @property int backup_bw
 * @property int notification
 * @property Scheme scheme
 * @property Plan plan
 * @property Server server
 * @property Time created_at
 * @property Time updated_at
 * @property Time expiry_at
 */
class Host extends Entity
{
    protected $dates = [
		'created_at',
		'updated_at',
		'expiry_at',
    ];

    protected $casts = [
        'login_id' => 'integer',
        'username' => 'string',
        'domain' => 'string',
        'password' => 'string',
        'liquid_id' => 'integer',
        'scheme_id' => 'integer',
        'plan_id' => 'integer',
        'backup_bw' => 'integer',
        'notification' => 'integer',
    ];

    /** @return Scheme */
    public function getScheme()
    {
        return (new SchemeModel())->find($this->attributes['scheme_id']);
    }

    /** @return Plan */
    public function getPlan()
    {
        return (new PlanModel())->find($this->attributes['plan_id']);
    }

    /** @return Server */
    public function getServer()
    {
        return (new PlanModel())->find($this->attributes['server_id']);
    }
}
