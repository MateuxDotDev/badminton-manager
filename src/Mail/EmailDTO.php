<?php

namespace App\Mail;

use App\Util\Traits\TemDataCriacao;
use App\Util\Traits\TemUuid;

class EmailDTO
{
    use TemDataCriacao, TemUuid;

    public function __construct(
        public string $toName,
        public string $toEmail,
        public string $subject,
        public string $body,
        public string $altBody,
        public ?int $idNotificacao = null,
    ) {}
}
