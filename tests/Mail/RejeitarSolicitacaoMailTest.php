<?php

namespace Tests\Mail;

use App\Mail\SolicitacaoRejeitadaMail;
use App\Util\Exceptions\MailException;
use App\Util\General\Dates;
use App\Util\Mail\MailerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RejeitarSolicitacaoMailTest extends TestCase
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
        $mail = new SolicitacaoRejeitadaMail($this->mailerMock);
        $this->assertInstanceOf(SolicitacaoRejeitadaMail::class, $mail);
    }

    /**
     * @throws MailException
     */
    public function testSend()
    {
        $mail = new SolicitacaoRejeitadaMail($this->mailerMock);

        $toEmail = 'test@example.com';
        $toName = 'Test User';
        $subject = 'Uma solicitação de dupla sua foi rejeitada.';
        $altBody = 'Test alternative body';

        $this->mailerMock
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                $this->equalTo($toEmail),
                $this->equalTo($toName),
                $this->equalTo($subject),
                $this->callback(function ($body) {
                    $this->assertStringContainsString('Olá {{ dest_tecnico }}!', $body);
                    $this->assertStringContainsString('A solicitação de formar dupla com o seu atleta {{ dest_nome }} e {{ rem_nome }} na competição {{ competicao }} foi cancelada! Mais detalhes', $body);
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
        $this->assertSame($altBody, $mail->getAltBody());
        $this->assertSame($subject, $mail->getSubject());
    }

    public function testFillTemplate()
    {
        $mail = new SolicitacaoRejeitadaMail($this->mailerMock);

        $templateData = [
            'dest_tecnico' => 'Técnico A',
            'dest_nome' => 'Atleta A',
            'rem_nome' => 'Atleta B',
            'competicao' => 'Competição A',
            'dest_sexo' => 'Masculino',
            'rem_sexo' => 'Masculino',
            'dest_idade' => '21',
            'rem_idade' => '21',
            'dest_nascimento' => '01/01/2000',
            'rem_nascimento' => '01/01/2000',
            'dest_info' => 'Informações adicionais do Atleta A',
            'rem_info' => 'Informações adicionais do Atleta B',
            'categoria' => 'Categoria A',
            'observacoes' => 'Observações A',
            'ano_atual' => Dates::currentYear(),
        ];

        foreach ($templateData as $key => $value) {
            $this->assertStringContainsString($key, $mail->getBody());
            $this->assertStringNotContainsString($value, $mail->getBody());
        }

        $mail->fillTemplate($templateData);

        foreach ($templateData as $key => $value) {
            $this->assertStringContainsString($value, $mail->getBody());
            $this->assertStringNotContainsString($key, $mail->getBody());
        }
    }
}
