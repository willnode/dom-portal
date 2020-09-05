<?php

namespace App\Models;

class LiquidModel
{
    public $customer;
    public $contacts;
    public $domains;
    public $pending_transactions;
    public $default_contacts;
    public $updated;
    public $created;

    public function __construct($liquid) {
        $this->customer = json_decode($liquid->liquid_cache_customer);
        $this->contacts = json_decode($liquid->liquid_cache_contacts);
        $this->domains = json_decode($liquid->liquid_cache_domains);
        $this->pending_transactions = json_decode($liquid->liquid_pending_transactions);
        $this->default_contacts = json_decode($liquid->liquid_default_contacts);
        $this->updated = $liquid->liquid_updated;
        $this->created = $liquid->liquid_created;
    }
}