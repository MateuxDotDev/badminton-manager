<?php

require_once('../../../vendor/autoload.php');

use App\Admin\Competicoes\Competicao;
use App\Admin\Competicoes\CompeticaoRepository;
use App\Database\ConnectionImp;
use App\Util\Exceptions\ResponseException;
use App\Util\Request;
use App\Util\Response;
use App\Util\Session;

Session::iniciar();

if (!Session::isAdmin()) {
    return Response::erroNaoAutorizado();
}

competicaoController()->enviar();

function competicaoController(): Response
{
    $req = Request::getJson();
    $acao = array_key_exists('acao', $req) ? $req['acao'] : '';

    return match ($acao) {
        'criarCompeticao' => criarCompeticao($req),
        'excluirCompeticao' => excluirCompeticao($req),
        'alterarCompeticao' => alterarCompeticao($req),
        default => Response::erro('Ação inválida', ['acao' => $acao])
    };
}

function criarCompeticao(array $req): Response
{
    try {
        Request::camposSaoValidos($req, ['nome', 'prazo']);
        $nome = $req['nome'];
        $prazo = DateTimeImmutable::createFromFormat('Y-m-d', $req['prazo']);
        if ($prazo === false) {
            throw new ResponseException(Response::erro("Prazo inválido"));
        }

        $competicao = (new Competicao)->setNome($nome)->setPrazo($prazo);
        if ($competicao->prazoPassou()) {
            throw new ResponseException(Response::erro("Prazo deve ser no futuro"));
        }

        $repo = new CompeticaoRepository(ConnectionImp::getInstance());
        $id = $repo->criarCompeticao($competicao);
        return Response::ok('Competição criada com sucesso', ['id' => $id]);
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

function excluirCompeticao(array $req): Response
{
    if ($resp = Request::camposSaoValidos($req, ['id'])) {
        return $resp;
    }

    // TODO
    // caso a competição já tenha inscrições, não pode ser excluída

    $id = $req['id'];
    try {
        $repo = new CompeticaoRepository(ConnectionImp::getInstance());
        $repo->excluirCompeticao($id);
        return Response::okExcluido();
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

function alterarCompeticao(array $req): Response
{
    try {
        Request::camposSaoValidos($req, ['id', 'nome', 'prazo']);

        $id = $req['id'];
        $nome = $req['nome'];
        $prazo = DateTimeImmutable::createFromFormat('Y-m-d', $req['prazo']);
        if ($prazo === false) {
            throw new ResponseException(Response::erro("Prazo inválido"));
        }

        $competicao = (new Competicao)
            ->setId($id)
            ->setNome($nome)
            ->setPrazo($prazo);
        if ($competicao->prazoPassou()) {
            throw new ResponseException(Response::erro("Prazo deve ser no futuro"));
        }

        $repo = new CompeticaoRepository(ConnectionImp::getInstance());
        if ($repo->alterarCompeticao($competicao)) {
            return Response::ok('Competição alterada com sucesso');
        } else {
            return Response::notFound();
        }
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}