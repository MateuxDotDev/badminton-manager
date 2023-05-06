<?php

namespace App\Util\Exceptions;

use App\Util\Http\Response;
use Exception;

class ValidatorException extends Exception
{
    private int $statusCode;
    private string $errorMessage;

    public function __construct($errorMessage, $statusCode = 400)
    {
        parent::__construct($errorMessage);
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
    }

    public function toResponse(): Response
    {
        return new Response($this->statusCode, $this->errorMessage);
    }
}
