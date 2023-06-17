<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaRepository;
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

$tecnicoLogado = $session->getTecnico();

$solicitacoes = (new SolicitacaoPendenteRepository($pdo))->getViaTecnico($tecnicoLogado->id());

$idAtletas     = [];
$idCompeticoes = [];
foreach ($solicitacoes as $solicitacao) {
    // Pode haver duplicados, mas não importa
    $idAtletas[] = $solicitacao->idAtletaDestinatario;
    $idAtletas[] = $solicitacao->idAtletaRemetente;
    $idCompeticoes[] = $solicitacao->idCompeticao;
}

$atletas     = array_index_by((new AtletaRepository($pdo))->getViaIds($idAtletas),         fn($a) => $a->id());
$competicoes = array_index_by((new CompeticaoRepository($pdo))->getViaIds($idCompeticoes), fn($c) => $c->id());
$categorias  = (new CategoriaRepository($pdo))->buscarCategorias();

dump($solicitacoes);
dump($atletas);
dump($competicoes);
dump($categorias);


$enviadas = [];
$recebidas = [];

foreach ($solicitacoes as $solicitacao) {
    if ($solicitacao->idAtletaRemetente == $tecnicoLogado->id()) {
        $enviadas[] = $solicitacao;
    } else {
        $recebidas[] = $solicitacao;
    }
}

?>

<?php Template::scripts() ?>

<?php Template::footer() ?>