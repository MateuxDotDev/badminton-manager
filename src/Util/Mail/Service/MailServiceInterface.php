<?php

namespace App\Util\Mail\Service;

use App\Notificacao\Notificacao;

interface MailServiceInterface
{
    function enviarDeNotificacao(Notificacao $notificacao): void;
}
