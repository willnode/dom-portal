<?php

namespace App\Libraries;

use App\Entities\Domain;
use App\Entities\Login;
use App\Entities\Scheme;
use App\Entities\Server;
use Config\Services;
use SimpleXMLElement;


class DigitalRegistra
{
	protected function callApi($route, $post = [])
	{
		$url = Services::request()->config->srsxURL;
		$user = Services::request()->config->srsxUsername;
		$pass = Services::request()->config->srsxPassword;

		if (ENVIRONMENT === 'production') {
			// @codeCoverageIgnoreStart
			// execute!
			$ch = curl_init($url . $route);
			$post['username'] = $user;
			$post['password'] = hash('sha256', $pass);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			$response = curl_exec($ch);
			curl_close($ch);
			if ($response) {
				$response = new SimpleXMLElement($response);
				log_message('notice', 'DOMAIN: ' . $route . "\n" . $response->asXML());
				$d = $response->children('resultData');
				return $d->count() === 0 ? (string)$response->result->resultMsg : $this->xml2array($d);
			} else {
				return '';
			}
			// @codeCoverageIgnoreEnd
		} else {
			$post['url'] = $route;
			return $post;
		}
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function domainCheck($domain)
	{
		return $this->callApi('/domain/check', ['domain' => $domain]);
	}

	public function domainRegister($data)
	{
		//  required: [domain, api_id, periode, ns1, ns2, fname, address1, city, state, country
		//             postcode, phonenumber, email, user_username, user_fname,
		//             user_company, user_address, user_city, user_province, user_country, user_postal_code ]
		$data = array_intersect_key($data, array_flip(
			[
				'domain', 'api_id', 'periode', 'ns1', 'ns2', 'ns3', 'ns4',
				'fname', 'lname', 'company', 'address1', 'address2', 'city', 'state',
				'country', 'postcode', 'phonenumber', 'email', 'user_username', 'user_fname',
				'user_lname', 'user_company', 'user_address', 'user_address2', 'user_city',
				'user_province', 'user_country', 'user_postal_code', 'autoactive', 'category'
			]
		));
		return $this->callApi('/domain/register', $data);
	}

	/**
	 * @param array $bio
	 * @param array $user
	 * @param int $years
	 * @param Domain $domain
	 * @param Scheme $scheme
	 * @param Server $server
	 * @param Login $login
	 */
	public function normalizeDomainInput($bio, $user, $years, $domain, $scheme, $server, $login)
	{
		is_array($user) || ($user = []);
		return array_filter([
			'domain' => $domain->name,
			'api_id' => $domain->id,
			'periode' => $years,
			'ns1' => $server ? 'nsp' . $server->domain : 'ns1.mysrsx.com',
			'ns2' => $server ? 'nss' . $server->domain : 'ns2.mysrsx.net',
			'fname' => $bio['fname'],
			'lname' => $bio['lname'] ?? '',
			'company' => $bio['company'],
			'address1' => $bio['address1'],
			'address2' => $bio['address2'] ?? '',
			'city' => $bio['city'],
			'state' => $bio['state'],
			'country' => $bio['country'],
			'postcode' => $bio['postal'],
			'phonenumber' => trim(str_replace([' ', '-'],'', $bio['tel']), ' +'),
			'email' => $bio['email'],
			'user_username' => $login->email,
			'user_fname' => $user['fname'] ?? $bio['fname'],
			'user_lname' => $user['lname'] ?? $bio['lname'] ?? '',
			'user_company' => $user['company'] ?? $bio['company'],
			'user_address' => $user['address1'] ?? $bio['address1'],
			'user_address2' => $user['address2'] ?? $bio['address2'] ?? '',
			'user_city' =>  $user['city'] ?? $bio['city'],
			'user_province' => $user['state'] ?? $bio['state'],
			'user_country' => $user['country'] ?? $bio['country'],
			'user_postal_code' => $user['postal'] ?? $bio['postal'],
			'autoactive' => 'on',
			'category' => $scheme->category,
		], function ($x) {
			return $x;
		});
	}
	/**
	 * @codeCoverageIgnore
	 */
	public function domainInfo($domain, $api_id)
	{
		return $this->callApi('/domain/info', ['domain' => $domain, 'api_id' => $api_id]);
	}
	/**
	 * @codeCoverageIgnore
	 */
	public function domainRenew($domain, $api_id, $periode)
	{
		return $this->callApi('/domain/renew', ['domain' => $domain, 'api_id' => $api_id, 'periode' => $periode]);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function dnsInfo($domain)
	{
		return $this->callApi('/dns/info', ['domain' => $domain]);
	}
	/**
	 * @codeCoverageIgnore
	 */
	protected function xml2array($xmlObject, $out = array())
	{
		foreach ((array) $xmlObject as $index => $node)
			$out[$index] = (is_object($node)) ? $this->xml2array($node) : $node;

		return $out;
	}
}
