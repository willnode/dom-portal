<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property string $alias
 * @property int $price_idr
 * @property int $price_usd
 * @property int $price_local
 * @property int $disk
 * @property int $net
 * @property int $dbs
 * @property int $subservs
 * @property int $features
 */
class Plan extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'alias' => 'string',
        'price_idr' => 'integer',
        'price_usd' => 'integer',
        'disk' => 'integer',
        'net' => 'integer',
        'dbs' => 'integer',
        'subservs' => 'integer',
        'features' => 'integer',
    ];

    public function getDiskBytes()
    {
        return $this->attributes['disk'] * 1024 * 1024;
    }

    public function getPriceLocal()
    {
        return intval($this->attributes['price_'.lang('Interface.currency')]);
    }

    public function getNetMonthlyBytes()
    {
        return $this->net * 1024 * 1024 * 1024 / 12;
    }
}