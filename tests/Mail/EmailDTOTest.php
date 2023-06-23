<?php

namespace Tests\Mail;

use App\Mail\EmailDTO;
use DateTime;
use PHPUnit\Framework\TestCase;

class EmailDTOTest extends TestCase
{
    public function testCanSetAndGetFields()
    {
        $dateTime = new DateTime();
        $uuid = 'test-uuid';
        $toName = 'Test';
        $toEmail = 'test@example.com';
        $subject = 'Test Subject';
        $body = 'Test Body';
        $altBody = 'Test Alt Body';
        $idNotificacao = 1;

        $dto = new EmailDTO($toName, $toEmail, $subject, $body, $altBody, $idNotificacao);

        $dto->setDataCriacao($dateTime)
            ->setUuid($uuid);

        $this->assertSame($dateTime, $dto->dataCriacao());
        $this->assertSame($uuid, $dto->id());
        $this->assertSame($toName, $dto->toName);
        $this->assertSame($toEmail, $dto->toEmail);
        $this->assertSame($subject, $dto->subject);
        $this->assertSame($body, $dto->body);
        $this->assertSame($altBody, $dto->altBody);
        $this->assertSame($idNotificacao, $dto->idNotificacao);
    }
}
