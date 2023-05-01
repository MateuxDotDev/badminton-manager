<?php
use App\Admin\AdminRepository;

require('../../vendor/autoload.php');

use App\Admin\Login\Login;
use App\Admin\Login\LoginRepository;
use App\Util\Database\Connection;
use App\Util\Http\Request;
use App\Util\Http\Response;
use App\Util\SessionOld;

loginAdminController()->enviar();

function loginAdminController(): Response
{
    $req = Request::getJson();
    $pdo = Connection::getInstance();

    $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
    return match ($acao) {
        'login' => acaoLogin($pdo, $req),
        default => Response::erro('Ação inválida', ['acao' => $acao])
    };
}

function acaoLogin(PDO $pdo, array $req): Response
{
    try {
        Request::camposRequeridos($req, ['usuario', 'senha']);

        $repo = new AdminRepository($pdo);
        $admin = $repo->getViaNome($req['usuario']);
        if ($admin === null) {
            return Response::erro('Usuário administrador não encontrado');
        }

        $ok = $admin->senhaCriptografada()->validar($req['usuario'], $req['senha']);

        if ($ok) {
            SessionOld::iniciar();
            SessionOld::setAdmin();
            return Response::ok();
        } else {
            return Response::erro('Senha incorreta');
        }

    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

