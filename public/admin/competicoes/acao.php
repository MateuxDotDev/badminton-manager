<?php

require_once('../../../vendor/autoload.php');

use App\Admin\Competicoes\Competicao;
use App\Admin\Competicoes\CompeticaoRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\General\OldSession;
use App\Util\Http\Request;
use App\Util\Http\HttpStatus;
use App\Util\Http\Response;

OldSession::iniciar();

if (!OldSession::isAdmin()) {
    return Response::erroNaoAutorizado();
}

try {
    competicaoController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

/**
 * @throws ValidatorException
 */
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
        Request::camposRequeridos($req, ['nome', 'prazo']);
        $nome = $req['nome'];
        $prazo = Dates::parseDay($req['prazo']);
        $descricao = $req['descricao'];
        if ($prazo === false) {
            throw new ValidatorException("Prazo inválido");
        }

        $competicao = (new Competicao)
            ->setNome($nome)
            ->setPrazo($prazo)
            ->setDescricao($descricao);

        if ($competicao->prazoPassou()) {
            throw new ValidatorException("Prazo deve ser no futuro", 400, ['prazo' => $prazo]);
        }

        $repo = new CompeticaoRepository(Connection::getInstance());
        $id = $repo->criarCompeticao($competicao);
        return Response::ok('Competição criada com sucesso', ['id' => $id]);
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

/**
 * @throws ValidatorException
 */
function excluirCompeticao(array $req): Response
{
    Request::camposRequeridos($req, ['id']);

    // TODO: caso a competição já tenha inscrições, não pode ser excluída

    $id = $req['id'];
    try {
        $repo = new CompeticaoRepository(Connection::getInstance());
        $repo->excluirCompeticao($id);
        return Response::okExcluido();
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

function alterarCompeticao(array $req): Response
{
    try {
        Request::camposRequeridos($req, ['id', 'nome', 'prazo']);

        $repo = new CompeticaoRepository(Connection::getInstance());

        $existente = $repo->getViaId($req['id']);

        if ($existente == null) throw new ValidatorException("Competição não encontrada", HttpStatus::NOT_FOUND);

        $id = $req['id'];
        $nome = $req['nome'];
        $prazo = Dates::parseDay($req['prazo']);
        $descricao = $req['descricao'];
        if ($prazo === false) {
            throw new ValidatorException("Prazo inválido");
        }

        $atualizada = (new Competicao)
            ->setId($id)
            ->setNome($nome)
            ->setPrazo($prazo)
            ->setDescricao($descricao);

        if (!$existente->prazoPassou() && $atualizada->prazoPassou()) {
            throw new ValidatorException("Novo prazo deve ser no futuro");
        }

        if ($repo->alterarCompeticao($atualizada)) {
            return Response::ok('Competição alterada com sucesso');
        } else {
            return Response::notFound();
        }
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}
