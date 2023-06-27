<?php

require_once(__DIR__ . '/../../../../../vendor/autoload.php');

use App\Tecnico\Dupla\DuplaRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;

try {
    $session = UserSession::obj();
    if (!$session->isTecnico()) {
        Response::erroNaoAutorizado()->enviar();
    }

    duplasController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

/**
 * @throws ValidatorException
 */
function duplasController(): Response
{
    $req = Request::getDados();
    $acao = Request::getAcao($req);
    return match ($acao) {
        'desfazer' => desfazerDupla($req),
        default => Response::erro('Ação inválida', ['acao' => $acao]),
    };
}

/**
 * @throws ValidatorException
 */
function desfazerDupla(array $req): Response
{
    Request::camposRequeridos($req, ['idDupla']);
    $idDupla = $req['idDupla'];

    $duplaRepo = new DuplaRepository(Connection::getInstance());
    $desfeita = $duplaRepo->desfazer($idDupla, UserSession::obj()->getTecnico()->id());
    if ($desfeita) {
        return Response::ok('Dupla desfeita com sucesso');
    }

    return Response::erro('Não foi possível desfazer a dupla');
}
