<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int id
 * @property int login_id
 * @property string password
 * @property mixed[] cache_customer
 * @property mixed[] cache_contacts
 * @property mixed[] cache_domains
 * @property mixed[] pending_transactions
 * @property mixed[] default_contacts
 * @property Time created_at
 * @property Time updated_at
 */
class Liquid extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'login_id' => 'integer',
        'password' => 'string',
        'cache_customer' => 'json-array',
        'cache_contacts' => 'json-array',
        'cache_domains' => 'json-array',
        'pending_transactions' => 'json-array',
        'default_contacts' => 'json-array',
    ];
}