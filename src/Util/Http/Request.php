<?php

namespace App\Util\Http;

use App\Util\Exceptions\ValidatorException;

class Request
{
    public static function getDados(): array
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        switch ($metodo) {
            case 'GET':
                return $_GET;
            case 'POST':
                if (!empty($_FILES)) {
                    $json = self::getJson();
                    return array_merge($json != [] ? $json : $_POST, $_FILES);
                }
                return self::getJson();
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                return self::getJson();
            default:
                die('Metodo não suportado');
        }
    }

    private static function getJson(): array
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
                throw new ValidatorException("Campo $campo faltando na requisição.");
            }
        }
    }
}
