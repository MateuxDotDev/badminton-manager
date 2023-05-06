<?php

namespace App\Util\Http;

use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use Exception;

readonly class Response
{
    public function __construct(
        private int    $code = 200,
        private string $mensagem = '',
        private array  $dados = [],
    ) {}

    public static function ok(string $mensagem='', array $dados=[]): Response
    {
        return new Response(200, $mensagem, $dados);
    }

    public static function okExcluido(): Response
    {
        return new Response(204);
    }

    public static function erro(string $mensagem='', array $dados=[]): Response
    {
        return new Response(400, $mensagem, $dados);
    }

    public static function erroException(Exception $e): Response
    {
        if ($e instanceof ValidatorException) {
            return $e->toResponse();
        }

        return new Response(500, 'Ocorreu um erro inesperado', ['exception' => $e]);
    }

    public static function erroNaoAutorizado(): Response
    {
        return new Response(401, 'Usuário não autorizado para essa ação');
    }

    public static function notFound(): Response
    {
        return new Response(404, 'Recurso não encontrado');
    }

    private static function parseError($data): Response
    {
        if ($data instanceof Exception) {
            return self::erroException($data);
        } elseif (is_string($data)) {
            return self::erro($data);
        } else {
            return self::erro('Ocorreu um erro inesperado', $data);
        }
    }

    public function statusCode(): int
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
        http_response_code($this->statusCode());
        header('Content-Type: application/json');
        die(json_encode(
            $this->array(),
            JSON_PRETTY_PRINT
        ));
    }
}
