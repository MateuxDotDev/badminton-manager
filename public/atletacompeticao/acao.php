<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Util\Http\Request;
use App\Util\Http\Response;
use App\Util\Database\Connection;
use App\Tecnico\Tecnico;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoCategoria;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoCategoriaRepository;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoDuplaRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoDupla;
use App\Categorias\Categoria;
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


function atletaCompeticaoController(): Response
{
    $acao = $_POST['acao'] ?? 'Ação não informada';
    $pdo  = Connection::getInstance();
    return match ($acao) {
        'cadastrar' => realizarCadastro($pdo),
        default => Response::erro('Ação inválida', ['acao' => $acao]),
    };
}

function realizarCadastro($pdo) : Response
{

    if($_POST['userChoice']){
        return $_POST['userChoice'] == 1 ? realizarCadastroAtletaSelecionado($pdo) : realizarCadastroComNovoAtleta($pdo);
    }

    return Response::erro('Não foi possível identificar a opção de cadastro selecionada. Por favor escolha entre "Selecionar atleta cadastrado" ou "Cadastrar novo atleta".');
}

function realizarCadastroAtletaSelecionado($pdo): Response
{
    try{
        $pdo->beginTransaction();
        $retorno = cadastrarAtletaCompeticao($pdo, new Atleta());

        $pdo->commit();
        return $retorno;
    }catch(Exception $e){
        $pdo->rollback();
        return Response::erroException($e);
    }
}

function realizarCadastroComNovoAtleta($pdo): Response
{
    try{
        $pdo->beginTransaction();
        $response = cadastrarNovoAtleta($pdo);
        if($response->statusCode() == HttpStatus::OK){
            $atleta = $response->dados()['atleta'];
            $response = cadastrarAtletaCompeticao($pdo, $atleta);
            $pdo->commit();
            return $response;
        }else{
            $pdo->rollback();
            return $response;
        }
    }catch(Exception $e){
        $pdo->rollback();
        return Response::erroException($e);
    }
}

function cadastrarNovoAtleta($pdo): Response
{
    $atleta = validaAtleta();
    $imagemService = new UploadImagemService();

    if (isset($_FILES["cadastrar_foto"]) && !empty($_FILES["cadastrar_foto"]["name"])) {
        $atleta->setFoto($imagemService->upload($_FILES["cadastrar_foto"]));
    } else {
        $atleta->setFoto('default.png');
    }

    $repo = new AtletaRepository($pdo, $imagemService);
    $repo->defineTransaction(false);
    $criado = $repo->criarAtleta($atleta);
    if ($criado > 0) {
        $atleta->setId($criado);
        return Response::ok('', ['atleta' => $atleta]);
    }

    return Response::erro('Erro ao cadastrar atleta');
}

function cadastrarAtletaCompeticao($pdo, Atleta $atleta): Response
{
    if(!$atleta->id()){
        $atleta = getAtletaById($pdo);
    }

    $atletaCompeticao = new AtletaCompeticao();
    $atletaCompeticao->setAtleta($atleta);
    $atletaCompeticao->competicao()->setId($_POST["competicao"]);
    $atletaCompeticao->setInformacao($_POST["informacao"]);
    
    $repo = new AtletaCompeticaoRepository($pdo);
    $repo->defineTransaction(false);
    if($repo->cadastrarAtletaCompeticao($atletaCompeticao)){
        $response = cadastrarAtletaCompeticaoCategoria($pdo, $atletaCompeticao);
        if($response->statusCode() == HttpStatus::BAD_REQUEST){
            $pdo->rollback();
            return $response;
        }
        $response = cadastrarAtletaCompeticaoDupla($pdo, $atletaCompeticao);
        if($response->statusCode() == HttpStatus::BAD_REQUEST){
            $pdo->rollback();
            return $response;
        }

        return Response::ok('Atleta inserido na competição');
    }else{
        $pdo->rollback();
        return Response::erro("Não foi possível cadastrar o atleta na competição");
    }
}

