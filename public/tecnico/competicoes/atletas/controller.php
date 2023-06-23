<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Mail\MailRepositoryInterface;
use App\Mail\NovaSolicitacaoMail;
use App\Notificacao\NotificacaoRepository;
use App\Competicoes\PesquisaAtletaCompeticao;
use App\Notificacao\TipoNotificacao;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Solicitacao\EnviarSolicitacao;
use App\Tecnico\Solicitacao\EnviarSolicitacaoDTO;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;
use App\Util\Mail\Mailer;

try {
    atletaCompeticaoController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

/**
 * @throws Exception
 */
function atletaCompeticaoController(): Response
{
    $req = Request::getDados();
    $acao = Request::getAcao($req);

    return match ($acao) {
        'pesquisar' => pesquisarAtletas($req),
        'enviarSolicitacao' => enviarSolicitacao($req),
        default => Response::erro("Ação desconhecida: '$acao'")
    };
}

// TODO mover para AtletaCompeticaoRepository quando tiver

function pesquisarAtletas($req): Response
{
    $pdo   = Connection::getInstance();
    $dados = PesquisaAtletaCompeticao::parse($req);

    $colunaOrdenacao = match($dados->colunaOrdenacao) {
        'nomeAtleta'    => 'a.nome_completo',
        'nomeTecnico'   => 't.nome_completo',
        'clube'         => 'clu.nome',
        'idade'         => 'a.data_nascimento',
        'dataAlteracao' => 'ac.criado_em',
        default         => null,
    };
    if ($colunaOrdenacao === null) {
        throw new ValidatorException('Coluna de ordenação inválida');
    }


    $ordenacao = $dados->ordenacao;
    // maior idade = menor data de nascimento
    if ($dados->colunaOrdenacao == 'idade') {
        $ordenacao = $ordenacao->inversa();
    }
    $ordenacaoString = $ordenacao->value;


    $condicoes  = [];
    $parametros = [];


    $condicoes[]  = 'ac.competicao_id = ?';
    $parametros[] = $dados->idCompeticao;


    $session = UserSession::obj();
    $tecnico = $session->getTecnico();

    // Não mostrar os atletas do próprio técnico
    $condicoes[]  = 'a.tecnico_id != ?';
    $parametros[] = $tecnico->id();


    $pesquisarTermos = function (string $coluna, string $texto) use (&$condicoes, &$parametros): void {
        $termos = preg_split('/\s+/', $texto);
        foreach ($termos as $termo) {
            $condicoes[]  = $coluna.' ILIKE ?';
            $parametros[] = '%' . $termo . '%';
        }
    };

    if ($dados->nomeAtleta  != null) $pesquisarTermos('a.nome_completo', $dados->nomeAtleta);
    if ($dados->nomeTecnico != null) $pesquisarTermos('t.nome_completo', $dados->nomeTecnico);
    if ($dados->clube       != null) $pesquisarTermos('clu.nome',        $dados->clube);


    $colunaIdade = 'extract(year from age(a.data_nascimento))';

    if ($dados->idadeMaiorQue != null) {
        $condicoes[]  = $colunaIdade . ' >= ?';
        $parametros[] = $dados->idadeMaiorQue;
    }

    if ($dados->idadeMenorQue != null) {
        $condicoes[]  = $colunaIdade . ' <= ?';
        $parametros[] = $dados->idadeMenorQue;
    }


    $pesquisarIn = function (string $coluna, array $valores) use (&$condicoes, &$parametros) {
        if (empty($valores)) return;
        $condicoes[] = $coluna . ' in (' . implode(',', array_fill(0, count($valores), '?')) . ')';
        foreach ($valores as $valor) {
            $parametros[] = $valor;
        }
    };

    $pesquisarIn('acc_filtrar.categoria_id', $dados->idCategorias);
    $pesquisarIn('a.sexo', array_map(fn(Sexo $s): string => $s->value, $dados->sexoAtleta));
    $pesquisarIn('acs_filtrar.sexo_dupla', array_map(fn(Sexo $s): string => $s->value, $dados->sexoDupla));


    $where = implode(' AND ', $condicoes);


    $sql = <<<SQL
          select a.id,
                 a.nome_completo,
                 a.data_nascimento,
                 a.sexo,
                 extract(year from age(a.data_nascimento)) as idade,
                 t.id as tecnico_id,
                 t.nome_completo as tecnico_nome_completo,
                 clu.id as clube_id,
                 clu.nome as clube_nome,
                 jsonb_agg(distinct
                    jsonb_build_object(
                        'id', cat.id,
                        'descricao', cat.descricao
                    )
                 ) as categorias,
                 jsonb_agg(distinct acs.sexo_dupla) as sexo_dupla,
                 ac.alterado_em,
                 a.informacoes as informacoes_atleta,
                 t.informacoes as informacoes_tecnico,
                 ac.informacoes as informacoes_atleta_competicao,
                 a.path_foto
            from atleta a
            join atleta_competicao ac on ac.atleta_id = a.id
            join tecnico t on t.id = a.tecnico_id
            join clube clu on clu.id = t.clube_id
            join atleta_competicao_categoria acc_filtrar on (acc_filtrar.atleta_id, acc_filtrar.competicao_id) = (ac.atleta_id, ac.competicao_id)
            join atleta_competicao_categoria acc on (acc.atleta_id, acc.competicao_id) = (ac.atleta_id, ac.competicao_id)
            join categoria cat on cat.id = acc.categoria_id
            join atleta_competicao_sexo_dupla acs_filtrar on (acs_filtrar.atleta_id, acs_filtrar.competicao_id) = (ac.atleta_id, ac.competicao_id)
            join atleta_competicao_sexo_dupla acs on (acs.atleta_id, acs.competicao_id) = (ac.atleta_id, ac.competicao_id)
           where $where
        group by ac.competicao_id, ac.atleta_id, a.id, t.id, clu.id
        order by $colunaOrdenacao $ordenacaoString
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    $resultados = [];

    while ($row = $stmt->fetch()) {
        $categorias = json_decode($row['categorias'], true);
        $sexoDupla  = json_decode($row['sexo_dupla'], true);

        $resultados[] = [
            'id' => $row['id'],
            'nome' => $row['nome_completo'],
            'dataNascimento' => $row['data_nascimento'],
            'idade' => $row['idade'],
            'sexo' => $row['sexo'],
            'pathFoto' => $row['path_foto'],
            'informacoes' => $row['informacoes_atleta'],
            'informacoesCompeticao' => $row['informacoes_atleta_competicao'],
            'tecnico' => [
                'id' => $row['tecnico_id'],
                'nome' => $row['tecnico_nome_completo'],
                'informacoes' => $row['informacoes_tecnico'],
                'clube' => [
                    'id' => $row['clube_id'],
                    'nome' => $row['clube_nome'],
                ],
            ],
            // alteração do cadastro do atleta na competição, não do atleta em si
            'dataAlteracao' => $row['alterado_em'],
            'categorias' => $categorias,
            'sexoDupla' => $sexoDupla,
        ];
    }

    return Response::ok('Busca realizada com sucesso', ['resultados' => $resultados]);
}

/**
 * @throws ValidatorException
 */
function enviarSolicitacao(array $req): Response
{
    $dto = EnviarSolicitacaoDTO::parse($req);

    $pdo = Connection::getInstance();

    $session = UserSession::obj();

    $competicoesRepo  = new CompeticaoRepository($pdo);
    $solicitacoesRepo = new SolicitacaoPendenteRepository($pdo);
    $notificacoesRepo = new NotificacaoRepository($pdo);
    $mailRepo = new MailRepository($pdo);
    $tecnicoRepo = new TecnicoRepository($pdo);
    $atletaRepo = new AtletaRepository($pdo);
    $categoriaRepo = new CategoriaRepository($pdo);

    try {
        $pdo->beginTransaction();

        $enviar = new EnviarSolicitacao($pdo, $session, $competicoesRepo, $solicitacoesRepo, $notificacoesRepo);
        $id = $enviar($dto);

        $atletaDest = $atletaRepo->getViaId($dto->idAtletaDestinatario);
        $atletaRem = $atletaRepo->getViaId($dto->idAtletaRemetente);
        $tecnicoDest = $tecnicoRepo->getViaAtleta($atletaDest->id());
        $tecnicoRem = $tecnicoRepo->getViaAtleta($atletaRem->id());
        $categoria = $categoriaRepo->getCategoriaById($dto->idCategoria);
        $notificacoes = $notificacoesRepo->getViaId1($id, TipoNotificacao::SOLICITACAO_ENVIADA);
        $notificacaoIdRem = 0;
        foreach ($notificacoes as $notificacao) {
            if ($notificacao['tecnico_id'] == $tecnicoRem->id()) {
                $notificacaoIdRem = $notificacao['id'];
                break;
            }
        }
        $competicao = $competicoesRepo->getViaId($dto->idCompeticao);

        $mail = new NovaSolicitacaoMail(new Mailer());
        $mail->fillTemplate([
            'dest_tecnico' => $tecnicoDest->nomeCompleto(),
            'competicao' => $competicao->nome(),
            'rem_nome' => $atletaRem->nomeCompleto(),
            'dest_nome' => $atletaDest->nomeCompleto(),
            'dest_sexo' => $atletaDest->sexo()->toString(),
            'rem_sexo' => $atletaRem->sexo()->toString(),
            'dest_idade' => $atletaDest->idade(),
            'rem_idade' => $atletaRem->idade(),
            'dest_nascimento' => $atletaDest->dataNascimento()->format('d/m/Y'),
            'rem_nascimento' => $atletaRem->dataNascimento()->format('d/m/Y'),
            'dest_info' => $atletaDest->informacoesAdicionais(),
            'rem_info' => $atletaRem->informacoesAdicionais(),
            'categoria' => $categoria->descricao(),
            'observacoes' => $dto->informacoes,
            'rem_tec_nome' => $tecnicoRem->nomeCompleto(),
            'rem_tec_clube' => $tecnicoRem->clube()->nome(),
            'rem_tec_info' => $tecnicoRem->informacoes(),
            'rem_tec_email' => $tecnicoRem->email(),
            'link_aceite' => 'TODO',
            'link_rejeicao' => 'TODO',
            'ano_atual' => date('Y'),
        ]);

        $mailDto = new EmailDTO(
            $tecnicoDest->nomeCompleto(),
            $tecnicoDest->email(),
            $mail->getSubject(),
            $mail->getBody(),
            $mail->getAltBody(),
            $notificacaoIdRem
        );
        $mailRepo->criar($mailDto);

        $pdo->commit();
        return Response::ok('Solicitação enviada com sucesso', ['id' => $id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
