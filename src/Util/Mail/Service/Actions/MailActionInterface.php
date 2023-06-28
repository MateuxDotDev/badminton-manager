<?php

namespace App\Util\Mail\Service\Actions;

use App\Notificacao\Notificacao;
use PDO;
use Exception;

interface MailActionInterface
{

    /**
     * @throws Exception
     */
    function enviarDeNotificacao(Notificacao $notificacao, PDO $pdo): void;
}
