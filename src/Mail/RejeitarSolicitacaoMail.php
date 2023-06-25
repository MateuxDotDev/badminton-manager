<?php

namespace App\Mail;

use App\Util\Mail\MailerInterface;

class RejeitarSolicitacaoMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/rejeitar-solicitacao.html'),
            'Uma solicitação de dupla sua foi rejeitada.'
        );
    }
}
