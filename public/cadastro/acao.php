<?php
use App\Senha;

require_once(__DIR__.'/../../vendor/autoload.php');
use App\Admin\Login\Login;
use App\Tecnico\{TecnicoRepository, Tecnico, Clube};
use App\Util\Database\Connection;
use App\Util\Exceptions\ResponseException;
use App\Util\Http\{Request, Response};

try {
    cadastroController()->enviar();
} catch (ResponseException $e) {
    $e->response()->enviar();
}

function cadastroController(): Response {
    $req = Request::getJson();
    $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
    $pdo = Connection::getInstance();
    return match($acao) {
        'cadastro' => realizarCadastro($pdo, $req),
        default => Response::erro("Ação '$acao' inválida")
    };
}

function realizarCadastro(PDO $pdo, array $req): Response {
    try {
        Request::camposRequeridos($req, ['email', 'senha', 'clube']);

        $email = filter_var($req['email'], FILTER_SANITIZE_EMAIL);
        if (false === ($email = filter_var($req['email'], FILTER_VALIDATE_EMAIL))) {
            return Response::erro('E-mail inválido');
        }

        $nomeClube = htmlspecialchars($req['clube']);
        $clube = (new Clube)->setNome($nomeClube);

        $informacoes = htmlspecialchars(array_key_exists('informacoes', $req) ? $req['informacoes'] : '');

        $repo = new TecnicoRepository(Connection::getInstance());

        $jaExiste = null !== $repo->getViaEmail($email);
        if ($jaExiste) {
            return Response::erro('Já existe um técnico cadastrado com esse e-mail');
        }

        $login = new Login($email, $req['senha']);
        $salt = Login::gerarSalt();
        $hash = $login->gerarHash($salt);

        $tecnico = (new Tecnico)
            ->setEmail($req['email'])
            ->setNomeCompleto($req['nome'])
            ->setInformacoes($informacoes)
            ->setClube($clube)
            ->setSenha(Senha::from($hash, $salt))
            ;

        $repo->criarTecnico($tecnico);

        return Response::ok('Técnico cadastrado com sucesso', [
            'id' => $tecnico->id(),
            'idClube' => $tecnico->clube()->id(),
        ]);

    } catch (Exception $e) {
        return Response::erroException($e);
    }
}