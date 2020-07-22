<?php

namespace App\Controllers;

use App\Models\BannedNames;
use App\Models\CountryCodes;
use App\Models\LiquidRegistrar;
use App\Models\PaymentGate;
use App\Models\VirtualMinShell;
use CodeIgniter\Email\Email as EmailEmail;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Email;
use Config\Services;
use ErrorException;

class User extends BaseController
{
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		if (!$this->session->get('login_id')) {
			Services::response()->redirect(href(
				'login?r=' . Services::request()->detectPath()
			))->pretend(false)->send();
			exit;
		} else {
			$this->request->setLocale($this->session->lang);
		}
	}

	public function index()
	{
		return $this->response->redirect('/user/hosting');
	}

	protected function listHosting()
	{
		return view('user/hosting/list', [
			'list' => $this->db->table('hosting__display')->where([
				'hosting_login' => $this->session->login_id
			])->get()->getResult(),
			'page' => 'hosting',
		]);
	}
	protected function createHosting()
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'plan' => 'required|is_not_unique[plans.plan_id]',
				'username' => 'required|alpha_dash|min_length[5]|' .
					'max_length[32]|is_unique[hosting.hosting_username]',
				'slave' => 'required|is_not_unique[slaves.slave_id]',
				'password' => 'required|min_length[8]',
				'domain_mode' => empty($_POST['domain_mode']) ? 'permit_empty' : 'required|in_list[free,buy,custom]',
				'template' => 'required|is_not_unique[templates.template_id]',
			])) {
				$data = array_intersect_key(
					$this->request->getPost(),
					array_flip(['plan', 'username', 'slave', 'template', 'password', 'domain_mode'])
				);
				if (empty($data['domain_mode'])) $data['domain_mode'] = 'free';
				if (!$plan = fetchOne('plans', ['plan_id' => $data['plan']])) return;
				if (!$slave = fetchOne('slaves', ['slave_id' => $data['slave']])) return;
				if (!$template = fetchOne('templates', ['template_id' => $data['template']])) return;
				if (array_search(strtolower($data['username']), (new BannedNames())->names) !== FALSE) return;
				$hosting = [
					'hosting_login' => $this->session->login_id,
					'hosting_username' => strtolower($data['username']),
					'hosting_password' => $data['password'] ?? '',
					'hosting_slave' => $data['slave'],
				];
				$domain = [
					'domain_login' => $this->session->login_id,
				];
				$payment = [
					'purchase_active' => 1,
					'purchase_plan' => $data['plan'],
					'purchase_invoiced' => date('Y-m-d H:i:s', \time()),
					'purchase_template' => $data['template'] ? $template->template_alias : '',
				];
				if ($plan->plan_alias !== 'Free') {
					if ($this->validate([
						'custom_cname' => $data['domain_mode'] === 'custom' ? 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
							'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[domain.domain_name]' : 'permit_empty',
						'buy_cname' => $data['domain_mode'] === 'buy' ? 'required|regex_match[/^[-\w]+$/]' : 'permit_empty',
						'buy_scheme' => $data['domain_mode'] === 'buy' ? 'required|is_not_unique[schemes.scheme_id]' : 'permit_empty',
						'years' => 'required|greater_than[0]|less_than[6]',
					])) {
						if ($data['domain_mode'] === 'free') {
							$domain['domain_name'] = $hosting['hosting_username'] . '.dom.my.id';
							$domain['domain_scheme'] = 1;
							$domprice = 0;
						} else if ($data['domain_mode'] === 'buy') {
							$scheme = fetchOne('schemes', ['scheme_id' => $_POST['buy_scheme']]);
							$domain['domain_name'] = $_POST['buy_cname'] . $scheme->scheme_alias;
							$domain['domain_scheme'] = $_POST['buy_scheme'];
							$domprice = $scheme->scheme_price * 1000;
							$liq = fetchOne('liquid', ['liquid_login' => $this->session->login_id]);
							$liqc = json_decode($liq->liquid_default_contacts);
							$liquid['domain_name'] = $domain['domain_name'];
							$liquid['customer_id'] = $liq->liquid_id;
							$liquid['years'] = $_POST['years'];
							$liquid['registrant_contact_id'] = $liqc->registrant_contact->contact_id;
							$liquid['billing_contact_id'] = $liqc->billing_contact->contact_id;
							$liquid['admin_contact_id'] = $liqc->admin_contact->contact_id;
							$liquid['tech_contact_id'] = $liqc->tech_contact->contact_id;
							$liquid['purchase_privacy_protection'] = '0';
							// $liquid['ns'] = 'ns1.dom.my.id,ns2.dom.my.id';
							$liquid['invoice_option'] = 'only_add';
							$rrr = (new LiquidRegistrar())->issuePurchaseDomain($liquid);
							$domain['domain_liquid'] = $rrr->domain_id;
							$payment['purchase_liquid'] = $liq->liquid_id . '|' . $rrr->transaction_id;
						} else if ($data['domain_mode'] === 'custom') {
							$domain['domain_name'] = $hosting['custom_cname'];
							$domprice = 0;
						}
						$payment['purchase_expired'] = $domain['domain_expired'] = date('Y-m-d H:i:s', strtotime("+$_POST[years] years", \time()));
						$payment['purchase_status'] = 'pending';
						$payment['purchase_years'] = $_POST['years'];
						$payment['purchase_challenge'] = random_int(111111111, 999999999);
						$payment['purchase_price'] = ($plan->plan_price * 10000 + $domprice) * $_POST['years'] + 5000;
					} else {
						$this->request->setMethod('get');
						return $this->createHosting();
					}
				} else {
					// Free plan. Just create
					$payment['purchase_status'] = 'active';
					$payment['purchase_expired'] = $domain['domain_expired'] = date('Y-m-d H:i:s', strtotime("+3 months", \time()));
					$domain['domain_name'] = $hosting['hosting_username'] . '.dom.my.id';
					$domain['domain_scheme'] = 1;
					(new VirtualMinShell())->createHosting(
						$hosting['hosting_username'],
						$hosting['hosting_password'],
						$this->session->email,
						$hosting['hosting_username'] . '.dom.my.id',
						$slave->slave_alias,
						$plan->plan_alias,
						$plan->plan_features,
						$payment['purchase_template']
					);
					(new VirtualMinShell)->addToServerDNS(
						$hosting['hosting_username'],
						$slave->slave_ip
					);
					log_message('notice', VirtualMinShell::$output);
				}
				// Send to Database
				if ($this->db->table('domain')->insert($domain)) {
					$hosting['hosting_domain'] = $this->db->insertID();
					if ($this->db->table('hosting')->insert($hosting)) {
						$payment['purchase_hosting'] = $this->db->insertID();
						if ($this->db->table('purchase')->insert($payment)) {
							return $this->response->redirect('/user/hosting/invoices/' . $payment['purchase_hosting']);
						}
					}
				}
			}
		}
		return view('user/hosting/create', [
			'plans' => $this->db->table('plans')->select([
				'plan_id', 'plan_alias', 'plan_price'
			])->get()->getResult(),
			'slaves' => $this->db->table('slaves__usage')->get()->getResult(),
			'liquid' => fetchOne('liquid', ['liquid_login' => $this->session->login_id]),
			'schemes' => $this->db->table('schemes')->get()->getResult(),
			'templates' => $this->db->table('templates')->get()->getResult(),
			'validation' => $this->validator,
		]);
	}
	protected function upgradeHosting($data)
	{
		if ($this->request->getMethod() === 'post') {
			if ($this->validate([
				'plan' => 'required|is_not_unique[plans.plan_id]',
				'mode' => 'required|in_list[new,extend,upgrade]'
			])) {
				$post = array_intersect_key(
					$this->request->getPost(),
					array_flip(['plan', 'mode'])
				);
				if (!$plan = $this->db->table('plans')->getWhere(['plan_id' => $post['plan']])->getRow()) return;
				if ($post['mode'] === 'extend' && $post['plan'] != $data->purchase_plan) return;
				if ($post['mode'] === 'upgrade' && $post['plan'] <= $data->purchase_plan) return;
				$payment = [
					'purchase_active' => 1,
					'purchase_hosting' => $data->hosting_id,
					'purchase_plan' => $post['plan'],
					'purchase_invoiced' => date('Y-m-d H:i:s', \time()),
				];
				if ($plan->plan_alias !== 'Free') {
					if ($this->validate([
						'years' => $post['mode'] !== 'upgrade' ? 'required|greater_than[0]|less_than[6]' : 'permit_empty',
					])) {
						$post['years'] = $_POST['years'] ?? $data->purchase_years;
						if ($post['mode'] === 'new') {
							$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+$post[years] years", \time()));
							$payment['purchase_price'] = $plan->plan_price * 10000 * $post['years'] + 5000;
						} else if ($post['mode'] === 'extend') {
							$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+$post[years] years", strtotime($data->purchase_expired)));
							$payment['purchase_price'] = $plan->plan_price * 10000 * $post['years'] + 5000;
						} else if ($post['mode'] === 'upgrade') {
							$payment['purchase_expired'] = $data->purchase_expired;
							$payment['purchase_price'] = ($plan->plan_price - $data->plan_price) * 10000 * $post['years'] + 5000;
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
					$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+3 months", \time()));
					$payment['purchase_status'] = 'active';
					(new VirtualMinShell())->upgradeHosting(
						$data->domain_name,
						$data->slave_alias,
						$data->plan_features,
						$plan->plan_alias,
						$plan->plan_features
					);
					if ($data->plan_alias !== 'Free' && $data->domain_scheme != 1) {
						// Change back to free domain
						if ($data->domain_scheme) {
							// Create new domain, leave the original
							if (!$this->db->table('domain')->insert([
								'domain_login' => $data->domain_login,
								'domain_name' => $data->hosting_username . '.dom.my.id',
								'domain_scheme' => 1,
							])) {
								$this->request->setMethod('get');
								return $this->upgradeHosting($data);
							}
							$this->db->table('hosting')->update([
								'hosting_domain' => $this->db->insertID(),
							], ['hosting_id' => $data->hosting_id]);
						} else {
							// Change current alias domain
							$this->db->table('domain')->update([
								'domain_name' =>  $data->hosting_username . '.dom.my.id',
								'domain_scheme' => 1,
							], ['domain_id' => $data->domain_id]);
						}
						(new VirtualMinShell)->cnameHosting(
							$data->domain_name,
							$data->slave_alias,
							$data->hosting_username . '.dom.my.id'
						);
						(new VirtualMinShell)->addToServerDNS(
							$data->hosting_username,
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
			'plans' => $this->db->table('plans')->select([
				'plan_id', 'plan_alias', 'plan_price'
			])->get()->getResult(),
		]);
	}
	protected function detailHosting($data)
	{
		return view('user/hosting/detail', [
			'data' => $data,
		]);
	}
	protected function loginHosting($data)
	{
		return view('user/hosting/login', [
			'uri' => "https://$data->slave_alias.dom.my.id:8443/session_login.cgi",
			'user' => $data->hosting_username,
			'pass' => $data->hosting_password,
		]);
	}
	protected function seeHosting($data)
	{
		$shown = ($_GET['show'] ?? '') === 'password';
		return view('user/hosting/see', [
			'id' => $data->hosting_id,
			'slave' => $data->slave_alias,
			'user' => $data->hosting_username,
			'pass' => $shown ? esc($data->hosting_password) :
				'&bullet;&bullet;&bullet;&bullet;&bullet;&bullet;&bullet;&bullet;',
			'shown' => $shown,
		]);
	}
	protected function renameHosting($data)
	{
		if ($this->request->getMethod() === 'post' && $data->plan_alias !== 'Free' && $data->purchase_status === 'active') {
			if ($this->validate([
				'username' => 'required|alpha_dash|min_length[5]|' .
					'max_length[32]|is_unique[hosting.hosting_username]',
			])) {
				if (array_search(strtolower($_POST['username']), (new BannedNames())->names) !== FALSE) return;

				(new VirtualMinShell())->renameHosting(
					$data->hosting_cname ?: $data->default_domain,
					$data->slave_alias,
					strtolower($_POST['username'])
				);
				$this->db->table('hosting')->update([
					'hosting_username' => strtolower($_POST['username'])
				], ['hosting_id' => $data->hosting_id]);

				return view('user/hosting/output', [
					'output' => VirtualMinShell::$output,
					'link' => '/user/hosting/detail/' . $data->hosting_id
				]);
			}
		}
		return view('user/hosting/rename', [
			'data' => $data,
		]);
	}
	protected function cnameHosting($data)
	{
		if ($this->request->getMethod() === 'post' && $data->plan_alias !== 'Free' && $data->purchase_status === 'active') {
			if ($this->validate([
				'cname' => 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
					'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosting.hosting_cname]',
			])) {
				if (strpos('dom.my.id', strtolower($_POST['cname'])) === FALSE) {
					if (!$data->hosting_cname) {
						(new VirtualMinShell)->removeFromServerDNS(
							$data->hosting_username
						);
					}
					(new VirtualMinShell())->cnameHosting(
						$data->hosting_cname ?: $data->default_domain,
						$data->slave_alias,
						strtolower($_POST['cname'])
					);

					$this->db->table('hosting')->update([
						'hosting_cname' => strtolower($_POST['cname'])
					], ['hosting_id' => $data->hosting_id]);

					return view('user/hosting/output', [
						'output' => VirtualMinShell::$output,
						'link' => '/user/hosting/invoices/' . $data->hosting_id,
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
		$history = $this->db->table('purchase')->orderBy('purchase_invoiced', 'DESC')
			->getWhere(['purchase_hosting' => $data->hosting_id])->getResult();
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
						'hosting_id' => $data->hosting_id
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
						'purchase_hosting' => $data->hosting_id,
					]);
					return $this->response->redirect('/user/hosting/invoices/' . $data->hosting_id);
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
				return $this->response->redirect('/user/hosting/invoices/' . $data->hosting_id);
			}
		}
		return view('user/hosting/invoices', [
			'data' => $data,
			'purchases' => $history,
		]);
	}
	protected function deleteHosting($data)
	{
		if ($this->request->getMethod() === 'post' && $data->plan_alias === 'Free' && ($_POST['wordpass'] ?? '') === $data->hosting_username) {
			// Handle domain
			if ($data->domain_scheme == 1) {
				// Remove domain
				(new VirtualMinShell)->removeFromServerDNS(
					$data->domain_name
				);
				$this->db->table('domain')->delete([
					'domain_id' => $data->domain_id,
				]);
			} else if ($data->domain_scheme) {
				// Set NULL
				$this->db->table('hosting')->update([
					'hosting_domain' => null,
				], [
					'hosting_id' => $data->hosting_id,
				]);
			} else {
				// Self registering, just delete
				$this->db->table('domain')->delete([
					'domain_id' => $data->domain_id,
				]);
			}
			(new VirtualMinShell())->deleteHosting($data->domain_name, $data->slave_alias);
			$this->db->table('hosting')->delete([
				'hosting_id' => $data->hosting_id,
			]);
			log_message('notice', VirtualMinShell::$output);
			return $this->response->redirect('/user/hosting/');
		}
		return view('user/hosting/delete', [
			'data' => $data,
		]);
	}
	public function hosting($page = 'list', $id = 0)
	{
		if (!$this->session->email_verified) {
			return $this->verify_email();
		}
		if ($page === 'list') {
			return $this->listHosting();
		} else if ($page === 'create') {
			return $this->createHosting();
		} else {
			$data = $this->db->table('hosting__display')->where([
				'hosting_id' => $id,
				'hosting_login' => $this->session->login_id
			])->get()->getRow();
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
			$scheme = fetchOne('schemes', ['scheme_id' => $_GET['scheme']]);
			if (strlen($name) > 512 || strpos($name, '.') !== false || !$scheme || $scheme->scheme_price == 0) {
				return $this->response->setJSON(['status' => 'invalid']);
			}
			$domain = $name . $scheme->scheme_alias;
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
				'domain_scheme' => 'required|is_not_unique[schemes.scheme_id]',
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
				$scheme = fetchOne('schemes', ['scheme_id' => $post['domain_scheme']]);
				if ($scheme->scheme_price == 0) return;
				$post['domain_name'] .= $scheme->scheme_alias;
				unset($post['domain_scheme']);
				$post['customer_id'] = $this->liquid->liquid_id;
				// $post['ns'] = 'ns1.dom.my.id,ns2.dom.my.id';
				$post['invoice_option'] = 'only_add';
				log_message('notice', (new LiquidRegistrar())->issuePurchaseDomain($post));
				return $this->syncDomain();
			}
		}

		return view('user/domain/create', [
			'schemes' => $this->db->table('schemes')->get()->getResult(),
			'contacts' => json_decode($this->liquid->liquid_cache_contacts ?: '[]'),
		]);
	}
	protected function listDomain()
	{
		if (strtolower($_POST['action'] ?? '') === 'sync') {
			return $this->syncDomain();
		}
		return view('user/domain/list', [
			'list' => $this->db->table('domain')->where([
				'domain_login' => $this->session->login_id
			])->get()->getResult(),
			'page' => 'domain',
		]);
	}

	protected function loginDomain()
	{
		return view('user/domain/login', [
			'user' => $this->session->email,
			'pass' => $this->liquid->liquid_password,
			'uri' => $this->request->config->liquidCustomer,
		]);
	}
	protected function detailDomain($domain)
	{
		return view('user/domain/detail', [
			'data' => $domain,
			'hosting' => fetchOne('hosting', ['hosting_domain' => $domain->domain_id])
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
						'liquid_id' => $data->customer_id,
						'liquid_password' => $post['password'],
						'liquid_login' => $_SESSION['login_id'],
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
	protected function syncDomain()
	{
		$liquid = new LiquidRegistrar();
		// get ID matches email
		$data = $liquid->getCustomerWithEmail($_SESSION['email']);
		if ($data && count($data) > 0) {
			$liquid_cache_customer = $data[0];
			$liquid_id = $liquid_cache_customer->customer_id;
			$liquid_cache_contacts = $liquid->getListOfContacts($liquid_id);
			$liquid_cache_domains = $liquid->getListOfDomains($liquid_id);
			$liquid_pending_transactions = $liquid->getPendingTransactions($liquid_id);
			$liquid_default_contacts = $liquid->getDefaultContacts($liquid_id);

			$this->db->table('liquid')->update([
				'liquid_id' => $liquid_id,
				'liquid_cache_customer' => json_encode($liquid_cache_customer),
				'liquid_cache_domains' => json_encode($liquid_cache_domains),
				'liquid_cache_contacts' => json_encode($liquid_cache_contacts),
				'liquid_pending_transactions' => json_encode($liquid_pending_transactions),
				'liquid_default_contacts' => json_encode($liquid_default_contacts),
			], [
				'liquid_login' => $this->session->login_id,
			]);
		} else {
			$this->db->table('liquid')->delete([
				'liquid_login' => $this->session->login_id,
			]);
		}
		return $this->response->redirect('/user/domain');
	}

	protected $liquid;

	public function domain($page = 'list', $id = 0)
	{
		if (!$this->session->email_verified) {
			return $this->verify_email();
		}
		$this->liquid = fetchOne('liquid', ['liquid_login' => $this->session->login_id]);
		if ($this->liquid) {
			if ($page == 'list') {
				return $this->listDomain();
			} else if ($page == 'check') {
				return $this->checkDomain();
			} else if ($page == 'login') {
				return $this->loginDomain();
			} else if ($page == 'create') {
				return $this->createDomain();
			} else {
				$domain = fetchOne('domain', [
					'domain_id' => $id,
					'domain_login' => $this->session->login_id
				]);
				if ($domain) {
					if ($page == 'detail') {
						return $this->detailDomain($domain);
					} else if ($page == 'invoice') {
						return $this->invoiceDomain($domain);
					} else if ($page == 'delete') {
						return $this->deleteDomain($domain);
					}
				}
			}
			return $this->response->redirect('/user/domain');
		} else {
			return $this->introDomain();
		}
	}
	public function verify_email()
	{
		if ($this->request->getMethod() === 'post' && ($this->request->getPost('action') === 'resend')) {
			$data = fetchOne('login', ['login_id' => $this->session->login_id]);
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
			$em->setSubject('Verifikasi Email | DOM Cloud');
			$em->setMessage(view('static/verify_email', [
				'name' => $data->name,
				'link' => base_url('verify?code=' . urlencode(base64_encode($data->email . ':' . $data->otp)))
			]));
			if (!$em->send()) {
				log_message('critical', $em->printDebugger());
				throw new ErrorException("Unable to send message");
			}
			$this->session->destroy();
			return $this->response->redirect('/id/login?msg=emailsent');
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
				'email' => $this->session->email_verified ? 'permit_empty' : 'required|valid_email',
				'lang' => 'required|in_list[id,en]',
			])) {
				$data = array_intersect_key(
					$this->request->getPost(),
					array_flip(
						['name', 'email', 'phone', 'lang']
					)
				);
				if ($this->session->email_verified) {
					unset($data['email']);
				}
				$this->db->table('login')->update($data, [
					'login_id' => $this->session->login_id
				]);
				foreach ($data as $key => $value) {
					$this->session->set($key, $value);
				}
				$this->request->setLocale($this->session->lang);
			}
		}
		return view('user/profile', [
			'data' => fetchOne('login', ['login_id' => $this->session->login_id]),
			'email_verified' => $this->session->email_verified,
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
					'login_id' => $this->session->login_id
				]);
			}
		}
		return $this->response->redirect('/user/profile');
	}

	public function delete()
	{
		$ok = $this->db->table('hosting')->where(['hosting_login' => $this->session->login_id])->countAll() === 0;
		$ok = $ok && $this->db->table('domain')->where(['domain_login' => $this->session->login_id])->countAll() === 0;
		if ($ok && $this->request->getMethod() === 'post' && strpos($this->request->getPost('wordpass'), 'Y') !== FALSE) {
			$liquid = fetchOne('liquid', [
				'liquid_login' =>  $this->session->login_id
			]);
			if ($liquid) {
				(new LiquidRegistrar())->deleteCustomer($liquid->liquid_id);
				$this->db->table('liquid')->delete([
					'liquid_login' => $this->session->login_id,
				]);
			}
			$this->db->table('login')->delete([
				'login_id' => $this->session->login_id,
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
