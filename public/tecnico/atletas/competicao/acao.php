<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use App\Util\Http\Response;
use App\Util\Database\Connection;
use App\Tecnico\Tecnico;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Competicoes\Competicao;
use App\Categorias\CategoriaRepository;
use App\Tecnico\Atleta\Sexo;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemService;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;

try {
    atletaCompeticaoController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}

/**
 * @throws ValidatorException
 */
function atletaCompeticaoController(): Response
{
    $acao = $_POST['acao'] ?? 'Ação não informada';
    $pdo  = Connection::getInstance();
    return match ($acao) {
        'cadastrar' => realizarCadastro($pdo),
        default => Response::erro('Ação inválida', ['acao' => $acao]),
    };
}

/**
 * @throws ValidatorException
 */
function realizarCadastro($pdo) : Response
{
    if ($_POST['userChoice']) {
        return $_POST['userChoice'] == 1
             ? realizarCadastroAtletaSelecionado($pdo)
             : realizarCadastroComNovoAtleta($pdo);
    }

    return Response::erro(
        'Não foi possível identificar a opção de cadastro selecionada. ' .
        'Por favor escolha entre "Selecionar atleta cadastrado" ou "Cadastrar novo atleta".'
    );
}

/**
 * @throws ValidatorException
 */
function realizarCadastroAtletaSelecionado($pdo): Response
{
    $atleta     = getAtletaSelecionadoValidado($pdo);
    $competicao = getCompeticao($pdo);

    $atletaCompeticao = (new AtletaCompeticao)
        ->setAtleta($atleta)
        ->setInformacao($_POST['informacao'])
        ->setCompeticao($competicao)
        ->addCategoria(...getAtletaCategoria($pdo, $atleta, $competicao))
        ->addSexoDupla(...getSexoDuplaValidado())
        ;

    try {
        $pdo->beginTransaction();
        $response = cadastrarAtletaCompeticao($pdo, $atletaCompeticao);

        if ($response->statusCode() == HttpStatus::OK) {
            $pdo->commit();
            return $response;
        } else {
            $pdo->rollback();
            return $response;
        }
    } catch (Exception $e) {
        $pdo->rollback();
        return Response::erroException($e);
    }
}

/**
 * @throws ValidatorException
 */
function realizarCadastroComNovoAtleta($pdo): Response
{
    $atleta     = validaAtleta();
    $competicao = getCompeticao($pdo);
    $atletaCompeticao = (new AtletaCompeticao)
        ->setAtleta($atleta)
        ->setInformacao($_POST['informacao'])
        ->setCompeticao($competicao)
        ->addCategoria(...getAtletaCategoria($pdo, $atleta, $competicao))
        ->addSexoDupla(...getSexoDuplaValidado())
        ;

    try {
        $pdo->beginTransaction();
        $response = cadastrarNovoAtleta($pdo, $atleta);
        if ($response->statusCode() == HttpStatus::OK) {
            $response = cadastrarAtletaCompeticao($pdo, $atletaCompeticao);
            $pdo->commit();
        } else {
            $pdo->rollback();
        }
        return $response;
    } catch (Exception $e) {
        $pdo->rollback();
        return Response::erroException($e);
    }
}

/**
 * @throws ValidatorException
 * @throws Exception
 */
function cadastrarNovoAtleta($pdo, Atleta $atleta): Response
{
    $imagemService = new UploadImagemService();

    if (isset($_FILES["cadastrar_foto"]) && !empty($_FILES["cadastrar_foto"]["name"])) {
        $atleta->setFoto($imagemService->upload($_FILES["cadastrar_foto"]));
    } else {
        $atleta->setFoto('default.png');
    }

    $repo = new AtletaRepository($pdo, $imagemService);
    $criado = $repo->criarAtleta($atleta);
    if ($criado > 0) {
        $atleta->setId($criado);
        return Response::ok('', ['atleta' => $atleta]);
    }

    return Response::erro('Erro ao cadastrar atleta');
}

