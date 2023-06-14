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
