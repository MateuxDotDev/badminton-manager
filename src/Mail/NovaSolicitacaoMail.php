<?php

namespace App\Mail;

use App\Util\Exceptions\MailException;
use App\Util\Mail\MailerInterface;

class NovaSolicitacaoMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/nova-solicitacao.html'),
            'Você recebeu uma nova solicitação de Dupla!'
        );
    }
}
