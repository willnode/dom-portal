<?php

namespace App\Models;

use App\Entities\Login;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Model;
use Config\Services;

class LoginModel extends Model
{
    protected $table         = 'login';
    protected $allowedFields = [
        'id', 'name', 'email', 'phone', 'password', 'otp', 'lang', 'email_verified_at', 'trustiness',
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Login';
    protected $useTimestamps = true;

    /** @return Login|null */
    public function atEmail($email)
    {
        $this->builder()->where('email', $email);
        return $this->find()[0] ?? null;
    }

    public function login(Login $data)
    {
        $s = Services::session();
        $s->set('login', $data->id);
        $s->set('name', $data->name);
        $s->set('email', $data->email);
    }

    /** @return int|null */
    public function register($data, $thenLogin = true, $autoVerified = false)
    {
        $data = array_intersect_key($data, array_flip(
            ['name', 'email', 'phone', 'password']
        ));
        $data['lang'] = Services::request()->getLocale();
        if (!empty($data['password']))
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        if ($autoVerified) {
            $data['email_verified_at'] = date('Y-m-d H:i:s');
        }
        if ($this->save($data)) {
            if ($thenLogin) {
                Services::session()->set('login', $this->insertID);
                Services::session()->set('name', $data['name'] ?? '');
                Services::session()->set('email', $data['email'] ?? '');
            }
            return $this->insertID;
        }
        return null; // @codeCoverageIgnore
    }
}
