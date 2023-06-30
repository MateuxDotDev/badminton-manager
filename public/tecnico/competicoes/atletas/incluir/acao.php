<?php

require_once(__DIR__ . '/../../../../../vendor/autoload.php');

use App\Categorias\CategoriaRepository;
use App\Competicoes\Competicao;
use App\Competicoes\CompeticaoRepository;
use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Conta\Cadastrar;
use App\Tecnico\Conta\CadastroDTO;
use App\Tecnico\Conta\LoginDTO;
use App\Tecnico\Conta\RealizarLogin;
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Mail\Service\MailService;
use App\Util\Services\UploadImagemService\UploadImagemService;

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
    try {
        $pdo->beginTransaction();

        $session = UserSession::obj();
        if (!$session->isTecnico()) {
            $tecnicoRepo = new TecnicoRepository($pdo);

            $excecaoCadastro = null;
            try {
                $cadastrar = new Cadastrar($tecnicoRepo);
                $cadastrar(new CadastroDTO(
                    $_POST['novo_tecnico_email'],
                    $_POST['novo_tecnico_nome'],
                    $_POST['novo_tecnico_senha'],
                    $_POST['novo_tecnico_clube'],
                    $_POST['novo_tecnico_informacoes'],
                ));
            } catch (Exception $excecaoCadastro) {}

            $excecaoLogin = null;
            try {
                $realizarLogin = new RealizarLogin($tecnicoRepo, $session);
                $realizarLogin(new LoginDTO(
                    $_POST['novo_tecnico_email'],
                    $_POST['novo_tecnico_senha'],
                ));
            } catch (Exception $excecaoLogin) {}

            // O ideal na verdade seria:
            // Quando cadastro falha porque já existe usuário com esse e-mail, tentar fazer login;
            // Quando cadastro falha por outro motivo (e.g. e-mail inválido), já retornar esse motivo.
            // Como não temos uma forma muito boa de detectar o motivo da falha no cadastro
            // (teria que ver se o getMesasge() contém uma substring tipo "já existe", gambiarra),
            // vai ficar assim mesmo.

            if ($excecaoCadastro || $excecaoLogin) {
                throw new ValidatorException($excecaoCadastro->getMessage().'; '.$excecaoLogin->getMessage());
            }
        }

        $response = null;
        if ($_POST['userChoice']) {
            $response = $_POST['userChoice'] == 1
                ? realizarCadastroAtletaSelecionado($pdo)
                : realizarCadastroComNovoAtleta($pdo);
        } else {
            $response = Response::erro(
                'Não foi possível identificar a opção de cadastro selecionada. ' .
                'Por favor escolha entre "Selecionar atleta cadastrado" ou "Cadastrar novo atleta".'
            );
        }

        $pdo->commit();
        return $response;
    } catch (Exception $e) {
        $pdo->rollback();
        return Response::erroException($e);
    }
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

    $response = cadastrarAtletaCompeticao($pdo, $atletaCompeticao);
    return $response;
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

    cadastrarNovoAtleta($pdo, $atleta);
    $response = cadastrarAtletaCompeticao($pdo, $atletaCompeticao);
    return $response;
}

/**
 * @throws ValidatorException
 * @throws Exception
 */
function cadastrarNovoAtleta($pdo, Atleta $atleta): void
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
        return;
    }

    throw new ValidatorException('Erro ao cadastrar atleta');
}

/**
 * @throws Exception
 */
function cadastrarAtletaCompeticao(PDO $pdo, AtletaCompeticao $dados): Response
{
    $repo = new AtletaCompeticaoRepository($pdo);

    if ($repo->incluirAtletaCompeticao($dados)) {
        $notificacaoRepo = new NotificacaoRepository($pdo);
        $mailService = new MailService($pdo);

        $notificacao = Notificacao::inclusaoCompeticao($dados->atleta()->tecnico()->id(), $dados->competicao()->id());
        $notificacao->setId($notificacaoRepo->criar($notificacao));
        $mailService->enviarDeNotificacao($notificacao);

        return Response::ok('Atleta inserido na competição');
    } else {
        return Response::erro('Algo deu errado ao inserir o atleta na competição');
    }
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
            $categoria = $repoCategoria->getById($valor);
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

    $tecnico = UserSession::obj()->getTecnico();

    return (new Atleta())
        ->setNomeCompleto($req['cadastrar_nomeCompleto'])
        ->setSexo(Sexo::from($req['cadastrar_sexo']))
        ->setDataNascimento($dataNascimento)
        ->setInformacoesAdicionais($req['cadastrar_observacoes'])
        ->setTecnico($tecnico);
}
