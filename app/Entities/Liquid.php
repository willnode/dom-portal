<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int $login_id
 * @property string $password
 * @property mixed[] $customer
 * @property mixed[] $contacts
 * @property mixed[] $domains
 * @property mixed[] $pending_transactions
 * @property mixed $default_contacts
 * @property Time $created_at
 * @property Time $updated_at
 */
class Liquid extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'login_id' => 'integer',
        'password' => 'string',
        'customer' => 'json',
        'contacts' => 'json',
        'domains' => 'json',
        'pending_transactions' => 'json',
        'default_contacts' => 'json',
    ];
}