<?php

namespace App\Tecnico\Solicitacao;

use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Atleta\TipoDupla;
use App\Tecnico\Dupla\DuplaRepository;
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
        private DuplaRepository $duplaRepo,
    ) {}

    private function getSolicitacao(int $id): array
    {
        $sql = <<<SQL
            SELECT s.id               AS id
                 , ori.id             AS atleta_origem_id
                 , ori.sexo           AS atleta_origem_sexo
                 , ori.nome_completo  AS atleta_origem_nome
                 , dest.id            AS atleta_destino_id
                 , dest.sexo          AS atleta_destino_sexo
                 , dest.nome_completo AS atleta_destino_nome
                 , ori.tecnico_id     AS tecnico_origem_id
                 , dest.tecnico_id    AS tecnico_destino_id
                 , s.categoria_id     AS categoria_id
                 , cat.descricao      AS categoria_descricao
                 , comp.id            AS competicao_id
                 , comp.prazo         AS competicao_prazo
              FROM solicitacao_dupla_pendente s
              JOIN atleta ori  ON ori.id  = s.atleta_origem_id
              JOIN atleta dest ON dest.id = s.atleta_destino_id
              JOIN competicao comp ON comp.id = s.competicao_id
              JOIN categoria cat ON cat.id = s.categoria_id
             WHERE s.id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();
        return empty($rows) ? [] : $rows[0];
    }

    /**
     * @throws ValidatorException
     */
    private function getSolicitacao404(int $id): array
    {
        $solicitacao = $this->getSolicitacao($id);
        if (empty($solicitacao)) {
            throw new ValidatorException("Não encontramos solicitação pendente de id $id", HttpStatus::NOT_FOUND);
        }
        return $solicitacao;
    }

    /**
     * @throws ValidatorException
     */
    private function validarPrazo(array $solicitacao): void
    {
        $prazo = Dates::parseDay($solicitacao['competicao_prazo']);
        if ($prazo === null) {
            throw new ValidatorException(
                'Erro interno: prazo da competição é inválido',
                HttpStatus::INTERNAL_SERVER_ERROR
            );
        }

        $prazoPassou = $this->dataAgora->getTimestamp() >= $prazo->getTimestamp();
        if ($prazoPassou) {
            throw new ValidatorException('O prazo da competição já passou, duplas não podem mais ser formadas');
        }
    }

    /**
     * @throws ValidatorException
     */
    public function rejeitar(int $idPendente): void
    {
        $solicitacao = $this->getSolicitacao404($idPendente);

        $idTecnicoLogado = $this->session->getTecnico()->id();
        if ($idTecnicoLogado != $solicitacao['tecnico_destino_id']) {
            throw new ValidatorException('Você não está autorizado a rejeitar essa solicitação', HttpStatus::FORBIDDEN);
        }

        $this->validarPrazo($solicitacao);

        $idConcluida = $this->concluidaRepo->concluirRejeitada($idPendente);

        $notificacoes = [
            Notificacao::solicitacaoRecebidaRejeitada($solicitacao['tecnico_destino_id'], $idConcluida),
            Notificacao::solicitacaoEnviadaRejeitada($solicitacao['tecnico_origem_id'], $idConcluida),
        ];
        foreach ($notificacoes as $notificacao) {
            $this->notificacaoRepo->criar($notificacao);
        }
    }

    /**
     * @throws ValidatorException
     */
    public function cancelar(int $id): void
    {
        $solicitacao = $this->getSolicitacao404($id);

        $idTecnicoLogado = $this->session->getTecnico()->id();
        if ($solicitacao['tecnico_origem_id'] != $idTecnicoLogado) {
            throw new ValidatorException(
                'Você não tem autorização para cancelar essa solicitação',
                HttpStatus::FORBIDDEN
            );
        }

        $this->validarPrazo($solicitacao);

        $this->concluidaRepo->concluirCancelada($id);
    
        // Sem notificações mesmo, pra economizar tempo de desenvolvimento
    }


    private function getSolicitacoesParaCancelar(array $aceita): array
    {

        $sql = <<<SQL
            SELECT s.id
                 , ori.id          as atleta_origem_id
                 , dest.id         as atleta_destino_id
                 , ori.tecnico_id  as tecnico_origem_id
                 , dest.tecnico_id as tecnico_destino_id
              FROM solicitacao_dupla_pendente s
              JOIN atleta ori
                ON ori.id = s.atleta_origem_id
              JOIN atleta dest
                ON dest.id = s.atleta_destino_id
             WHERE s.id               != :aceita_id
               AND s.competicao_id     = :competicao_id
               AND s.categoria_id      = :categoria_id
               AND (s.atleta_destino_id IN (:ori_id, :dest_id) OR
                    s.atleta_origem_id  IN (:ori_id, :dest_id))
               AND ((ori.sexo = :ori_sexo  AND dest.sexo = :dest_sexo) OR
                    (ori.sexo = :dest_sexo AND dest.sexo = :ori_sexo))
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'aceita_id'     => $aceita['id'],
            'competicao_id' => $aceita['competicao_id'],
            'categoria_id'  => $aceita['categoria_id'],
            'ori_id'        => $aceita['atleta_origem_id'],
            'ori_sexo'      => $aceita['atleta_origem_sexo'],
            'dest_id'       => $aceita['atleta_destino_id'],
            'dest_sexo'     => $aceita['atleta_destino_sexo'],
        ]);

        return $stmt->fetchAll();
    }

    /**
     * @throws ValidatorException
     */
    public function aceitar(int $idPendente): void
    {
        $pendente = $this->getSolicitacao404($idPendente);

        $idTecnicoLogado = $this->session->getTecnico()->id();
        if ($idTecnicoLogado != $pendente['tecnico_destino_id']) {
            throw new ValidatorException(
                'Você não tem autorização para aceitar essa solicitação',
                HttpStatus::FORBIDDEN
            );
        }

        $this->validarPrazo($pendente);


        // A princípio não deveria acontecer de um deles estar indisponível
        // porque quando uma solicitação é aceita e a dupla é formada, as outras solicitações no mesmo
        // "esquema" (tipo e categoria) são canceladas. Mesmo assim fazemos a validação para garantir.

        $tipoDupla = TipoDupla::criar(
            Sexo::from($pendente['atleta_origem_sexo']),
            Sexo::from($pendente['atleta_destino_sexo'])
        )->toString();
        $descricaoDupla = $tipoDupla.' '.$pendente['categoria_descricao'];

        $destinatarioIndisponivel = $this->duplaRepo->temDupla(
            $pendente['competicao_id'],
            $pendente['atleta_destino_id'],
            $pendente['categoria_id'],
            Sexo::from($pendente['atleta_origem_sexo'])
        );
        if ($destinatarioIndisponivel) {
            throw new ValidatorException(
                'O seu atleta '.$pendente['atleta_destino_nome'].' já formou uma dupla '.$descricaoDupla
            );
        }

        $remetenteIndisponivel = $this->duplaRepo->temDupla(
            $pendente['competicao_id'],
            $pendente['atleta_origem_id'],
            $pendente['categoria_id'],
            Sexo::from($pendente['atleta_destino_sexo']),
        );
        if ($remetenteIndisponivel) {
            throw new ValidatorException(
                'O atleta '.$pendente['atleta_origem_nome'].' já formou uma dupla '.$descricaoDupla
            );
        }


        $idConcluidaAceita = $this->concluidaRepo->concluirAceita($pendente['id']);

        $this->duplaRepo->criarDupla(
            $pendente['competicao_id'],
            $pendente['atleta_origem_id'],
            $pendente['atleta_destino_id'],
            $pendente['categoria_id'],
            $idConcluidaAceita
        );

        $pendentesParaCancelar = $this->getSolicitacoesParaCancelar($pendente);

        $notificacoes = [];

        foreach ($pendentesParaCancelar as $pendenteCancelar) {
            $idConcluidaCancelada = $this->concluidaRepo->concluirCancelada(
                $pendenteCancelar['id'],
                $idConcluidaAceita
            );

            $cancelouEnvioDeOutroTecnico = $pendenteCancelar['tecnico_origem_id'] != $pendente['tecnico_origem_id']
                                        && $pendenteCancelar['tecnico_origem_id'] != $pendente['tecnico_destino_id'];
            if ($cancelouEnvioDeOutroTecnico) {
                $notificacoes[] = Notificacao::solicitacaoEnviadaCancelada(
                    $pendenteCancelar['tecnico_origem_id'],
                    $idConcluidaCancelada
                );
            }
        }

        $notificacoes[] = Notificacao::solicitacaoEnviadaAceita($pendente['tecnico_origem_id'], $idConcluidaAceita);
        $notificacoes[] = Notificacao::solicitacaoRecebidaAceita($pendente['tecnico_destino_id'], $idConcluidaAceita);

        foreach ($notificacoes as $notificacao) {
            $this->notificacaoRepo->criar($notificacao);
        }
    }
}
