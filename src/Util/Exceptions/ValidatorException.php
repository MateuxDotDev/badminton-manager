<?php

namespace App\Util\Exceptions;

use Exception;

class ValidatorException extends Exception
{
    private string $status;
    private array $errors;

    public function __construct(string $status, array $errors)
    {
        parent::__construct($status);
        $this->status = $status;
        $this->errors = $errors;
    }

    public function __toString(): string
    {
        return json_encode([
            'status' => $this->status,
            'errors' => $this->errors
        ]);
    }
}