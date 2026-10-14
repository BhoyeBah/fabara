<?php
namespace App\Support;

class Money
{
    public static function fmt($v): string
    {
        $c = Setting::get('currency');
        return number_format((float) $v, 0, ',', ' ') . ($c ? ' ' . $c : '');
    }
}