function cadastrarAtletaCompeticaoCategoria(PDO $pdo, AtletaCompeticao $atletaCompeticao): Response
{
    $repo = new AtletaCompeticaoCategoriaRepository($pdo);
    $repo->defineTransaction(false);
    $atletaCompeticaoCategoria = new AtletaCompeticaoCategoria();
    $atletaCompeticaoCategoria->setAtletaCompeticao($atletaCompeticao);

    if($categorias = getCategoriasFormulario($pdo)){
        foreach($categorias as $chave => $categoria){
            if(!validaCategoriaAtleta($categoria, $atletaCompeticao->atleta())){
                return Response::erro('A categoria ' . $categoria->descricao() . ' selecionada se torna invalida com relação a idade do(a) atleta');
            }

            $atletaCompeticaoCategoria->setCategoria($categoria);
            if($repo->cadastrarAtletaCompeticaoCategoria($atletaCompeticaoCategoria)){
                return Response::ok('');
            }else{
                return Response::erro('Não foi possível cadastrar a categoria ' . $categoria->descricao() . ' ao atleta na competição');
            }
        }
    }else{
        return Response::erro('Não foi selecionado uma categoria');
    }


}

function cadastrarAtletaCompeticaoDupla(PDO $pdo, AtletaCompeticao $atletaCompeticao): Response
{
    $repo = new AtletaCompeticaoDuplaRepository($pdo);
    $repo->defineTransaction(false);
    $atletaCompeticaoDupla = new AtletaCompeticaoDupla();
    $atletaCompeticaoDupla->setAtletaCompeticao($atletaCompeticao);

    if($tipoDuplas = getTipoDuplaFormulario()){
        foreach($tipoDuplas as $tipoDupla => $valor){
            $atletaCompeticaoDupla->setTipoDupla(Sexo::from($valor));
            if($repo->cadastrarAtletaCompeticaoDupla($atletaCompeticaoDupla)){
                return Response::ok();
            }else{
                return Response::erro('Não foi possível cadastrar o tipo de dupla ' . $valor . ' do atleta na competição');
            }
        }
    }else{
        return Response::erro('Não foi selecionado um tipo de dupla');
    }
}

function getCategoriasFormulario(PDO $pdo): array
{
    $categorias = [];
    foreach($_POST as $chave => $valor){
        if(explode('-',$chave)[0] == 'categoria'){
            $repoCategoria = new CategoriaRepository($pdo);
            $categoria = $repoCategoria->getCategoriaById($valor);
            $categorias[$valor] = $categoria;
        }
    }

    return $categorias;
}

function getTipoDuplaFormulario(){
    $tipoDupla = [];
    foreach($_POST as $chave => $valor){
        if($chave == 'check-masculina'){
            $tipoDupla['Masculino'] = 'M';
        }
        if($chave == 'check-feminina'){
            $tipoDupla['Feminino'] = 'F';
        }
    }

    return $tipoDupla;
}

function getAtletaById(PDO $pdo): ?Atleta
{
    $repo = new AtletaRepository($pdo, new UploadImagemService());
    return $repo->getAtletaViaId($_POST["atleta"]);
}

function validaCategoriaAtleta(Categoria $categoria, Atleta $atleta): bool
{
    $sucesso = true;
    if($categoria->idadeMenorQue()){
        $sucesso = !($categoria->idadeMenorQue() < $atleta->idade());
    } else if($categoria->idadeMaiorQue()){
        $sucesso = !($categoria->idadeMaiorQue() > $atleta->idade());
    }

    return $sucesso;
}

/**
 * @throws ValidatorException
 */
function validaAtleta(): Atleta
{
    $camposNecessarios = ['cadastrar_nomeCompleto', 'cadastrar_sexo', 'cadastrar_dataNascimento', 'cadastrar_observacoes'];
    $req = $_POST;

    foreach ($camposNecessarios as $campo) {
        if (!array_key_exists($campo, $req)) {
            throw new ValidatorException("Campo $campo faltando na requisição");
        }
    }

    $dataNascimento = Dates::parseDay($req['cadastrar_dataNascimento']);
    if (!($dataNascimento instanceof DateTimeInterface)) {
        throw new ValidatorException('Data de nascimento inválida');
    }

    $dataNascimento->setTime(0, 0);
    $currentDate = new DateTime();
    $currentDate->setTime(0, 0);
    if ($dataNascimento >= $currentDate) {
        throw new ValidatorException('Data de nascimento não pode estar no futuro');
    }
    
    $tecnico = new Tecnico();
    $tecnico->setId($_POST["tecnico"]);

    if (!($tecnico instanceof Tecnico)) {
        throw new ValidatorException('Técnico não encontrado');
    }

    return (new Atleta())
        ->setNomeCompleto($req['cadastrar_nomeCompleto'])
        ->setSexo(Sexo::from($req['cadastrar_sexo']))
        ->setDataNascimento($dataNascimento)
        ->setInformacoesAdicionais($req['cadastrar_observacoes'])
        ->setTecnico($tecnico);
}