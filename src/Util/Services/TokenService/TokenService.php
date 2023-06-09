<?php

namespace App\Util\Services\TokenService;

use App\Util\Environment\Environment;
use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use App\Util\Http\Response;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class TokenService implements TokenServiceInterface
{
    public static function createToken(int $expiresInDays, array $additionalData = []): string
    {
        $payload = array_merge(
            [
                "iss" => Environment::getBaseUrl(),
                "aud" => Environment::getBaseUrl(),
                "iat" => time(),
                "exp" => time() + $expiresInDays * 24 * 60 * 60,
            ],
            $additionalData
        );

        return JWT::encode($payload, Environment::getJwtSecret(), 'HS256');
    }

    /**
     * @throws ValidatorException
     * @throws ResponseException
     */
    public static function decodeToken(string $token): stdClass
    {
        try {
            return JWT::decode($token, new Key(Environment::getJwtSecret(), 'HS256'));
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Expired token')) {
                throw new ValidatorException('O token expirou', HttpStatus::UNAUTHORIZED);
            }

            if (str_contains($e->getMessage(), 'Signature verification failed')) {
                throw new ValidatorException('Token inválido', HttpStatus::UNAUTHORIZED);
            }

            throw new ResponseException(Response::erroException($e));
        }
    }
}
