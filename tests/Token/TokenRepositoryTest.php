<?php

namespace Tests\Token;

use App\Token\TokenRepository;
use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\Response;
use App\Util\Services\TokenService\TokenServiceInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

class TokenRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $pdoStatement;
    private TokenServiceInterface $tokenService;
    private TokenRepository $tokenRepository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->pdoStatement = $this->createMock(PDOStatement::class);
        $this->tokenService = $this->createMock(TokenServiceInterface::class);
        $this->tokenRepository = new TokenRepository($this->pdo, $this->tokenService);
    }

    /**
     * @throws ValidatorException
     * @throws ResponseException
     */
    public function testCreateToken(): void
    {
        $expiresInDays = 1;
        $maxUsage = 1;
        $additionalData = ['additionalData' => 'data'];
        $token = 'token';

        $this->tokenService->expects($this->once())
            ->method('createToken')
            ->with($expiresInDays, $additionalData)
            ->willReturn($token);

        $this->tokenService->expects($this->once())
            ->method('decodeToken')
            ->with($token)
            ->willReturn(new stdClass());

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->tokenRepository->createToken($expiresInDays, $maxUsage, $additionalData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('decodedToken', $result);
    }

    /**
     * @throws ValidatorException
     * @throws ResponseException
     */
    public function testConsumeToken(): void
    {
        $token = 'token';
        $row = ['qtd_usos_permitidos' => 1];

        $this->pdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($row);

        $this->pdoStatement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);

        $this->tokenService->expects($this->once())
            ->method('decodeToken')
            ->with($token)
            ->willReturn(new stdClass());

        $result = $this->tokenRepository->consumeToken($token);

        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @throws ResponseException
     */
    public function testConsumeTokenThrowsValidatorException(): void
    {
        $token = 'token';

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Token inválido ou expirou.');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdoStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->tokenRepository->consumeToken($token);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testCreateTokenThrowsExceptionWhenFailsToExecuteSql(): void
    {
        $expiresInDays = 1;
        $maxUsage = 1;
        $additionalData = ['additionalData' => 'data'];
        $token = 'token';

        $responseMock = $this->createMock(Response::class);

        $this->tokenService->expects($this->once())
            ->method('createToken')
            ->with($expiresInDays, $additionalData)
            ->willReturn($token);

        $this->tokenService->expects($this->once())
            ->method('decodeToken')
            ->with($token)
            ->willThrowException(new ResponseException($responseMock));

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->expectException(ResponseException::class);

        $this->tokenRepository->createToken($expiresInDays, $maxUsage, $additionalData);
    }

    /**
     * @throws ResponseException
     */
    public function testConsumeTokenThrowsValidatorExceptionWhenTokenIsInvalidOrExpired(): void
    {
        $token = 'token';

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Token inválido ou expirou.');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->tokenRepository->consumeToken($token);
    }

    /**
     * @throws ResponseException
     */
    public function testConsumeTokenThrowsValidatorExceptionWhenTokenHasBeenUsedUp(): void
    {
        $token = 'token';
        $row = ['qtd_usos_permitidos' => 1];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Token já foi utilizado o máximo permitido.');

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->pdoStatement);

        $this->pdoStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($row);

        $this->pdoStatement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $this->tokenRepository->consumeToken($token);
    }
}
