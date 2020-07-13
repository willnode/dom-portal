<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CronJob extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'cronjob';
    protected $description = 'Do Scheduled Server Collection.';

    public function run(array $params)
    {
		echo 'OK';
    }
}