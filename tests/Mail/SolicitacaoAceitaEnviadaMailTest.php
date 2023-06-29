<?php

namespace Tests\Mail;

use App\Mail\SolicitacaoAceitaEnviadaMail;
use App\Mail\SolicitacaoAceitaRecebidaMail;
use App\Util\Exceptions\MailException;
use App\Util\General\Dates;
use App\Util\Mail\MailerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SolicitacaoAceitaEnviadaMailTest extends TestCase
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
        $mail = new SolicitacaoAceitaEnviadaMail($this->mailerMock, 'Atleta A', 'Atleta B', 'Competição A');
        $this->assertInstanceOf(SolicitacaoAceitaEnviadaMail::class, $mail);
    }

    /**
     * @throws MailException
     */
    public function testSend()
    {
        $atletaDest = 'Atleta A';
        $atletaRem = 'Atleta B';
        $competicao = 'Competição A';

        $mail = new SolicitacaoAceitaEnviadaMail($this->mailerMock, $atletaDest, $atletaRem, $competicao);

        $toEmail = 'test@example.com';
        $toName = $atletaDest;
        $subject = 'A solicitação de dupla entre o seu atleta Atleta A e o atleta Atleta B para a competição Competição A foi aceita!';
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
                    $this->assertStringContainsString('A solicitação de dupla entre o seu atleta {{ dest_nome }} e o atleta {{ rem_nome }} para a competição {{ competicao }} foi aceita! Aqui estão mais detalhes:', $body);
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
        $mail = new SolicitacaoAceitaEnviadaMail($this->mailerMock, $atletaDest, $atletaRem, $competicao);

        $templateData = [
            'dest_tecnico' => 'Técnico A',
            'dest_nome' => $atletaDest,
            'rem_nome' => $atletaRem,
            'competicao' => $competicao,
            'dest_sexo' => 'Masculino',
            'rem_sexo' => 'Feminino',
            'dest_idade' => '18',
            'rem_idade' => '18',
            'dest_nascimento' => '20/02/2001',
            'rem_nascimento' => '17/09/1994',
            'dest_info' => 'Informações adicionais do atleta A',
            'rem_info' => 'Informações adicionais do atleta B',
            'categoria' => 'Categoria A',
            'link_desfazer' => 'http://localhost:8080/desfazer-solicitacao/1234567890',
            'ano_atual' => Dates::currentYear(),
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
