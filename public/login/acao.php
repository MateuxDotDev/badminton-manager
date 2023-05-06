<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Tecnico\Conta\LoginDTO;
use App\Tecnico\Conta\RealizarLogin;
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Http\Request as Req;
use App\Util\Http\Response as Res;

loginController()->enviar();

function loginController(): Res
{
    $req  = Req::getDados();
    $acao = Req::getAcao($req);
    $pdo  = Connection::getInstance();
    return match ($acao) {
        'getDadosConta' => getDadosConta($pdo, $req),
        'login' => realizarLogin($pdo, $req),
        default => Res::erro('Ação inválida', ['acao' => $acao]),
    };
}

function getDadosConta(PDO $pdo, array $req): Res
{
    try {
        Req::camposRequeridos($req, ['email']);
        $repo = new TecnicoRepository($pdo);
        $email = filter_var($req['email'], FILTER_SANITIZE_EMAIL);
        $tecnico = $repo->getViaEmail($email);
        return Res::ok('', [
            'existe'   => $tecnico != null,
            'temSenha' => $tecnico != null && $tecnico->senhaCriptografada() != null
        ]);
    } catch (Exception $e) {
        return Res::erroException($e);
    }
}

function realizarLogin(PDO $pdo, array $req): Res
{
    $parsed = LoginDTO::parse($req);
    if ($parsed instanceof LoginDTO) {
        session_start();
        $repo = new TecnicoRepository($pdo);
        $session = UserSession::obj();
        $realizarLogin = new RealizarLogin($repo, $session);
        $result = $realizarLogin($parsed);
        return Res::fromResult($result);
    } else {
        return Res::erro($parsed);
    }
}
