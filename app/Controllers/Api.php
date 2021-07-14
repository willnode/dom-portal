<?php

namespace App\Controllers;

use App\Entities\Plan;
use App\Entities\Purchase;
use App\Libraries\DigitalRegistra;
use App\Libraries\GitHubOAuth;
use App\Libraries\Recaptha;
use App\Libraries\TemplateDeployer;
use App\Libraries\TransferWiseGate;
use App\Libraries\VirtualMinShell;
use App\Models\DomainModel;
use App\Models\HostModel;
use App\Models\LoginModel;
use App\Models\PlanModel;
use App\Models\PurchaseModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Google\Client;

class Api extends BaseController
{
    /**
     * @codeCoverageIgnore
     */
    protected function signinoauth($email, $name = null)
    {
        $login = (new LoginModel())->atEmail($email);
        if ($login) {
            (new LoginModel())->login($login);
        } else {
            $id = (new LoginModel())->register([
                'email' => $email,
                'name' => $name,
            ], true, true);
            if ($id && ENVIRONMENT === 'production') {
                (new LoginModel())->find($id)->sendVerifyEmail();
            }
        }
        if ($r = $this->request->getCookie('r')) {
            $this->response->deleteCookie('r');
        }
        return $this->response->redirect(base_url($r ?? 'user'));
    }
    /**
     * @codeCoverageIgnore
     */
    public function signin($provider)
    {
        if ($provider === 'google') {
            if ($code = $this->request->getGet('id_token')) {
                $client = new Client(['client_id' => $this->request->config->googleClient]);
                $payload = $client->verifyIdToken($code);
                if ($payload) {
                    log_message('notice', json_encode($payload));
                    if (isset($payload['email'])) {
                        return $this->signinoauth($payload['email'], $payload['name'] ?? '');
                    }
                }
            }
            return view('user/redirgoogle');
        } elseif ($provider === 'github') {
            $lib = new GitHubOAuth();
            if ($code = $this->request->getGet('code')) {
                $token = $lib->verifyCode($code);
                if ($token && ($user = $lib->getPrimaryEmail($token))) {
                    if (isset($user->email)) {
                        return $this->signinoauth($user->email, $lib->getUserInfo($token)->name ?? '');
                    }
                }
                return $this->response->redirect(href('login?hint=fail'));
            }
            return $this->response->redirect($lib->getConsentURL());
        }
        throw new PageNotFoundException();
    }

    public function notify()
    {
        $r = $this->request;
        if (
            $r->getGet('id') && $r->getGet('challenge') &&
            $r->getGet('secret') === $r->config->ipaymuSecret &&
            $r->getPost('trx_id') && $r->getPost('via') &&
            $r->getPost('status')  == 'berhasil'
        ) {
            /** @var Purchase */
            $purchase = (new PurchaseModel())->find($r->getGet('id'));
            if ($purchase && $purchase->metadata->_challenge == $r->getGet('challenge')) {

                // At this point we process the purchase
                // In case anything fails, at least we have record it.

                $purchase->status = 'active';
                $metadata = $purchase->metadata;
                $metadata->_id = $r->getPost('trx_id');
                $metadata->_invoiced = date('Y-m-d H:i:s');
                $metadata->_via = $r->getPost('via');
                $metadata->_challenge = null;

                log_message('notice', 'PURCHASE: ' . json_encode($metadata));

                if ($purchase->domain_id) {
                    $domain = $purchase->domain;
                    if ($domain->status === 'pending') {
                        if ($metadata->registrar ?? null) {
                            (new DigitalRegistra())->domainRegister($metadata->registrar);
                            $domain->status = 'active';
                            (new DomainModel())->save($domain);
                        } else if ($metadata->registrarTransfer ?? null) {
                            (new DigitalRegistra())->domainTransfer($metadata->registrarTransfer);
                            $domain->status = 'active';
                            (new DomainModel())->save($domain);
                        }
                    } else if ($metadata->registrarRenew ?? null) {
                        (new DigitalRegistra())->domainRenew($domain->name, $domain->id, $metadata->registrarRenew);
                        $domain->status = 'active';
                        (new DomainModel())->save($domain);
                    }
                    $login = $domain->login;
                }
                if ($purchase->host_id) {

                    $host = $purchase->host;
                    $login = $host->login;
                    $vm = new VirtualMinShell();

                    if ($metadata->domain && $host->domain != $metadata->domain) {
                        $vm->cnameHost(
                            $host->domain,
                            $sv = $host->server->alias,
                            $metadata->domain
                        );
                        if ($purchase->domain_id && $host->status != 'pending') {
                            $vm->enableFeature($metadata->domain, $sv, 'dns');
                        } else if (!$purchase->domain_id && $host->status != 'pending') {
                            $vm->disableFeature($metadata->domain, $sv, 'dns');
                        }
                        $host->domain = $metadata->domain;
                    }
                    if ($metadata->plan) {
                        /** @var Plan */
                        $plan = (new PlanModel())->find($metadata->plan);
                        if ($host->status === 'pending') {
                            // First time creation
                            $vm->createHost(
                                $host->username,
                                $host->password,
                                $login->email,
                                $host->domain,
                                $sv = $host->server->alias,
                                $plan->alias
                            );
                            if ($purchase->domain_id) {
                                $vm->enableFeature($host->domain, $sv, 'dns');
                            }
                            if ($metadata->template) {
                                $template = $metadata->template;
                            }
                        } else {
                            // Re-enable and upgrade
                            $vm->enableHost(
                                $host->domain,
                                $host->server->alias
                            );
                            $vm->upgradeHost(
                                $host->domain,
                                $host->server->alias,
                                $plan->alias,
                            );
                        }
                        $host->plan_id = $metadata->plan;
                        $host->status = 'active';
                        $host->addons += $plan->net * (3 * 1024 / 12);
                        if ($login->trustiness < $metadata->plan) {
                            $login->trustiness = $metadata->plan;
                            (new LoginModel())->save($login);
                        }
                    }
                    if ($metadata->expiration) {
                        $host->notification = 0;
                        $host->expiry_at = $metadata->expiration;
                    }
                    if ($metadata->addons) {
                        $host->addons += $metadata->addons * 1024;
                        isset($plan) || ($plan = $host->plan);
                        // Add more bandwidth
                        $vm->adjustBandwidthHost(
                            ($host->addons + ($plan->net * 1024 / 12)),
                            $host->domain,
                            $host->server->alias
                        );
                        if (!$metadata->plan) {
                            // Re-enable (in case disabled by bandwidth)
                            $vm->enableHost(
                                $host->domain,
                                $host->server->alias
                            );
                        }
                    }
                    if ($host->hasChanged()) {
                        (new HostModel())->save($host);
                    }
                    if ($vm->output) {
                        $vm->saveOutput($host, 'Applying upgrade');
                    }
                    if (isset($template)) {
                        // @codeCoverageIgnoreStart
                        (new TemplateDeployer())->schedule(
                            $host->id,
                            $host->domain,
                            $metadata->template
                        );
                        // @codeCoverageIgnoreEnd
                    }
                }
                $purchase->metadata = $metadata;
                (new PurchaseModel())->save($purchase);
                sendEmail($login->email, lang('Email.receiptTitle'), view('email/receipt', [
                    'name' => $login->name,
                    'price' => format_money($metadata->price, $metadata->price_unit),
                    'description' => $purchase->niceMessage,
                    'id' => $metadata->_id,
                    'timestamp' => $metadata->_invoiced,
                    'via' => $metadata->_via,
                ]));
                return "OK";
            }
        }
        throw new PageNotFoundException(); // @codeCoverageIgnore
    }

