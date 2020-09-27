<?php

namespace App\Controllers;

use App\Entities\Plan;
use App\Entities\Purchase;
use App\Entities\Scheme;
use App\Libraries\LiquidRegistrar;
use App\Libraries\Recaptha;
use App\Libraries\SendGridEmail;
use App\Libraries\TemplateDeployer;
use App\Libraries\VirtualMinShell;
use App\Models\HostDeploysModel;
use App\Models\HostModel;
use App\Models\LiquidModel;
use App\Models\LoginModel;
use App\Models\PlanModel;
use App\Models\PurchaseModel;
use App\Models\SchemeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use ErrorException;

class Home extends BaseController
{
	public function index()
	{
		return $this->response->redirect(href('login'));
	}

	public function notify()
	{
		if (
			isset($_GET['id'], $_GET['challenge'], $_GET['secret']) &&
			($_GET['secret'] == $this->request->config->paymentSecret &&
				isset($_POST['trx_id'], $_POST['sid'], $_POST['status'], $_POST['via']) &&
				$_POST['status'] == 'berhasil' // iPaymu notification
			)
		) {
			/** @var Purchase */
			$data = (new PurchaseModel())->find($_GET['id']);
			if ($data && $data->metadata->_challenge == $_GET['challenge']) {

				// At this point we process the purchase
				// In case anything fails, at least we have record it.

				$data->status = 'active';
				$host = $data->host;
				$login = $host->login;
				$metadata = $data->metadata;
				$metadata->_id = $_POST['trx_id'];
				$metadata->_invoiced = date('Y-m-d H:i:s');
				$metadata->_via = $_POST['via'];
				$metadata->_challenge = null;

				log_message('notice', 'PURCHASE: ' . json_encode($metadata));

				if ($metadata->liquid && $metadata->scheme) {
					/** @var Scheme */
					$scheme = (new SchemeModel())->find($metadata->scheme);
					$liquid = (new LiquidModel())->atLogin($login->id);
					$registrar = new LiquidRegistrar();
					$registrar->confirmFundDomain($liquid->id, [
						'amount' => ($metadata->years - 1) * $scheme->renew_idr +
							($host->scheme_id ? $scheme->renew_idr : $scheme->price_idr),
						'description' => "Funds for " . ($metadata->domain ?? $host->domain),
					]);
					$registrar->confirmPurchaseDomain($liquid->id, [
						'transaction_id' => $metadata->liquid,
					]);
					$this->db->table('liquid')->update([
						'cache_domains' => json_encode($registrar->getListOfDomains($liquid->id)),
						'pending_transactions' => json_encode($registrar->getPendingTransactions($liquid->id)),
					], [
						'id' => $liquid->id,
					]);
					// Save
					if ($metadata->domain)
						$host->domain = $metadata->domain;
					$host->scheme_id = $metadata->scheme;
				}
				if ($metadata->plan) {
					/** @var Plan */
					$plan = (new PlanModel())->find($metadata->plan);
					if ($host->status === 'pending') {
						// First time creation
						(new VirtualMinShell())->createHost(
							$host->username,
							$host->password,
							$login->email,
							$host->domain,
							$host->server->alias,
							$plan->alias,
							$plan->features
						);
						if ($metadata->template) {
							(new TemplateDeployer())->schedule(
								$host->id,
								$host->domain,
								$metadata->template
							);
						}
					} else {
						// Re-enable and upgrade
						(new VirtualMinShell())->enableHost(
							$host->domain,
							$host->server->alias
						);
						(new VirtualMinShell())->upgradeHost(
							$host->domain,
							$host->server->alias,
							$host->plan->alias,
							$host->plan->plan_features,
							$plan->alias,
							$plan->features
						);
					}
					$host->expiry_at = $metadata->expiration;
					$host->plan = $metadata->plan;
					$host->status = 'active';
					$host->addons += $plan->net * 1024 / 12;
					if ($login->trustiness < $metadata->plan) {
						$login->trustiness = $metadata->plan;
						(new LoginModel())->save($login);
					}

				}
				if ($metadata->addons) {
					$host->addons += $metadata->addons * 1024;
					// Add more bandwidth
					(new VirtualMinShell())->adjustBandwidthHost(
						($host->addons + ($plan->net * 1024 / 12)),
						$host->domain,
						$host->server->alias
					);
					if (!$metadata->plan) {
						// Re-enable (in case disabled by bandwidth)
						(new VirtualMinShell())->enableHost(
							$host->domain,
							$host->server->alias
						);
					}
				}
				$data->metadata = $metadata;
				(new PurchaseModel())->save($data);
				if ($host->hasChanged()) {
					(new HostModel())->save($host);
				} {
					// Email
					$plan = (new PlanModel())->find($metadata->plan)->alias;
					$desc = ($metadata->liquid ? lang('Host.formatInvoiceAlt', [
						$plan,
						$data->domain,
					]) : lang('Host.formatInvoice', [
						$plan,
					]));

					(new SendGridEmail())->send('receipt_email', 'billing', [[
						'to' => [[
							'email' => $login->email,
							'name' => $login->name,
						]],
						'dynamic_template_data' => [
							'name' => $login->name,
							'price' => format_money($metadata->price),
							'description' => $desc,
							'id' => $metadata->_id,
							'timestamp' => $metadata->_invoiced,
							'via' => $metadata->_via,
						]
					]]);
				}
				return "OK";
			}
		}
		throw new PageNotFoundException();
	}

