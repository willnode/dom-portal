<?php

namespace App\Controllers;

use App\Entities\Plan;
use App\Entities\Purchase;
use App\Libraries\DigitalRegistra;
use App\Libraries\Recaptha;
use App\Libraries\SendGridEmail;
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

class Home extends BaseController
{
	public function index()
	{
		return $this->response->redirect(href('login')); // @codeCoverageIgnore
	}

	public function verify()
	{
		if ($code = $this->request->getGet('code')) {
			if (count($code = explode(':', base64_decode($code, true), 2)) === 2) {
				if (($row = (new LoginModel())->atEmail($code[0])) && $row->otp === $code[1]) {
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
		throw new PageNotFoundException(); // @codeCoverageIgnore
	}

	public function login()
	{
		if ($this->session->has('login')) {
			return $this->response->redirect('/user'); // @codeCoverageIgnore
		}
		if ($r = $this->request->getGet('r')) {
			return $this->response->setCookie('r', $r, 0)->redirect(href('login'));
		}
		if ($this->request->getMethod() === 'post') {
			$post = $this->request->getPost();
			if (isset($post['email'], $post['password'])) {
				$login = (new LoginModel())->atEmail($post['email']);
				if ($login && password_verify(
					$post['password'],
					$login->password
				)) {
					(new LoginModel())->login($login);
					if ($r = $this->request->getCookie('r')) {
						$this->response->deleteCookie('r');
					}
					return $this->response->redirect(base_url($r ?: 'user'));
				}
			}
			$m = lang('Interface.wrongLogin'); // @codeCoverageIgnore
		}
		return view('static/login', [
			'message' => $m ?? (($_GET['msg'] ?? '') === 'emailsent' ? lang('Interface.emailSent') : null)
		]);
	}

	public function import()
	{
		$post = array_intersect_key($this->request->getGet(), array_flip([
			'from', 'code', 'lang'
		]));
		return $this->response->redirect('/user/host/create?' . http_build_query($post));
	}

	/**
	 * @codeCoverageIgnore
	 */
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
				'g-recaptcha-response' => ENVIRONMENT === 'production' ? 'required' : 'permit_empty',
			])) {
				if (ENVIRONMENT !== 'production' || (new Recaptha())->verify($_POST['g-recaptcha-response'])) {
					$id = (new LoginModel())->register($this->request->getPost());
					(new LoginModel())->find($id)->sendVerifyEmail();
					if ($r = $this->request->getCookie('r')) {
						$this->response->deleteCookie('r');
					}
					return $this->response->redirect(base_url($r ?: 'user'));
				}
			}
			return redirect()->back()->withInput()->with('errors', $this->validator->listErrors()); // @codeCoverageIgnore
		}
	}

	public function forgot()
	{
		if ($this->request->getMethod() === 'post' && $this->validate([
			'email' => 'required|valid_email',
			'g-recaptcha-response' => ENVIRONMENT === 'production' ? 'required' : 'permit_empty',
		])) {
			if (ENVIRONMENT !== 'production' || (new Recaptha())->verify($_POST['g-recaptcha-response'])) {
				if ($login = (new LoginModel())->atEmail($this->request->getPost('email'))) {

					if (!$login->otp) {
						$login->otp = random_int(111111111, 999999999);
						(new LoginModel())->save($login);
					}

					(new SendGridEmail())->send('recover_email', 'billing', [[
						'to' => [[
							'email' => $login->email,
							'name' => $login->name,
						]],
						'dynamic_template_data' => [
							'name' => $login->name,
							'reset_url' => base_url('reset?code=' . urlencode(base64_encode($login->email . ':' . $login->otp))),
						]
					]]);

					return $this->response->redirect(href('login?msg=emailsent'));
				}
			}
		}
		return view('static/forgot', [
			'recapthaSite' => (new Recaptha())->recapthaSite,
			'message' => $m ?? null,
		]);
	}

	public function reset()
	{
		if ($code = $this->request->getGet('code')) {
			if (count($code = explode(':', base64_decode($code, true))) == 2) {
				if (($login = (new LoginModel())->atEmail($code[0])) && $login->otp === $code[1]) {
					if ($this->request->getMethod() === 'post' && $this->validate([
						'password' => 'required|min_length[8]',
						'passconf' => 'required|matches[password]',
					])) {
						$login->password = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
						$login->email_verified_at = date('Y-m-d H:i:s');
						$login->otp = null;
						(new LoginModel())->save($login);
						return $this->response->redirect(href('login'));
					}
					return view('static/forgot_reset');
				}
			}
		}
		throw new PageNotFoundException(); // @codeCoverageIgnore
	}

	//--------------------------------------------------------------------

}
