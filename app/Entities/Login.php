<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $password
 * @property string $otp
 * @property string $lang
 * @property Time $email_verified_at
 */
class Login extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'phone' => '?string',
        'password' => 'string',
        'otp' => '?string',
        'lang' => 'string',
        'email_verified_at' => '?timestamp',
    ];
}