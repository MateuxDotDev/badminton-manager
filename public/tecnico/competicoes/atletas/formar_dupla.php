<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\TecnicoRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();

Template::head('Solicitação para formar dupla');
if (!$session->isTecnico()) {
    // TODO (task futura) Por enquanto a parte para técnico não logado não está implementada
    Template::naoAutorizado();
}
Template::nav($session);

$pdo = Connection::getInstance();

if (!array_key_exists('atleta', $_GET)) {
    echo 'Atleta não encontrado';
    return;
}

$idAtleta = $_GET['atleta'];

$atletaRepo  = new AtletaRepository($pdo);
$tecnicoRepo = new TecnicoRepository($pdo);

$atleta = $atletaRepo->getViaId($idAtleta);

if ($atleta == null) {
    echo 'Atleta não encontrado';
    return;
}

$atleta->setTecnico($tecnicoRepo->getViaAtleta($atleta->id()));


dump($atleta);

// TODO prosseguir quando tiver o AtletaCompeticao, AtletaCompeticaoRepository etc. (merge)
?>

<?php Template::scripts() ?>

<?php Template::footer() ?>