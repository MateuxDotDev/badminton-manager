<?php

namespace App\Util;

class Dump
{
    public static function d(mixed $x): void
    {
        echo '<pre>';
        var_dump($x);
        echo '</pre>';
    }

    public static function dd(mixed $x): never
    {
        self::d($x);
        die;
    }

    public static function jd(mixed $x): void
    {
        echo '<pre>';
        echo json_encode($x, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo '</pre>';
    }

    public static function jdd(mixed $x): never
    {
        self::jd($x);
        die;
    }

}