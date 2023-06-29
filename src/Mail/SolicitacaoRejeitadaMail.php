<?php

namespace App\Mail;

use App\Util\Mail\MailerInterface;

class SolicitacaoRejeitadaMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/solicitacao-rejeitada.html'),
            'Uma solicitação de dupla sua foi rejeitada.'
        );
    }
}
