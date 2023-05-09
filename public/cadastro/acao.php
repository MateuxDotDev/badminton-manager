<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Tecnico\Conta\Cadastrar;
use App\Tecnico\Conta\CadastroDTO;
use App\Util\Exceptions\ValidatorException;
use App\Tecnico\{TecnicoRepository};
use App\Util\Database\Connection;
use App\Util\Http\{Request, Response};

try {
    cadastroController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function cadastroController(): Response
{
    try {
        $req = Request::getJson();
        $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
        return match($acao) {
            'cadastro' => realizarCadastro($req),
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
function realizarCadastro(array $req): Response
{
    $dto = CadastroDTO::parse($req);
    $cadastrar = new Cadastrar(new TecnicoRepository(Connection::getInstance()));
    $ids = $cadastrar($dto);
    return Response::ok('Técnico cadastrado com sucesso', $ids);
}