function cadastrarAtletaCompeticao(PDO $pdo, AtletaCompeticao $dados): Response
{
    $repo = new AtletaCompeticaoRepository($pdo);

    $repo->incluirAtletaCompeticao($dados);

    return Response::ok('Atleta inserido na competição');
}

function getCompeticao(PDO $pdo): ?Competicao
{
    $repo = new CompeticaoRepository($pdo);
    return $repo->getViaId($_POST['competicao']);
}

function getAtletaSelecionadoValidado(PDO $pdo): ?Atleta
{
    if (!$idAtleta = $_POST['atleta']) {
        throw new ValidatorException('Selecione ou cadastre um atleta para a competição');
    }
    if ($atleta = getAtletaById($pdo, $idAtleta)) {
        return $atleta;
    }

    throw new ValidatorException('Não foi possível encontrar o atleta selecionado');
}

function getAtletaCategoria(PDO $pdo, Atleta $atleta, Competicao $competicao): array
{
    $categorias = [];
    if ($categorias = getCategoriasFormulario($pdo)) {
        foreach ($categorias as $categoria) {
            if (!$categoria->podeParticipar($atleta->dataNascimento(), $competicao->prazo())) {
                throw new ValidatorException(
                    'O atleta não tem uma idade adequada para jogar na categoria ' .
                    $categoria->descricao()
                );
            }
            $categorias[] = $categoria;
        }
    } else {
        throw new ValidatorException('Não foi selecionada uma categoria');
    }

    return $categorias;
}

function getSexoDuplaValidado(): array
{
    if (!$tiposDuplas = getSexoDuplaFormulario()) {
        throw new ValidatorException('Não foi selecionado um tipo de dupla');
    }
    return $tiposDuplas;
}

function getCategoriasFormulario(PDO $pdo): array
{
    $categorias = [];
    foreach ($_POST as $chave => $valor) {
        if (explode('-', $chave)[0] == 'categoria') {
            $repoCategoria = new CategoriaRepository($pdo);
            $categoria = $repoCategoria->getCategoriaById($valor);
            $categorias[$valor] = $categoria;
        }
    }

    return $categorias;
}

function getSexoDuplaFormulario(): array
{
    $sexoDupla = [];
    foreach ($_POST as $chave => $valor) {
        if ($chave == 'check-masculina') {
            $sexoDupla[] = Sexo::MASCULINO;
        }
        if ($chave == 'check-feminina') {
            $sexoDupla[] = Sexo::FEMININO;
        }
    }

    return $sexoDupla;
}

function getAtletaById(PDO $pdo, int $idAtleta): ?Atleta
{
    $repo = new AtletaRepository($pdo, new UploadImagemService());
    return $repo->getViaId($idAtleta);
}

/**
 * @throws ValidatorException
 */
function validaAtleta(): Atleta
{
    $req = $_POST;
    $camposNecessarios = [
        'cadastrar_nomeCompleto',
        'cadastrar_sexo',
        'cadastrar_dataNascimento',
        'cadastrar_observacoes'
    ];

    foreach ($camposNecessarios as $campo) {
        if (!array_key_exists($campo, $req)) {
            throw new ValidatorException("Campo $campo faltando na requisição");
        }
    }

    $dataNascimento = Dates::parseDay($req['cadastrar_dataNascimento']);
    if (!($dataNascimento instanceof DateTimeInterface)) {
        throw new ValidatorException('Data de nascimento inválida');
    }

    $currentDate = new DateTime();
    $currentDate->setTime(0, 0);
    if ($dataNascimento >= $currentDate) {
        throw new ValidatorException('Data de nascimento não pode estar no futuro');
    }

    $tecnico = new Tecnico();
    $tecnico->setId($_POST["tecnico"]);

    return (new Atleta())
        ->setNomeCompleto($req['cadastrar_nomeCompleto'])
        ->setSexo(Sexo::from($req['cadastrar_sexo']))
        ->setDataNascimento($dataNascimento)
        ->setInformacoesAdicionais($req['cadastrar_observacoes'])
        ->setTecnico($tecnico);
}
