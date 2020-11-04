<?php

namespace App\Models;

use CodeIgniter\Model;

class HostCouponModel extends Model
{
    protected $table      = 'hosts__coupon';
    protected $primaryKey = 'code';
    protected $allowedFields = ['redeems'];
    protected $returnType = 'App\Entities\HostCoupon';
}