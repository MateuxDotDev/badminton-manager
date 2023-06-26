<?php

namespace App\Token;

use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use App\Util\Services\TokenService\TokenService;
use App\Util\Services\TokenService\TokenServiceInterface;
use PDO;
use stdClass;

class TokenRepository implements TokenRepositoryInterface
{
    private const TOKEN_KEY = ':token';

    public function __construct(
        private readonly PDO $pdo,
        private readonly TokenServiceInterface $tokenService = new TokenService()
    ) {}

    /**
     * @throws ValidatorException
     * @throws ResponseException
     */
    public function createToken(int $expiresInDays, int $maxUsage, array $additionalData = []): array
    {
        $sql = <<<SQL
            INSERT INTO token_acao (token, expira_em, qtd_usos_permitidos)
            VALUES (:token, :expira_em, :qtd_usos_permitidos)
        SQL;

        $token = $this->tokenService->createToken($expiresInDays, $additionalData);

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(self::TOKEN_KEY, $token);
        $stmt->bindValue(':expira_em', date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days")));
        $stmt->bindValue(':qtd_usos_permitidos', $maxUsage);
        $stmt->execute();

        return [
            'token' => $token,
            'decodedToken' => $this->tokenService->decodeToken($token),
        ];
    }

    /**
     * @throws ValidatorException
     * @throws ResponseException
     */
    public function consumeToken(string $token): stdClass
    {
        $sql = <<<SQL
            SELECT qtd_usos_permitidos
            FROM token_acao
            WHERE token = :token
            AND (expira_em IS NULL OR expira_em > now())
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(self::TOKEN_KEY, $token);
        $stmt->execute();
        $row = $stmt->fetch();
        if (!$row) {
            throw new ValidatorException('Token inválido ou expirou.');
        }

        $sql = <<<SQL
            SELECT COUNT(*)
            FROM uso_token_acao
            WHERE token = :token
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(self::TOKEN_KEY, $token);
        $stmt->execute();
        $usos = $stmt->fetchColumn();

        if ($row['qtd_usos_permitidos'] !== null && $usos >= $row['qtd_usos_permitidos']) {
            throw new ValidatorException('Token já foi utilizado o máximo permitido.');
        }

        $sql = <<<SQL
            INSERT INTO uso_token_acao (token, data_hora)
            VALUES (:token, now())
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(self::TOKEN_KEY, $token);
        $stmt->execute();

        return $this->tokenService->decodeToken($token);
    }
}
