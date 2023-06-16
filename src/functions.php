<?php

function array_index_by(array $a, int|string $key): array
{
    $r = [];
    foreach ($a as $x) {
        if (!array_key_exists($key, $x)) {
            continue;
        }
        $r[$x[$key]] = $x;
    }
    return $r;
}

function array_some(array $a, callable $p): bool
{
    foreach ($a as $x) {
        if ($p($x)) {
            return true;
        }
    }
    return false;
}

function array_every(array $a, callable $p): bool
{
    foreach ($a as $x) {
        if (!$p($x)) {
            return false;
        }
    }
    return true;
}