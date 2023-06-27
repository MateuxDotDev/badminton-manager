<?php

namespace App\Mail;

use App\Util\Mail\MailerInterface;

class SolicitacaoAceitaRecebidaMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer, string $destinatario, string $remetente, string $competicao)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/solicitaca-aceita-recebida.html'),
            'Voçê aceitou solicitação de dupla entre o seu atleta ' . $destinatario . ' e o atleta ' . $remetente . ' para a competição ' . $competicao . '!'
        );
    }
}
