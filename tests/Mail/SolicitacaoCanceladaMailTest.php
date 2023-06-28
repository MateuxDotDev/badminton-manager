<?php

namespace Tests\Mail;

use App\Mail\SolicitacaoCanceladaMail;
use App\Util\Exceptions\MailException;
use App\Util\Mail\MailerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SolicitacaoCanceladaMailTest extends TestCase
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
        $mail = new SolicitacaoCanceladaMail($this->mailerMock, 'Atleta A', 'Atleta B', 'Competição A');
        $this->assertInstanceOf(SolicitacaoCanceladaMail::class, $mail);
    }

    /**
     * @throws MailException
     */
    public function testSend()
    {
        $atletaDest = 'Atleta A';
        $atletaRem = 'Atleta B';
        $competicao = 'Competição A';

        $mail = new SolicitacaoCanceladaMail($this->mailerMock, $atletaDest, $atletaRem, $competicao);

        $toEmail = 'test@example.com';
        $toName = $atletaDest;
        $subject = 'A solicitação de formar dupla com o seu atleta ' . $atletaDest . '  e ' . $atletaRem . '  na competição ' . $competicao . ' foi cancelada!';
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
                    $this->assertStringContainsString('A solicitação de formar dupla com o seu atleta {{ dest_nome }} e {{ rem_nome }} na competição {{ competicao }} foi cancelada! Mais detalhes:', $body);
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
        $atletaDest = 'Atleta A';
        $atletaRem = 'Atleta B';
        $competicao = 'Competição A';
        $mail = new SolicitacaoCanceladaMail($this->mailerMock, $atletaDest, $atletaRem, $competicao);

        $templateData = [
            'dest_tecnico' => 'Técnico A',
            'dest_nome' => $atletaDest,
            'rem_nome' => $atletaRem,
            'competicao' => $competicao,
            'dest_sexo' => 'Masculino',
            'rem_sexo' => 'Feminino',
            'dest_idade' => '18',
            'rem_idade' => '18',
            'dest_nascimento' => '01/01/2000',
            'rem_nascimento' => '01/01/2000',
            'dest_info' => 'Informações adicionais do atleta A',
            'rem_info' => 'Informações adicionais do atleta B',
            'categoria' => 'Categoria A',
            'observacoes' => 'Observações adicionais',
            'ano_atual' => date('Y'),
        ];

        foreach ($templateData as $key => $value) {
            $this->assertStringContainsString('{{ ' . $key . ' }}', $mail->getBody());
            $this->assertStringNotContainsString($value, $mail->getBody());
        }

        $mail->fillTemplate($templateData);

        foreach ($templateData as $key => $value) {
            $this->assertStringContainsString($value, $mail->getBody());
            $this->assertStringNotContainsString($key, $mail->getBody());
        }
    }
}
