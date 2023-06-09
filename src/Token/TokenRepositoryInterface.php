<?php

namespace App\Token;

use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use stdClass;

interface TokenRepositoryInterface
{
    /**
     * @param int $expiresInDays    - Number of days the token will be valid
     * @param int $maxUsage         - Maximum number of times the token can be used
     * @param array $additionalData - Additional data to be added to the token
     * @return array                - Array containing token and decoded token
     */
    public function createToken(int $expiresInDays, int $maxUsage, array $additionalData = []): array;


    /**
     * @throws ValidatorException
     * @throws ResponseException
     */
    public function consumeToken(string $token): stdClass;

}
