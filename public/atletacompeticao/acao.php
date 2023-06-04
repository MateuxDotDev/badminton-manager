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
    $dados = [];
    $dados['atleta'] = getAtletaSelecionadoValidado($pdo);
    $dados['atletaCompeticaoCategoria'] = getAtletaCompeticaoCategoriaValidada($pdo, $dados['atleta']);
    $dados['tipo_dupla'] = getTipoDuplaValidado();

    try{
        $pdo->beginTransaction();
        $response = cadastrarAtletaCompeticao($pdo, $dados);

        if($response->statusCode() == HttpStatus::OK){
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

function realizarCadastroComNovoAtleta($pdo): Response
{
    $dados = [];
    $dados['atleta'] = validaAtleta();;
    $dados['atletaCompeticaoCategoria'] = getAtletaCompeticaoCategoriaValidada($pdo, $dados['atleta']);
    $dados['tipo_dupla'] = getTipoDuplaValidado();

    try{
        $pdo->beginTransaction();
        $response = cadastrarNovoAtleta($pdo, $dados['atleta']);
        if($response->statusCode() == HttpStatus::OK){
            $response = cadastrarAtletaCompeticao($pdo, $dados);
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

function cadastrarNovoAtleta($pdo, Atleta $atleta): Response
{
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

function cadastrarAtletaCompeticao($pdo, array $dados): Response
{
    $atletaCompeticao = new AtletaCompeticao();
    $atletaCompeticao->setAtleta($dados['atleta']);
    $atletaCompeticao->competicao()->setId($_POST["competicao"]);
    $atletaCompeticao->setInformacao($_POST["informacao"]);
    
    $repo = new AtletaCompeticaoRepository($pdo);
    $repo->defineTransaction(false);
    if($repo->cadastrarAtletaCompeticao($atletaCompeticao)){
        $response = cadastrarAtletaCompeticaoCategoria($pdo, $atletaCompeticao, $dados);
        if($response->statusCode() == HttpStatus::BAD_REQUEST){
            $pdo->rollback();
            return $response;
        }
        $response = cadastrarAtletaCompeticaoDupla($pdo, $atletaCompeticao, $dados);
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

function cadastrarAtletaCompeticaoCategoria(PDO $pdo, AtletaCompeticao $atletaCompeticao, array $dados): Response
{
    $repo = new AtletaCompeticaoCategoriaRepository($pdo);
    $repo->defineTransaction(false);
    $categoriasCompeticao = $dados['atletaCompeticaoCategoria'];
    
    /* @var $atletaCompeticaoCategoria AtletaCompeticaoCategoria */
    foreach($categoriasCompeticao as $atletaCompeticaoCategoria){
        $atletaCompeticaoCategoria->setAtletaCompeticao($atletaCompeticao);
        $atletaCompeticaoCategoria->setCategoria($atletaCompeticaoCategoria->categoria());
        if(!$repo->cadastrarAtletaCompeticaoCategoria($atletaCompeticaoCategoria)){
            return Response::erro('Não foi possível cadastrar a categoria ' . $categoria->descricao() . ' ao atleta na competição');
        }
    }
    return Response::ok('');
}

function cadastrarAtletaCompeticaoDupla(PDO $pdo, AtletaCompeticao $atletaCompeticao, array $dados): Response
{
    $repo = new AtletaCompeticaoDuplaRepository($pdo);
    $repo->defineTransaction(false);
    $atletaCompeticaoDupla = new AtletaCompeticaoDupla();
    $atletaCompeticaoDupla->setAtletaCompeticao($atletaCompeticao);
    $tipoDuplas = $dados['tipo_dupla'];
    
    foreach($tipoDuplas as $valor){
        $atletaCompeticaoDupla->setTipoDupla(Sexo::from($valor));
        if(!$repo->cadastrarAtletaCompeticaoDupla($atletaCompeticaoDupla)){
            return Response::erro('Não foi possível cadastrar o tipo de dupla ' . $valor . ' do atleta na competição');
        }
    }
    
    return Response::ok('');
}

function getAtletaSelecionadoValidado(PDO $pdo): ?Atleta
{
    if($atleta = getAtletaById($pdo)){
        return $atleta;
    }

    throw new ValidatorException('Não foi possível encontrar o atleta selecionado');
}

function getAtletaCompeticaoCategoriaValidada(PDO $pdo, Atleta $atleta): array
{
    $categoriasCompeticao = [];
    if($categorias = getCategoriasFormulario($pdo)){
        foreach($categorias as $chave => $categoria){
            if(!validaCategoriaAtleta($categoria, $atleta)){
                throw new ValidatorException('A categoria ' . $categoria->descricao() . ' selecionada se torna invalida com relação a idade do(a) atleta');
            }

            $acc = new AtletaCompeticaoCategoria();
            $acc->setCategoria($categoria);
            $categoriasCompeticao[] = $acc;
        }
    }else{
        throw new ValidatorException('Não foi selecionado uma categoria');
    }

    return $categoriasCompeticao;
}

function getTipoDuplaValidado(){
    if(!$tiposDuplas = getTipoDuplaFormulario()){
        throw new ValidatorException('Não foi selecionado um tipo de dupla');
    }

    return $tiposDuplas;
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