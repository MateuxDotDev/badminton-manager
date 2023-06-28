<?php

namespace App\Mail;

use App\Util\Mail\MailerInterface;

class SolicitacaoCanceladaMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer, string $atletaDest, string $ateltaRem, string $competicao)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/solicitacao-cancelada.html'),
            'A solicitação de formar dupla com o seu atleta ' . $atletaDest . '  e ' . $ateltaRem . '  na competição ' . $competicao . ' foi cancelada!'
        );
    }
}
