<?php

namespace App\Util\Services\TokenService;

use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use stdClass;

interface TokenServiceInterface
{
    /**
     * Generates a token with the given ID and expiration date
     *
     * @param int $expiresInDays     - Number of days until the token expires
     * @param array $additionalData  - Additional data to be added to the token
     * @return string                - The token
     */
    public static function createToken(int $expiresInDays, array $additionalData = []): string;

    /**
     * Decodes and validates a token and returns the payload
     *
     * @param string $token - The token to be decoded
     * @return stdClass           - The payload of the token
     *
     * @throws ValidatorException - When a token is not valid
     * @throws ResponseException  - Generic exception
     */
    public static function decodeToken(string $token): stdClass;
}
