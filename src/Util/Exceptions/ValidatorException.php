<?php

namespace App\Util\Exceptions;

use App\Util\Http\HttpStatus;
use App\Util\Http\Response;
use Exception;

class ValidatorException extends Exception
{
    private HttpStatus $statusCode;
    private array $data;
    private string $errorMessage;

    public function __construct(
        string $errorMessage,
        HttpStatus $statusCode = HttpStatus::BAD_REQUEST,
        array $data = [],
    ) {
        parent::__construct($errorMessage, $statusCode->value);
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
        $this->data = $data;
    }

    public function toResponse(): Response
    {
        return new Response($this->statusCode, $this->errorMessage, $this->data);
    }
}