	public function verify()
	{
		if (!empty($_GET['code'])) {
			$code = explode(':', base64_decode($_GET['code'], true));
			if (count($code) == 2) {
				$row = fetchOne('login', [
					'email' => $code[0],
					'otp' => $code[1],
				]);
				if ($row) {
					$this->db->table('login')->update([
						'email_verified_at' => date('Y-m-d H:i:s'),
						'otp' => null,
						'trustiness' => max($row->trustiness, 1)
					], ['email' => $code[0]]);
					$this->request->setLocale($row->lang ?: 'id');
					return view('static/verified', [
						'email' => $code[0],
					]);
				}
			}
		}
		throw new PageNotFoundException();
	}

	public function login()
	{
		if ($this->session->has('login')) {
			return $this->response->redirect('/user');
		}

		if ($this->request->getMethod() === 'post') {
			if (isset($_POST['email'], $_POST['password'])) {
				$login = $this->db->table('login')->getWhere([
					'email' => $_POST['email']
				])->getRow();
				if ($login && password_verify(
					$_POST['password'],
					$login->password
				)) {
					$this->session->set('login', $login->id);
					return $this->response->redirect(base_url($_GET['r'] ?? 'user'));
				}
			}
			$m = lang('Interface.wrongLogin');
		}
		return view('static/login', [
			'message' => $m ?? (($_GET['msg'] ?? '') === 'emailsent' ? lang('Interface.emailSent') : null)
		]);
	}

	public function import()
	{
		return $this->response->redirect('/user/host/create?from=' . urlencode($this->request->getGet('from')));
	}

	public function logout()
	{
		if ($this->session->login) {
			$this->request->setLocale((new LoginModel())->find($this->session->login)->lang);
		}
		$this->session->destroy();
		return $this->response->redirect(href('login'));
	}

	public function register()
	{
		if ($this->request->getMethod() === 'get') {
			return view('static/register', [
				'errors' => $this->session->errors,
				'recapthaSite' => (new Recaptha())->recapthaSite,
			]);
		} else {
			if ($this->validate([
				'name' => 'required|min_length[3]|max_length[255]',
				'email' => 'required|valid_email|is_unique[login.email]',
				'password' => 'required|min_length[8]',
				'passconf' => 'required|matches[password]',
				'g-recaptcha-response' => ENVIRONMENT === 'production' ? 'required' : 'permit_empty',
			])) {
				if (ENVIRONMENT !== 'production' || (new Recaptha())->verify($_POST['g-recaptcha-response'])) {
					$data = array_intersect_key(
						$this->request->getPost(),
						array_flip(
							['name', 'email', 'phone', 'password']
						)
					);
					$data['lang'] = $this->request->getLocale();
					$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
					$this->db->table('login')->insert($data);
					$_POST['action'] = 'resend';
					$this->session->login = $this->db->insertID();
					$u = new User();
					$u->initController($this->request, $this->response, $this->logger);
					$u->verify_email();
					return $this->response->redirect(base_url($_GET['r'] ?? 'user'));
				}
			}
			return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
		}
	}

	public function forgot()
	{
		if ($this->request->getMethod() === 'post' && $this->validate([
			'email' => 'required|valid_email',
			'g-recaptcha-response' => ENVIRONMENT === 'production' ? 'required' : 'permit_empty',
		])) {
			if (ENVIRONMENT !== 'production' || (new Recaptha())->verify($_POST['g-recaptcha-response'])) {
				$data = fetchOne('login', ['email' => $_POST['email']]);
				if ($data) {

					if (!$data->otp) {
						$data->otp = random_int(111111111, 999999999);
						$this->db->table('login')->update([
							'otp' => $data->otp
						], [
							'login_id' => $data->login_id
						]);
					}

					$em = \Config\Services::email();
					$em->setTo($data->email);
					$em->setSubject('Reset Password Akun | DOM Cloud');
					$em->setMessage(view('static/reset_email', [
						'name' => $data->name,
						'link' => base_url('forgot_reset?code=' . urlencode(base64_encode($data->email . ':' . $data->otp)))
					]));
					if (!$em->send()) {
						log_message('critical', $em->printDebugger());
						throw new ErrorException("Unable to send message");
					}
					$this->session->destroy();
					return $this->response->redirect('/id/login?msg=emailsent');
				}
			}
		}
		return view('static/forgot', [
			'recapthaSite' => (new Recaptha())->recapthaSite,
			'message' => $m ?? null,
		]);
	}

	public function forgot_reset()
	{
		if (!empty($_GET['code'])) {
			$code = explode(':', base64_decode($_GET['code'], true));
			if (count($code) == 2) {
				$row = fetchOne('login', [
					'email' => $code[0],
					'otp' => $code[1],
				]);
				if ($row) {
					if ($this->request->getMethod() === 'post' && $this->validate([
						'password' => 'required|min_length[8]',
						'passconf' => 'required|matches[password]',
					])) {
						$this->db->table('login')->update([
							'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
							'email_verified_at' => date('Y-m-d H:i:s'),
							'otp' => null,
						], ['email' => $code[0]]);
						$_POST['email'] = $code[0];
						return $this->login();
					} else {
						return view('static/forgot_reset');
					}
				}
			}
		}
		throw new PageNotFoundException();
	}

	//--------------------------------------------------------------------

}
