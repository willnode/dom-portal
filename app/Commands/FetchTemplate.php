<?php

namespace App\Commands;

use App\Libraries\VirtualMinShell;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class FetchTemplate extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'template';
    protected $description = 'Process Template to DB';

    public function run(array $params)
    {
        $db = Database::connect();
        $db->table('templates')->where('1=1')->delete();
        $dir = ROOTPATH . 'templates' . DIRECTORY_SEPARATOR;
        foreach (scandir($dir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'yml') continue;
            $obj = yaml_parse_file($dir.$file);
            $id = substr($file, 0, strlen($file) - 4);
            $domains = ($obj['target']['domains'] ?? []);
            $matches = ($obj->target->matches ?? ['.*']);
            $priority = $obj->target->priority ?? 100;
                        $db->table('templates')->insert([
                            'id' => $id,
                            'metadata' => json_encode($obj)
                        ]);
            foreach ($domains as $domain) {
                foreach ($matches as $match) {
                    $db->table('templates__index')->insert([
                        'domain' => $domain,
                        'match' => $match,
                        'priority' => $priority,
                        'target' => $id,
                    ]);
                }
            }
        }
    }
}
