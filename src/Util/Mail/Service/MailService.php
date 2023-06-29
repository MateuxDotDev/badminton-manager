<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Util\Mail\Service;

use App\Notificacao\Notificacao;
use App\Notificacao\TipoNotificacao;
use App\Util\Mail\Service\Actions\MailNovaSolicitacaoAction;
use App\Util\Mail\Service\Actions\MailSolicitacaoAceitaRecebidaAction;
use App\Util\Mail\Service\Actions\MailSolicitacaoCanceladaAction;
use App\Util\Mail\Service\Actions\MailSolicitacaoEnviadaRecebidaAction;
use App\Util\Mail\Service\Actions\MailSolicitacaoRejeitadaAction;
use Exception;
use PDO;

class MailService implements MailServiceInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws Exception
     */
    public function enviarDeNotificacao(Notificacao $notificacao): void
    {
        switch ($notificacao->tipo) {
            case TipoNotificacao::SOLICITACAO_ENVIADA_CANCELADA:
                $action = new MailSolicitacaoCanceladaAction();
                break;
            case TipoNotificacao::SOLICITACAO_RECEBIDA_ACEITA:
                $action = new MailSolicitacaoAceitaRecebidaAction();
                break;
            case TipoNotificacao::SOLICITACAO_ENVIADA_ACEITA:
                $action = new MailSolicitacaoEnviadaRecebidaAction();
                break;
            case TipoNotificacao::SOLICITACAO_RECEBIDA:
                $action = new MailNovaSolicitacaoAction();
                break;
            case TipoNotificacao::SOLICITACAO_ENVIADA_REJEITADA:
                $action = new MailSolicitacaoRejeitadaAction();
                break;
            default:
                return;
        }

        $action->enviarDeNotificacao($notificacao, $this->pdo);
    }
}
