<?php

require_once('../../vendor/autoload.php');

use App\Admin\Competicoes\Competicao;
use App\Admin\Competicoes\CompeticaoRepository;
use App\Database\ConnectionImp;
use App\Util\Request;
use App\Util\Response;
use App\Util\Session;

$pdo = ConnectionImp::getInstance();
$req = Request::getJson();

Session::iniciar();
competicaoController($pdo, $req)->enviar();

function competicaoController(PDO $pdo, array $req): Response
{
    if (!Session::isAdmin()) {
        return Response::erroNaoAutorizado();
    }

    $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
    return match ($acao) {
        'criarCompeticao' => criarCompeticao($pdo ,$req),
        'excluirCompeticao' => excluirCompeticao($pdo, $req),
        'alterarCompeticao' => alterarCompeticao($pdo, $req),
        default => Response::erro('Ação inválida', ['acao' => $acao])
    };
}

function criarCompeticao(PDO $pdo, array $req): Response
{
    if ($resp = Request::validarCamposPresentes($req, ['nome', 'prazo'])) {
        return $resp;
    }
    $nome = $req['nome'];
    $prazo = DateTimeImmutable::createFromFormat('Y-m-d', $req['prazo']);
    if ($prazo === false) {
        return Response::erro("Prazo inválido");
    }
    $competicao = (new Competicao)->setNome($nome)->setPrazo($prazo);
    if ($competicao->prazoPassou()) {
        return Response::erro("Prazo deve ser no futuro");
    }
    try {
        $repo = new CompeticaoRepository($pdo);
        $id = $repo->criarCompeticao($competicao);
        return Response::ok('Competição criada com sucesso', ['id' => $id]);
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

function excluirCompeticao(PDO $pdo, array $req): Response
{
    if ($resp = Request::validarCamposPresentes($req, ['id'])) {
        return $resp;
    }

    // TODO
    // caso a competição já tenha inscrições, não pode ser excluída

    $id = $req['id'];
    try {
        $repo = new CompeticaoRepository($pdo);
        $repo->excluirCompeticao($id);
        return Response::okExcluido();
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

function alterarCompeticao(PDO $pdo, array $req): Response
{
    if ($resp = Request::validarCamposPresentes($req, ['id', 'nome', 'prazo'])) {
        return $resp;
    }
    $id = $req['id'];
    $nome = $req['nome'];
    $prazo = DateTimeImmutable::createFromFormat('Y-m-d', $req['prazo']);
    if ($prazo === false) {
        return Response::erro("Prazo inválido");
    }

    $competicao = (new Competicao)
        ->setId($id)
        ->setNome($nome)
        ->setPrazo($prazo);
    if ($competicao->prazoPassou()) {
        return Response::erro("Prazo deve ser no futuro");
    }

    try {
        $repo = new CompeticaoRepository($pdo);
        if ($repo->alterarCompeticao($competicao)) {
            return Response::ok('Competição alterada com sucesso');
        } else {
            return Response::notFound();
        }
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}