    /**
     * @codeCoverageIgnore
     */
    public function notifyws()
    {
        if ($this->request->getMethod() === 'post') {
            $json = file_get_contents('php://input');
            $gate = (new TransferWiseGate());
            if ($gate->sign($json, $this->request->getHeader('X-Signature-SHA256'))) {
                if ($purchase = $gate->getTransferInfo(json_decode($json)->resource->id ?? '')) {
                    if ($ref = intval(trim($purchase->details->reference, ' \t\n\r\0#"'))) {
                        /** @var Purchase */
                        if (($invoice = (new PurchaseModel())->find($ref))) {
                            $metadata = $invoice->metadata;
                            // 0.5 USD offset for in case of there's rounding error or something I don't aware of.
                            // Basically if you been here and read this, it's okay to not include transaction fee from us.
                            // But don't take this for granted! I won't solve any transaction error if you made a purchase without it :)
                            if ($metadata->price_unit === strtolower($purchase->targetCurrency) && $metadata->price <= $purchase->targetValue + 0.5) {
                                $metadata->_status = $purchase->status;
                                $metadata->_id = $purchase->id;
                                $invoice->metadata = $metadata;
                                (new PurchaseModel())->save($invoice);
                                if ($invoice->status === 'pending' && $purchase->status === 'funds_converted') {
                                    // Execute the fuckin payment. Imitate what iPaymu did
                                    $_GET = [];
                                    $_GET['id'] = $invoice->id;
                                    $_GET['challenge'] = $metadata->_challenge;
                                    $_GET['secret'] = $this->request->config->ipaymuSecret;
                                    $_POST = [];
                                    $_POST['trx_id'] = $metadata->_id;
                                    $_POST['status'] = 'berhasil';
                                    $_POST['via'] = "Transferwise ($purchase->targetValue $purchase->targetCurrency)";
                                    $this->notify();
                                }
                            }
                        }
                    }
                }
                return 'OK';
            }
        }
        throw new PageNotFoundException();
    }

    /**
     * @codeCoverageIgnore
     */
    public function notifypp($secret)
    {
        $r = $this->request;
        if ($r->getMethod() === 'post') {
            $body = json_decode($this->request->getBody());
            if (($body->event_type ?? '') == 'CHECKOUT.ORDER.APPROVED' && isset($body->resource->purchase_units[0])) {
                $r->setGlobal('get', $get_data = [
                    'id' => $body->resource->purchase_units[0]->reference_id,
                    'challenge' => $body->resource->purchase_units[0]->custom_id,
                    'secret' => $secret,
                ]);
                $r->setGlobal('post', $post_data = [
                    'trx_id' => $body->id,
                    'via' => 'PayPal',
                    'status' => 'berhasil',
                ]);
                $r->setGlobal('request', array_merge($get_data, $post_data));
                return $this->notify();
            } else {
                // further inspection
                log_message('notice', $this->request->getBody());
            }
        }
        return 'OK';
    }
}
