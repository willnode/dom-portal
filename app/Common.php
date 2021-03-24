<?php

use CodeIgniter\I18n\Time;
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

function imsort(array $ar)
{
    sort($ar);
    return $ar;
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

function get_gravatar( $email, $s = 80, $d = 'mp', $r = 'g' ) {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    return $url;
}

function calculateRemaining($current, $addons, $plan)
{
    return floor(min($addons, max(0, $addons - ($current - $plan))));
}

function humanize(Time $time)
{
    $now  = IntlCalendar::fromDateTime(Time::now($time->timezone)->toDateTimeString());
    $time = $time->getCalendar()->getTime();

    $years   = $now->fieldDifference($time, IntlCalendar::FIELD_YEAR);
    $months  = $now->fieldDifference($time, IntlCalendar::FIELD_MONTH);
    $days    = $now->fieldDifference($time, IntlCalendar::FIELD_DAY_OF_YEAR);
    $hours   = $now->fieldDifference($time, IntlCalendar::FIELD_HOUR_OF_DAY);
    $minutes = $now->fieldDifference($time, IntlCalendar::FIELD_MINUTE);

    $phrase = null;

    if ($years !== 0) {
        $phrase = lang('Time.years', [abs($years)]);
        $before = $years < 0;
    } else if ($months !== 0) {
        $phrase = lang('Time.months', [abs($months)]);
        $before = $months < 0;
    } else if ($days !== 0 && (abs($days) >= 7)) {
        $weeks  = ceil($days / 7);
        $phrase = lang('Time.weeks', [abs($weeks)]);
        $before = $days < 0;
    } else if ($days !== 0) {
        $before = $days < 0;

        // Yesterday/Tomorrow special cases
        if (abs($days) === 1) {
            return $before ? lang('Time.yesterday') : lang('Time.tomorrow');
        }

        $phrase = lang('Time.days', [abs($days)]);
    } else if ($hours !== 0) {
        $phrase = lang('Time.hours', [abs($hours)]);
        $before = $hours < 0;
    } else if ($minutes !== 0) {
        $phrase = lang('Time.minutes', [abs($minutes)]);
        $before = $minutes < 0;
    } else {
        return lang('Time.now');
    }

    return $before ? lang('Time.ago', [$phrase]) : lang('Time.inFuture', [$phrase]);
}
