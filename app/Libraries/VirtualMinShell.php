<?php

namespace App\Libraries;

use Config\Services;

class VirtualMinShell
{
	static $output;

	protected function execute($cmd, $title = '')
	{
		if (/*ENVIRONMENT === 'production'*/true || $title === NULL) {
			set_time_limit(300);
			$username = Services::request()->config->sudoWebminUser;
			$password = Services::request()->config->sudoWebminPass;
			if ($title !== NULL)
				VirtualMinShell::$output .= 'HOSTING: ' . $title . ' (' . $cmd . ')' . "\n";
			$ch = curl_init($cmd);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$response = curl_exec($ch);
			if ($title !== NULL)
				VirtualMinShell::$output .= $response . "\n";
			curl_close($ch);
			return $response;
		} else {
			VirtualMinShell::$output .= 'HOSTING: ' . $title . "\n";
			VirtualMinShell::$output .= $cmd . "\n";
		}
	}
	protected function wrapWget($params, $server)
	{
		$port = Services::request()->config->sudoWebminPort;
		return "https://$server.domcloud.id:$port/virtual-server/remote.cgi?$params";
	}
	protected $featureFlags = [
		"&dir=&webmin=&web=&unix=",
		"&dns=",
		"&virtualmin-awstats=",
	];
	public function createHosting($username, $password, $email, $domain, $server, $plan, $privilenge)
	{
		$flags = "";
		foreach ($this->featureFlags as $level => $flag) {
			if ($privilenge >= $level) {
				$flags .= $flag;
			}
			$epassword = urlencode($password);
			$cmd = "program=create-domain&user=$username&pass=$epassword" .
				"&email=$email&domain=$domain&plan=$plan&limits-from-plan=$flags";
			$this->execute($this->wrapWget($cmd, $server), " Create Hosting for $domain ");
		}
	}
	public function upgradeHosting($domain, $server, $oldprivilenge, $newplan, $newprivilenge)
	{
		$cmd = "program=modify-domain&domain=$domain&apply-plan=$newplan";
		$this->execute($this->wrapWget($cmd, $server));
		if ($oldprivilenge !== $newprivilenge) {
			$command = $newprivilenge > $oldprivilenge ? 'enable-feature' : 'disable-feature';
			$from = $newprivilenge > $oldprivilenge ? $oldprivilenge : $newprivilenge;
			$to = $newprivilenge < $oldprivilenge ? $oldprivilenge : $newprivilenge;
			$flags = "";
			foreach ($this->featureFlags as $level => $flag) {
				if ($level > $from && $level <= $to) {
					$flags .= $flag;
				}
			}
			$cmd = "program=$command&domain=$domain$flags";
			$this->execute($this->wrapWget($cmd, $server));
		}
	}
	public function renameHosting($domain, $server, $newusername)
	{
		$cmd = "program=modify-domain&domain=$domain&user=$newusername";
		$this->execute($this->wrapWget($cmd, $server), " Rename hosting $domain ");
	}
	public function cnameHosting($domain, $server, $newdomain)
	{
		$cmd = "program=modify-domain&domain=$domain&newdomain=$newdomain";
		$this->execute($this->wrapWget($cmd, $server), " Change domain for $domain ");
	}
	public function addToServerDNS($username, $slave_ip)
	{
		// $cmd = "program=modify-dns&domain=dom.my.id&add-record=$username+A+$slave_ip&add-record=www.$username+A+$slave_ip";
		// $this->execute($this->wrapWget($cmd, 'portal'), " Adding DNS record for $username to central DOM ");
	}
	public function removeFromServerDNS($username)
	{
		// $cmd = "program=modify-dns&domain=dom.my.id&remove-record=$username+A&remove-record=www.$username+A";
		// $this->execute($this->wrapWget($cmd, 'portal'), " Removing DNS record for $username to central DOM ");
	}
	public function resetHosting($domain, $server, $newpw)
	{
		$cmd = "program=modify-domain&domain=$domain&pass=$newpw";
		$this->execute($this->wrapWget($cmd, $server), "Reset Host Password");
	}
	public function enableHosting($domain, $server)
	{
		$cmd = "program=enable-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $server));
	}
	public function disableHosting($domain, $server, $why)
	{
		$cmd = "program=disable-domain&domain=$domain&why=" . urlencode($why);
		$this->execute($this->wrapWget($cmd, $server));
	}
	public function deleteHosting($domain, $server)
	{
		$cmd = "program=delete-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $server), " Delete Hosting for $domain ");
	}
	public function requestLetsEncrypt($domain, $server)
	{
		$cmd = "program=generate-letsencrypt-cert&domain=$domain&renew=2";
		$this->execute($this->wrapWget($cmd, $server), " Let's Encrypt for $domain ");
	}
	public function enableFeature($domain, $server, $features)
	{
		$cmd = "program=enable-feature&domain=$domain" . implode('', array_map(function ($x) {
			return "&$x=";
		}, $features));
		$this->execute($this->wrapWget($cmd, $server), " Enable Features for $domain ");
	}
	public function adjustBandwidthHosting($bw_mb, $domain, $server)
	{
		$bw_bytes = floor($bw_mb) * 1024 * 1024;
		$cmd = "program=modify-domain&domain=$domain&bw=$bw_bytes";
		$this->execute($this->wrapWget($cmd, $server), " Adjust Bandwidth $domain to $bw_bytes bytes ");
	}
	public function createDatabase($name, $type, $domain, $server)
	{
		$name = urlencode($name);
		$cmd = "program=create-database&domain=$domain&name=$name&type=$type";
		$this->execute($this->wrapWget($cmd, $server), " Create database $domain named $name ");
	}
	public function modifyWebHome($home, $domain, $server)
	{
		$home = urlencode($home);
		$cmd = "program=modify-web&domain=$domain&document-dir=$home";
		$this->execute($this->wrapWget($cmd, $server), " Set home $domain named $home ");
	}
	public function listDomainsInfo($server)
	{
		$cmd = "program=list-domains&multiline=&toplevel=";
		$data = $this->execute($this->wrapWget($cmd, $server), NULL);

		$data = explode("\n", $data);
		$result = [];
		$neskey = null;
		$nesval = [];
		foreach ($data as $line) {
			$line = rtrim($line);
			if (strlen($line) >= 4 && $line[0] === ' ') {
				$line = explode(':', ltrim($line), 2);
				$nesval[$line[0]] = ltrim($line[1]);
			} else if (strlen($line) >= 0) {
				if ($neskey) {
					$result[$neskey] = $nesval;
					$nesval = [];
				}
				$neskey = $line;
			} else {
				$result[$neskey] = $nesval;
				break;
			}
		}
		return $result;
	}
	public function listBandwidthInfo($server)
	{
		$cmd = "program=list-bandwidth&all-domains=";
		$data = $this->execute($this->wrapWget($cmd, $server), NULL);

		$data = explode("\n", $data);
		$result = [];
		$neskey = null;
		$nesval = [];
		foreach ($data as $line) {
			$line = rtrim($line);
			if (strlen($line) >= 4 && $line[0] === ' ') {
				$line = explode(':', ltrim($line), 3);
				$nesval[$line[0]] = ltrim($line[2]);
			} else if (strlen($line) >= 0) {
				if ($neskey) {
					$result[$neskey] = $nesval;
					$nesval = [];
				}
				$neskey = rtrim($line, ":\r");
			} else {
				$result[$neskey] = $nesval;
				break;
			}
		}
		return $result;
	}
	public function listSystemInfo($server)
	{
		$cmd = "program=info";
		$data = $this->execute($this->wrapWget($cmd, $server), NULL);
		$data = str_replace('*', '-', $data);
		return $data;
	}
}
