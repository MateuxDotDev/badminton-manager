<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');
require_once(__DIR__ . '/../util.php');

use App\Tecnico\Atleta\AtletaRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Services\UploadImagemService\UploadImagemService;

$session = UserSession::obj();

if ($session->getTecnico() === null) {
    Response::erroNaoAutorizado()->enviar();
}

try {
    cadastroController($session)->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function cadastroController(UserSession $session): Response
{
    try {
        $acao = $_POST['acao'] ?? 'Ação não informada';
        return match ($acao) {
            'cadastrar' => realizarCadastro($session),
            default => Response::erro("Ação '$acao' inválida")
        };
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}

/**
 * @throws ValidatorException
 * @throws Exception
 */
function realizarCadastro(UserSession $session): Response
{
    $atleta = validaAtleta($_POST, $session->getTecnico());
    $imagemService = new UploadImagemService();

    if (isset($_FILES["foto"]) && !empty($_FILES["foto"]["name"])) {
        $atleta->setFoto($imagemService->upload($_FILES["foto"]));
    } else {
        $atleta->setFoto('default.png');
    }

    $repo = new AtletaRepository(Connection::getInstance(), $imagemService);
    $criado = $repo->criarAtleta($atleta);
    if ($criado > 0) {
        return Response::ok('Atleta cadastrado com sucesso');
    }

    return Response::erro('Erro ao cadastrar atleta');
}
