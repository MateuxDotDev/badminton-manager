<?php

namespace App\Util;

// poderia ser uma classe Request não só com esses métodos estáticos,
// mas podia ser um objeto que a gente instancia mesmo
// tipo a Request do symfony e outros frameworks
use App\Util\Exceptions\ResponseException;

class Request
{
    public static function getJson(): array
    {
        $json = file_get_contents('php://input');
        if ($json === false) {
            die('Erro ao ler o corpo da request');
        }
        return json_decode($json, true);
    }

    /**
     * @throws ResponseException
     */
    public static function camposSaoValidos(array $req, array $camposRequeridos): true
    {
        foreach ($camposRequeridos as $campo) {
            if (!array_key_exists($campo, $req)) {
                throw new ResponseException(Response::erro("Campo faltando na requisição", ['campo' => $campo]));
            }
        }
        return true;
    }
}
