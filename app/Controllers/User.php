<?php

namespace App\Controllers;

use App\Entities\Host;
use App\Entities\Liquid;
use App\Entities\Login;
use App\Entities\Plan;
use App\Entities\Purchase;
use App\Entities\PurchaseMetadata;
use App\Entities\Scheme;
use App\Libraries\BannedNames;
use App\Libraries\CountryCodes;
use App\Libraries\LiquidRegistrar;
use App\Libraries\PaymentGate;
use App\Libraries\SendGridEmail;
use App\Libraries\VirtualMinShell;
use App\Models\HostModel;
use App\Models\LiquidModel;
use App\Models\LoginModel;
use App\Models\PlanModel;
use App\Models\PurchaseModel;
use App\Models\SchemeModel;
use App\Models\ServerModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;

class User extends BaseController
{
	/** @var Login */
	protected $login;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		if (!$this->session->get('login') || (!($this->login = (new LoginModel())->find($this->session->login)))) {
			Services::response()->redirect(href(
				'login?r=' . Services::request()->detectPath()
			))->pretend(false)->send();
			exit;
		} else {
			$this->request->setLocale($this->login->lang);
		}
	}

	public function index()
	{
		return $this->response->redirect('/user/hosting');
	}

	protected function listHosting()
	{
		return view('user/hosting/list', [
			'list' => (new HostModel())->atLogin($this->login->id)->find(),
			'page' => 'hosting',
		]);
	}
	protected function createHosting()
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'plan' => 'required|is_not_unique[plans.id]',
				'username' => 'required|alpha_dash|min_length[5]|' .
					'max_length[32]|is_unique[hosts.username]',
				'server' => 'required|is_not_unique[servers.id]',
				'password' => 'required|min_length[8]',
				'domain_mode' => empty($_POST['domain_mode']) ? 'permit_empty' : 'required|in_list[free,buy,custom]',
			])) {
				$data = array_intersect_key(
					$this->request->getPost(),
					array_flip(['plan', 'username', 'server', 'template', 'password', 'domain_mode'])
				);
				if (empty($data['domain_mode'])) $data['domain_mode'] = 'free';
				$plan = (new PlanModel())->find($data['plan']);
				$server = (new ServerModel())->find($data['server']);
				if (array_search(strtolower($data['username']), (new BannedNames())->names) !== FALSE) return;
				$hosting = new Host([
					'login_id' => $this->login->id,
					'username' => strtolower($data['username']),
					'password' => $data['password'],
					'server_id' => $data['server'],
					'plan_id' => $data['plan'],
				]);
				if ($plan->price_idr != 0) {
					if ($this->validate([
						'custom_cname' => $data['domain_mode'] === 'custom' ? 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
							'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosts.domain]' : 'permit_empty',
						'buy_cname' => $data['domain_mode'] === 'buy' ? 'required|regex_match[/^[-\w]+$/]' : 'permit_empty',
						'buy_scheme' => $data['domain_mode'] === 'buy' ? 'required|is_not_unique[schemes.id]' : 'permit_empty',
						'years' => 'required|integer|greater_than[0]|less_than[6]',
						'addons' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[1000]',
					])) {
						// Not Free, so add invoice details
						$payment = new Purchase([
							'status' => 'pending',
						]);
						$payment_metadata = new PurchaseMetadata([
							"type" => "hosting",
							"price" => 0.0,
							"price_unit" => lang('Interface.currency'),
							"template" => $data['template'] ?? '',
							"expiration" => null,
							"years" => intval($_POST['years']),
							"plan" => intval($data['plan']),
							"addons" => intval($_POST['addons']),
							"liquid" => null,
							"_challenge" => random_int(111111111, 999999999),
							"_id" => null,
							"_via" => null,
							"_issued" => date('Y-m-d H:i:s'),
							"_invoiced" => null,
						]);
						if ($data['domain_mode'] === 'free') {
							$hosting->domain = $hosting->username . $server->domain;
							$hosting->scheme_id = $server->scheme_id;
						} else if ($data['domain_mode'] === 'buy') {
							/** @var Scheme */
							$scheme = (new SchemeModel())->find($_POST['buy_scheme']);
							$hosting->domain = $_POST['buy_cname'] . $scheme->alias;
							$hosting->scheme_id = $_POST['buy_scheme'];
							$payment_metadata->price +=  $scheme->{'price_' . $payment_metadata->price_unit};
							if ($payment_metadata->years > 1) {
								$payment_metadata->price +=  $scheme->{'renew_' . $payment_metadata->price_unit} * ($payment_metadata->years - 1);
							} {
								// Execute liquid right away
								$liq = fetchOne('liquid', ['login_id' => $this->login->id]);
								$liqc = json_decode($liq->liquid_default_contacts);
								$liquid['domain_name'] = $hosting->domain;
								$liquid['customer_id'] = $liq->liquid_id;
								$liquid['years'] = $_POST['years'];
								$liquid['registrant_contact_id'] = $liqc->registrant_contact->contact_id;
								$liquid['billing_contact_id'] = $liqc->billing_contact->contact_id;
								$liquid['admin_contact_id'] = $liqc->admin_contact->contact_id;
								$liquid['tech_contact_id'] = $liqc->tech_contact->contact_id;
								$liquid['purchase_privacy_protection'] = '0';
								$liquid['invoice_option'] = 'only_add';
								$rrr = (new LiquidRegistrar())->issuePurchaseDomain($liquid);
								$hosting->liquid_id = $rrr->domain_id;
								$payment_metadata->liquid = implode('|', [$liq->liquid_id, $rrr->transaction_id, $scheme->id]);
							}
						} else if ($data['domain_mode'] === 'custom') {
							$hosting->domain = $_POST['custom_cname'];
						}
						$payment_metadata->expiration = $hosting->expiry_at = date('Y-m-d H:i:s', strtotime("+$_POST[years] years", \time()));
						$payment_metadata->price += $plan->{'price_' . $payment_metadata->price_unit} * $_POST['years'];
						$payment_metadata->price += ['idr' => 4000, 'usd' => 0.32][$payment_metadata->price_unit] * $payment_metadata->addons;
						$payment_metadata->price += ['idr' => 5000, 'usd' => 0.4][$payment_metadata->price_unit];
						$hosting->status = 'pending';
						$hosting->expiry_at = $payment_metadata->expiration;
						$payment->metadata = $payment_metadata;
					} else {
						$this->request->setMethod('get');
						return $this->createHosting();
					}
				} else {
					// Free plan. Just create
					$hosting->status = /*$data['template'] ? 'starting' :*/ 'active';
					$hosting->expiry_at = date('Y-m-d H:i:s', strtotime("+2 months", \time()));
					$hosting->domain = $hosting->username . $server->domain;
					$hosting->scheme_id = $server->scheme_id;
					(new VirtualMinShell())->createHosting(
						$hosting->username,
						$hosting->password,
						$this->login->email,
						$hosting->domain,
						$server->alias,
						$plan->alias,
						$plan->features,
						$data['template']
					);
					(new VirtualMinShell)->addToServerDNS(
						$hosting->username,
						$server->ip
					);
					log_message('notice', VirtualMinShell::$output);
				}
				// Send to Database
				if ($id = (new HostModel())->insert($hosting)) {
					if (isset($payment)) {
						$payment->hosting_id = $id;
						(new PurchaseModel())->insert($payment);
					}
					return $this->response->redirect('/user/hosting/invoices/' . $id);
				}
			}
		}
		return view('user/hosting/create', [
			'plans' => (new PlanModel())->find(),
			'servers' => (new ServerModel())->find(),
			'schemes' => (new SchemeModel())->find(),
			'liquid' => (new LiquidModel())->atLogin($this->login->id),
			'validation' => $this->validator,
		]);
	}
	protected function upgradeHosting($data)
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'plan' => 'required|is_not_unique[plans.id]',
				'mode' => 'required|in_list[new,extend,upgrade]'
			])) {
				$post = array_intersect_key(
					$this->request->getPost(),
					array_flip(['plan', 'mode'])
				);
				if (!$plan = $this->db->table('plans')->getWhere(['id' => $post['plan']])->getRow()) return;
				if ($post['mode'] === 'extend' && $post['plan'] != $data->purchase_plan) return;
				if ($post['mode'] === 'upgrade' && $post['plan'] <= $data->purchase_plan) return;
				$payment = [
					'purchase_active' => 1,
					'purchase_hosting' => $data->id,
					'purchase_plan' => $post['plan'],
					'purchase_invoiced' => date('Y-m-d H:i:s', \time()),
				];
				if ($plan->plan_price != 0) {
					if ($this->validate([
						'years' => $post['mode'] !== 'upgrade' ? 'required|greater_than[0]|less_than[6]' : 'permit_empty',
					])) {
						$post['years'] = $_POST['years'] ?? $data->purchase_years;
						if ($post['mode'] === 'new') {
							$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+$post[years] years", \time()));
							$payment['purchase_price'] = $plan->plan_price * 1000 * $post['years'] + 5000;
						} else if ($post['mode'] === 'extend') {
							$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+$post[years] years", strtotime($data->purchase_expired)));
							$payment['purchase_price'] = $plan->plan_price * 1000 * $post['years'] + 5000;
						} else if ($post['mode'] === 'upgrade') {
							$payment['purchase_expired'] = $data->purchase_expired;
							$payment['purchase_price'] = ($plan->plan_price - $data->plan_price) * 1000 * $post['years'] + 5000;
						}
						$payment['purchase_years'] = $post['years'];
						$payment['purchase_status'] = 'pending';
						$payment['purchase_challenge'] = random_int(111111111, 999999999);
						$this->db->table('purchase')->update([
							'purchase_active' => 2,
						], ['purchase_id' => $data->purchase_id]);
					} else {
						$this->request->setMethod('get');
						return $this->upgradeHosting($data);
					}
				} else {
					// Downgrade to free
					$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+2 months", \time()));
					$payment['purchase_status'] = 'active';
					(new VirtualMinShell())->upgradeHosting(
						$data->domain_name,
						$data->slave_alias,
						$data->plan_features,
						$plan->plan_alias,
						$plan->plan_features
					);
					if ($data->plan_price != 0 && $data->domain_scheme != 1) {
						// Change back to free domain
						if ($data->domain_scheme) {
							// Create new domain, leave the original
							if (!$this->db->table('domain')->insert([
								'domain_login' => $data->domain_login,
								'domain_name' => $data->username . '.dom.my.id',
								'domain_scheme' => 1,
							])) {
								$this->request->setMethod('get');
								return $this->upgradeHosting($data);
							}
							$this->db->table('hosting')->update([
								'domain' => $this->db->insertID(),
							], ['id' => $data->id]);
						} else {
							// Change current alias domain
							$this->db->table('domain')->update([
								'domain_name' =>  $data->username . '.dom.my.id',
								'domain_scheme' => 1,
							], ['domain_id' => $data->domain_id]);
						}
						(new VirtualMinShell)->cnameHosting(
							$data->domain_name,
							$data->slave_alias,
							$data->username . '.dom.my.id'
						);
						(new VirtualMinShell)->addToServerDNS(
							$data->username,
							$data->slave_ip
						);

						log_message('notice', VirtualMinShell::$output);
					}
					$this->db->table('purchase')->update([
						'purchase_status' => 'expired',
						'purchase_active' => 0,
					], ['purchase_id' => $data->purchase_id]);
				}
				if ($this->db->table('purchase')->insert($payment)) {
					return $this->response->redirect('/user/hosting/invoices/' . $payment['purchase_hosting']);
				}
			}
		}
		return view('user/hosting/upgrade', [
			'data' => $data,
			'purchase' => $data->purchase,
			'liquid' => (new LiquidModel())->atLogin($this->login->id),
			'schemes' => (new SchemeModel())->find(),
			'plans' => (new PlanModel())->find(),
		]);
	}
	/** @param Host $data */
	protected function detailHosting($data)
	{
		return view('user/hosting/detail', [
			'data' => $data,
		]);
	}
	protected function loginHosting($data)
	{
		return view('user/hosting/login', [
			'uri' => 'https://'.$data->server->alias.'.domcloud.id:8443/session_login.cgi',
			'user' => $data->username,
			'pass' => $data->password,
		]);
	}
	protected function seeHosting($data)
	{
		$shown = ($_GET['show'] ?? '') === 'password';
		return view('user/hosting/see', [
			'id' => $data->id,
			'slave' => $data->server->alias,
			'user' => $data->username,
			'pass' => $shown ? esc($data->password) :
			'&bullet;&bullet;&bullet;&bullet;&bullet;&bullet;&bullet;&bullet;',
			'rawuser' => $data->username,
			'rawpass' => $data->password,
			'shown' => $shown,
		]);
	}
	protected function renameHosting($data)
	{
		if ($this->request->getMethod() === 'post' && $data->plan_price != 0 && $data->purchase_status === 'active') {
			if ($this->validate([
				'username' => 'required|alpha_dash|min_length[5]|' .
					'max_length[32]|is_unique[hosts.username]',
			])) {
				if (array_search(strtolower($_POST['username']), (new BannedNames())->names) !== FALSE) return;

				(new VirtualMinShell())->renameHosting(
					$data->cname ?: $data->default_domain,
					$data->slave_alias,
					strtolower($_POST['username'])
				);
				$this->db->table('hosting')->update([
					'username' => strtolower($_POST['username'])
				], ['id' => $data->id]);

				return view('user/hosting/output', [
					'output' => VirtualMinShell::$output,
					'link' => '/user/hosting/detail/' . $data->id
				]);
			}
		}
		return view('user/hosting/rename', [
			'data' => $data,
		]);
	}
	protected function cnameHosting($data)
	{
		if ($this->request->getMethod() === 'post' && $data->plan_price != 0 && $data->status === 'active') {
			if ($this->validate([
				'cname' => 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
					'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosting.cname]',
			])) {
				if (strpos('dom.my.id', strtolower($_POST['cname'])) === FALSE) {
					if (!$data->cname) {
						(new VirtualMinShell)->removeFromServerDNS(
							$data->username
						);
					}
					(new VirtualMinShell())->cnameHosting(
						$data->cname ?: $data->default_domain,
						$data->slave_alias,
						strtolower($_POST['cname'])
					);

					$this->db->table('hosting')->update([
						'cname' => strtolower($_POST['cname'])
					], ['id' => $data->id]);

					return view('user/hosting/output', [
						'output' => VirtualMinShell::$output,
						'link' => '/user/hosting/invoices/' . $data->id,
					]);
				}
			}
		}
		return view('user/hosting/cname', [
			'data' => $data,
		]);
	}
	protected function invoicesHosting($data)
	{
		$history = (new PurchaseModel())->atHost($data->id)->descending()->find();
		if ($this->request->getMethod() === 'post' && !empty($action = $_POST['action']) && $data->purchase_status === 'pending') {
			if ($action === 'cancel') {
				if ($data->purchase_liquid) {
					$r = explode('|', $data->purchase_liquid);
					(new LiquidRegistrar())->cancelPurchaseDomain($r[0], [
						'transaction_id' => $r[1],
					]);
				}
				if (count($history) === 1) {
					$this->db->table('hosting')->delete([
						'id' => $data->id
					]);
					return $this->response->redirect('user/hosting');
				} else {
					$this->db->table('purchase')->delete([
						'purchase_id' => $data->purchase_id
					]);
					$this->db->table('purchase')->update([
						'purchase_active' => 1
					], [
						'purchase_active' => 2,
						'purchase_hosting' => $data->id,
					]);
					return $this->response->redirect('/user/hosting/invoices/' . $data->id);
				}
			} else if ($action === 'pay') {
				$pay = (new PaymentGate())->createPayment(
					$data->purchase_id,
					$data->purchase_price,
					"Hosting $data->plan_alias $data->purchase_years Tahun" . ($data->purchase_liquid ? " dengan domain $data->domain_name" : ""),
					$data->purchase_challenge
				);
				if ($pay && isset($pay->sessionID)) {
					return $this->response->redirect(
						$this->request->config->paymentURL . $pay->sessionID
					);
				}
				return $this->response->redirect('/user/hosting/invoices/' . $data->id);
			}
		}
		return view('user/hosting/invoices', [
			'data' => $data,
			'current' => $data->purchase,
			'history' => $history,
		]);
	}
	/** @param Host $data */
	protected function deleteHosting($data)
	{
		if ($this->request->getMethod() === 'post' && $data->plan_id == 1 && ($_POST['wordpass'] ?? '') === $data->username) {
			// Handle domain
			if ($data->scheme_id == 1) {
				// Remove domain
				(new VirtualMinShell)->removeFromServerDNS(
					$data->domain
				);
			}
			(new VirtualMinShell())->deleteHosting($data->domain, $data->server->alias);
			(new HostModel())->delete($data->id);
			log_message('notice', VirtualMinShell::$output);
			return $this->response->redirect('/user/hosting/');
		}
		return view('user/hosting/delete', [
			'data' => $data,
		]);
	}
	public function hosting($page = 'list', $id = 0)
	{
		if (!$this->login->email_verified_at) {
			return $this->verify_email();
		}
		if ($page === 'list') {
			return $this->listHosting();
		} else if ($page === 'create') {
			return $this->createHosting();
		} else {
			$data = (new HostModel())->atLogin($this->login->id)->find($id);
			if ($data) {
				if ($page === 'detail') {
					return $this->detailHosting($data);
				} else if ($page === 'login') {
					return $this->loginHosting($data);
				} else if ($page === 'rename') {
					return $this->renameHosting($data);
				} else if ($page === 'cname') {
					return $this->cnameHosting($data);
				} else if ($page === 'see') {
					return $this->seeHosting($data);
				} else if ($page === 'upgrade') {
					return $this->upgradeHosting($data);
				} else if ($page === 'invoices') {
					return $this->invoicesHosting($data);
				} else if ($page === 'delete') {
					return $this->deleteHosting($data);
				}
			} else {
				return $this->response->redirect('/user/hosting');
			}
		}
		throw new PageNotFoundException();
	}

	protected function checkDomain()
	{
		if (!empty($_GET['name']) && !empty($_GET['scheme'])) {
			$name = $_GET['name'];
			/** @var Scheme */
			$scheme = (new SchemeModel())->find($_GET['scheme']);
			if (strlen($name) > 512 || strpos($name, '.') !== false || !$scheme || $scheme->{'price_'.lang('Interface.currency')} == 0) {
				return $this->response->setJSON(['status' => 'invalid']);
			}
			$domain = $name . $scheme->alias;
			$response = (new LiquidRegistrar())->isDomainAvailable($domain);
			// possible values: error, regthroughothers, available
			$status = 'error';
			if (isset($response[0]->{$domain}->status)) {
				$status = $response[0]->{$domain}->status;
			} else if (isset($response[0]->{""}->status)) {
				$status = $response[0]->{""}->status;
			}
			return $this->response->setJSON([
				'status' => $status,
				'domain' => $domain,
				'price' => $scheme->scheme_price,
				'renew' => $scheme->scheme_renew,
			]);
		}
		return $this->response->setJSON(['status' => 'invalid']);
	}

	protected function createDomain()
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'domain_name' => 'required|regex_match[/^[-\w]+$/]',
				'domain_scheme' => 'required|is_not_unique[schemes.id]',
				'years' => 'required|greater_than[0]|less_than[6]',
				'registrant_contact_id' => 'required|integer',
				'billing_contact_id' => 'required|integer',
				'admin_contact_id' => 'required|integer',
				'tech_contact_id' => 'required|integer',
			])) {
				$post = array_intersect_key(
					$this->request->getPost(),
					array_flip([
						'domain_name', 'domain_scheme', 'years', 'registrant_contact_id',
						'billing_contact_id', 'admin_contact_id', 'tech_contact_id',
						'purchase_privacy_protection'
					])
				);
				$scheme = (new SchemeModel())->find($post['domain_scheme']);
				if ($scheme->scheme_price == 0) return;
				$post['domain_name'] .= $scheme->scheme_alias;
				unset($post['domain_scheme']);
				$post['customer_id'] = $this->liquid->liquid_id;
				$post['invoice_option'] = 'only_add';
				log_message('notice', $rrr = (new LiquidRegistrar())->issuePurchaseDomain($post));
				return $this->syncDomain();
			}
		}

		return view('user/domain/create', [
			'schemes' => $this->db->table('schemes')->get()->getResult(),
			'contacts' => $this->liquid->contacts,
		]);
	}
	protected function listDomain()
	{
		if (strtolower($_POST['action'] ?? '') === 'sync') {
			return $this->syncDomain();
		}
		return view('user/domain/list', [
			'liquid' => $this->liquid,
			'page' => 'domain',
		]);
	}

	protected function loginDomain()
	{
		return view('user/domain/login', [
			'user' => $this->login->email,
			'pass' => $this->liquid->password,
			'uri' => $this->request->config->liquidCustomer,
		]);
	}
	protected function detailDomain($domain)
	{
		return view('user/domain/detail', [
			'data' => $domain,
			'hosting' => fetchOne('hosting', ['domain' => $domain->domain_id])
		]);
	}
	protected function invoiceDomain($id)
	{
		return view('user/domain/invoice', []);
	}
	protected function deleteDomain($id)
	{
		return view('user/domain/domain', []);
	}
	protected function introDomain()
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'name' => 'required',
				'email' => 'required|valid_email',
				'password' => 'required|min_length[8]',
				'company' => 'required',
				'tel_no' => 'required',
				'tel_cc_no' => 'required|integer',
				'address_line_1' => 'required',
				'city' => 'required',
				'state' => 'required',
				'country_code' => 'required',
				'zipcode' => 'required',
			])) {
				$post = array_intersect_key(
					$this->request->getPost(),
					array_flip([
						'name', 'email', 'password', 'company', 'tel_no', 'tel_cc_no',
						'alt_tel_no', 'alt_tel_cc_no', 'address_line_1', 'address_line_2', 'address_line_1',
						'city', 'state', 'country_code', 'zipcode'
					])
				);
				$data = (new LiquidRegistrar())->createCustomer($post);
				if (isset($data->customer_id)) {
					$this->db->table('liquid')->insert([
						'id' => $data->customer_id,
						'password' => $post['password'],
						'login_id' => $this->login->id,
					]);
					$s = $this->syncDomain();
					return ($_GET['then'] ?? '') !== 'reload' ? $s :
						'<!doctype html><body><script>window.opener.location.reload();window.close();</script></body>';
				}
				return view('user/hosting/output', [
					'output' => json_encode($data),
					'link' => '/user/domain/',
				]);
			}
		}
		return view('user/domain/intro', [
			'page' => 'domain',
			'data' => $_SESSION,
			'codes' => CountryCodes::$codes,
		]);
	}
	protected function topupDomain()
	{

		return view('user/domain/topup', [
			'data' => $this->liquid
		]);
	}
	protected function syncDomain()
	{
		$liquid = new LiquidRegistrar();
		// get ID matches email
		$data = $liquid->getCustomerWithEmail($this->login->email);
		if ($data && count($data) > 0) {
			$customer = $data[0];
			$liquid_id = $customer->customer_id;
			$contacts = $liquid->getListOfContacts($liquid_id);
			$domains = $liquid->getListOfDomains($liquid_id);
			$liquid_pending_transactions = $liquid->getPendingTransactions($liquid_id);
			$liquid_default_contacts = $liquid->getDefaultContacts($liquid_id);

			$this->db->table('liquid')->update([
				'id' => $liquid_id,
				'customer' => json_encode($customer),
				'domains' => json_encode($domains),
				'contacts' => json_encode($contacts),
				'pending_transactions' => json_encode($liquid_pending_transactions),
				'default_contacts' => json_encode($liquid_default_contacts),
			], [
				'login_id' => $this->login->id,
			]);
		} else {
			$this->db->table('liquid')->delete([
				'login_id' => $this->login->id,
			]);
		}
		return $this->response->redirect('/user/domain');
	}

	protected $liquid;

	public function domain($page = 'list', $id = 0)
	{
		if (!$this->login->email_verified_at) {
			return $this->verify_email();
		}
		if ($this->liquid = (new LiquidModel())->atLogin($this->login->id)) {
			if ($page == 'list') {
				return $this->listDomain();
			} else if ($page == 'check') {
				return $this->checkDomain();
			} else if ($page == 'login') {
				return $this->loginDomain();
			} else if ($page == 'create') {
				return $this->createDomain();
			} else if ($page == 'topup') {
				return $this->topupDomain();
			}
			return $this->response->redirect('/user/domain');
		} else {
			return $this->introDomain();
		}
	}

	public function status()
	{
		return view('user/status', [
			'page' => 'status',
		]);
	}

	public function verify_email()
	{
		if ($this->request->getMethod() === 'post' && ($_POST['action'] === 'resend')) {
			$data = $this->login;
			if (!$data->otp) {
				$data->otp = random_int(111111111, 999999999);
				$this->db->table('login')->update([
					'otp' => $data->otp
				], [
					'login_id' => $data->login_id
				]);
			}
			(new SendGridEmail())->send('verify_email', 'billing', [[
				'to' => [[
					'email' => $data->email,
					'name' => $data->name,
				]],
				'dynamic_template_data' => [
					'name' => $data->name,
					'verify_url' => base_url('verify?code=' . urlencode(base64_encode($data->email . ':' . $data->otp))),
				]
			]]);
			$this->session->destroy();
			return $this->response->redirect("/$data->lang/login?msg=emailsent");
		}
		return view('user/veremail', [
			'email' => $this->session->email,
		]);
	}
	public function profile()
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'name' => 'required|min_length[3]|max_length[255]',
				'phone' => 'required|min_length[8]|max_length[16]',
				'email' => $this->login->email_verified_at ? 'permit_empty' : 'required|valid_email',
				'lang' => 'required|in_list[id,en]',
			])) {
				$data = array_intersect_key(
					$this->request->getPost(),
					array_flip(
						['name', 'email', 'phone', 'lang']
					)
				);
				$data['id'] = $this->login->id;
				if ($this->login->email_verified_at) {
					unset($data['email']);
				}
				(new LoginModel())->save($data);
				$this->request->setLocale($data['lang']);
				return $this->response->redirect('/user/profile');
			}
		}
		return view('user/profile', [
			'data' => $this->login,
			'email_verified' => $this->login->email_verified_at,
			'page' => 'profile',
		]);
	}

	public function reset()
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'password' => 'required|min_length[8]',
				'passconf' => 'required|matches[password]',
			])) {
				$this->db->table('login')->update([
					'password' => password_hash($_POST['password'], PASSWORD_BCRYPT)
				], [
					'login_id' => $this->login->id
				]);
			}
		}
		return $this->response->redirect('/user/profile');
	}

	public function delete()
	{
		$ok = $this->db->table('hosts')->where(['login_id' => $this->login->id])->countAll() === 0;
		$ok = $ok && count((new LiquidModel())->atLogin($this->login->id)->domains ?? []) === 0;
		if ($ok && $this->request->getMethod() === 'post' && strpos($this->request->getPost('wordpass'), 'Y') !== FALSE) {
			$liquid = fetchOne('liquid', [
				'liquid_login' =>  $this->login->id
			]);
			if ($liquid) {
				(new LiquidRegistrar())->deleteCustomer($liquid->liquid_id);
				$this->db->table('liquid')->delete([
					'liquid_login' => $this->login->id,
				]);
			}
			$this->db->table('login')->delete([
				'login_id' => $this->login->id,
			]);
			$this->session->destroy();
			return $this->response->redirect('/');
		}
		return view('user/delete', [
			'ok' => $ok,
		]);
	}

	//--------------------------------------------------------------------

}
