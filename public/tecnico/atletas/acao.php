<?php

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/util.php');

use App\Tecnico\Atleta\AtletaRepository;
use App\Token\TokenRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;
use App\Util\Services\TokenService\TokenService;
use App\Util\Services\UploadImagemService\UploadImagemService;
use stdClass;

try {
    $decodedToken = null;
    $session = UserSession::obj();
    $reqToken = Request::getJson()['token'] ?? null;

    if ($session->getTecnico() === null && $reqToken === null) {
        Response::erroNaoAutorizado()->enviar();
    }

    if ($session->getTecnico() !== null) {
        $idTecnico = $session->getTecnico()->id();
    } else {
        $tokenRepo = new TokenRepository(Connection::getInstance(), new TokenService());
        $decodedToken = $tokenRepo->consumeToken($reqToken);
        if (empty($decodedToken->acao) || !in_array($decodedToken->acao, ['alterar', 'remover'])) {
            Response::erroNaoAutorizado()->enviar();
        }
    }
    atletasController($session, $token)->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function atletasController(?UserSession $session, ?stdClass $token): Response
{
    try {
        $req = Request::getJson();
        $acao = $req['acao'] ?? 'Ação não informada';
        return match ($acao) {
            'alterar' => alterarAtleta($req, $session, $token),
            'remover' => removerAtleta($req, $session, $token),
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
function alterarAtleta(array $req, ?UserSession $session, ?stdClass $token): Response
{
    if ($session === null && $token->acao !== 'alterar') {
        Response::erroNaoAutorizado()->enviar();
    }
    Request::camposRequeridos($req, ['id', 'fotoPerfil']);
    $atleta = validaAtleta($req);
    $atleta->setId($req['id']);
    $imagemService = new UploadImagemService();

    if (isset($_FILES["foto"]) && !empty($_FILES["foto"]["name"])) {
        $atleta->setFoto($imagemService->upload($_FILES["foto"]));
    } else {
        $atleta->setFoto($req['fotoPerfil']);
    }

    $repo = new AtletaRepository(Connection::getInstance(), $imagemService);
    $atualizado = $repo->atualizarAtleta($atleta);
    if ($atualizado) {
        return Response::ok('Atleta atualizado com sucesso');
    }

    return Response::erro('Erro ao cadastrar atleta');
}

/**
 * @throws ValidatorException
 */
function removerAtleta(array $req, ?UserSession $session, ?stdClass $token): Response
{
    if ($session === null && $token->acao !== 'remover') {
        Response::erroNaoAutorizado()->enviar();
    }

    Request::camposRequeridos($req, ['id']);
    $id = $req['id'];

    if (!is_numeric($id)) {
        return Response::erro('ID inválido');
    }

    $repo = new AtletaRepository(Connection::getInstance(), new UploadImagemService());
    $removido = $repo->removerAtleta($id);
    if ($removido) {
        return Response::ok('Atleta removido com sucesso');
    }

    return Response::erro('Erro ao remover atleta');
}
