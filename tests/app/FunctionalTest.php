<?php

namespace App\Database;

use App\Controllers\Api;
use App\Controllers\Home;
use App\Controllers\User;
use App\Entities\Domain;
use App\Entities\Host;
use App\Entities\Login;
use App\Libraries\SendGridEmail;
use App\Libraries\VirtualMinShell;
use App\Models\DomainModel;
use App\Models\HostModel;
use App\Models\LoginModel;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Services;

class FunctionalTest extends CIDatabaseTestCase
{
    protected $namespace  = null;

    public function testRegister()
    {
        // check database migration and prepare controller

        $this->assertTrue($this->db->tableExists('login'));
        $this->assertTrue($this->db->table('login')->countAllResults() === 0);
        ($home = new Home())->initController($req = Services::request(), Services::response(), Services::logger());

        // execute register and check email sent

        $req->setMethod('post');
        $req->setGlobal('post', $login_data = [
            'name' => 'Contoso User',
            'email' => 'contoso@example.com',
            'password' => 'mycontosouser',
        ]);
        $req->setGlobal('request', $login_data);
        $home->register();
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login !== null);
        $this->assertTrue($login->otp !== null);
        $this->assertTrue(SendGridEmail::$sentEmail === 'verify_email');
        $sentEmail = json_decode(SendGridEmail::$sentBody)->personalizations[0]->to[0]->email ?? '';
        $this->assertTrue($sentEmail === $login->email);

        // okay, check if we can verify

