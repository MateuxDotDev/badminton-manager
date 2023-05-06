<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Util\Exceptions\ValidatorException;
use App\Tecnico\{Clube, Tecnico, TecnicoRepository};
use App\Util\Database\Connection;
use App\Util\General\SenhaCriptografada;
use App\Util\Http\{Request, Response};

try {
    cadastroController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function cadastroController(): Response
{
    $req = Request::getJson();
    $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
    return match($acao) {
        'cadastro' => realizarCadastro($req),
        default => Response::erro("Ação '$acao' inválida")
    };
}

function realizarCadastro(array $req): Response
{
    try {
        Request::camposRequeridos($req, ['email', 'senha', 'clube']);

        $clube       = (new Clube)->setNome(htmlspecialchars($req['clube']));
        $informacoes = htmlspecialchars(array_key_exists('informacoes', $req) ? $req['informacoes'] : '');

        if (false === ($email = filter_var($req['email'], FILTER_VALIDATE_EMAIL))) {
            throw new ValidatorException('E-mail inválido');
        }

        $repo = new TecnicoRepository(Connection::getInstance());

        $jaExiste = null !== $repo->getViaEmail($email);
        if ($jaExiste) {
            throw new ValidatorException('Já existe um técnico cadastrado com esse e-mail', 403);
        }

        $senha = SenhaCriptografada::criptografar($email, $req['senha']);

        $tecnico = (new Tecnico)
            ->setEmail($req['email'])
            ->setNomeCompleto($req['nome'])
            ->setInformacoes($informacoes)
            ->setClube($clube)
            ->setSenhaCriptografada($senha);

        $repo->criarTecnico($tecnico);

        return Response::ok('Técnico cadastrado com sucesso', [
            'id' => $tecnico->id(),
            'idClube' => $tecnico->clube()->id(),
        ]);
    } catch (Exception $e) {
        return Response::erroException($e);
    }
}
