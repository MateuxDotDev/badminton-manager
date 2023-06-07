<?php

namespace App\Tecnico\Solicitacao;

use App\Competicoes\CompeticaoRepository;
use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Atleta\AtletaEmCompeticao;
use App\Tecnico\Atleta\AtletaEmCompeticaoRepository;
use App\Tecnico\Atleta\ResultadoCompatibilidade as Compat;
use App\Tecnico\Atleta\TipoDupla;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\HttpStatus;

readonly class EnviarSolicitacao
{
    public function __construct(
        private UserSession $session,
        private CompeticaoRepository $competicoes,
        private AtletaEmCompeticaoRepository $atletasEmCompeticoes,
        private SolicitacaoPendenteRepository $solicitacoes,
        private NotificacaoRepository $notificacoes,
    ) {}

    public function __invoke(EnviarSolicitacaoDTO $dto): int
    {
        $competicoes = $this->competicoes;

        $competicao = $competicoes->getViaId($dto->idCompeticao);
        if ($competicao == null) {
            throw new ValidatorException('Competição não encontrada', HttpStatus::NOT_FOUND);
        }
        if ($competicao->prazoPassou()) {
            throw new ValidatorException('O prazo da competição já passou');
        }

        $atletasEmCompeticoes = $this->atletasEmCompeticoes;

        $idRemetente    = $dto->idAtletaRemetente;
        $idDestinatario = $dto->idAtletaDestinatario;
        $idCategoria    = $dto->idCategoria;

        $remetente = $atletasEmCompeticoes->get($idRemetente, $competicao->id());

        if ($remetente == null) {
            throw new ValidatorException('Atleta não encontrado (id '.$idRemetente.')', HttpStatus::NOT_FOUND);
        }

        $tecnicoLogado = $this->session->getTecnico();

        if ($remetente->idTecnico() != $tecnicoLogado->id()) {
            $erro = 'Técnico não autorizado a enviar solicitações envolvendo o atleta de ID '.$idRemetente;
            throw new ValidatorException($erro, HttpStatus::FORBIDDEN);
        }

        $destinatario = $atletasEmCompeticoes->get($idDestinatario, $competicao->id());
        if ($destinatario == null) {
            throw new ValidatorException('Atleta não encontrado (id '.$idDestinatario.')', HttpStatus::NOT_FOUND);
        }

        $compatibilidade = AtletaEmCompeticao::podemFormarDupla($remetente, $destinatario, $dto->idCategoria);
        if ($compatibilidade != Compat::OK) {
            $tipoDupla = TipoDupla::criar($remetente->sexoAtleta(), $destinatario->sexoAtleta());
            $mensagem = match($compatibilidade) {
                Compat::CATEGORIA_INCOMPATIVEL => 'Não jogam ambos na categoria selecionada',
                Compat::SEXO_INCOMPATIVEL => 'Um dos atletas não precisa formar dupla ' . $tipoDupla->toString(),
                Compat::MESMO_TECNICO => 'Ambos os atletas têm o mesmo técnico',
            };
            throw new ValidatorException('Atletas incompatíveis: ' . $mensagem);
        }

        $solicitacoes = $this->solicitacoes;

        $existente = $solicitacoes->getEnvolvendo($competicao->id(), $idRemetente, $idDestinatario, $idCategoria);
        if ($existente != null) {
            throw new ValidatorException('Já existe uma solicitação pendente envolvendo esses atletas e essa categoria na competição');
        }

        // TODO (em task futura): Validar se esses atletas realmente ainda precisam de dupla
        // (se um deles é homem e o outro já tem dupla masculina, então não dá pra formar dupla)
        // (porém se um deles já tem uma dupla de um sexo mas não do outro, e precisa de dupla do outro sexo, e o outro atleta é esse outro sexo, então ok)

        $idSolicitacao = $solicitacoes->enviar($dto);

        $notificacoes = $this->notificacoes;
        $notificacoes->criar(Notificacao::solicitacaoEnviada ($remetente->idTecnico(),    $idSolicitacao));
        $notificacoes->criar(Notificacao::solicitacaoRecebida($destinatario->idTecnico(), $idSolicitacao));

        return $idSolicitacao;
    }
}