<?php

namespace App\Controllers;

use App\Entities\Host;
use App\Entities\Liquid;
use App\Entities\Login;
use App\Entities\Plan;
use App\Entities\Purchase;
use App\Entities\PurchaseMetadata;
use App\Entities\Scheme;
use App\Entities\Server;
use App\Entities\ServerStat;
use App\Libraries\BannedNames;
use App\Libraries\CountryCodes;
use App\Libraries\LiquidRegistrar;
use App\Libraries\IpaymuGate;
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
use App\Models\ServerModel;
use App\Models\ServerStatModel;
use App\Models\TemplatesModel;
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
			$path = Services::request()->detectPath('REQUEST_URI');
			$query = Services::request()->detectPath('QUERY_STRING');
			$this->session->destroy();
			Services::response()->redirect(href(
				'login?r=' . urlencode($path . ($query ? '?' . $query : ''))
			))->pretend(false)->send();
			exit;
		} else {
			$this->request->setLocale($this->login->lang);
		}
	}

	public function index()
	{
		return $this->response->redirect('/user/host');
	}

	protected function listHost()
	{
		return view('user/host/list', [
			'list' => (new HostModel())->atLogin($this->login->id)->find(),
			'page' => 'hosting',
		]);
	}
	protected function processNewDomainTransaction($domain, $years)
	{
		$liq = (new LiquidModel())->atLogin($this->login->id);
		$liqc = $liq->default_contacts;
		$liquid['domain_name'] = $domain;
		$liquid['customer_id'] = $liq->id;
		$liquid['years'] = $years;
		$liquid['registrant_contact_id'] = $liqc->registrant_contact->contact_id;
		$liquid['billing_contact_id'] = $liqc->billing_contact->contact_id;
		$liquid['admin_contact_id'] = $liqc->admin_contact->contact_id;
		$liquid['tech_contact_id'] = $liqc->tech_contact->contact_id;
		$liquid['purchase_privacy_protection'] = '0';
		$liquid['invoice_option'] = 'only_add';
		return (new LiquidRegistrar())->issuePurchaseDomain($liquid);
	}
	protected function createHost()
	{
		$count = $this->db->table('hosts')->where('login_id', $this->login->id)->countAllResults();
		$ok = $count < ($this->login->trustiness === 0 ? 1 : ($this->login->trustiness * 5));
		if ($ok && $this->request->getMethod() === 'post') {
			if ($this->validate([
				'plan' => 'required|is_not_unique[plans.id]',
				'username' => 'required|alpha_dash|min_length[5]|' .
					'max_length[32]|is_unique[hosts.username]',
				'server' => 'required|is_not_unique[servers.id]',
				'password' => 'required|min_length[8]|regex_match[/^[^\'"\/\\\\:]+$/]',
				'domain_mode' => empty($_POST['domain_mode']) ? 'permit_empty' : 'required|in_list[free,buy,custom]',
			])) {
				$data = array_intersect_key(
					$this->request->getPost(),
					array_flip(['plan', 'username', 'server', 'template', 'password', 'domain_mode'])
				);
				if (empty($data['domain_mode'])) $data['domain_mode'] = 'free';
				/** @var Plan */
				$plan = (new PlanModel())->find($data['plan']);
				/** @var Server */
				$server = (new ServerModel())->find($data['server']);
				if (array_search(strtolower($data['username']), (new BannedNames())->names) !== FALSE) return;
				$hosting = new Host([
					'login_id' => $this->login->id,
					'username' => strtolower($data['username']),
					'password' => $data['password'],
					'server_id' => $data['server'],
					'plan_id' => $data['plan'],
					'scheme_id' => null,
				]);
				if ($plan->price_local != 0) {
					if ($this->validate([
						'custom_cname' => $data['domain_mode'] === 'custom' ? 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
							'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosts.domain]' : 'permit_empty',
						'buy_cname' => $data['domain_mode'] === 'buy' ? 'required|regex_match[/^[-\w]+$/]' : 'permit_empty',
						'buy_scheme' => $data['domain_mode'] === 'buy' ? 'required|is_not_unique[schemes.id]' : 'permit_empty',
						'years' => 'required|integer|greater_than[0]|less_than[6]',
						'addons' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[10000]',
					])) {
						// Not Free, so add invoice details
						$payment = new Purchase([
							'status' => 'pending',
						]);
						$metadata = new PurchaseMetadata([
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
							"_status" => null,
						]);
						if ($data['domain_mode'] === 'free') {
							$hosting->domain = $hosting->username . $server->domain;
						} else if ($data['domain_mode'] === 'buy') {
							/** @var Scheme */
							$scheme = (new SchemeModel())->find($_POST['buy_scheme']);
							$hosting->domain = $_POST['buy_cname'] . $scheme->alias;
							$hosting->scheme_id = $_POST['buy_scheme'];
							$metadata->price += $scheme->price_local + $scheme->renew_local * ($metadata->years - 1); {
								$rrr = $this->processNewDomainTransaction($hosting->domain, $metadata->years);
								$hosting->liquid_id = $rrr->domain_id;
								$metadata->liquid = $rrr->transaction_id;
							}
						} else if ($data['domain_mode'] === 'custom') {
							$hosting->domain = $_POST['custom_cname'];
						}
						$metadata->expiration = $hosting->expiry_at = date('Y-m-d H:i:s', strtotime("+$metadata->years years", \time()));
						$metadata->price += $plan->price_local * $_POST['years'];
						$metadata->price += ['idr' => 500, 'usd' => 0.05][$metadata->price_unit] * $metadata->addons;
						$metadata->price += ['idr' => 5000, 'usd' => 0.5][$metadata->price_unit];
						$hosting->status = 'pending';
						$hosting->expiry_at = $metadata->expiration;
						$payment->metadata = $metadata->toRawArray();
					} else {
						$this->request->setMethod('get');
						return $this->createHost();
					}
				} else {
					// Free plan. Just create
					$hosting->status = $data['template'] ? 'starting' : 'active';
					$hosting->expiry_at = date('Y-m-d H:i:s', strtotime("+2 months", \time()));
					$hosting->domain = $hosting->username . $server->domain;
					(new VirtualMinShell())->createHost(
						$hosting->username,
						$hosting->password,
						$this->login->email,
						$hosting->domain,
						$server->alias,
						$plan->alias,
						$plan->features
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
						$payment->host_id = $id;
						(new PurchaseModel())->insert($payment);
					} else if ($data['template']) {
						(new TemplateDeployer())->schedule(
							$id,
							$hosting->domain,
							$data['template']
						);
					}
					return $this->response->redirect('/user/host/invoices/' . $id);
				}
			}
		}
		return view('user/host/create', [
			'plans' => (new PlanModel())->find(),
			'servers' => (new ServerModel())->find(),
			'schemes' => (new SchemeModel())->find(),
			'liquid' => (new LiquidModel())->atLogin($this->login->id),
			'templates' => (new TemplatesModel())->atLang($this->login->lang)->findAll(),
			'trustiness' => $this->login->trustiness,
			'validation' => $this->validator,
			'ok' => $ok,
		]);
	}
	/** @param Host $host */
	protected function upgradeHost($host)
	{
		if ($this->request->getMethod() === 'post') {
			if (!($current = $host->purchase) && $_POST['plan'] === '1') {
				// Preliminating check. If current is free then requesting free again:
				// Just expand the expiry time and do nothing else.
				$host->expiry_at = date('Y-m-d H:i:s', strtotime("+2 months", \time()));
				(new HostModel())->save($host);
				return $this->response->redirect('/user/host/invoices/' . $host->id);
			}
			// Anything goes out of rule... nice try hackers.
			$mode = $_POST['mode'] ?? '';
			if (!$this->validate([
				'mode' => $host->plan_id === 1 ? 'required|in_list[new]' : 'required|in_list[new,extend,upgrade,topup]',
				'plan' => $mode === 'topup' ? 'permit_empty' : 'required|greater_than[1]|is_not_unique[plans.id]',
				'years' => $mode === 'new' || $mode === 'extend' ? 'required|greater_than[0]|less_than[6]' : 'permit_empty',
				'addons' => ($_POST['addons'] ?? null) ? 'required|integer|greater_than_equal_to[0]|less_than_equal_to[10000]' : 'permit_empty',
			]) || ($mode === 'upgrade' && $_POST['plan'] <= $host->plan_id)) {
				return;
			}
			$payment = new Purchase([
				'status' => 'pending',
			]);
			$metadata = new PurchaseMetadata([
				"type" => $mode,
				"price" => 0.0, // later
				"price_unit" => lang('Interface.currency'),
				"template" => null,
				"expiration" => $host->expiry_at, // later
				"years" => $mode === 'new' || $mode === 'extend' ?  intval($_POST['years']) : null,
				"plan" => $mode === 'upgrade' || $mode === 'new' ? intval($_POST['plan']) : $host->plan_id,
				"addons" => intval($_POST['addons'] ?? '0'),
				"liquid" => null, // later
				"scheme" => null, // later
				"_challenge" => random_int(111111111, 999999999),
				"_id" => null,
				"_via" => null,
				"_issued" => date('Y-m-d H:i:s'),
				"_invoiced" => null,
				"_status" => null,
			]);
			/** @var Plan */
			$plan = (new PlanModel())->find($plan);
			if ($mode === 'new') {
				$metadata->expiration = date('Y-m-d H:i:s', strtotime("+$metadata->years years", \time()));
				$metadata->price = $plan->price_local * $metadata->years; {
					// Setup Domain too
					$domain_mode = $_POST['domain_mode'] ?? 'free';
					if (!$this->validate([
						'custom_cname' => $domain_mode === 'custom' ? 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
							'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosts.domain]' : 'permit_empty',
						'buy_cname' => $domain_mode === 'buy' ? 'required|regex_match[/^[-\w]+$/]' : 'permit_empty',
						'buy_scheme' => $domain_mode === 'buy' ? 'required|is_not_unique[schemes.id]' : 'permit_empty',
					])) {
						return;
					}
					if ($domain_mode == 'buy') {
						/** @var Scheme */
						$scheme = (new SchemeModel())->find($_POST['buy_scheme']);
						$metadata->domain = $_POST['buy_cname'] . $scheme->alias;
						$metadata->scheme = $scheme->id;
						$metadata->price += $scheme->price_local + $scheme->renew_local * ($metadata->years - 1);
						$rrr = $this->processNewDomainTransaction($metadata->domain, $metadata->years);
						$host->liquid_id = $rrr->domain_id;
						$metadata->liquid = $rrr->transaction_id;
					} else if ($domain_mode == 'custom') {
						$metadata->domain = $_POST['custom_cname'];
					}
				}
			} else if ($mode === 'extend') {
				$metadata->expiration = date('Y-m-d H:i:s', strtotime("+$metadata->years years", strtotime($host->expiry_at)));
				$metadata->price = $plan->price_local *  $metadata->years;
				// Todo: also expand domain
			} else if ($mode === 'upgrade') {
				// The years need to be revamped
				$metadata->years = max(1, ceil($host->expiry_at->difference(now())->getYears()));
				$metadata->price = ($plan->price_local - $host->plan->price_local) * $metadata->years;
			}
			$metadata->price += ['idr' => 500, 'usd' => 0.05][$metadata->price_unit] * $metadata->addons;
			$metadata->price += ['idr' => 5000, 'usd' => 0.5][$metadata->price_unit];
			$payment->metadata = $metadata->toRawArray();
			$payment->host_id = $host->id;
			(new PurchaseModel())->save($payment);
			return $this->response->redirect('/user/host/invoices/' . $host->id);
		}
		return view('user/host/upgrade', [
			'data' => $host,
			'purchase' => $host->purchase,
			'liquid' => (new LiquidModel())->atLogin($this->login->id),
			'schemes' => (new SchemeModel())->find(),
			'plans' => (new PlanModel())->find(),
		]);
	}
	/** @param Host $host */
	protected function detailHost($host)
	{
		return view('user/host/detail', [
			'host' => $host,
			'plan' => $host->plan,
			'stat' => $host->stat,
		]);
	}
	protected function seeHost($host)
	{
		$shown = ($_GET['show'] ?? '') === 'password';
		return view('user/host/see', [
			'host' => $host,
			'id' => $host->id,
			'slave' => $host->server->alias,
			'user' => $host->username,
			'pass' => $shown ? esc($host->password) : str_repeat('&bullet;', 8),
			'rawuser' => $host->username,
			'rawpass' => $host->password,
			'shown' => $shown,
		]);
	}

	/** @param Host $host */
	protected function dnsHost($host)
	{
		if ($this->request->getMethod() === 'post') {
			$domain = $host->domain;
			if (!empty($_POST['sub'])) {
				$domain = $_POST['sub'] . '.' . $host->domain;
			}
			$heads = dns_get_record($domain, DNS_A | DNS_TXT | DNS_CNAME | DNS_MX | DNS_NS);
			return $this->response->setJSON($heads);
		}
		return view('user/host/dns', [
			'host' => $host
		]);
	}
	/** @param Host $host */
	protected function sslHost($host)
	{
		if ($this->request->getMethod() === 'post') {
			if (($_POST['action'] ?? '') == 'fix3') {
				(new VirtualMinShell())->enableFeature($host->domain, $host->server->alias, ['ssl']);
				(new VirtualMinShell())->requestLetsEncrypt($host->domain, $host->server->alias);
				return $this->response->redirect('/user/host/ssl/' . $host->id);
			} else if (($_POST['action'] ?? '') == 'fix4') {
				(new VirtualMinShell())->enableFeature($host->domain, $host->server->alias, ['ssl']);
				(new VirtualMinShell())->requestLetsEncrypt($host->domain, $host->server->alias);
				return $this->response->redirect('/user/host/ssl/' . $host->id);
			}
			$t = [0, 0, 0, 0];
			$domain = $host->domain;
			$heads = @get_headers("http://$domain/");
			// CLI::write(json_encode($heads));
			if ($heads) {
				$t[0] = 1;
				if (array_search("Location: https://$domain/", $heads)) {
					$t[3] = 1;
				}
				$heads = @get_headers("https://$domain/");
				if ($heads) {
					$t[2] = 1;
				}
				$heads = @get_headers(($t[3] ? "https:" : "http:") . "//$domain/.well-known/");
				if ($heads && strpos($heads[0], "403 Forbidden") !== false) {
					$t[1] = $t[3] ? $t[2] : 1;
				}
			}
			return $this->response->setJSON($t);
		}
		return view('user/host/ssl', [
			'host' => $host
		]);
	}
	/** @param Host $host */
	protected function renameHost($host)
	{
		if ($this->request->getMethod() === 'post' && $host->status === 'active') {
			if (!$this->validate([
				'username' => 'required|alpha_dash|min_length[5]|max_length[32]|is_unique[hosts.username]',
			])) {
				return redirect()->back();
			}
			$username = strtolower($_POST['username']);
			if (array_search($username, (new BannedNames())->names) !== FALSE) return;
			(new VirtualMinShell())->renameHost(
				$host->domain,
				$host->server->alias,
				$username
			);
			if ($host->plan_id === 1) {
				$newcname = $username.$host->server->domain;
				(new VirtualMinShell())->cnameHost(
					$host->domain,
					$host->server->alias,
					$newcname
				);
				$host->domain = $newcname;
			}
			$host->username = $username;
			(new HostModel())->save($host);
		}
		return view('user/host/rename', [
			'host' => $host,
		]);
	}
	/** @param Host $host */
	protected function cnameHost($host)
	{
		if ($this->request->getMethod() === 'post' && !$host->liquid_id && $host->plan_id !== 1 && $host->status === 'active') {
			if (isset($_POST['cname'])) {
				if (!$this->validate([
					'cname' => 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
						'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosting.domain]',
				])) {
					return;
				}
				$domain = strtolower($_POST['cname']);
				$server = $host->server;
				if ($host->domain === $host->username . $server->domain) {
					(new VirtualMinShell)->removeFromServerDNS(
						$host->username
					);
				} else if (strpos($domain, $server->domain) !== false) {
					return; // Nice try, hackers.
				}
				(new VirtualMinShell())->cnameHost(
					$host->domain,
					$host->alias,
					$domain
				);
				$host->domain = $domain;
				(new HostModel())->save($host);
			} else {
				(new VirtualMinShell)->addToServerDNS(
					$host->username,
					$host->server->ip
				);
			}
			return redirect()->back();
		}
		return view('user/host/cname', [
			'host' => $host,
		]);
	}
	protected function deployesHost($host)
	{
		if ($this->request->getMethod() === 'post' && isset($_POST['template'])) {
			(new TemplateDeployer())->schedule($host->id, $host->domain, $_POST['template']);
			return $this->response->redirect('/user/host/deploys/' . $host->id);
		}
		return view('user/host/deployes', [
			'host' => $host,
			'deploys' => (new HostDeploysModel())->atHost($host->id)->find(),
		]);
	}
	/** @param Host $host */
	protected function invoicesHost($host)
	{
		/** @var Purchase[] */
		$history = (new PurchaseModel())->atHost($host->id)->descending()->find();
		$current = $history[0] ?? null;
		if ($this->request->getMethod() === 'post' && !empty($action = $_POST['action']) && $current && $current->status === 'pending') {
			$metadata = $current->metadata;
			if ($action === 'cancel') {
				if ($metadata->liquid) {
					(new LiquidRegistrar())->cancelPurchaseDomain((new LiquidModel())->atLogin($this->login->id)->id, [
						'transaction_id' => $metadata->liquid,
					]);
				}
				if (count($history) === 1) {
					(new HostModel())->delete($host->id);
					return $this->response->redirect('user/host');
				} else {
					(new PurchaseModel())->delete($current->id);
					return $this->response->redirect('/user/host/invoices/' . $host->id);
				}
			} else if ($action === 'pay' && $metadata->price_unit === 'idr') {
				$plan = (new PlanModel())->find($metadata->plan)->alias ?? '';
				$pay = (new IpaymuGate())->createPayment(
					$current->id,
					$metadata->price,
					lang('Host.formatInvoiceAlt', [
						"<b>$plan</b>",
						"<b>$metadata->domain</b>",
					]) . lang("Host.formatInvoiceSum", ["<b>$metadata->price</b>"]),
					$metadata->_challenge
				);
				if ($pay && isset($pay->sessionID)) {
					return $this->response->redirect(
						$this->request->config->ipaymuURL . $pay->sessionID
					);
				}
				return $this->response->redirect('/user/host/invoices/' . $host->id);
			}
		}
		return view('user/host/invoices', [
			'host' => $host,
			'current' => $host->purchase,
			'history' => $history,
		]);
	}
	/** @param Host $host */
	protected function deleteHost($host)
	{
		if ($this->request->getMethod() === 'post' && $host->plan_id == 1 && ($_POST['wordpass'] ?? '') === $host->username) {
			(new VirtualMinShell)->removeFromServerDNS($host->domain);
			(new VirtualMinShell())->deleteHost($host->domain, $host->server->alias);
			(new HostModel())->delete($host->id);
			log_message('notice', VirtualMinShell::$output);
			return $this->response->redirect('/user/host/');
		}
		return view('user/host/delete', [
			'host' => $host,
		]);
	}
	public function host($page = 'list', $id = 0)
	{
		if ($page === 'list') {
			return $this->listHost();
		} else if ($page === 'create') {
			return $this->createHost();
		} else {
			$host = (new HostModel())->atLogin($this->login->id)->find($id);
			if ($host) {
				if ($page === 'detail') {
					return $this->detailHost($host);
				} else if ($page === 'rename') {
					return $this->renameHost($host);
				} else if ($page === 'cname') {
					return $this->cnameHost($host);
				} else if ($page === 'deploys') {
					return $this->deployesHost($host);
				} else if ($page === 'see') {
					return $this->seeHost($host);
				} else if ($page === 'ssl') {
					return $this->sslHost($host);
				} else if ($page === 'dns') {
					return $this->dnsHost($host);
				} else if ($page === 'upgrade') {
					return $this->upgradeHost($host);
				} else if ($page === 'invoices') {
					return $this->invoicesHost($host);
				} else if ($page === 'delete') {
					return $this->deleteHost($host);
				}
			} else {
				return $this->response->redirect('/user/host');
			}
		}
		throw new PageNotFoundException();
	}

	protected function checkDomain()
	{
		if (!empty($_GET['name']) && !empty($_GET['scheme'])) {
			$name = strtolower($_GET['name']);
			/** @var Scheme */
			$scheme = (new SchemeModel())->find($_GET['scheme']);
			if (strlen($name) > 512 || strpos($name, '.') !== false || !$scheme || $scheme->{'price_' . lang('Interface.currency')} == 0) {
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
				/** @var Scheme */
				$scheme = (new SchemeModel())->find($post['domain_scheme']);
				if ($scheme->price_local == 0) return;
				$post['domain_name'] .= $scheme->alias;
				unset($post['domain_scheme']);
				$post['customer_id'] = $this->liquid->id;
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
				return view('user/host/output', [
					'output' => json_encode($data),
					'link' => '/user/domain/',
				]);
			}
		}
		return view('user/domain/intro', [
			'page' => 'domain',
			'data' => $this->login,
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

	/** @var Liquid */
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
		$id = $_GET['server'] ?? null;
		$servers = (new ServerModel())->findAll();
		/** @var Server */
		if ($id === null || !($server = (new ServerModel())->find($id))) {
			return $this->response->redirect('?server=' . $servers[0]->id);
		}
		return view('user/status', [
			'page' => 'status',
			'server' => $server,
			'servers' => $servers,
			'stat' => $server->stat->metadata,
			'stat_update' => $server->stat->updated_at->humanize(),
		]);
	}

	public function verify_email()
	{
		if ($this->request->getMethod() === 'post' && ($_POST['action'] === 'resend')) {
			$data = $this->login;
			if (!$data->otp) {
				$data->otp = random_int(111111111, 999999999);
				(new LoginModel())->save($data);
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
			return $this->response->redirect("/$data->lang/login?msg=emailsent");
		}
		return view('user/veremail', [
			'email' => $this->login->email,
		]);
	}
	public function profile()
	{
		if ($this->request->getMethod() === 'post') {
			if (($_POST['action'] ?? '') === 'resend') {
				return $this->verify_email();
			} else
			if ($this->validate([
				'name' => 'required|min_length[3]|max_length[255]',
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
			'email_verified_at' => $this->login->email_verified_at,
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
		$ok = $ok && count(($liquid = (new LiquidModel())->atLogin($this->login->id))->domains ?? []) === 0;
		if ($ok && $this->request->getMethod() === 'post' && strpos($this->request->getPost('wordpass'), 'Y') !== FALSE) {
			if ($liquid) {
				(new LiquidRegistrar())->deleteCustomer($liquid->id);
				(new LiquidModel())->delete($liquid->id);
			}
			(new LoginModel())->delete($this->login->id);
			$this->session->destroy();
			return $this->response->redirect('/');
		}
		return view('user/delete', [
			'ok' => $ok,
		]);
	}

	//--------------------------------------------------------------------

}
