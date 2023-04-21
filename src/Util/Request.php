<?php

namespace App\Util;

// poderia ser uma classe Request não só com esses métodos estáticos,
// mas podia ser um objeto que a gente instancia mesmo
// tipo a Request do symfony e outros frameworks
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

    public static function validarCamposPresentes(array $req, array $camposRequeridos): Response|false
    {
        foreach ($camposRequeridos as $campo) {
            if (!array_key_exists($campo, $req)) {
                return Response::erro("Campo faltando na requisição", ['campo' => $campo]);
            }
        }
        return false;
    }
}
