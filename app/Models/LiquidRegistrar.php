<?php

namespace App\Models;

use Config\Services;

class LiquidRegistrar
{
	protected function callApi($route, $post = null)
	{
		$url = Services::request()->config->liquidURL;
		$id = Services::request()->config->liquidID;
		$key = Services::request()->config->liquidKey;
		$ch = curl_init($url . $route);
		//echo $url . $route; exit;

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $id . ":" . $key);
		if ($post) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		// execute!
		$response = curl_exec($ch);
		curl_close($ch);
		if ($response) {
			$json = json_decode($response);
			$type = ($json->type ?? '');
			if ($type == 'unauthorized' || $type == 'invalid_request') {
				echo view('user/hosting/output', [
					'output' => $type == 'unauthorized' ?
						'The registrar service was busy. Try again later in 15 minutes.' :
						$json->message ?? json_encode($json),
					'link' => '/user/domain/',
				]);
				exit;
			}
			return $json;
		} else {
			return null;
		}
	}

	public function createCustomer($data)
	{
		return $this->callApi('/customers', $data);
	}

	public function isDomainAvailable($domain)
	{
		error_log(urlencode($domain));
		return $this->callApi('/domains/availability?domain=' . urlencode($domain));
	}

	public function getCustomerWithEmail($email)
	{
		return $this->callApi('/customers?email=' . urlencode($email));
	}

	public function getListOfContacts($customer_id)
	{
		return $this->callApi("/customers/$customer_id/contacts?limit=100&page_no=1&status=Active");
	}

	public function getListOfDomains($customer_id)
	{
		return $this->callApi("/domains?customer_id=$customer_id&limit=100&page_no=1");
	}

	public function getDefaultContacts($customer_id)
	{
		return $this->callApi("/customers/$customer_id/contacts/default");
	}

	public function getPendingTransactions($customer_id)
	{
		return $this->callApi("/customers/$customer_id/transactions?limit=100&page_no=1&only_pending=true");
	}

	public function issuePurchaseDomain($query)
	{
		return $this->callApi("/domains", $query);
	}

	public function confirmPurchaseDomain($query)
	{
		# code...
	}

	public function deleteDomain($id)
	{
		# code...
	}

	public function deleteCustomer($id)
	{
		# code...
	}
}
