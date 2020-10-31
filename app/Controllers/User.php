<?php

namespace App\Controllers;

use App\Entities\Domain;
use App\Entities\Host;
use App\Entities\Liquid;
use App\Entities\Login;
use App\Entities\Plan;
use App\Entities\Purchase;
use App\Entities\PurchaseMetadata;
use App\Entities\Scheme;
use App\Entities\Server;
use App\Libraries\BannedNames;
use App\Libraries\DigitalRegistra;
use App\Libraries\IpaymuGate;
use App\Libraries\TemplateDeployer;
use App\Libraries\VirtualMinShell;
use App\Models\DomainModel;
use App\Models\HostDeployModel;
use App\Models\HostModel;
use App\Models\LoginModel;
use App\Models\PlanModel;
use App\Models\PurchaseModel;
use App\Models\SchemeModel;
use App\Models\ServerModel;
use App\Models\TemplatesModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;
use DateTime;

class User extends BaseController
{
	/** @var Login */
	protected $login;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		if (!$this->session->get('login') || (!($this->login = (new LoginModel())->find($this->session->login)))) {
			// @codeCoverageIgnoreStart
			$path = Services::request()->detectPath('REQUEST_URI');
			$query = Services::request()->detectPath('QUERY_STRING');
			$this->session->destroy();
			Services::response()->redirect(href(
				'login?r=' . urlencode($path . ($query ? '?' . $query : ''))
			))->pretend(false)->send();
			exit;
			// @codeCoverageIgnoreEnd
		} else {
			$this->request->setLocale($this->login->lang);
		}
	}

	public function index()
	{
		return $this->response->redirect('/user/host'); // @codeCoverageIgnore
	}

	protected function listHost()
	{
		return view('user/host/list', [
			'list' => (new HostModel())->atLogin($this->login->id)->find(),
			'page' => 'hosting',
		]);
	}
	/**
	 * @param PurchaseMetadata $metadata
	 * @param mixed $input
	 * @param Server $server
	 * @return Domain|string|null if valid, return final domain object/string
	 */
	protected function processNewDomainTransaction($metadata, $input, $server = null)
	{
		if (!is_array($input)) {
			return null; // @codeCoverageIgnore
		} elseif ($this->validator->reset()->setRules([
			'scheme' => 'required|is_not_unique[schemes.id]',
			'name' => 'required|regex_match[/^[-\w]+$/]',
		])->run($input)) {
			if (empty($input['bio']) || !$this->validator->reset()->setRules([
				'fname' => 'required|max_length[32]',
				'company' => 'required|max_length[32]',
				'email' => 'required|valid_email|max_length[63]',
				'tel' => 'required|max_length[15]',
				'country' => 'required|max_length[3]',
				'state' => 'required|max_length[32]',
				'city' => 'required|max_length[32]',
				'postal' => 'required|max_length[8]',
				'address1' => 'required|max_length[255]',
			])->run(($bio = json_decode($input['bio'], true))['owner']))
				return null; // @codeCoverageIgnore
			/** @var Scheme */
			$scheme = (new SchemeModel())->find($input['scheme']);
			$domain = new Domain([
				'name' => $input['name'] . $scheme->alias,
				'login_id' => $this->login->id,
				'scheme_id' => $scheme->id,
				'status' => 'pending',
			]);
			$model = new DomainModel();
			if (!$model->save($domain)) return null;
			$metadata->price += $scheme->price_local + $scheme->renew_local * ($metadata->years - 1);
			$metadata->registrar = (new DigitalRegistra())->normalizeDomainInput(
				$bio['owner'],
				$bio['user'] ?? [],
				$metadata->years,
				$domain,
				$domain->scheme,
				$server,
				$this->login
			);
			$domain->id = $model->getInsertID();
			return $domain;
		} elseif ($this->validator->reset()->setRules([
			'custom' => 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
				'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosts.domain]'
		])->run($input)) {
			return $metadata->domain = $input['custom'];
		} else return null; // @codeCoverageIgnore
	}

	/**
	 * @param IncomingRequest $request
	 * @param Purchase $payment
	 * @param Host $hosting
	 */
	protected function checkNewDomainTransaction($request, $payment, $hosting)
	{
		$metadata = $payment->metadata;
		if ($newdomain = $this->processNewDomainTransaction($metadata, $request->getPost('domain'), $hosting->server)) {
			$payment->metadata = $metadata;
			if ($newdomain instanceof Domain) {
				$payment->domain_id = $newdomain->id;
				$hosting->domain = $newdomain->name;
			} else {
				$hosting->domain = $newdomain;
			}
		} else {
			$hosting->domain = $hosting->username . $hosting->server->domain; // @codeCoverageIgnore
		}
	}
	protected function createHost()
	{
		$r = $this->request;
		$count = $this->db->table('hosts')->where('login_id', $this->login->id)->countAllResults();
		$ok = $count < ($this->login->trustiness === 0 ? 1 : ($this->login->trustiness * 5));
		if ($ok && $r->getMethod() === 'post') {
			if ($this->validate([
				'plan' => 'required|is_not_unique[plans.id]',
				'username' => 'required|alpha_dash|min_length[5]|' .
					'max_length[32]|is_unique[hosts.username]',
				'server' => 'required|is_not_unique[servers.id]',
				'password' => 'required|min_length[8]|regex_match[/^[^\'"\/\\\\:]+$/]',
			])) {
				$data = array_intersect_key(
					$r->getPost(),
					array_flip(['plan', 'username', 'server', 'template', 'password'])
				);
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
				]);

				if ($plan->price_local !== 0) {
					if ($this->validate([
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
							"years" => intval($r->getPost('years')),
							"plan" => intval($data['plan']),
							"addons" => intval($r->getPost('addons')),
							"_challenge" => random_int(111111111, 999999999),
							"_id" => null,
							"_via" => null,
							"_issued" => date('Y-m-d H:i:s'),
							"_invoiced" => null,
							"_status" => null,
						]);
						$metadata->price += $plan->price_local * $r->getPost('years');
						$metadata->price += ['idr' => 5000, 'usd' => 0.5][$metadata->price_unit];
						$metadata->price += ['idr' => 1000, 'usd' => 0.1][$metadata->price_unit] * $metadata->addons;
						$metadata->expiration = date('Y-m-d H:i:s', strtotime("+$metadata->years years"));
						$hosting->expiry_at = $metadata->expiration;
						$hosting->status = 'pending';
						$payment->metadata = $metadata;
						$this->checkNewDomainTransaction($this->request, $payment, $hosting);
					} else {
						return $this->response->redirect('/user/host/'); // @codeCoverageIgnore
					}
				} else {
					// Free plan. Just create
					$hosting->status = ($data['template'] ?? '') ? 'starting' : 'active';
					$hosting->expiry_at = date('Y-m-d H:i:s', strtotime("+2 months", \time()));
					$hosting->domain = $hosting->username . $server->domain;
					(new VirtualMinShell())->createHost(
						$hosting->username,
						$hosting->password,
						$this->login->email,
						$hosting->domain,
						$server->alias,
						$plan->alias
					);
					log_message('notice', VirtualMinShell::$output);
				}
				// Send to Database
				if ($id = (new HostModel())->insert($hosting)) {
					if (isset($payment)) {
						$payment->host_id = $id;
						(new PurchaseModel())->insert($payment);
					} else if ($data['template'] ?? '') {
						// @codeCoverageIgnoreStart
						(new TemplateDeployer())->schedule(
							$id,
							$hosting->domain,
							$data['template']
						);
						// @codeCoverageIgnoreEnd
					}
					return $this->response->redirect('/user/host/invoices/' . $id);
				}
			}
			return redirect()->back()->withInput()->with('errors', $this->validator->listErrors()); // @codeCoverageIgnore
		}
		return view('user/host/create', [
			'plans' => (new PlanModel())->find(),
			'servers' => (new ServerModel())->find(),
			'schemes' => (new SchemeModel())->find(),
			'templates' => (new TemplatesModel())->atLang($this->login->lang)->findAll(),
			'trustiness' => $this->login->trustiness,
			'email' => $this->login->email,
			'validation' => $this->validator,
			'ok' => $ok,
		]);
	}
	/** @param Host $host */
	protected function upgradeHost($host)
	{
		$req = $this->request;
		if ($req->getMethod() === 'post') {
			if (!$host->purchase && $req->getPost('plan') === '1') {
				// Preliminating check. If current is free then requesting free again:
				// Just expand the expiry time and do nothing else.
				$host->expiry_at = date('Y-m-d H:i:s', strtotime("+2 months", \time()));
				(new HostModel())->save($host);
				return $this->response->redirect('/user/host/invoices/' . $host->id);
			}
			// Anything goes out of rule... nice try hackers.
			$mode = $req->getPost('mode');
			if (!$this->validate([
				'mode' => $host->plan_id === 1 ? 'required|in_list[new]' : 'required|in_list[new,extend,upgrade,topup]',
				'plan' => $mode === 'topup' || $mode === 'extend' ? 'permit_empty' : 'required|greater_than[1]|is_not_unique[plans.id]',
				'years' => $mode === 'new' || $mode === 'extend' ? 'required|greater_than[0]|less_than[6]' : 'permit_empty',
				'addons' => $req->getPost('addons') ? 'required|integer|greater_than_equal_to[0]|less_than_equal_to[10000]' : 'permit_empty',
			]) || ($mode === 'upgrade' && $req->getPost('plan') <= $host->plan_id)) {
				return; // @codeCoverageIgnore
			}
			$payment = new Purchase([
				'status' => 'pending',
			]);
			$metadata = new PurchaseMetadata([
				"type" => $mode,
				"price" => 0.0, // later
				"price_unit" => lang('Interface.currency'),
				"template" => null,
				"expiration" => $host->expiry_at->toDateTimeString(), // later
				"years" => $mode === 'new' || $mode === 'extend' ?  intval($req->getPost('years')) : null,
				"plan" => $mode === 'upgrade' || $mode === 'new' ? intval($req->getPost('plan')) : null,
				"addons" => intval($req->getPost('addons')),
				"_challenge" => random_int(111111111, 999999999),
				"_id" => null,
				"_via" => null,
				"_issued" => date('Y-m-d H:i:s'),
				"_invoiced" => null,
				"_status" => null,
			]);
			/** @var Plan */
			$plan = (new PlanModel())->find($metadata->plan);
			if ($mode === 'new') {
				$metadata->expiration = date('Y-m-d H:i:s', strtotime("+$metadata->years years", \time()));
				$metadata->price = $plan->price_local * $metadata->years;
			} else if ($mode === 'extend') {
				$metadata->expiration = date('Y-m-d H:i:s', strtotime("+$metadata->years years", strtotime($host->expiry_at)));
				$metadata->price = $host->purchase->price_local *  $metadata->years;
				// Todo: also expand domain
			} else if ($mode === 'upgrade') {
				// The years need to be revamped
				$metadata->years = max(1, ceil($host->expiry_at->difference(new DateTime())->getYears()));
				$metadata->price = ($plan->price_local - $host->plan->price_local) * $metadata->years;
			}
			$metadata->price += ['idr' => 1000, 'usd' => 0.1][$metadata->price_unit] * $metadata->addons;
			$metadata->price += ['idr' => 5000, 'usd' => 0.5][$metadata->price_unit];
			$payment->metadata = $metadata;
			$payment->host_id = $host->id;
			if ($mode === 'new')
				// Setup Domain too
				$this->checkNewDomainTransaction($this->request, $payment, $host);
			(new PurchaseModel())->save($payment);
			return $this->response->redirect('/user/host/invoices/' . $host->id);
		}
		return view('user/host/upgrade', [
			'data' => $host,
			'purchase' => $host->purchase,
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
			'webminport' => $this->request->config->sudoWebminPort,
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
			// @codeCoverageIgnoreStart
			$domain = $host->domain;
			if (!empty($this->request->getPost('sub'))) {
				$domain = $this->request->getPost('sub') . '.' . $host->domain;
			}
			$heads = dns_get_record($domain, DNS_A | DNS_TXT | DNS_CNAME | DNS_MX | DNS_NS);
			return $this->response->setJSON($heads);
			// @codeCoverageIgnoreEnd
		}
		return view('user/host/dns', [
			'host' => $host
		]);
	}
	/** @param Host $host */
	protected function nginxHost($host)
	{
		if ($this->request->getMethod() === 'post') {
			// @codeCoverageIgnoreStart
			$nginx = (new VirtualMinShell())->getNginxConfig($host->domain, $host->server->alias);
			return $this->response->setContentType('application/nginx')->setBody($nginx);
			// @codeCoverageIgnoreEnd
		}
		return view('user/host/nginx', [
			'host' => $host
		]);
	}
	/** @param Host $host  */
	protected function sslHost($host)
	{
		if ($this->request->getMethod() === 'post') {
			// @codeCoverageIgnoreStart
			if (($this->request->getPost('action')) == 'fix3') {
				(new VirtualMinShell())->enableFeature($host->domain, $host->server->alias, ['ssl']);
				(new VirtualMinShell())->requestLetsEncrypt($host->domain, $host->server->alias);
				return $this->response->redirect('/user/host/ssl/' . $host->id);
			} else if (($this->request->getPost('action')) == 'fix4') {
				(new VirtualMinShell())->enableFeature($host->domain, $host->server->alias, ['ssl']);
				(new VirtualMinShell())->requestLetsEncrypt($host->domain, $host->server->alias);
				return $this->response->redirect('/user/host/ssl/' . $host->id);
			}
			$t = [0, 0, 0, 0];
			$domain = $host->domain;
			$heads = @get_headers("http://$domain/");
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
			// @codeCoverageIgnoreEnd
		}
		return view('user/host/ssl', [
			'host' => $host
		]);
	}
	/**  @param Host $host */
	protected function renameHost($host)
	{
		if ($this->request->getMethod() === 'post' && $host->status === 'active') {
			if (!$this->validate([
				'username' => 'required|alpha_dash|min_length[5]|max_length[32]|is_unique[hosts.username]',
			])) {
				return redirect()->back(); // @codeCoverageIgnore
			}
			$username = strtolower($this->request->getPost('username'));
			if (array_search($username, (new BannedNames())->names) !== FALSE) return;
			(new VirtualMinShell())->renameHost(
				$host->domain,
				$host->server->alias,
				$username
			);
			if ($host->plan_id === 1) {
				$newcname = $username . $host->server->domain;
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
			if ($this->request->getPost('cname')) {
				$server = $host->server;
				if (!$this->validate([
					'cname' => 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
						'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosts.domain]',
				])) {
					$domain = $host->username . $host->server->domain; // @codeCoverageIgnore
				} else {
					$domain = strtolower($this->request->getPost('cname'));
					if (strpos($domain, $server->domain) !== false) {
						return; // @codeCoverageIgnore
					}
				}
				(new VirtualMinShell())->cnameHost(
					$host->domain,
					$host->alias,
					$domain
				);
				$host->domain = $domain;
				(new HostModel())->save($host);
			}
			return redirect()->back();
		}
		return view('user/host/cname', [
			'host' => $host,
		]);
	}
	protected function deployesHost($host)
	{
		if ($this->request->getMethod() === 'post' && $this->request->getPost('template')) {
			// @codeCoverageIgnoreStart
			(new TemplateDeployer())->schedule($host->id, $host->domain, $this->request->getPost('template'));
			return $this->response->redirect('/user/host/deploys/' . $host->id);
			// @codeCoverageIgnoreEnd
		}
		return view('user/host/deployes', [
			'host' => $host,
			'deploys' => (new HostDeployModel())->atHost($host->id)->find(),
		]);
	}
	/** @param Host $host */
	protected function invoicesHost($host)
	{
		/** @var Purchase[] */
		$history = (new PurchaseModel())->atHost($host->id)->descending()->find();
		$current = $history[0] ?? null;
		if ($this->request->getMethod() === 'post' && !empty($action = $this->request->getPost('action')) && $current && $current->status === 'pending') {
			$metadata = $current->metadata;
			if ($action === 'cancel') {
				if (count($history) > 1 || $host->status !== 'pending') {
					(new PurchaseModel())->delete($current->id);
					return $this->response->redirect('/user/host/invoices/' . $host->id);
					// @codeCoverageIgnoreStart
				} else {
					(new HostModel())->delete($host->id);
					return $this->response->redirect('user/host');
				}
			} else if ($action === 'pay' && $metadata->price_unit === 'idr') {
				$plan = (new PlanModel())->find($metadata->plan)->alias ?? '';
				$pay = (new IpaymuGate())->createPayment(
					$current->id,
					$metadata->price,
					lang('Host.formatInvoiceAlt', [
						"$plan",
						"$metadata->domain",
					]) . lang("Host.formatInvoiceSum", ["$metadata->price"]),
					$metadata->_challenge
				);
				if ($pay && isset($pay->sessionID)) {
					return $this->response->redirect(
						$this->request->config->ipaymuURL . $pay->sessionID
					);
				}
				return $this->response->redirect('/user/host/invoices/' . $host->id);
			}
			// @codeCoverageIgnoreEnd
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
		if ($this->request->getMethod() === 'post' && $host->plan_id === 1 && ($this->request->getPost('wordpass')) === $host->username) {
			// @codeCoverageIgnoreStart
			(new VirtualMinShell())->deleteHost($host->domain, $host->server->alias);
			(new HostModel())->delete($host->id);
			log_message('notice', VirtualMinShell::$output);
			return $this->response->redirect('/user/host/');
			// @codeCoverageIgnoreEnd
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
			if ($host = (new HostModel())->atLogin($this->login->id)->find($id)) {
				switch ($page) {
					case 'detail':
						return $this->detailHost($host);
					case 'rename':
						return $this->renameHost($host);
					case 'cname':
						return $this->cnameHost($host);
					case 'deploys':
						return $this->deployesHost($host);
					case 'see':
						return $this->seeHost($host);
					case 'ssl':
						return $this->sslHost($host);
					case 'dns':
						return $this->dnsHost($host);
					case 'nginx':
						return $this->nginxHost($host);
					case 'upgrade':
						return $this->upgradeHost($host);
					case 'invoices':
						return $this->invoicesHost($host);
					case 'delete':
						return $this->deleteHost($host);
				}
			} else {
				return $this->response->redirect('/user/host'); // @codeCoverageIgnore
			}
		}
		throw new PageNotFoundException(); // @codeCoverageIgnore
	}

	/**
	 * @codeCoverageIgnore
	 */
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
			$response = (new DigitalRegistra())->domainCheck($domain);
			// possible values: error, regthroughothers, available
			$status = 'error';
			if ($response === 'Domain Available') {
				$status = 'available';
			} else if ($status instanceof string) {
				$status = $status;
			}
			return $this->response->setJSON([
				'status' => $status,
				'domain' => $domain,
				'price' => $scheme->price_local,
				'renew' => $scheme->renew_local,
			]);
		}
		return $this->response->setJSON(['status' => 'invalid']);
	}

	protected function createDomain()
	{
		$r = $this->request;
		if ($r->getMethod() === 'post') {
			if ($this->validate([
				'years' => 'required|greater_than[0]|less_than[6]',
			])) {
				$payment = new Purchase([
					'status' => 'pending',
				]);
				$metadata = new PurchaseMetadata([
					"type" => "domain",
					"price" => 0.0,
					"price_unit" => lang('Interface.currency'),
					"expiration" => null,
					"years" => intval($r->getPost('years')),
					"_challenge" => random_int(111111111, 999999999),
					"_id" => null,
					"_via" => null,
					"_issued" => date('Y-m-d H:i:s'),
					"_invoiced" => null,
					"_status" => null,
				]);
				$metadata->price += ['idr' => 5000, 'usd' => 0.5][$metadata->price_unit];
				$metadata->expiration = date('Y-m-d H:i:s', strtotime("+$metadata->years years"));
				if ($newdomain = $this->processNewDomainTransaction($metadata, $r->getPost('domain'))) {
					if ($newdomain instanceof Domain) {
						$payment->metadata = $metadata;
						$payment->domain_id = $newdomain->id;
						(new PurchaseModel())->save($payment);
					}
				}
			}
		}

		return view('user/domain/create', [
			'schemes' => (new SchemeModel())->findAll(),
		]);
	}
	protected function listDomain()
	{
		return view('user/domain/list', [
			'domains' => (new DomainModel())->atLogin($this->login->id)->findAll(),
			'page' => 'domain',
		]);
	}

	protected function detailDomain($domain)
	{
		return view('user/domain/detail', [
			'domain' => $domain,
		]);
	}
	protected function invoicesDomain($domain)
	{
		return view('user/domain/invoice', [
			'domain' => $domain,
		]);
	}


	/** @var Liquid */
	protected $liquid;

	public function domain($page = 'list', $id = 0)
	{
		if ($page == 'list') {
			return $this->listDomain();
		} else if ($page == 'check') {
			return $this->checkDomain(); // @codeCoverageIgnore
		} else if ($page == 'create') {
			return $this->createDomain();
		} else {
			if ($domain = (new DomainModel())->atLogin($this->login->id)->find($id)) {
				switch ($page) {
					case 'detail':
						return $this->detailDomain($domain);
					case 'invoices':
						return $this->invoicesDomain($domain);
				}
			}
		}
		return $this->response->redirect('/user/domain'); // @codeCoverageIgnore
	}

	public function status()
	{
		$id = $this->request->getGet('server');
		$servers = (new ServerModel())->findAll();
		/** @var Server */
		if ($id === null || !($server = (new ServerModel())->find($id))) {
			return $this->response->redirect('?server=' . $servers[0]->id);
		}
		return view('user/status', [
			'page' => 'status',
			'server' => $server,
			'servers' => $servers,
			'stat' => $server->stat ? $server->stat->metadata : null,
			'stat_update' => $server->stat ? $server->stat->updated_at->humanize() : null,
		]);
	}

	public function profile()
	{
		if ($this->request->getMethod() === 'post') {
			if (($this->request->getPost('action')) === 'resend') {
				// @codeCoverageIgnoreStart
				$this->login->sendVerifyEmail();
				return $this->response->redirect("/{$this->login->lang}/login?msg=emailsent");
				// @codeCoverageIgnoreEnd
			} else if ($this->validate([
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
				'passtest' => 'required',
				'password' => 'required|min_length[8]',
				'passconf' => 'required|matches[password]',
			]) && password_verify($this->request->getPost('passtest'), $this->login->password)) {
				$this->login->password = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
				(new LoginModel())->save($this->login);
			}
		}
		return $this->response->redirect('/user/profile');
	}

	public function delete()
	{
		$ok = (new HostModel())->atLogin($this->login->id)->countAllResults() === 0;
		$ok = $ok && (new DomainModel())->atLogin($this->login->id)->countAllResults() === 0;
		if ($ok && $this->request->getMethod() === 'post' && strpos($this->request->getPost('wordpass'), 'Y') !== FALSE) {
			(new LoginModel())->delete($this->login->id);
			if (ENVIRONMENT !== 'testing')
				$this->session->destroy(); // @codeCoverageIgnore
			return $this->response->redirect('/');
		}
		return view('user/delete', [
			'ok' => $ok,
		]);
	}

	//--------------------------------------------------------------------

}
