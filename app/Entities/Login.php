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
 * @property int $trustiness 0=not verifying the email, max hosts is 1, warn on purchase. 1=not puchasing anything, max hosts is 5. 2=not purchasing pro or higher yet, max hosts is 10
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
        'trustiness' => 'int',
    ];
}