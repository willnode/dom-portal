<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property float $price Applied Price
 * @property string $price_unit Applied Price Unit [idr|usd]
 * @property int|null $years Reservation in years
 * @property int|null $plan Plan ID to be applied
 * @property int|null $scheme Scheme Domain ID to be applied
 * @property int|null $addons Addons in GB to be applied (excluding bonus from plan)
 * @property string|null $domain Name Domain to be applied
 * @property string|null $expiration Host expiration date to be applied
 * @property string|null $template Zip/Git path to template to be applied
 * @property string|null $liquid Liquid Transaction ID  to be applied
 * @property string|null $_challenge Purchase Challenge Code (iPaymu only)
 * @property string|null $_status Status ID from Gateway (TransferWise only)
 * @property string|null $_id Purchase ID from Gateway
 * @property string|null $_via Purchase Method Reported
 * @property string|null $_issued Timestamp when Purchase Issued
 * @property string|null $_invoiced Timestamp when Purchase Invoiced
 */
class PurchaseMetadata extends Entity
{
}
