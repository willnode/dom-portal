<?php

namespace App\Entities;

use App\Models\DomainModel;
use App\Models\HostModel;
use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int|null $host_id
 * @property int|null $domain_id
 * @property string $status
 * @property string $niceMessage
 * @property PurchaseMetadata $metadata
 * @property Host|null $host
 * @property Domain|null $domain
 */
class Purchase extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'host_id' => '?integer',
        'domain_id' => '?integer',
        'status' => 'string',
    ];

    /** @return Host */
    public function getHost()
    {
        $id = $this->attributes['host_id'];
        return $id ? (new HostModel())->find($id) : null;
    }

    /** @return Domain */
    public function getDomain()
    {
        $id = $this->attributes['domain_id'];
        return $id ? (new DomainModel())->find($id) : null;
    }

    public function getMetadata()
    {
        return new PurchaseMetadata(json_decode($this->attributes['metadata'], true));
    }

    /** @param PurchaseMetadata $x */
    public function setMetadata($x)
    {
        $this->attributes['metadata'] = json_encode($x->toRawArray());
    }

    public function getNiceMessage()
    {
        $host = $this->host;
        $domain = $this->domain;
        $metadata = $this->metadata;
        $word = '';
        switch (lang('Interface.code')) {
            case 'en':
                if ($domain) {
                    $dword = '';
                    if ($metadata->registrar) {
                        $dword = 'Purchase of ';
                    } else if ($metadata->registrarRenew) {
                        $dword = 'Extend of ';
                    } else if ($metadata->registrarTransfer) {
                        $dword = 'Transfer of ';
                    }
                    $dword .= "domain {$domain->name} ";
                    $word = $dword;
                }
                if ($host) {
                    $hword = '';
                    if ($host->status === 'pending') {
                        $hword = 'Purchase of ';
                    } else if ($metadata->plan) {
                        if ($metadata->plan > $host->plan) {
                            $hword = 'Upgrade of ';
                        } else if ($metadata->plan < $host->plan) {
                            $hword = 'Downgrade of ';
                        } else {
                            $hword = 'Renew of ';
                        }
                    } else if ($metadata->years) {
                        $hword = 'Extend of ';
                    }
                    if ($hword) {
                        $hword .= "{$host->plan->alias} hosting plan ";
                        if (!isset($dword)) {
                            $hword .= "on {$host->domain} ";
                        } else {
                            $hword .= "with $dword ";
                        }
                    }
                    if ($metadata->addons) {
                        $bword = "data transfer add-ons {$metadata->addons} MB ";
                        if ($hword) {
                            $hword .= "with $bword ";
                        } else {
                            $hword = $bword . " on {$host->domain} ";
                        }
                    }
                    $word = $hword;
                }
                break;
            case 'id':
                if ($domain) {
                    $dword = '';
                    if ($metadata->registrar) {
                        $dword = 'Pembelian ';
                    } else if ($metadata->registrarRenew) {
                        $dword = 'Perpanjangan ';
                    } else if ($metadata->registrarTransfer) {
                        $dword = 'Transfer ';
                    }
                    $dword .= "domain {$domain->name} ";
                    $word = $dword;
                }
                if ($host) {
                    $hword = '';
                    if ($host->status === 'pending') {
                        $hword = 'Pembelian ';
                    } else if ($metadata->plan) {
                        if ($metadata->plan > $host->plan) {
                            $hword = 'Upgrade ';
                        } else if ($metadata->plan < $host->plan) {
                            $hword = 'Downgrade ';
                        } else {
                            $hword = 'Pembaruan ';
                        }
                    } else if ($metadata->years) {
                        $hword = 'Perpanjangan ';
                    }
                    if ($hword) {
                        $hword .= "paket hosting {$host->plan->alias} ";
                        if (!isset($dword)) {
                            $hword .= "pada {$host->domain} ";
                        } else {
                            $hword .= "dengan $dword ";
                        }
                    }
                    if ($metadata->addons) {
                        $bword = "tambahan data transfer {$metadata->addons} MB ";
                        if ($hword) {
                            $hword .= "serta $bword ";
                        } else {
                            $hword = $bword . " pada {$host->domain} ";
                        }
                    }
                    $word = $hword;
                }
                break;
        }
        return rtrim($word);
    }
}
