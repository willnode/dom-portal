<?php

namespace App\Controllers;

use App\Models\LiquidRegistrar;
use App\Models\Recaptha;
use App\Models\VirtualMinShell;
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
			(ENVIRONMENT === 'development' || (
				$_GET['secret'] == $this->request->config->paymentSecret &&
				isset($_POST['trx_id'], $_POST['sid'], $_POST['status'], $_POST['via']) &&
				$_POST['status'] == 'berhasil' // iPaymu notification
			))
		) {
			$data = $this->db->table('hosting__display')->where([
				'purchase_id' => $_GET['id'],
				'purchase_challenge' => $_GET['challenge']
			])->get()->getRow();
			if ($data) {
				// At this point we process the purchase
				// In case anything fails, at least we have record it.

				$login = fetchOne('login', ['login_id' => $data->hosting_login]);

				$receipt = [
					'name' => $login->name,
					'id_payment' => $_POST['trx_id'] ?? '-',
					'id_purchase' => $data->purchase_id,
					'name_purchase' => "Hosting $data->plan_alias $data->purchase_years Tahun" . ($data->purchase_liquid ? " dengan domain $data->domain_name" : ""),
					'amount_purchase' => 'Rp '.number_format($data->purchase_price, 0, ',', '.'),
					'time_purchase' => date('Y-m-d H:i:s'),
					'via_purchase' => $_POST['via'] ?? '-',
				];

				log_message('notice', 'PURCHASE: '.json_encode($receipt));

				if ($data->purchase_liquid) {
					$r = explode('|', $data->purchase_liquid);
					$liquid = (new LiquidRegistrar());
					$liquid->confirmPurchaseDomain($r[0], [
						'transaction_id' => $r[1],
						'cancel_invoice' => '0',
					]);
					$this->db->table('liquid')->update([
						'liquid_cache_domains' => json_encode($liquid->getListOfDomains($r[0])),
						'liquid_pending_transactions' => json_encode($liquid->getPendingTransactions($r[0])),
					], [
						'liquid_id' => $r[0],
					]);
				}
				if ($this->db->table('purchase')->update([
					'purchase_status' => 'active',
					'purchase_invoiced' => date('Y-m-d H:i:s'),
					'purchase_challenge' => null,
					'purchase_template' => null,
				], ['purchase_id' => $data->purchase_id])) {
					$old =  $this->db->table('purchase')->getWhere([
						'purchase_active' => 2,
						'purchase_hosting' => $data->hosting_id,
					])->getRow();
					if ($old) {
						$this->db->table('purchase')->update([
							'purchase_active' => 0,
							'purchase_status' => 'expired',
						], [
							'purchase_id' => $old->purchase_id,
						]);
						(new VirtualMinShell())->enableHosting(
							$data->domain_name,
							$data->slave_alias
						);
						(new VirtualMinShell())->upgradeHosting(
							$data->domain_name,
							$data->slave_alias,
							$this->db->table('plans')->getWhere([
								'plan_id' => $old->purchase_plan
							])->getRow()->plan_features,
							$data->plan_alias,
							$data->plan_features
						);
					} else {
						(new VirtualMinShell())->createHosting(
							$data->hosting_username,
							$data->hosting_password,
							$login->email,
							$data->domain_name,
							$data->slave_alias,
							$data->plan_alias,
							$data->plan_features,
							$data->purchase_template
						);
					}
					log_message('notice', VirtualMinShell::$output);
					$em = \Config\Services::email();
					$em->setTo($login->email);
					$em->setSubject('Pembayaran | DOM Cloud');
					$em->setMessage(view('static/receipt_email', $receipt));
					if (!$em->send()) {
						log_message('critical', $em->printDebugger());
					}
					return 'OK';
				}
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
						'email_verified' => date('Y-m-d H:i:s'),
						'otp' => null,
					], $row);
					$this->session->destroy();
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
		if ($this->session->has('login_id')) {
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
					foreach ($login as $key => $value) {
						$this->session->set($key, $value);
					}
					return $this->response->redirect('/user');
				}
			}
		}
		return view('static/login');
	}

	public function logout()
	{
		$this->session->destroy();
		return $this->response->redirect(href('login'));
	}

	public function register()
	{

		if ($this->request->getMethod() === 'get') {
			return view('static/register', [
				'validation' => $this->validator,
				'recapthaSite' => (new Recaptha())->recapthaSite,
			]);
		} else {
			if ($this->validate([
				'name' => 'required|min_length[3]|max_length[255]',
				'phone' => 'required|min_length[8]|max_length[16]',
				'email' => 'required|valid_email',
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
					$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
					$this->db->table('login')->insert($data);
					return $this->login();
				}
			}
			return view('static/register', [
				'validation' => $this->validator
			]);
		}
	}

	//--------------------------------------------------------------------

}
