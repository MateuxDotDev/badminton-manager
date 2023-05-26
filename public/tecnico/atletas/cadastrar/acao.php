<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Tecnico;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Services\UploadImagemService\UploadImagemService;

try {
    cadastroController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

function cadastroController(): Response
{
    try {
        $acao = $_POST['acao'] ?? 'Ação não informada';
        return match ($acao) {
            'cadastrar' => realizarCadastro(),
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
function realizarCadastro(): Response
{
    $atleta = validaAtleta();
    $imagemService = new UploadImagemService();

    if (isset($_FILES["foto"]) && !empty($_FILES["foto"]["name"])) {
        $atleta->setFoto($imagemService->upload($_FILES["foto"]));
    } else {
        $atleta->setFoto('default.png');
    }

    $repo = new AtletaRepository(Connection::getInstance(), $imagemService);
    $criado = $repo->criarAtleta($atleta);
    if ($criado > 0) {
        return Response::ok('Atleta cadastrado com sucesso');
    }

    return Response::erro('Erro ao cadastrar atleta');
}

/**
 * @throws ValidatorException
 */
function validaAtleta(): Atleta
{
    $camposNecessarios = ['nomeCompleto', 'sexo', 'dataNascimento', 'observacoes'];
    $req = $_POST;

    foreach ($camposNecessarios as $campo) {
        if (!array_key_exists($campo, $req)) {
            throw new ValidatorException("Campo $campo faltando na requisição");
        }
    }

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

    $session = UserSession::obj();
    $tecnico = $session->getTecnico();

    if (!($tecnico instanceof Tecnico)) {
        throw new ValidatorException('Técnico não encontrado');
    }

    return (new Atleta())
        ->setNomeCompleto($req['nomeCompleto'])
        ->setSexo(Sexo::from($req['sexo']))
        ->setDataNascimento($dataNascimento)
        ->setInformacoesAdicionais($req['observacoes'])
        ->setTecnico($tecnico);
}
