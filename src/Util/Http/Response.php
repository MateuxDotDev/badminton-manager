<?php

namespace App\Util\Http;

use App\Util\Exceptions\ValidatorException;
use Exception;

readonly class Response
{
    public function __construct(
        private HttpStatus  $code = HttpStatus::OK,
        private string      $mensagem = '',
        private array       $dados = [],
    ) {}

    public static function ok(string $mensagem='', array $dados=[]): Response
    {
        return new Response(HttpStatus::OK, $mensagem, $dados);
    }

    public static function okExcluido(): Response
    {
        return new Response(HttpStatus::NO_CONTENT);
    }

    public static function erro(string $mensagem='', array $dados=[]): Response
    {
        return new Response(HttpStatus::BAD_REQUEST, $mensagem, $dados);
    }

    public static function erroException(Exception $e): Response
    {
        if ($e instanceof ValidatorException) {
            return $e->toResponse();
        }

        return new Response(HttpStatus::INTERNAL_SERVER_ERROR, 'Ocorreu um erro inesperado', ['exception' => $e]);
    }

    public static function erroNaoAutorizado(): Response
    {
        return new Response(HttpStatus::UNAUTHORIZED, 'Usuário não autorizado para essa ação');
    }

    public static function notFound(): Response
    {
        return new Response(HttpStatus::NOT_FOUND, 'Recurso não encontrado');
    }

    public function statusCode(): HttpStatus
    {
        return $this->code;
    }

    public function mensagem(): string
    {
        return $this->mensagem;
    }

    public function array(): array
    {
        $a = [];
        if ($this->mensagem !== '') {
            $a['mensagem'] = $this->mensagem;
        }
        foreach ($this->dados as $chave => $valor) {
            $a[$chave] = $valor;
        }
        return $a;
    }

    public function enviar(): never
    {
        http_response_code($this->statusCode()->value);
        header('Content-Type: application/json');
        die(json_encode(
            $this->array(),
            JSON_PRETTY_PRINT
        ));
    }
}
