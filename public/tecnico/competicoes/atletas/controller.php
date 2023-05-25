<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Tecnico\Atleta\PesquisaAtleta;
use App\Tecnico\Atleta\Sexo;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\Request;
use App\Util\Http\Response;

try {
    atletaCompeticaoController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

/**
 * @throws \Exception
 */
function atletaCompeticaoController(): Response
{
    $req = Request::getDados();
    $acao = Request::getAcao($req);

    return match ($acao) {
        'pesquisar' => pesquisarAtletas($req),
        default => Response::erro("Ação desconhecida: '$acao'")
    };
}

// TODO mover para AtletaCompeticaoRepository quando tiver

function pesquisarAtletas($req): Response
{
    $pdo   = Connection::getInstance();
    $dados = PesquisaAtleta::parse($req);

    // TODO fazer como enum também
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


    $pesquisaTermos = function (string $coluna, string $texto) use (&$condicoes, &$parametros): void {
        $termos = preg_split('/\s+/', $texto);
        foreach ($termos as $termo) {
            $condicoes[]  = $coluna.' ILIKE ?';
            $parametros[] = '%' . $termo . '%';
        }
    };

    if ($dados->nomeAtleta  != null) $pesquisaTermos('a.nome_completo', $dados->nomeAtleta);
    if ($dados->nomeTecnico != null) $pesquisaTermos('t.nome_completo', $dados->nomeTecnico);
    if ($dados->clube       != null) $pesquisaTermos('c.nome',          $dados->clube);


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

    $pesquisarIn('cat.id', $dados->idCategorias);
    $pesquisarIn('a.sexo', array_map(fn(Sexo $s): string => $s->value, $dados->sexoAtleta));
    $pesquisarIn('acs.sexo_dupla', array_map(fn(Sexo $s): string => $s->value, $dados->sexoDupla));


    $where = implode(' AND ', $condicoes);

    $limit     = $dados->limit;
    $offset    = $dados->offset;


    // TODO ainda falta testar [[cada um]] dos filtros + ordenação + paginação


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
                 ac.alterado_em
            from atleta a
            join atleta_competicao ac on ac.atleta_id = a.id
            join tecnico t on t.id = a.tecnico_id
            join clube clu on clu.id = t.clube_id
            join atleta_competicao_categoria acc on (acc.atleta_id, acc.competicao_id) = (ac.atleta_id, ac.competicao_id)
            join categoria cat on cat.id = acc.categoria_id
            join atleta_competicao_sexo_dupla acs on (acs.atleta_id, acs.competicao_id) = (ac.atleta_id, ac.competicao_id)
           where $where
        group by ac.competicao_id, ac.atleta_id, a.id, t.id, clu.id
        order by $colunaOrdenacao $ordenacaoString
           limit $limit offset $offset
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
            'tecnico' => [
                'id' => $row['tecnico_id'],
                'nome' => $row['tecnico_nome_completo'],
                'clube' => [
                    'id' => $row['clube_id'],
                    'clube' => $row['clube_nome'],
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