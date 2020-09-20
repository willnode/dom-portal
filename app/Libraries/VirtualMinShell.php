<?php

namespace App\Libraries;

use Config\Services;

class VirtualMinShell
{
	static $output;

	protected function execute($cmd, $title = '')
	{
		if (/*ENVIRONMENT === 'production'*/ true|| $title === NULL) {
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
	protected function wrapWget($params, $slave_dn)
	{
		$port = Services::request()->config->sudoWebminPort;
		return "https://$slave_dn.domcloud.id:$port/virtual-server/remote.cgi?$params";
	}
	protected $featureFlags = [
		"&dir=&webmin=&web=&mysql=&unix=",
		"&ssl=&dns=",
		"&virtualmin-awstats=",
	];
	public function createHosting($username, $password, $email, $domain, $slave, $plan, $privilenge, $template)
	{
		$flags = "";
		foreach ($this->featureFlags as $level => $flag) {
			if ($privilenge >= $level) {
				$flags .= $flag;
			}
			$epassword = urlencode($password);
			$cmd = "program=create-domain&user=$username&pass=$epassword" .
			"&email=$email&domain=$domain&plan=$plan&limits-from-plan=$flags";
			$this->execute($this->wrapWget($cmd, $slave), " Create Hosting for $domain ");
		}
		if ($template) {
			(new TemplateDeployer())->deploy("sv01.dom.my.id", $domain, $username, $password, $template);
		}
	}
	public function upgradeHosting($domain, $slave, $oldprivilenge, $newplan, $newprivilenge)
	{
		$cmd = "program=modify-domain&domain=$domain&apply-plan=$newplan";
		$this->execute($this->wrapWget($cmd, $slave));
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
			$this->execute($this->wrapWget($cmd, $slave));
		}
	}
	public function renameHosting($domain, $slave, $newusername)
	{
		$cmd = "program=modify-domain&domain=$domain&user=$newusername";
		$this->execute($this->wrapWget($cmd, $slave), " Rename hosting $domain ");
	}
	public function cnameHosting($domain, $slave, $newdomain)
	{
		$cmd = "program=modify-domain&domain=$domain&newdomain=$newdomain";
		$this->execute($this->wrapWget($cmd, $slave), " Change domain for $domain ");
	}
	public function addToServerDNS($username, $slave_ip)
	{
		$cmd = "program=modify-dns&domain=dom.my.id&add-record=$username+A+$slave_ip&add-record=www.$username+A+$slave_ip";
		$this->execute($this->wrapWget($cmd, 'portal'), " Adding DNS record for $username to central DOM ");
	}
	public function removeFromServerDNS($username)
	{
		$cmd = "program=modify-dns&domain=dom.my.id&remove-record=$username+A&remove-record=www.$username+A";
		$this->execute($this->wrapWget($cmd, 'portal'), " Removing DNS record for $username to central DOM ");
	}
	public function resetHosting($domain, $slave, $newpw)
	{
		$cmd = "program=modify-domain&domain=$domain&pass=$newpw";
		$this->execute($this->wrapWget($cmd, $slave), "Reset Host Password");
	}
	public function enableHosting($domain, $slave)
	{
		$cmd = "program=enable-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function disableHosting($domain, $slave, $why)
	{
		$cmd = "program=disable-domain&domain=$domain&why=".urlencode($why);
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function deleteHosting($domain, $slave)
	{
		$cmd = "program=delete-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $slave), " Delete Hosting for $domain ");
	}
	public function adjustBandwidthHosting($bw_mb, $domain, $slave)
	{
		$bw_bytes = floor($bw_mb) * 1024 * 1024;
		$cmd = "program=modify-domain&domain=$domain&bw=$bw_bytes";
		$this->execute($this->wrapWget($cmd, $slave), " Adjust Bandwidth $domain to $bw_bytes bytes ");
	}
	public function listDomainsInfo($slave)
	{
		$cmd = "program=list-domains&multiline=";
		$data = $this->execute($this->wrapWget($cmd, $slave), NULL);

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
	public function listBandwidthInfo($slave)
	{
		$cmd = "program=list-bandwidth&all-domains=";
		$data = $this->execute($this->wrapWget($cmd, $slave), NULL);

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
	public function listSystemInfo($slave)
	{
		$cmd = "program=info";
		$data = $this->execute($this->wrapWget($cmd, $slave), NULL);
		return $data;
	}
}
