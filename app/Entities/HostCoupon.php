<?php

namespace App\Entities;

use App\Models\HostModel;
use CodeIgniter\Entity;
use CodeIgniter\I18n\Time;

/**
 * @property string $code
 * @property int $redeems
 * @property float $min
 * @property float $max
 * @property float $discount
 * @property Time $expiry_at
 */
class HostCoupon extends Entity
{

	protected $dates = [
		'expiry_at',
	];


    protected $casts = [
        'code' => 'string',
        'redeems' => 'integer',
        'currency' => 'string',
        'min' => 'float',
        'max' => 'float',
        'discount' => 'float',
        'default_plan_id' => 'int',
    ];
}
