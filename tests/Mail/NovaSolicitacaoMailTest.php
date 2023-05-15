<?php

namespace Tests\Mail;

use App\Mail\NovaSolicitacaoMail;
use App\Util\Exceptions\MailException;
use App\Util\Mail\MailerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NovaSolicitacaoMailTest extends TestCase
{
    private MockObject|MailerInterface $mailerMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mailerMock = $this->createMock(MailerInterface::class);
    }

    public function testConstruct()
    {
        $mail = new NovaSolicitacaoMail($this->mailerMock);
        $this->assertInstanceOf(NovaSolicitacaoMail::class, $mail);
    }

    /**
     * @throws MailException
     */
    public function testSend()
    {
        $mail = new NovaSolicitacaoMail($this->mailerMock);

        $toEmail = 'test@example.com';
        $toName = 'Test User';
        $subject = 'Você recebeu uma nova solicitação de Dupla!';
        $altBody = 'Test alternative body';

        $this->mailerMock
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                $this->equalTo($toEmail),
                $this->equalTo($toName),
                $this->equalTo($subject),
                $this->callback(function ($body) {
                    $this->assertStringContainsString('Olá {{ nome_tecnico }}!', $body);
                    $this->assertStringContainsString('Você recebeu uma nova solicitação de dupla!', $body);
                    return true;
                }),
                $this->equalTo($altBody)
            )
            ->willReturn(true);

        $mail->setToEmail($toEmail);
        $mail->setToName($toName);
        $mail->setAltBody($altBody);

        $result = $mail->send();

        $this->assertTrue($result);
    }
}
