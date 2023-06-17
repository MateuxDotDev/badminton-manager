<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();
if (!$session->isTecnico()) {
    Template::naoAutorizado();
}

Template::head('Solicitações de formação de dupla');

$pdo = Connection::getInstance();
$repo = new SolicitacaoPendenteRepository($pdo);

$tecnico = $session->getTecnico();

[
    'competicoes'  => $competicoes,
    'atletas'      => $atletas,
    'solicitacoes' => $solicitacoes
] = $repo->getViaTecnico($session->getTecnico()->id());

// TODO
// 2 abas
// enviadas / recebidas

$recebidas = array_filter($solicitacoes, function($solicitacao) use ($atletas, $tecnico) {
    $atleta = $atletas[$solicitacao->idAtletaDestinatario];
    return $atleta->tecnico()->id() == $tecnico->id();
});

$enviadas = array_filter($solicitacoes, function($solicitacao) use ($atletas, $tecnico) {
    $atleta = $atletas[$solicitacao->idAtletaRemetente];
    return $atleta->tecnico()->id() == $tecnico->id();
});

// uma aba para recebidas e outra para enviadas
// solicitação recebida: pode aceitar / rejeitar
// solicitação enviada: pode cancelar


?>

<?php Template::scripts() ?>

<?php Template::footer() ?>