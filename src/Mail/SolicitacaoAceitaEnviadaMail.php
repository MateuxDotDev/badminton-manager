<?php

namespace App\Mail;

use App\Util\Mail\MailerInterface;

class SolicitacaoAceitaEnviadaMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer, string $destinatario, string $remetente, string $competicao)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/solicitacao-aceita-enviada.html'),
            'A solicitação de dupla entre o seu atleta ' . $destinatario . ' e o atleta ' . $remetente . ' para a competição ' . $competicao . ' foi aceita! '
        );
    }
}
