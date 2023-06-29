<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Dupla\DuplaRepository;
use App\Tecnico\Solicitacao\AcaoSolicitacao;
use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Util\Http\Response;
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
