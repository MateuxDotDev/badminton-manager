<?php
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\Http\Request as Req;
use App\Util\Http\Response as Res;

require_once(__DIR__.'/../../vendor/autoload.php');

loginController()->enviar();

function loginController(): Res
{
    $req  = Req::getDados();
    $acao = Req::getAcao($req);
    $pdo  = Connection::getInstance();
    return match ($acao) {
        'getDadosConta' => getDadosConta($pdo, $req),
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
            'temSenha' => $tecnico != null && $tecnico->temSenha()
        ]);
    } catch (Exception $e) {
        return Res::erroException($e);
    }
}