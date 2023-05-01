<?php
use App\Admin\Login\Login;
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\Http\Request as Req;
use App\Util\Http\Response as Res;
use App\Util\Session;

require_once(__DIR__.'/../../vendor/autoload.php');

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

function realizarLogin(PDO $pdo, array $req)
{
    try {
        Req::camposRequeridos($req, ['email', 'senha']);
        $email = filter_var($req['email'], FILTER_SANITIZE_EMAIL);
        $senha = $req['senha'];

        $repo = new TecnicoRepository($pdo);
        $tecnico = $repo->getViaEmail($email);
        if ($tecnico === null) {
            return Res::erro('Técnico não encontrado');
        }

        $ok = $tecnico->senhaCriptografada()?->validar($email, $senha) ?? false;
        if (!$ok) {
            return Res::erro('Senha incorreta');
        }

        Session::iniciar();
        Session::setTecnico($tecnico);
        return Res::ok();

    } catch (Exception $e) {
        return Res::erroException($e);
    }
}