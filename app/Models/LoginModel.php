<?php namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    protected $table         = 'login';
    protected $allowedFields = [
        'id', 'name', 'email', 'phone', 'password', 'otp', 'lang', 'email_verified_at',
    ];
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Login';
    protected $useTimestamps = true;
}