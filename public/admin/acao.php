<?php

use App\Admin\AdminRepository;
use App\Admin\Login\Login;
use App\Admin\Login\LoginRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\OldSession;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;

require('../../vendor/autoload.php');

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
            throw new ValidatorException('Usuário administrador não encontrado', 404);
        }

        $ok = $admin->senhaCriptografada()->validar($req['usuario'], $req['senha']);

        if ($ok) {
            $session = new UserSession($_SESSION);
            $session->setAdmin();
            return Response::ok();
        } else {
            return throw new ValidatorException('Senha incorreta', 401);
        }

    } catch (ValidatorException $exception) {
        return $exception->response() ?? Response::erro($exception);
    } catch (Exception $e) {
        return Response::erro($e);
    }
}

