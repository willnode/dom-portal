<?php

namespace App\Entities;

use App\Models\LoginModel;
use App\Models\PurchaseModel;
use App\Models\SchemeModel;
use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int $login_id
 * @property string $name
 * @property string $status
 * @property int $scheme_id
 * @property Purchase $purchase
 * @property Scheme $scheme
 * @property Login $login
 */
class Domain extends Entity
{
    protected $dates = [
		'created_at',
		'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'login_id' => 'integer',
        'name' => 'string',
        'scheme_id' => 'integer',
    ];

    /** @return Scheme */
    public function getScheme()
    {
        return (new SchemeModel())->find($this->attributes['scheme_id']);
    }

    /** @return Purchase|null */
    public function getPurchase()
    {
        return (new PurchaseModel())->atDomain($this->attributes['id'])->descending()->findAll(1)[0] ?? null;
    }

    /** @return Login */
    public function getLogin()
    {
        return (new LoginModel())->find($this->attributes['login_id']);
    }
}
