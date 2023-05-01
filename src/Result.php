<?php

namespace App;

readonly class Result
{
    public function __construct(
        private bool $ok,
        private mixed $data,
    ) {}

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public static function ok(mixed $data=null)
    {
        return new Result(true, $data);
    }

    public static function error(mixed $data=null)
    {
        return new Result(false, $data);
    }
}