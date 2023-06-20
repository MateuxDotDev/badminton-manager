<?php

namespace App\Util\General;

use DateTimeImmutable;
use DateTimeInterface;

class Dates
{
    const DAY = 'Y-m-d';
    const MICRO = 'Y-m-d H:i:s.u';
    const BRAZIL = 'd/m/Y H:i:s';
    const BRAZIL_DAY = 'd/m/Y';

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

    public static function formatBr(?DateTimeInterface $d): ?string
    {
        return self::format($d, self::BRAZIL);
    }

    public static function formatDayBr(?DateTimeInterface $d): ?string
    {
        return self::format($d, self::BRAZIL_DAY);
    }

    public static function age(DateTimeInterface $d): int
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($d);
        return $diff->y;
    }
}
