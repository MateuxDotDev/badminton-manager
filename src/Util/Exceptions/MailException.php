<?php

namespace App\Util\Exceptions;

use Exception;

class MailException extends Exception
{
    private array $mailData;
    private Exception $previous;

    public function __construct(string $message, array $mailData = [], Exception $previous = null)
    {
        parent::__construct($message);
        $this->mailData = $mailData;
        $this->previous = $previous;
    }

    public function previous(): Exception
    {
        return $this->previous;
    }

    public function mailData(): array
    {
        return $this->mailData;
    }

    public function __toString(): string
    {
        return json_encode($this->mailData, JSON_PRETTY_PRINT);
    }
}
