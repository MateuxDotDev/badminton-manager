<?php

namespace App\Util\Http;

// poderia ser uma classe Request não só com esses métodos estáticos,
// mas podia ser um objeto que a gente instancia mesmo
// tipo a Request do symfony e outros frameworks
use App\Util\Exceptions\ValidatorException;

class Request
{
    public static function getDados(): array
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        return ($metodo == 'GET') ? $_GET : self::getJson();
    }

    // TODO tornar getJson private, utilizar somente getDados por fora
    public static function getJson(): array
    {
        $json = file_get_contents('php://input');
        if ($json === false) {
            die('Erro ao ler o corpo da request');
        }
        return json_decode($json, true) ?? [];
    }

    public static function getAcao($req): ?string
    {
        return array_key_exists('acao', $req) ? $req['acao'] : null;
    }

    /**
     * @throws ValidatorException
     */
    public static function camposRequeridos(array $req, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (!array_key_exists($campo, $req)) {
                throw new ValidatorException("Campo faltando na requisição", ['campo' => $campo]);
            }
        }
    }
}
