<?php

namespace App\Mail;

use App\Util\Exceptions\MailException;
use App\Util\Mail\MailerInterface;

class NovaSolicitacaoMail
{
    private MailerInterface $mailer;
    private string $toName;
    private string $toEmail;
    private string $subject;
    private string $body;
    private array $data;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->body = file_get_contents(__DIR__ . '/../Util/Mail/Template/nova-solicitacao.html');
    }

    public function prepare(string $toName, string $toEmail, string $subject, array $data)
    {
        $this->toName = $toName;
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->data = $data;
    }

    public function renderHtml(): void
    {
        $this->put('assunto', $this->subject);
        $this->put('nome_tecnico', $this->data['nome_tecnico']);
        $this->put('convite_atleta', $this->data['convite_atleta']);
        $this->put('convite_tecnico', $this->data['convite_tecnico']);
        $this->put('convite_sexo', $this->data['convite_sexo']);
        $this->put('convite_categoria', $this->data['convite_categoria']);
        $this->put('convite_modalidade', $this->data['convite_observacoes']);
        $this->put('link_aceite', $this->data['link_aceite']);
        $this->put('link_recusa', $this->data['link_recusa']);
        $this->put('ano_atual', date('Y'));
    }

    private function put(string $key, string $value): void
    {
        $this->body = str_replace("{{ $key }}", $value, $this->body);
    }

    public function getAltBody(): string
    {
        return strip_tags($this->body);
    }

    /**
     * @throws MailException
     */
    public function send(): bool
    {
        return $this->mailer->sendEmail(
            $this->toEmail,
            $this->toName,
            $this->subject,
            $this->body,
            $this->getAltBody()
        );
    }
}
