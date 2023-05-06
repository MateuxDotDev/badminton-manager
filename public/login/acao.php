<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Tecnico\Conta\LoginDTO;
use App\Tecnico\Conta\RealizarLogin;
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;

try {
    loginController()->enviar();
} catch (Exception $e) {
    return Response::erroException($e)->enviar();
}

function loginController(): Response
{
    $req  = Request::getDados();
    $acao = Request::getAcao($req);
    $pdo  = Connection::getInstance();
    return match ($acao) {
        'getDadosConta' => getDadosConta($pdo, $req),
        'login' => realizarLogin($pdo, $req),
        default => Response::erro('Ação inválida', ['acao' => $acao]),
    };
}

/**
 * @throws Exception
 */
function getDadosConta(PDO $pdo, array $req): Response
{
    Request::camposRequeridos($req, ['email']);
    $repo = new TecnicoRepository($pdo);
    $email = filter_var($req['email'], FILTER_SANITIZE_EMAIL);
    $tecnico = $repo->getViaEmail($email);
    return Response::ok('', [
        'existe'   => $tecnico != null,
        'temSenha' => $tecnico != null && $tecnico->senhaCriptografada() != null
    ]);
}

/**
 * @throws Exception
 */
function realizarLogin(PDO $pdo, array $req): Response
{
    $dto = LoginDTO::parse($req);
    session_start();
    $realizarLogin = new RealizarLogin(
        new TecnicoRepository($pdo),
        UserSession::obj(),
    );
    $realizarLogin($dto);
    return Response::ok('Login realizado com sucesso');
}
