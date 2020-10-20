<?php

namespace App\Database;

use App\Controllers\Home;
use App\Controllers\User;
use App\Entities\Host;
use App\Entities\Login;
use App\Libraries\SendGridEmail;
use App\Libraries\VirtualMinShell;
use App\Models\HostModel;
use App\Models\LoginModel;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Services;
use Faker\Factory;
use Faker\Generator;

class SignUpTest extends CIDatabaseTestCase
{
    protected $namespace  = null;

    protected Generator $faker;

    public function testRegister()
    {
        // check database migration
        $this->assertTrue($this->db->tableExists('login'));
        $this->assertTrue($this->db->table('login')->countAllResults() === 0);
        // prepare controller
        $home = new Home();
        $home->initController($req = Services::request(), Services::response(), Services::logger());
        // prepare request
        $req->setMethod('post');
        $req->setGlobal('post', $login_data = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email,
            'password' => $this->faker->password(8),
        ]);
        $req->setGlobal('request', $login_data);
        // execute register
        $home->register();
        // check user added
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login !== null);
        // check email verification sent
        $this->assertTrue($login->otp !== null);
        $this->assertTrue(SendGridEmail::$sentEmail === 'verify_email');
        $sentEmail = json_decode(SendGridEmail::$sentBody)->personalizations[0]->to[0]->email ?? '';
        $this->assertTrue($sentEmail === $login->email);
        // check email verification action
        $req->setMethod('get');
        $req->setGlobal('get', $otp_data = [
            'code' => base64_encode("$login->email:$login->otp"),
        ]);
        $req->setGlobal('request', $otp_data);
        $home->verify();
        /** @var Login */
        $login = (new LoginModel())->find(1);
        $this->assertTrue($login->otp === null);
        $this->assertTrue($login->trustiness === 1);
        $this->assertTrue($login->email_verified_at !== null);
        // check login
        $req->setMethod('post');
        $req->setGlobal('post', $login_data);
        $req->setGlobal('request', $login_data);
        $home->login() && $this->assertTrue(Services::session()->login === 1);
    }

    public function testCreateFreeAndUpgradeHost()
    {
        (new LoginModel())->register([
            'name' => 'Contoso User',
            'email' => 'contoso@example.com',
            'password' => 'mycontosouser',
        ], true, true);
        $user = new User();
        $user->initController($req = Services::request(), Services::response(), Services::logger());
        // Check create
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
            ['id', 'login_id', 'username', 'domain', 'password', 'status', 'server_id', 'plan_id']
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
            'program=create-domain&user=contoso&pass=mycontoso&email=contoso@example.com'.
            '&domain=contoso.dom.my.id&plan=Freedom&limits-from-plan=&dir=&webmin='.
            '&virtualmin-nginx=&virtualmin-nginx-ssl=&unix='
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = (new Factory())->create();
        $this->db->resetDataCache();
    }
}
