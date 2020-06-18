<?php

namespace App\Controllers;

use App\Models\Recaptha;
use App\Models\VirtualMinShell;
use CodeIgniter\Exceptions\PageNotFoundException;

class Home extends BaseController
{
	public function index()
	{
		return $this->response->redirect(href('login'));
	}

	public function notify()
	{
		if (isset($_GET['id'], $_GET['challenge'])) {
			$data = $this->db->table('hosting__display')->where([
				'purchase_id' => $_GET['id'],
				'purchase_challenge' => $_GET['challenge']
			])->get()->getRow();
			if ($data) {
				if ($this->db->table('purchase')->update([
					'purchase_status' => 'active',
					'purchase_challenge' => null,
					'purchase_password' => null,
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
							$data->hosting_cname ?: $data->default_domain,
							$data->slave_alias
						);
						(new VirtualMinShell())->upgradeHosting(
							$data->hosting_cname ?: $data->default_domain,
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
							$data->purchase_password,
							$this->db->table('login')->getWhere([
								'login_id' => $data->hosting_login
							])->getRow()->email,
							$data->hosting_cname ?: $data->default_domain,
							$data->slave_alias,
							$data->plan_alias,
							$data->plan_features,
							$data->purchase_template
						);
					}

					return $this->response->redirect('user/hosting/invoices/' . $data->hosting_id);
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
