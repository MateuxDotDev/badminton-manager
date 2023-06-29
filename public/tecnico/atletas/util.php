<?php

use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Tecnico;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\Http\Request;

require_once(__DIR__ . '/../../../vendor/autoload.php');

/**
 * @throws ValidatorException
 */
function validaAtleta(array $req, ?Tecnico $tecnico = null): Atleta
{
    $camposNecessarios = ['nomeCompleto', 'sexo', 'dataNascimento', 'observacoes'];

    Request::camposRequeridos($req, $camposNecessarios);

    $dataNascimento = Dates::parseDay($req['dataNascimento']);
    if (!($dataNascimento instanceof DateTimeInterface)) {
        throw new ValidatorException('Data de nascimento inválida');
    }

    $dataNascimento->setTime(0, 0);
    $currentDate = new DateTime();
    $currentDate->setTime(0, 0);
    if ($dataNascimento >= $currentDate) {
        throw new ValidatorException('Data de nascimento não pode estar no futuro');
    }

    $atleta = (new Atleta())
        ->setNomeCompleto($req['nomeCompleto'])
        ->setSexo(Sexo::from($req['sexo']))
        ->setDataNascimento($dataNascimento)
        ->setInformacoesAdicionais($req['observacoes']);

    return $tecnico != null ? $atleta->setTecnico($tecnico) : $atleta;
}
