<?php namespace App\Models;

use CodeIgniter\Model;

class SchemeModel extends Model
{
    protected $table         = 'schemes';
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Scheme';
}