<?php

namespace App\Util\Exceptions;

use App\Util\Response;
use Exception;

class ResponseException extends Exception
{
    private Response $response;

    public function __construct(Response $response)
    {
        parent::__construct($response->mensagem());
        $this->response = $response;
    }

    public function response(): Response
    {
        return $this->response;
    }
}
