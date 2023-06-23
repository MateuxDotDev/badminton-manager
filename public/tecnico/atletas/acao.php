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
use App\Util\Services\TokenService\AcoesToken;
use App\Util\Services\TokenService\TokenService;
use App\Util\Services\UploadImagemService\UploadImagemService;

try {
    $decodedToken = null;
    $session = UserSession::obj();
    $req = Request::getDados();
    $reqToken = $req['token'] ?? null;

    if (!array_key_exists('acao', $req)) {
        return Response::erro('Ação não informada');
    }

    if (!$session->isTecnico() && $reqToken === null) {
        Response::erroNaoAutorizado()->enviar();
    }

    if (!$session->isTecnico()) {
        $tokenRepo = new TokenRepository(Connection::getInstance(), new TokenService());
        $decodedToken = $tokenRepo->consumeToken($reqToken);
        $acoesValidas = [AcoesToken::ALTERAR_ATLETA->value, AcoesToken::REMOVER_ATLETA->value];
        if (empty($decodedToken->acao) || !in_array($decodedToken->acao, $acoesValidas)) {
            Response::erroNaoAutorizado()->enviar();
        }
    }

    atletasController($req, $session, $decodedToken)->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function atletasController(array $request, ?UserSession $session, ?stdClass $token): Response
{
    try {
        $acao = $request['acao'];
        return match ($acao) {
            'alterar' => alterarAtleta($request, $session, $token),
            'remover' => removerAtleta($request, $session, $token),
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
    if ($session === null && $token->acao !== AcoesToken::ALTERAR_ATLETA->value) {
        Response::erroNaoAutorizado()->enviar();
    }

    Request::camposRequeridos($req, ['id', 'fotoPerfil']);
    $atleta = validaAtleta($req);
    $atleta->setId($req['id']);
    $imagemService = new UploadImagemService();

    if (isset($req["foto"]) && !empty($req["foto"]["name"])) {
        $atleta->setFoto($imagemService->upload($req["foto"]));
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
    if ($session === null && $token->acao !== AcoesToken::REMOVER_ATLETA->value) {
        Response::erroNaoAutorizado()->enviar();
    }

    Request::camposRequeridos($req, ['id']);
    $id = $req['id'];

    if (!is_numeric($id)) {
        return Response::erro('ID inválido');
    }

    $pdo = Connection::getInstance();
    try {
        $pdo->beginTransaction();
        $repo = new AtletaRepository($pdo, new UploadImagemService());

        $removido = $repo->removerAtleta($id);
        if ($removido) {
            $pdo->commit();
            return Response::ok('Atleta removido com sucesso');
        }

        return Response::erro('Erro ao remover atleta');
    } catch (Exception $e) {
        $pdo->rollBack();
        return Response::erroException($e);
    }
}
