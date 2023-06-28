<?php

namespace App\Mail;

use App\Util\Mail\MailerInterface;

class InclusaoCompeticaoMail extends MailTemplate
{
    public function __construct(MailerInterface $mailer, string $atleta, string $competicao)
    {
        parent::__construct(
            $mailer,
            file_get_contents(__DIR__ . '/../Util/Mail/Template/inclusao-competicao.html'),
            'Você incluiu recentemente o atleta ' . $atleta . ' na competição ' . $competicao . '!'
        );
    }
}
