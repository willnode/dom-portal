<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property string $alias
 * @property int $price_idr
 * @property int $renew_idr
 * @property int $price_usd
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
}
