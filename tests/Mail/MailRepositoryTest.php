<?php

namespace Tests\Mail;

use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Util\Exceptions\ValidatorException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MailRepositoryTest extends TestCase
{
    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testCanCreateEmail(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(true);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($pdoStatement);
        $pdo->method('lastInsertId')->willReturn('1');

        $dto = new EmailDTO('name', 'test@example.com', 'subject', 'body', 'altBody', 1);

        $repository = new MailRepository($pdo);
        $result = $repository->criar($dto);

        $this->assertSame(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testFailToCreateEmail(): void
    {
        $this->expectException(ValidatorException::class);

        $pdo = $this->createMock(PDO::class);

        $dto = new EmailDTO('name', 'test@example.com', 'subject', 'body', 'altBody');

        $repository = new MailRepository($pdo);
        $repository->criar($dto);
    }

    /**
     * @throws Exception
     */
    public function testCanGetAtivas(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(true);
        $pdoStatement->method('fetchAll')->willReturn([
            [
                'id' => 'uuid',
                'destinatario' => 'name',
                'email_destino' => 'test@example.com',
                'assunto' => 'subject',
                'conteudo' => 'body',
                'alt_conteudo' => 'altBody',
                'notificacao_id' => 1,
                'criado_em' => '2023-10-15 10:30:00.000000',
            ],
        ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($pdoStatement);

        $repository = new MailRepository($pdo);
        $emails = $repository->ativas();

        $this->assertCount(1, $emails);
        $this->assertInstanceOf(EmailDTO::class, $emails[0]);
    }

    /**
     * @throws Exception
     */
    public function testCanMarkAsEnviadas(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(true);
        $pdoStatement->method('rowCount')->willReturn(1);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($pdoStatement);

        $repository = new MailRepository($pdo);
        $count = $repository->enviadas([1]);

        $this->assertSame(1, $count);
    }
}
