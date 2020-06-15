<?php

namespace App\Controllers;

use App\Models\BannedNames;
use App\Models\PaymentGate;
use App\Models\VirtualMinShell;
use Config\Services;

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
				'template' => 'permit_empty|in_list[wordpress,phpbb,opencart]',
			])) {
				$data = array_intersect_key(
					$this->request->getPost(),
					array_flip(['plan', 'username', 'slave'])
				);
				if (!$plan = $this->db->table('plans')->getWhere(['plan_id' => $data['plan']])->getRow()) return;
				if (!$slave = $this->db->table('slaves')->getWhere(['slave_id' => $data['slave']])->getRow()) return;
				if (array_search(strtolower($data['username']), (new BannedNames())->names) !== FALSE) return;
				$hosting = [
					'hosting_login' => $this->session->login_id,
					'hosting_username' => strtolower($data['username']),
					'hosting_slave' => $data['slave'],
				];
				$payment = [
					'purchase_active' => 1,
					'purchase_plan' => $data['plan'],
					'purchase_invoiced' => date('Y-m-d H:i:s', \time()),
					'purchase_template' => $data['template'] ?? '',
				];
				if ($plan->plan_alias !== 'Free') {
					if ($this->validate([
						'cname' => 'required|regex_match[/^[a-zA-Z0-9][a-zA-Z0-9_.-]' .
							'{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/]|is_unique[hosting.hosting_cname]',
						'years' => 'required|greater_than[0]|less_than[6]',
					])) {
						$data = array_intersect_key(
							$this->request->getPost(),
							array_flip(['cname', 'years'])
						);
						$hosting['hosting_cname'] = strtolower($data['cname']);
						$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+$data[years] years", \time()));
						$payment['purchase_status'] = 'pending';
						$payment['purchase_years'] = $data['years'];
						$payment['purchase_challenge'] = random_int(111111, 999999);
						$payment['purchase_price'] = $plan->plan_price * 10000 * $data['years'] + 5000;
					} else {
						return view('user/hosting/create', [
							'plans' => $this->db->table('plans')->select([
								'plan_id', 'plan_alias', 'plan_price'
							])->get()->getResult(),
							'slaves' => $this->db->table('slaves__usage')->get()->getResult(),
							'validation' => $this->validator,
						]);
					}
				} else {
					$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+3 months", \time()));
					$payment['purchase_status'] = 'active';
					(new VirtualMinShell())->createHosting(
						$hosting['hosting_username'],
						$hosting['hosting_username'],
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
				}
				if ($this->db->table('hosting')->insert($hosting)) {
					$payment['purchase_hosting'] = $this->db->insertID();
					$this->db->table('purchase')->insert($payment);
					if ($plan->plan_alias !== 'Free') {
						$id = $this->db->insertID();
						$pay = (new PaymentGate())->createPayment(
							$id,
							$payment['purchase_price'],
							"Hosting $plan->plan_alias $data[years] Tahun",
							$payment['purchase_challenge']
						);
						if ($pay && isset($pay->sessionID)) {
							$this->db->table('purchase')->update([
								'purchase_session' => $pay->sessionID ?? ''
							], ['purchase_id' => $id]);
						}
					}
					return $this->response->redirect('/user/hosting/invoices/'.$payment['purchase_hosting']);
				}
			}
		}
		return view('user/hosting/create', [
			'plans' => $this->db->table('plans')->select([
				'plan_id', 'plan_alias', 'plan_price'
			])->get()->getResult(),
			'slaves' => $this->db->table('slaves__usage')->get()->getResult(),
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
						$payment['purchase_challenge'] = random_int(111111, 999999);
					} else {
						return view('user/hosting/create', [
							'plans' => $this->db->table('plans')->select([
								'plan_id', 'plan_alias', 'plan_price'
							])->get()->getResult(),
							'slaves' => $this->db->table('slaves__usage')->get()->getResult(),
							'validation' => $this->validator,
						]);
					}
				} else {
					$payment['purchase_expired'] = date('Y-m-d H:i:s', strtotime("+3 months", \time()));
					$payment['purchase_status'] = 'active';
					(new VirtualMinShell())->upgradeHosting(
						$data->hosting_cname ?: $data->default_domain,
						$data->slave_alias,
						$data->plan_features,
						$plan->plan_alias,
						$plan->plan_features
					);
					if ($data->plan_alias !== 'Free' && $data->hosting_cname) {
						// Downgrade to free
						(new VirtualMinShell)->cnameHosting(
							$data->hosting_cname,
							$data->slave_alias,
							$data->default_domain
						);
						(new VirtualMinShell)->addToServerDNS(
							$data->hosting_username,
							$data->slave_ip
						);
						$this->db->table('hosting')->update([
							'hosting_cname' => null
						], ['hosting_id' => $data->hosting_id]);
					}
					$this->db->table('purchase')->update([
						'purchase_status' => 'expired',
						'purchase_active' => 0,
					], ['purchase_id' => $data->purchase_id]);
				}
				if ($this->db->table('purchase')->insert($payment)) {
					if ($plan->plan_alias !== 'Free') {
						$id = $this->db->insertID();
						$pay = (new PaymentGate())->createPayment(
							$id,
							$payment['purchase_price'],
							"Hosting $plan->plan_alias $payment[purchase_years] Tahun",
							$payment['purchase_challenge']
						);
						if ($pay && isset($pay->sessionID)) {
							$this->db->table('purchase')->update([
								'purchase_session' => $pay->sessionID ?? ''
							], ['purchase_id' => $id]);
						}
						$this->db->table('purchase')->update([
							'purchase_active' => 2,
						], ['purchase_id' => $data->purchase_id]);
					}
					return $this->response->redirect('/user/hosting/invoices/'.$payment['purchase_hosting']);
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
					return $this->response->redirect('user/hosting/detail/'.$data->hosting_id);
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
					return $this->response->redirect('user/hosting/invoices/' . $data->hosting_id);
				}
			} else if ($action === 'renew') {
				$pay = (new PaymentGate())->createPayment(
					$data->purchase_id,
					$data->purchase_price,
					"Hosting $data->plan_alias $data->purchase_years Tahun",
					$data->purchase_challenge
				);
				if ($pay && isset($pay->sessionID)) {
					$this->db->table('purchase')->update([
						'purchase_session' => $pay->sessionID ?? ''
					], ['purchase_id' => $data->purchase_id]);
				}
				return $this->response->redirect('user/hosting/invoices/' . $data->hosting_id);

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
			(new VirtualMinShell())->deleteHosting($data->hosting_cname ?: $data->default_domain, $data->slave_alias);
			$this->db->table('hosting')->delete([
				'hosting_id' => $data->hosting_id,
			]);
			return $this->response->redirect('/user/hosting');
		}
		return view('user/hosting/delete', [
			'data' => $data,
		]);
	}
	public function hosting($page = 'list', $id = 0)
	{
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
				} else if ($page === 'rename') {
					return $this->renameHosting($data);
				} else if ($page === 'cname') {
					return $this->cnameHosting($data);
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
	}

	//--------------------------------------------------------------------

}
