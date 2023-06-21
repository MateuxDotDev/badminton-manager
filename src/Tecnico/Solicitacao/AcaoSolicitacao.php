<?php

namespace App\Tecnico\Solicitacao;

use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\General\UserSession;
use App\Util\Http\HttpStatus;
use \PDO;
use \DateTimeInterface;

readonly class AcaoSolicitacao
{
    public function __construct(
        private PDO $pdo,
        private UserSession $session,
        private DateTimeInterface $dataAgora,
        private NotificacaoRepository $notificacaoRepo,
        private SolicitacaoConcluidaRepository $concluidaRepo,
    ) {}

    private function getSolicitacao(int $id): array
    {
        $sql = <<<SQL
            SELECT ori.tecnico_id  as tecnico_origem_id
                 , dest.tecnico_id as tecnico_destino_id
                 , comp.prazo as competicao_prazo
              FROM solicitacao_dupla_pendente s
              JOIN atleta ori  ON ori.id  = s.atleta_origem_id
              JOIN atleta dest ON dest.id = s.atleta_destino_id
              JOIN competicao comp ON comp.id = s.competicao_id
             WHERE s.id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();
        return empty($rows) ? [] : $rows[0];
    }

    public function rejeitar(int $id): void
    {
        $solicitacao = $this->getSolicitacao($id);
        if (empty($solicitacao)) {
            throw new ValidatorException("Solicitação de id $id não encontrada", HttpStatus::NOT_FOUND);
        }

        $idTecnicoLogado = $this->session->getTecnico()->id();
        if ($idTecnicoLogado != $solicitacao['tecnico_destino_id']) {
            throw new ValidatorException('Você não está autorizado a rejeitar essa solicitação', HttpStatus::FORBIDDEN);
        }

        $prazo = Dates::parseDay($solicitacao['competicao_prazo']);
        if ($prazo === null) {
            throw new ValidatorException('Erro interno: prazo da competição é inválido');
        }

        $prazoPassou = $this->dataAgora->getTimestamp() >= $prazo->getTimestamp();
        if ($prazoPassou) {
            throw new ValidatorException('O prazo da competição já passou, duplas não podem mais ser formadas');
        }

        $this->concluidaRepo->concluirRejeitada($id);

        $notificacoes = [
            Notificacao::solicitacaoRecebidaRejeitada($solicitacao['tecnico_destino_id'], $id),
            Notificacao::solicitacaoEnviadaRejeitada($solicitacao['tecnico_origem_id'], $id),
        ];
        foreach ($notificacoes as $notificacao) {
            $this->notificacaoRepo->criar($notificacao);
        }
    }

    public function aceitar(int $id): void
    {
        throw new \Exception('TODO');
    }
}