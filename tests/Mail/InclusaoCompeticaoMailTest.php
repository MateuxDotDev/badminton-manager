<?php

namespace Tests\Mail;

use App\Mail\InclusaoCompeticaoMail;
use App\Util\Exceptions\MailException;
use App\Util\Mail\MailerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InclusaoCompeticaoMailTest extends TestCase
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
        $atleta = "John Doe";
        $competicao = "Campeonato Nacional";
        $mail = new InclusaoCompeticaoMail($this->mailerMock, $atleta, $competicao);

        $this->assertInstanceOf(InclusaoCompeticaoMail::class, $mail);
    }

    /**
     * @throws MailException
     */
    public function testSend()
    {
        $atleta = 'John Doe';
        $competicao = 'Campeonato Nacional';
        $mail = new InclusaoCompeticaoMail($this->mailerMock, $atleta, $competicao);
        $subject = 'Você incluiu recentemente o atleta ' . $atleta . ' na competição ' . $competicao . '!';

        $toEmail = 'test@example.com';
        $toName = 'Test User';
        $altBody = 'Test alternative body';

        $this->mailerMock
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                $this->equalTo($toEmail),
                $this->equalTo($toName),
                $this->equalTo($subject),
                $this->callback(function ($body) {
                    $this->assertStringContainsString('Você incluiu recentemente o atleta', $body);
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

    public function testFillTemplate()
    {
        $atleta = "John Doe";
        $competicao = "Campeonato Nacional";
        $mail = new InclusaoCompeticaoMail($this->mailerMock, $atleta, $competicao);

        $templateData = [
            'nome_tecnico' => 'Marina Souza',
            'nome_atleta' => 'Rodrigo Almeida',
            'nome_competicao' => 'Campeonato Brasileiro',
            'convite_atleta' => 'Bia Santos',
            'convite_clube' => 'Clube Maranhense',
            'convite_tecnico' => 'Roberto Carlos',
            'convite_sexo' => 'Feminino',
            'convite_observacoes' => 'Competidora altamente habilidosa',
            'link_alterar' =>  'http://example.com/tecnico/atletas/index.php?id=1&acao=alterar&token=token123',
            'link_remover' => 'http://example.com/tecnico/atletas/index.php?id=1&acao=remover&token=token123',
            'link_buscar' => 'http://example.com/tecnico/competicoes/',
            'ano_atual' => date('Y')
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
