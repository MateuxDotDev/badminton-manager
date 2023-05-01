<?php


namespace App\Util;

use \DateTimeImmutable;
use \DateTimeInterface;

class Dates
{
    const DAY = 'Y-m-d';
    const MICRO = 'Y-m-d H:i:s.u';

    private static function parse(?string $s, string $fmt): DateTimeImmutable|null|false
    {
        if ($s == null) return null;
        return DateTimeImmutable::createFromFormat($fmt, $s);
    }

    private static function format(?DateTimeInterface $d, string $fmt): ?string
    {
        return $d?->format($fmt);
    }

    public static function parseDay(?string $s)
    {
        return self::parse($s, self::DAY);
    }

    public static function parseMicro(?string $s)
    {
        return self::parse($s, self::MICRO);
    }

    public static function formatDay(?DateTimeInterface $d)
    {
        return self::format($d, self::DAY);
    }

    public static function formatMicro(?DateTimeInterface $d)
    {
        return self::format($d, self::MICRO);
    }
}