<?php namespace App\Models;

use CodeIgniter\Model;

class TemplatesModel extends Model
{
    protected $table         = 'templates';
    protected $allowedFields = [    ];
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    public function atLang($lang)
    {
        $this->builder()->where('lang', $lang)->orWhere('lang', '');
        return $this;
    }
}