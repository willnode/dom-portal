<?php

namespace App\Libraries;

use Config\Services;

class LiquidRegistrar
{
	protected function callApi($route, $post = null, $method = null)
	{
		$url = Services::request()->config->liquidURL;
		$id = Services::request()->config->liquidID;
		$key = Services::request()->config->liquidKey;
		$ch = curl_init($url . $route);
		// echo $url . $route; exit;

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $id . ":" . $key);
		if ($post) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if ($method) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
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
				log_message('error', 'DOMAIN: '.$route.'\n'. $response);
				echo view('user/host/output', [
					'output' => $type == 'unauthorized' ?
						'The registrar service was busy. Try again later in 15 minutes.' :
						json_encode($json),
					'link' => '/user/domain/',
				]);
				exit;
			} else {
				log_message('notice', 'DOMAIN: '.$route.'\n'. $response);
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

	public function cancelPurchaseDomain($customer_id, $query)
	{
		return $this->callApi("/customers/$customer_id/transactions/cancel", $query);
	}

	public function confirmFundDomain($customer_id, $query)
	{
		return $this->callApi("/customers/$customer_id/transactions/fund", $query);
	}

	public function confirmPurchaseDomain($customer_id, $query)
	{
		return $this->callApi("/customers/$customer_id/transactions/pay_add_only", $query);
	}

	public function deleteDomain($domain, $customer_id)
	{
		return $this->callApi("/domains/$domain?customer_id=$customer_id", null, "DELETE");
	}

	public function deleteCustomer($customer_id)
	{
		return $this->callApi("/customers/$customer_id", null, "DELETE");
	}
}
