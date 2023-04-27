<?php

require('../../vendor/autoload.php');

use App\Admin\Login\LoginRepository;
use App\Admin\Login\Login;
use App\Database\ConnectionImp;
use App\Util\Request;
use App\Util\Response;
use App\Util\Session;

loginAdminController()->enviar();

function loginAdminController(): Response
{
    $req = Request::getJson();

    $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
    return match ($acao) {
        'login' => acaoLogin($req),
        default => Response::erro('Ação inválida', ['acao' => $acao])
    };
}

function acaoLogin(array $req): Response
{
    try {
        Request::camposSaoValidos($req, ['usuario', 'senha']);

        $login = new Login($req['usuario'], $req['senha']);
        $repo = new LoginRepository(ConnectionImp::getInstance());
        if ($repo->validateLogin($login)) {
            Session::iniciar();
            Session::setAdmin();
            return Response::justOk();
        } else {
            return Response::erro('Usuário ou senha incorretos');
        }
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}
