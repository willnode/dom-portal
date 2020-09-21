<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property string $alias
 * @property int $price_local
 * @property int $price_idr
 * @property int $price_usd
 * @property int $renew_local
 * @property int $renew_idr
 * @property int $renew_usd
 */
class Scheme extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'alias' => 'string',
        'price_idr' => 'integer',
        'renew_idr' => 'integer',
        'price_usd' => 'integer',
        'renew_usd' => 'integer',
    ];

    public function getPriceLocal()
    {
        return $this->attributes['price_'.lang('Interface.currency')];
    }

    public function getRenewLocal()
    {
        return $this->attributes['renew_'.lang('Interface.currency')];
    }
}
