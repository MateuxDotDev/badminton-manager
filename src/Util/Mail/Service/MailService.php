<?php

namespace App\Util\Mail\Service;

use App\Notificacao\Notificacao;
use App\Notificacao\TipoNotificacao;
use App\Util\Mail\Service\Actions\MailActionInterface;
use App\Util\Mail\Service\Actions\MailSolicitacaoCanceladaAction;
use PDO;

class MailService implements MailServiceInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws \Exception
     */
    function enviarDeNotificacao(Notificacao $notificacao): void
    {
        /** @var  $action MailActionInterface */
        $action = null;

        if ($notificacao->tipo == TipoNotificacao::SOLICITACAO_ENVIADA_CANCELADA) {
            $action = new MailSolicitacaoCanceladaAction();
        }

        $action->enviarDeNotificacao($notificacao, $this->pdo);
    }
}
