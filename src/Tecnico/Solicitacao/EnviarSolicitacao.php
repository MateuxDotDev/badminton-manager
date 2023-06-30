<?php

namespace App\Tecnico\Solicitacao;

use App\Competicoes\CompeticaoRepositoryInterface;
use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepositoryInterface;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Atleta\TipoDupla;
use App\Tecnico\Dupla\DuplaRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\HttpStatus;
use App\Util\Mail\Service\MailServiceInterface;
use Exception;
use PDO;

readonly class EnviarSolicitacao
{
    public function __construct(
        private PDO $pdo,
        private UserSession $session,
        private CompeticaoRepositoryInterface $competicoes,
        private SolicitacaoPendenteRepositoryInterface $solicitacoes,
        private NotificacaoRepositoryInterface $notificacoes,
        private DuplaRepository $duplas,
        private MailServiceInterface $mailService,
    ) {}

    private function getAtleta(int $idCompeticao, int $idAtleta): ?array
    {
        $sql = <<<SQL
                  SELECT a.sexo
                       , a.nome_completo
                       , a.tecnico_id
                       , jsonb_agg(acc.categoria_id) as categorias
                       , jsonb_agg(acs.sexo_dupla) as sexo_dupla
                    FROM atleta_competicao ac
                    JOIN atleta a
                      ON a.id = ac.atleta_id
            NATURAL JOIN atleta_competicao_categoria acc
            NATURAL JOIN atleta_competicao_sexo_dupla acs
                   WHERE ac.atleta_id = :idAtleta
                     AND ac.competicao_id = :idCompeticao
                GROUP BY a.id, ac.atleta_id, ac.competicao_id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'idCompeticao' => $idCompeticao,
            'idAtleta'     => $idAtleta
        ]);

        $rows = $stmt->fetchAll();

        if (count($rows) != 1) {
            return null;
        }
        $row = $rows[0];

        return [
            'sexo'       => Sexo::from($row['sexo']),
            'nome'       => $row['nome_completo'],
            'idTecnico'  => (int) $row['tecnico_id'],
            'categorias' => json_decode($row['categorias'], true),
            'sexoDupla'  => array_map(fn($s) => Sexo::from($s), json_decode($row['sexo_dupla'], true)),
        ];
    }

    /**
     * @throws ValidatorException
     */
    private function validarCompatibilidade(array $a, array $b, int $categoria): void
    {
        $sexoOk = in_array($a['sexo'], $b['sexoDupla'])
               && in_array($b['sexo'], $a['sexoDupla']);

        $categoriaOk = in_array($categoria, $a['categorias'])
                    && in_array($categoria, $b['categorias']);

        $tecnicoOk = $a['idTecnico'] != $b['idTecnico'];

        $erro = null;
        if (!$sexoOk) {
            $tipo = TipoDupla::criar($a['sexo'], $b['sexo']);
            $erro = 'Um dos atletas não precisa formar dupla ' . $tipo->toString();
        } elseif (!$categoriaOk) {
            $erro = 'Não jogam ambos na categoria selecionada';
        } elseif (!$tecnicoOk) {
            $erro = 'Ambos têm o mesmo técnico';
        }

        if ($erro != null) {
            throw new ValidatorException("Atletas não são compatíveis: $erro");
        }
    }

    private function descricaoCategoria(int $id): string|false
    {
        $sql = <<<SQL
            SELECT descricao FROM categoria WHERE id = :id
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn(0);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function __invoke(EnviarSolicitacaoDTO $dto): void
    {
        $competicao = $this->competicoes->getViaId($dto->idCompeticao);
        if ($competicao == null) {
            throw new ValidatorException('Competição não encontrada', HttpStatus::NOT_FOUND);
        }
        if ($competicao->prazoPassou()) {
            throw new ValidatorException('O prazo da competição já passou');
        }

        $idRemetente    = $dto->idAtletaRemetente;
        $idDestinatario = $dto->idAtletaDestinatario;
        $idCategoria    = $dto->idCategoria;

        $remetente = $this->getAtleta($competicao->id(), $idRemetente);
        if ($remetente == null) {
            throw new ValidatorException('Atleta não encontrado (id '.$idRemetente.')', HttpStatus::NOT_FOUND);
        }

        $tecnicoLogado = $this->session->getTecnico();

        if ($remetente['idTecnico'] != $tecnicoLogado->id()) {
            $erro = 'Técnico não autorizado a enviar solicitações envolvendo o atleta de ID '.$idRemetente;
            throw new ValidatorException($erro, HttpStatus::FORBIDDEN);
        }

        $destinatario = $this->getAtleta($competicao->id(), $idDestinatario);
        if ($destinatario == null) {
            throw new ValidatorException('Atleta não encontrado (id '.$idDestinatario.')', HttpStatus::NOT_FOUND);
        }

        $this->validarCompatibilidade($remetente, $destinatario, $idCategoria);

        $existente = $this->solicitacoes->getViaIds($competicao->id(), $idRemetente, $idDestinatario, $idCategoria);
        if ($existente != null) {
            throw new ValidatorException('Essa solicitação já foi enviada');
        }

        $descricaoCategoria = $this->descricaoCategoria($idCategoria);
        if (!$descricaoCategoria) {
            throw new ValidatorException('Categoria inválida');
        }
        $tipoDupla = TipoDupla::criar($remetente['sexo'], $destinatario['sexo'])->toString();
        $descricaoDupla = $tipoDupla.' '.$descricaoCategoria;

        $destinatarioIndisponivel = $this->duplas->temDupla(
            $competicao->id(),
            $idDestinatario,
            $idCategoria,
            $remetente['sexo'],
        );
        if ($destinatarioIndisponivel) {
            throw new ValidatorException('O atleta '.$destinatario['nome'].' já formou tem uma dupla '.$descricaoDupla);
        }

        $remetenteIndisponivel = $this->duplas->temDupla(
            $competicao->id(),
            $idRemetente,
            $idCategoria,
            $destinatario['sexo'],
        );
        if ($remetenteIndisponivel) {
            throw new ValidatorException('O seu atleta '.$remetente['nome'].' já tem uma dupla '.$descricaoDupla);
        }

        $idSolicitacao = $this->solicitacoes->enviar($dto);

        $notificacoes = [];

        $notificacoes[] = Notificacao::solicitacaoEnviada($remetente['idTecnico'], $idSolicitacao);
        $notificacoes[] = Notificacao::solicitacaoRecebida($destinatario['idTecnico'], $idSolicitacao);

        foreach ($notificacoes as $notificacao) {
            $notificacao->setId($this->notificacoes->criar($notificacao));
            $this->mailService->enviarDeNotificacao($notificacao);
        }
    }
}
