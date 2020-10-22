<?php

use CodeIgniter\CLI\CLI;
use Config\Database;
use Config\Services;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

function href($url)
{
    $req = Services::request();
    return base_url($req->getLocale() . '/' . $url);
}

function format_money($money, $currency = null)
{
    $money = floatval($money);
    return ($currency ?: lang('Interface.currency')) == 'usd' ? number_format($money, 2) . ' US$' : 'Rp ' . number_format($money, 0, ',', '.');
}

function format_bytes($bytes, $precision = 1)
{
    $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return number_format($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * @codeCoverageIgnore
 */
function sanitize_shell_arg_dir($dir)
{
    return implode('/', array_map(function ($x) {
        return escapeshellarg($x);
    }, explode('/', trim($dir, ' /'))));
}
