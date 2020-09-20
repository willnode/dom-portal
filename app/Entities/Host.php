<?php

namespace App\Entities;

use App\Models\HostStatModel;
use App\Models\LoginModel;
use App\Models\PlanModel;
use App\Models\PurchaseModel;
use App\Models\SchemeModel;
use App\Models\ServerModel;
use CodeIgniter\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property int $id
 * @property int $login_id
 * @property string $username
 * @property string $domain
 * @property string $password
 * @property string $status
 * @property int $liquid_id
 * @property int $scheme_id
 * @property int $server_id
 * @property int $plan_id
 * @property int $addons
 * @property int $notification Notification Flag 1=about expire, 2=rollover mode, 4=disabled
 * @property Scheme $scheme
 * @property Plan $plan
 * @property Server $server
 * @property HostStat|null $stat
 * @property Login $login
 * @property Time $created_at
 * @property Time $updated_at
 * @property Time $expiry_at
 */
class Host extends Entity
{
    protected $dates = [
		'created_at',
		'updated_at',
		'expiry_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'login_id' => 'integer',
        'username' => 'string',
        'domain' => 'string',
        'password' => 'string',
        'status' => 'string',
        'liquid_id' => 'integer',
        'server_id' => 'integer',
        'scheme_id' => '?integer',
        'plan_id' => 'integer',
        'addons' => 'integer',
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
        return (new ServerModel())->find($this->attributes['server_id']);
    }

    /** @return HostStat|null */
    public function getStat()
    {
        return (new HostStatModel())->find($this->attributes['id']);
    }

    /** @return Purchase|null */
    public function getPurchase()
    {
        return (new PurchaseModel())->atHost($this->attributes['id'])->descending()->findAll(1)[0] ?? null;
    }

    /** @return Login|null */
    public function getLogin()
    {
        return (new LoginModel())->find($this->attributes['login_id']);
    }

    public function getAddonsBytes()
    {
        return $this->addons * 1024 * 1024;
    }
}
