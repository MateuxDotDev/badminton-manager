<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Tecnico\{TecnicoRepository};
use App\Tecnico\Conta\Cadastrar;
use App\Tecnico\Conta\CadastroDTO;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\{Request, Response};

try {
    cadastroController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function cadastroController(): Response
{
    try {
        $req = Request::getDados();
        $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
        return match($acao) {
            'cadastro' => realizarCadastro($req),
            'pesquisarClubes' => pesquisarClubes($req),
            default => Response::erro("Ação '$acao' inválida")
        };
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

/**
 * @throws ValidatorException
 * @throws Exception
 */
function realizarCadastro(array $req): Response
{
    $pdo = Connection::getInstance();
    try {
        $pdo->beginTransaction();

        $dto = CadastroDTO::parse($req);
        $cadastrar = new Cadastrar(new TecnicoRepository(Connection::getInstance()));
        $ids = $cadastrar($dto);

        $pdo->commit();
        return Response::ok('Técnico cadastrado com sucesso', $ids);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}


function pesquisarClubes(array $req): Response
{
    // TODO trazer somente os 20 resultados mais similares
    // atualmente busca os 20 por ordem de qtd de técnicos
    // pode tb trazer só 10 ou outro numero

    $termos = [];
    if (!empty($req) && isset($req['termos'])) {
        $termos = $req['termos'];
    }
    $termos = array_filter(array_map('trim', $termos), fn($termo) => !empty($termo));
    if (empty($termos)) {
        return Response::erro('Informe pelo menos um termo de busca');
    }

    $condicoes  = array_fill(0, count($termos), 'nome ILIKE ?');
    $parametros = array_map(fn($t) => "%$t%", $termos);

    $where = implode(' AND ', $condicoes);

    $sql = <<<SQL
           SELECT c.id, c.nome, COUNT(t.id) qtd_tecnicos
             FROM clube c
        LEFT JOIN tecnico t
               ON t.clube_id = c.id
            WHERE $where
         GROUP BY c.id, c.nome
         ORDER BY qtd_tecnicos DESC
            LIMIT 20
    SQL;

    try {
        $pdo = Connection::getInstance();

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);

        $resultados = [];
        while ($row = $stmt->fetch()) {
            $resultados[] = $row;
        }
        return Response::ok('', ['resultados' => $resultados]);
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}