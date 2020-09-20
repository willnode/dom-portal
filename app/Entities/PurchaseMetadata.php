<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property float $price
 * @property string $price_unit
 * @property string $template
 * @property string|null $expiration
 * @property int|null $years
 * @property int|null $plan
 * @property int|null $addons
 * @property string|null $liquid
 * @property string|null $_challenge
 * @property string|null $_id
 * @property string|null $_via
 * @property string|null $_issued
 * @property string|null $_invoiced
 */
class PurchaseMetadata extends Entity
{
}