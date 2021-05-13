<?php

namespace App\Database;

use App\Controllers\Home;
use App\Controllers\User;
use App\Models\DomainModel;
use App\Models\HostModel;
use App\Models\HostStatModel;
use App\Models\LoginModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Services;

class WebViewTest extends CIDatabaseTestCase
{
    protected $namespace  = null;

    public function testStaticView()
    {
        ($home = new Home())->initController($req = Services::request(), Services::response(), Services::logger());
        $this->assertTrue($home->index()->getStatusCode() === 302);
        $this->assertTrue($home->import()->getStatusCode() === 302);
        $this->assertTrue(is_string($home->login()));
        $this->assertTrue(is_string($home->register()));
        $this->assertTrue(is_string($home->forgot()));
        try {
            $this->assertEmpty($home->reset());
        } catch (\Throwable $th) {
            $this->assertTrue($th instanceof PageNotFoundException);
        }
        try {
            $this->assertEmpty($home->verify());
        } catch (\Throwable $th) {
            $this->assertTrue($th instanceof PageNotFoundException);
        }
    }

    public function testUserView()
    {
        (new LoginModel())->register([
            'name' => 'Contoso User',
            'email' => 'contoso@example.com',
            'password' => 'mycontosouser',
        ], true, true);

        // profile page
        ($user = new User())->initController($req = Services::request(), Services::response(), Services::logger());
        $this->assertTrue(is_string($user->profile()));

        // server page
        $this->assertTrue(is_string($user->status()));
        $this->assertTrue(is_string($user->profile()));
        $this->assertTrue(is_string($user->delete()));
        $this->assertTrue(is_string($user->sales()));

        // hosts page
        (new HostModel())->insert([
            'id' => 1,
            'login_id' => 1,
            'plan_id' => 1,
            'server_id' => 1,
            'username' => 'contoso',
            'password' => 'contoso',
            'domain' => 'contoso.dom.my.id',
            'expiry_at' => date('Y-m-d H:i:s'),
        ]);
        (new HostStatModel())->insert([
            'host_id' => 1,
            'domain' => 'contoso.dom.my.id',
            'identifier' => '10101',
            'password' =>  'contoso',
            'quota_server' => 0,
            'quota_user' => 0,
            'quota_db' => 0,
            'quota_net' => 0,
            'features' => 'web',
            'bandwidths' => null,
            'disabled' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        (new DomainModel())->insert([
            'id' => 1,
            'login_id' => 1,
            'name' => 'contoso.me',
            'scheme_id' => 1,
            'expiry_at' => date('Y-m-d H:i:s'),
        ]);
        $req->setGlobal('get', []);
        foreach (['en', 'id'] as $lang) {
            $req->setLocale($lang);
            $this->assertTrue(is_string($user->host('list')));
            $this->assertTrue(is_string($user->host('create')));
            foreach ([
                'detail', 'see', 'nginx', 'invoices', 'upgrade',
                'dns', 'ssl', 'rename', 'cname', 'delete', 'deploys',
                'transfer'
            ] as $page) {
                $this->assertTrue(is_string($user->host($page, 1)));
            }
            $this->assertTrue(is_string($user->domain('list')));
            $this->assertTrue(is_string($user->domain('create')));
            $this->assertTrue(is_string($user->domain('transfer')));
            foreach ([
                'detail', 'invoices', 'dns', 'renew'
            ] as $page) {
                $this->assertTrue(is_string($user->domain($page, 1)));
            }
        }
    }

    public function testRollingQuota()
    {
        // 3GB usage, 1GB plan, 2.5GB addons => 0.5
        $this->assertEquals(500, calculateRemaining(3000, 2500, 1000));
        // 500MB usage, 1GB plan, 2.5GB addons => 2.5
        $this->assertEquals(2500, calculateRemaining(500, 2500, 1000));
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
