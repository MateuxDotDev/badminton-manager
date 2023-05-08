<?php

namespace App\Util\Exceptions;

use Exception;

class MailException extends Exception
{
    private array $mailData;

    public function __construct(Exception $exception, array $mailData = [])
    {
        parent::__construct($exception);
        $this->mailData = $mailData;
    }

    public function mailData(): array
    {
        return $this->mailData;
    }

    public function __toString(): string
    {
        return parent::__toString() . PHP_EOL . json_encode($this->mailData, JSON_PRETTY_PRINT);
    }
}
