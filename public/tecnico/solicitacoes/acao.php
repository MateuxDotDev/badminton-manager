<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Mail\RejeitarSolicitacaoMail;
use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Notificacao\TipoNotificacao;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Dupla\DuplaRepository;
use App\Tecnico\Solicitacao\AcaoSolicitacao;
use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Tecnico\Solicitacao\SolicitacaoPendente;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Tecnico\TecnicoRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;
use App\Util\Database\Connection;
use App\Util\Mail\Mailer;
use App\Util\Mail\Service\MailService;

try {
    solicitacaoController()->enviar();
} catch (Exception $e) {
    Response::erroException($e)->enviar();
}


/**
 * @throws ValidatorException
 */
function solicitacaoController(): Response
{
    $req = Request::getDados();
    $acao = Request::getAcao($req);

    $session = UserSession::obj();
    if (!$session->isTecnico()) {
        return Response::erroNaoAutorizado();
    }

    return match ($acao) {
        'rejeitar' => rejeitarSolicitacao($req),
        'cancelar' => cancelarSolicitacao($req),
        'aceitar' => aceitarSolicitacao($req),
        default => Response::erro("Ação '$acao' desconhecida")
    };
}


function validarSolicitacaoId(array $req): int
{
    Request::camposRequeridos($req, ['id']);
    $id = filter_var($req['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new ValidatorException('O ID da solicitação é inválido');
    }
    return $id;
}


function construirAcaoSolicitacao(PDO $pdo): AcaoSolicitacao
{
    $session = UserSession::obj();
    $dataAgora = new DateTimeImmutable('now');
    $notificacaoRepo = new NotificacaoRepository($pdo);
    $concluidaRepo = new SolicitacaoConcluidaRepository($pdo);
    $duplaRepo = new DuplaRepository($pdo);
    $mailService = new MailService($pdo);

    return new AcaoSolicitacao($pdo, $session, $dataAgora, $notificacaoRepo, $concluidaRepo, $duplaRepo, $mailService);
}

/**
 * @throws ValidatorException
 */
function rejeitarSolicitacao(array $req): Response
{
    $pdo = Connection::getInstance();
    $id = validarSolicitacaoId($req);

    try {
        $pdo->beginTransaction();

        $solicitacoesRepo = new SolicitacaoPendenteRepository($pdo);
        $solicitacao = $solicitacoesRepo->getViaId($id);

        $acao = construirAcaoSolicitacao($pdo);
        $notificacoes = $acao->rejeitar($id);

        foreach ($notificacoes as $notificacao) {
            if ($notificacao['tipo'] == TipoNotificacao::SOLICITACAO_ENVIADA_REJEITADA) {
                rejeitarSolicitacaoMail($pdo, $solicitacao, $notificacao['id']);
            }
        }

        $pdo->commit();
        return Response::ok('Solicitação rejeitada com sucesso.');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * @throws ValidatorException
 */
function cancelarSolicitacao(array $req): Response
{
    $pdo = Connection::getInstance();
    $id = validarSolicitacaoId($req);

    try {
        $pdo->beginTransaction();

        $acao = construirAcaoSolicitacao($pdo);
        $acao->cancelar($id);

        $pdo->commit();
        return Response::ok('Solicitação cancelada com sucesso.');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * @throws Exception
 */
function rejeitarSolicitacaoMail(PDO $pdo, SolicitacaoPendente $solicitacao, int $idNotificacao): bool
{
    $mailRepo = new MailRepository($pdo);
    $tecnicoRepo = new TecnicoRepository($pdo);
    $atletaRepo = new AtletaRepository($pdo);
    $categoriaRepo = new CategoriaRepository($pdo);
    $competicaoRepo = new CompeticaoRepository($pdo);

    $atletaDest = $atletaRepo->getViaId($solicitacao->idAtletaRemetente);
    $atletaRem = $atletaRepo->getViaId($solicitacao->idAtletaDestinatario);
    $tecnicoDest = $tecnicoRepo->getViaAtleta($atletaDest->id());
    $categoria = $categoriaRepo->getById($solicitacao->idCategoria);
    $competicao = $competicaoRepo->getViaId($solicitacao->idCompeticao);

    $mail = new RejeitarSolicitacaoMail(new Mailer());

    $mail->fillTemplate([
        'dest_tecnico' => $tecnicoDest->nomeCompleto(),
        'dest_nome' => $atletaDest->nomeCompleto(),
        'rem_nome' => $atletaRem->nomeCompleto(),
        'competicao' => $competicao->nome(),
        'dest_sexo' => $atletaDest->sexo()->toString(),
        'rem_sexo' => $atletaRem->sexo()->toString(),
        'dest_idade' => Dates::age($atletaDest->dataNascimento()),
        'rem_idade' => Dates::age($atletaRem->dataNascimento()),
        'dest_nascimento' => Dates::formatDayBr($atletaDest->dataNascimento()),
        'rem_nascimento' => Dates::formatDayBr($atletaRem->dataNascimento()),
        'dest_info' => $atletaDest->informacoesAdicionais(),
        'rem_info' => $atletaRem->informacoesAdicionais(),
        'categoria' => $categoria->descricao(),
        'observacoes' => $solicitacao->informacoes,
        'ano_atual' => Dates::currentYear(),
    ]);

    $mailDto = new EmailDTO(
        toName:        $tecnicoDest->nomeCompleto(),
        toEmail:       $tecnicoDest->email(),
        subject:       $mail->getSubject(),
        body:          $mail->getBody(),
        altBody:       $mail->getAltBody(),
        idNotificacao: $idNotificacao
    );

    return $mailRepo->criar($mailDto) > 0;
}

/**
 * @throws ValidatorException
 */
function aceitarSolicitacao(array $req): Response
{
    $pdo = Connection::getInstance();
    $id = validarSolicitacaoId($req);

    try {
        $pdo->beginTransaction();

        $acao = construirAcaoSolicitacao($pdo);
        $acao->aceitar($id);

        $pdo->commit();
        return Response::ok('Dupla formada com sucesso!');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
