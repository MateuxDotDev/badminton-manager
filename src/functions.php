<?php

function array_index_by(array $a, int|string|callable $key): array
{
    $r = [];
    foreach ($a as $x) {
        if (is_callable($key)) {
            $r[$key($x)] = $x;
        } else {
            if (!array_key_exists($key, $x)) {
                continue;
            }
            $r[$x[$key]] = $x;
        }
    }
    return $r;
}
