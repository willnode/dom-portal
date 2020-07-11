<?php

namespace App\Models;

use Config\Services;

class VirtualMinShell
{
	static $output;

	protected function execute($cmd, $title = '')
	{
		if (ENVIRONMENT === 'production') {
			set_time_limit(300);
			$username = Services::request()->config->sudoWebminUser;
			$password = Services::request()->config->sudoWebminPass;
			VirtualMinShell::$output .= 'HOSTING: '.$title."\n";
			$ch = curl_init($cmd);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$response = curl_exec($ch);
			VirtualMinShell::$output .= $response."\n";
			curl_close($ch);
			return $response;
		} else {
			VirtualMinShell::$output .= 'HOSTING: '.$title."\n";
			VirtualMinShell::$output .= $cmd."\n";
		}
	}
	protected function wrapWget($params, $slave_dn)
	{
		$port = Services::request()->config->sudoWebminPort;
		return "https://$slave_dn.dom.my.id:$port/virtual-server/remote.cgi?$params";
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
		}
		if ($template) {
			$template = ucfirst($template) . '+Template';
			$flags .= "&template=$template";
		}
		$password = urlencode($password);
		$cmd = "program=create-domain&user=$username&pass=$password" .
			"&email=$email&domain=$domain&plan=$plan&limits-from-plan=$flags";
		$this->execute($this->wrapWget($cmd, $slave), " Create Hosting for $domain ");
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
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function enableHosting($domain, $slave)
	{
		$cmd = "program=enable-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function disableHosting($domain, $slave)
	{
		$cmd = "program=disable-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function deleteHosting($domain, $slave)
	{
		$cmd = "program=delete-domain&domain=$domain";
		$this->execute($this->wrapWget($cmd, $slave), " Delete Hosting for $domain ");
	}
}