        $req->setMethod('get');
        $req->setGlobal('get', $post_data = [
            'code' => base64_encode("$login->email:$login->otp"),
        ]);
        $req->setGlobal('request', $post_data);
        $home->verify();
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login->otp === null);
        $this->assertTrue($login->trustiness === 1);
        $this->assertTrue($login->email_verified_at !== null);
        (Services::session())->remove('login');

        // okay, check if we can reset password

        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'email' => $login->email,
        ]);
        $req->setGlobal('request', $post_data);
        $home->forgot();
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login->otp !== null);
        $req->setMethod('get');
        $req->setGlobal('get', $post_data = [
            'code' => base64_encode("$login->email:$login->otp"),
        ]);
        $this->assertTrue(is_string($home->reset()));
        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'password' => $login_data['password'],
            'passconf' => $login_data['password'],
        ]);
        $req->setGlobal('request', $post_data);
        $home->reset();
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login->otp === null);
        (Services::session())->remove('login');

        // now check login

        $req->setMethod('post');
        $req->setGlobal('post', $login_data);
        $req->setGlobal('request', $login_data);
        $home->login();

        $this->assertTrue(Services::session()->login === 1);

        // check can edit profile

        ($user = new User())->initController($req, Services::response(), Services::logger());
        $req->setGlobal('post', $post_data = [
            'phone' => '1555654678',
            'name' => $login_data['name'],
            'email' => $login_data['email'],
            'lang' => 'en',
        ]);
        $req->setGlobal('request', $post_data);
        $user->profile();

        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login->phone === $post_data['phone']);

        // check can change password

        $req->setGlobal('post', $post_data = [
            'passtest' => $login_data['password'],
            'password' => $ppp = strrev($login_data['password']),
            'passconf' => $ppp,
        ]);
        $req->setGlobal('request', $post_data);
        $user->reset();
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue(password_verify($ppp, $login->password));

        // check can delete

        $req->setGlobal('post', $post_data = [
            'wordpass' => 'Y',
        ]);
        $user->delete();
        $this->assertTrue((new LoginModel())->find(1) === null);
    }

    public function testCreateFreeAndUpgradeHost()
    {
        (new LoginModel())->register([
            'name' => 'Contoso User',
            'email' => 'contoso@example.com',
            'password' => 'mycontosouser',
        ], true, true);
        ($user = new User())->initController($req = Services::request(), Services::response(), Services::logger());

        // Check create host

        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'plan' => 1,
            'server' => 1,
            'username' => 'contoso',
            'password' => 'mycontoso',
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('create');
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertEquals(array_intersect_key($host->toRawArray(), array_flip(
            [
                'id', 'login_id', 'username', 'domain', 'password',
                'status', 'server_id', 'plan_id'
            ]
        )), [
            'id' => 1,
            'login_id' => 1,
            'username' => 'contoso',
            'domain' => 'contoso.dom.my.id',
            'password' => 'mycontoso',
            'status' => 'active',
            'server_id' => 1,
            'plan_id' => 1,
        ]);
        $this->assertEquals(explode("\n", trim(VirtualMinShell::$output)), [
            'program=create-domain&user=contoso&pass=mycontoso&email=contoso@example.com' .
                '&domain=contoso.dom.my.id&plan=Freedom&limits-from-plan=&dir=&webmin=' .
                '&virtualmin-nginx=&virtualmin-nginx-ssl=&unix='
        ]);
        VirtualMinShell::$output = '';

        // Okay, try rename

        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'username' => 'emily',
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('rename', $host->id);
        $this->assertTrue((new HostModel())->find(1)->domain === 'emily.dom.my.id');
        $this->assertEquals(explode("\n", trim(VirtualMinShell::$output)), [
            'program=modify-domain&domain=contoso.dom.my.id&user=emily',
            'program=modify-domain&domain=contoso.dom.my.id&newdomain=emily.dom.my.id'
        ]);
        VirtualMinShell::$output = '';

        // Okay, try extend (free)

        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'mode' => 'new',
            'plan' => 1,
            'years' => 1,
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('upgrade', $host->id);
        $newexp = (new HostModel())->find(1)->expiry_at;
        $this->assertTrue(strtotime($host->expiry_at) <= strtotime($newexp));

        // Okay, try upgrade

        $req->setGlobal('post', $post_data = [
            'mode' => 'new',
            'plan' => 2,
            'years' => 1,
            'domain' => [
                'custom' => 'emily.com'
            ]
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('upgrade', $host->id);
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertTrue(($purchase = $host->purchase)->status === 'pending');

        // Try to execute payment

        ($api = new Api())->initController($req, Services::response(), Services::logger());
        $req->setGlobal('get', [
            'id' => $purchase->id,
            'challenge' => $purchase->metadata->_challenge,
            'secret' => $req->config->ipaymuSecret,
        ]);
        $req->setGlobal('post', [
            'trx_id' => 123,
            'via' => 'Test',
            'status' => 'berhasil',
        ]);
        $api->notify();
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertTrue($host->plan_id === 2);
        $this->assertTrue(($purchase = $host->purchase)->status === 'active');
        $this->assertEquals(explode("\n", trim(VirtualMinShell::$output)), [
            'program=modify-domain&domain=emily.dom.my.id&newdomain=emily.com',
            'program=enable-domain&domain=emily.com',
            'program=modify-domain&domain=emily.com&apply-plan=Lite'
        ]);
        VirtualMinShell::$output = '';

        // Okay, try to change domain

        $req->setGlobal('post', $post_data = [
            'cname' => 'emily.me'
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('cname', 1);
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertTrue($host->domain === 'emily.me');
        $this->assertEquals(explode("\n", trim(VirtualMinShell::$output)), [
            'program=modify-domain&domain=emily.com&newdomain=emily.me',
        ]);
        VirtualMinShell::$output = '';

        // Okay, try add more addons

        $req->setGlobal('post', $post_data = [
            'mode' => 'topup',
            'addons' => 10,
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('upgrade', $host->id);
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertTrue(($purchase = $host->purchase)->status === 'pending');

        // Try to execute payment

        ($api = new Api())->initController($req, Services::response(), Services::logger());
        $req->setGlobal('get', [
            'id' => $purchase->id,
            'challenge' => $purchase->metadata->_challenge,
            'secret' => $req->config->ipaymuSecret,
        ]);
        $req->setGlobal('post', [
            'trx_id' => 123,
            'via' => 'Test',
            'status' => 'berhasil',
        ]);
        $api->notify();
        $this->assertTrue(($purchase = $host->purchase)->status === 'active');
        $this->assertEquals(explode("\n", trim(VirtualMinShell::$output)), [
            'program=modify-domain&domain=emily.me&bw=21474836480',
            'program=enable-domain&domain=emily.me',
        ]);
        VirtualMinShell::$output = '';

        // Okay, try to extend

        $req->setGlobal('post', $post_data = [
            'mode' => 'extend',
            'years' => 1,
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('upgrade', $host->id);
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertTrue(($purchase = $host->purchase)->status === 'pending');

        // This time, try to cancel

        $req->setGlobal('post', $post_data = [
            'action' => 'cancel',
        ]);
        $user->host('invoices', $host->id);
        $this->assertTrue(($purchase = $host->purchase)->status === 'active');

        // Okay, try to upgrade

        $req->setGlobal('post', $post_data = [
            'mode' => 'upgrade',
            'plan' => 3,
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('upgrade', $host->id);
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertEquals($host->purchase->metadata->price, 155000);
    }

    public function testCreateHostWithDomain()
    {
        (new LoginModel())->register([
            'name' => 'Contoso User',
            'email' => 'contoso@example.com',
            'password' => 'mycontosouser',
        ], true, true);
        ($user = new User())->initController($req = Services::request(), Services::response(), Services::logger());

        // Check create host

        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'plan' => 2,
            'server' => 1,
            'username' => 'contoso',
            'password' => 'mycontoso',
            'years' => 1,
            'addons' => 5,
            'domain' => [
                'scheme' => 1,
                'name' => 'example',
                'bio' => json_encode([
                    'owner' => [
                        'fname' => 'Contoso',
                        'company' => 'Contoso Company',
                        'email' => 'contoso@example.com',
                        'tel' => '15556667',
                        'country' => 'US',
                        'state' => 'California',
                        'city' => 'Vancouver',
                        'postal' => '11101',
                        'address1' => 'St. John',
                    ]
                ])
            ]
        ]);
        $req->setGlobal('request', $post_data);
        $user->host('create');
        /** @var Host */
        $host = (new HostModel())->find(1);
        $this->assertTrue(isset($host, $host->purchase, $host->domain_detail));
        $purchase = $host->purchase;
        $domain = $host->domain_detail;
        $meta = $purchase->metadata;
        $this->assertTrue($domain->status === 'pending' && $host->status === 'pending');
        $this->assertEquals($meta->toRawArray(), [
            'type' => "hosting",
            'price' => 220000,
            'price_unit' => "idr",
            'template' => "",
            'expiration' =>  $meta->expiration,
            'years' =>  1,
            'plan' => 2,
            'addons' => 5,
            'domain' => 'example.com',
            '_challenge' =>  $meta->_challenge,
            '_id' => NULL,
            '_via' =>  NULL,
            '_issued' => $meta->_issued,
            '_invoiced' => null,
            '_status' => null,
            'registrar' => [
                'domain' => "example.com",
                'api_id' => 1,
                'periode' => 1,
                'ns1' => "nsp.dom.my.id",
                'ns2' => "nss.dom.my.id",
                'fname' => "Contoso",
                'company' => "Contoso Company",
                'address1' => "St. John",
                'city' => "Vancouver",
                'state' => "California",
                'country' => "US",
                'postcode' => "11101",
                'phonenumber' => "15556667",
                'email' => "contoso@example.com",
                'user_username' => "contoso@example.com",
                'user_fname' => "Contoso",
                'user_company' => "Contoso Company",
                'user_address' => "St. John",
                'user_city' => "Vancouver",
                'user_province' => "California",
                'user_country' => "US",
                'user_postal_code' => "11101",
                'autoactive' => "on",
            ]
        ]);

        // Try to execute payment

        ($api = new Api())->initController($req, Services::response(), Services::logger());
        $req->setGlobal('get', [
            'id' => $purchase->id,
            'challenge' => $meta->_challenge,
            'secret' => $req->config->ipaymuSecret,
        ]);
        $req->setGlobal('post', [
            'trx_id' => 123,
            'via' => 'Test',
            'status' => 'berhasil',
        ]);
        $api->notify();
        $host = (new HostModel())->find(1);
        $purchase = $host->purchase;
        $domain = $host->domain_detail;
        $meta = $purchase->metadata;
        $this->assertTrue($domain->status === 'active', $host->status === 'active');
    }

    public function testRegisterDomain()
    {
        (new LoginModel())->register([
            'name' => 'Contoso User',
            'email' => 'contoso@example.com',
            'password' => 'mycontosouser',
        ], true, true);
        ($user = new User())->initController($req = Services::request(), Services::response(), Services::logger());
        $req->setMethod('post');
        $req->setGlobal('post', $post_data = [
            'years' => 1,
            'domain' => [
                'scheme' => 1,
                'name' => 'example',
                'bio' => json_encode([
                    'owner' => [
                        'fname' => 'Contoso',
                        'company' => 'Contoso Company',
                        'email' => 'contoso@example.com',
                        'tel' => '15556667',
                        'country' => 'US',
                        'state' => 'California',
                        'city' => 'Vancouver',
                        'postal' => '11101',
                        'address1' => 'St. John',
                    ]
                ])
            ]
        ]);
        $req->setGlobal('request', $post_data);
        $user->domain('create');

        /** @var Domain */
        $domain = (new DomainModel())->find(1);
        $this->assertTrue(isset($domain, $domain->purchase));
        $this->assertTrue($domain->status === 'pending');
        $meta = $domain->purchase->metadata;
        $this->assertEquals($meta->toRawArray(), [
            'type' => "domain",
            'price' => 165000,
            'price_unit' => "idr",
            'expiration' =>  $meta->expiration,
            'years' =>  1,
            'domain' =>  'example.com',
            '_challenge' =>  $meta->_challenge,
            '_id' => NULL,
            '_via' =>  NULL,
            '_issued' => $meta->_issued,
            '_invoiced' => null,
            '_status' => null,
            'registrar' => [
                'domain' => "example.com",
                'api_id' => 1,
                'periode' => 1,
                'ns1' => "ns1.mysrsx.com",
                'ns2' => "ns2.mysrsx.net",
                'fname' => "Contoso",
                'company' => "Contoso Company",
                'address1' => "St. John",
                'city' => "Vancouver",
                'state' => "California",
                'country' => "US",
                'postcode' => "11101",
                'phonenumber' => "15556667",
                'email' => "contoso@example.com",
                'user_username' => "contoso@example.com",
                'user_fname' => "Contoso",
                'user_company' => "Contoso Company",
                'user_address' => "St. John",
                'user_city' => "Vancouver",
                'user_province' => "California",
                'user_country' => "US",
                'user_postal_code' => "11101",
                'autoactive' => "on",
            ]
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->db->resetDataCache();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $req = Services::request();
        $req->setMethod('get');
        $req->setGlobal('get', []);
        $req->setGlobal('post', []);
        $req->setGlobal('request', []);
    }
}
