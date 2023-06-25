<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Mail\RejeitarSolicitacaoMail;
use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Dupla\DuplaRepository;
use App\Tecnico\Solicitacao\AcaoSolicitacao;
use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;
use App\Util\Database\Connection;
use App\Util\Mail\Mailer;
use App\Notificacao\TipoNotificacao;

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

    return new AcaoSolicitacao($pdo, $session, $dataAgora, $notificacaoRepo, $concluidaRepo, $duplaRepo);
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

        $acao = construirAcaoSolicitacao($pdo);
        $acao->rejeitar($id);

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

    $competicoesRepo  = new CompeticaoRepository($pdo);
    $solicitacoesRepo = new SolicitacaoPendenteRepository($pdo);
    $notificacoesRepo = new NotificacaoRepository($pdo);
    $duplaRepo = new DuplaRepository($pdo);
    $mailRepo = new MailRepository($pdo);
    $tecnicoRepo = new TecnicoRepository($pdo);
    $atletaRepo = new AtletaRepository($pdo);
    $categoriaRepo = new CategoriaRepository($pdo);

    try {
        $pdo->beginTransaction();

        $acao = construirAcaoSolicitacao($pdo);
        $idNotificacao = $acao->cancelar($id);

        $atletaDest = $atletaRepo->getViaId($dto->idAtletaDestinatario);
        $atletaRem = $atletaRepo->getViaId($dto->idAtletaRemetente);
        $tecnicoDest = $tecnicoRepo->getViaAtleta($atletaDest->id());
        $tecnicoRem = $tecnicoRepo->getViaAtleta($atletaRem->id());
        $categoria = $categoriaRepo->getCategoriaById($dto->idCategoria);
        $notificacoes = $notificacoesRepo->getViaId1($id, TipoNotificacao::SOLICITACAO_RECEBIDA_REJEITADA);
        $notificacaoIdRem = 0;
        foreach ($notificacoes as $notificacao) {
            if ($notificacao['tecnico_id'] == $tecnicoRem->id()) {
                $notificacaoIdRem = $notificacao['id'];
                break;
            }
        }

        $mail = new RejeitarSolicitacaoMail(new Mailer());

        $pdo->commit();
        return Response::ok('Solicitação cancelada com sucesso.');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
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
