<?php

namespace App\Util\General;

use DateTimeImmutable;
use DateTimeInterface;

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

    public static function parseDay(?string $s): DateTimeImmutable|false|null
    {
        return self::parse($s, self::DAY);
    }

    public static function parseMicro(?string $s): DateTimeImmutable|false|null
    {
        return self::parse($s, self::MICRO);
    }

    public static function formatDay(?DateTimeInterface $d): ?string
    {
        return self::format($d, self::DAY);
    }

    public static function formatMicro(?DateTimeInterface $d): ?string
    {
        return self::format($d, self::MICRO);
    }
}