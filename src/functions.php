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


function fill_template(string $template, array $data)
{
    return str_replace(
        array_map(fn ($key) => "{{ $key }}", array_keys($data)),
        array_values($data),
        $template
    );
}


function pluralize(int $n, string $singular, string $plural): string
{
    return $n == 1 ? "$n $singular" : "$n $plural";
}


function array_some(array $a, callable $p=null): bool
{
    $p ??= fn($x) => $x;
    foreach ($a as $x) {
        if ($p($x)) {
            return true;
        }
    }
    return false;
}


function array_every(array $a, callable $p=null): bool
{
    $p ??= fn($x) => $x;
    foreach ($a as $x) {
        if (!$p($x)) {
            return false;
        }
    }
    return true;
}
