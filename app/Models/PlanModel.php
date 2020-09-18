<?php namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table         = 'plans';
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Plan';
}