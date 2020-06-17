<?php

namespace App\Models;

use Config\Services;

class VirtualMinShell
{
	protected function execute($cmd)
	{
		if (ENVIRONMENT === 'production') {
			exec('echo "' . $cmd . '"|at now');
		} else {
			exec('start echo "' . $cmd . '"');
		}
	}
	protected function wrapWget($params, $slave_dn)
	{
		$username = Services::request()->config->sudoWebminUser;
		$password = Services::request()->config->sudoWebminPass;
		$port = Services::request()->config->sudoWebminPort;
		return "wget -O - --quiet --http-user=$username --http-passwd=$password --no-check-certificate " .
			"'https://$slave_dn.dom.my.id:$port/virtual-server/remote.cgi?$params'";
	}
	protected $featureFlags = [
		"&dir=&webmin=&web=&mysql=&unix=",
		"&ssl=",
		"&mail=",
		"&webalizer=",
		"&dns=",
		"&spam=&postgresql=",
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
			$flags .= "&template=$template";
		}
		$cmd = "program=create-domain&user=$username&pass=$password" .
			"&email=$email&domain=$domain&plan=$plan&limits-from-plan=$flags";
		$this->execute($this->wrapWget($cmd, $slave));
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
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function cnameHosting($domain, $slave, $newdomain)
	{
		$cmd = "program=modify-domain&domain=$domain&newdomain=$newdomain";
		$this->execute($this->wrapWget($cmd, $slave));
	}
	public function addToServerDNS($username, $slave_ip)
	{
		$cmd = "program=modify-dns&domain=dom.my.id&add-record=$username+A+$slave_ip";
		$this->execute($this->wrapWget($cmd, 'panel'));
	}
	public function removeFromServerDNS($username)
	{
		$cmd = "program=modify-dns&domain=dom.my.id&remove-record=$username+A";
		$this->execute($this->wrapWget($cmd, 'panel'));
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
		$this->execute($this->wrapWget($cmd, $slave));
	}
}
