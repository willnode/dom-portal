<?php

namespace App\Database;

use App\Controllers\Home;
use App\Models\LoginModel;
use CodeIgniter\Commands\Database\Migrate;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Database;
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
        // prepare controller
        $home = new Home();
        $req = Services::request();
        $ses = Services::session();
        $home->initController($req, Services::response(), Services::logger());
        // prepare request
        $req->setMethod('post');
        $req->setGlobal('post', $login = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email,
            'password' => $this->faker->password(8),
        ]);
        $req->setGlobal('request', $login);
        // execute register
        $home->register();
        $this->assertTrue($this->db->table('login')->countAllResults() === 1);
        $home->login();
        $this->assertTrue($ses->login === 1);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = (new Factory())->create();
        $this->db->resetDataCache();
    }
}
