<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Solicitacao\SolicitacaoPendente;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Util\Database\Connection;
use App\Util\General\Html;
use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();
if (!$session->isTecnico()) {
    Template::naoAutorizado();
}

Template::head('Solicitações de formação de dupla');
Template::nav($session);

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
$categorias  = array_index_by((new CategoriaRepository($pdo))->buscarCategorias(), fn($c) => $c->id());


$enviadas = [];
$recebidas = [];

$idTecnicoRemetente = $atletas[$solicitacao->idAtletaRemetente]->tecnico()->id();
foreach ($solicitacoes as $solicitacao) {
    if ($idTecnicoRemetente == $tecnicoLogado->id()) {
        $enviadas[] = $solicitacao;
    } else {
        $recebidas[] = $solicitacao;
    }
}

// TODO <select> de competições no topo da página ao lado do <h1> "Solicitações Pendentes"

function htmlSolicitacaoRecebida(SolicitacaoPendente $solicitacao)
{
    global $atletas, $competicoes, $categorias;

    static $template = '
        <div class="hover-shadow border rounded mt-3 d-flex flex-row flex-wrap p-3 pt-2 align-items-center " style="row-gap: 0.8rem; column-gap: 3rem;">
            <div class="d-flex flex-column gap-2">
                <small class="text-secondary">Remetente</small>
                <div class="d-flex flex-row gap-3 align-items-center">
                    <div class="d-flex flex-row gap-3">
                        {{ remetente_foto }}
                        {{ remetente_descricao }}
                    </div>
                    {{ remetente_tecnico }}
                    {{ remetente_clube }}
                </div>
            </div>
            <div class="d-flex flex-column gap-2">
                <small class="text-secondary">Deseja formar dupla com</small>
                <div class="d-flex flex-row gap-3 align-items-center">
                    <div class="d-flex flex-row gap-3">
                        {{ destinatario_foto }}
                        {{ destinatario_descricao }}
                    </div>
                    {{ dupla_categoria }}
                </div>
            </div>
            <div class="ms-auto d-flex flex-row gap-2 align-items-center h-100">
                <button class="btn btn-success">
                    <i class="bi bi-people"></i>
                    Aceitar
                </button>
                <button class="btn btn-danger">
                    <i class="bi bi-x"></i>
                    Rejeitar
                </button>
            </div>
        </div>
    ';

    $remetente    = $atletas[$solicitacao->idAtletaRemetente];
    $destinatario = $atletas[$solicitacao->idAtletaDestinatario];

    $categoria = $categorias[$solicitacao->idCategoria];

    $retorno = fill_template($template, [
        'remetente_foto'   => Html::imgAtleta($remetente->foto(), 80),
        'remetente_descricao'    => Html::campoDescricaoAtleta($remetente),
        'remetente_tecnico'      => Html::campo('Técnico', $remetente->tecnico()->nomeCompleto()),  // TODO abbr informações
        'remetente_clube'        => Html::campo('Clube', $remetente->tecnico()->clube()->nome()),
        'destinatario_foto'      => Html::imgAtleta($destinatario->foto(), 80),
        'destinatario_descricao' => Html::campoDescricaoAtleta($destinatario),
        'dupla_categoria'        => Html::campo('Categoria', $categoria->descricao()),
    ]);
    return $retorno;
}


function htmlSolicitacaoEnviada(SolicitacaoPendente $solicitacao)
{
    global $atletas, $competicoes, $categorias;

    static $template = '
        <div class="hover-shadow border rounded mt-3 d-flex flex-row flex-wrap p-3 pt-2 align-items-center " style="row-gap: 0.8rem; column-gap: 3rem;">
            <div class="d-flex flex-column gap-2">
                <small class="text-secondary">Seu atleta</small>
                <div class="d-flex flex-row gap-3 align-items-center">
                    <div class="d-flex flex-row gap-3">
                        {{ remetente_foto }}
                        {{ remetente_descricao }}
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column gap-2">
                <small class="text-secondary">Destinatário</small>
                <div class="d-flex flex-row gap-3 align-items-center">
                    <div class="d-flex flex-row gap-3">
                        {{ destinatario_foto }}
                        {{ destinatario_descricao }}
                    </div>
                    {{ destinatario_tecnico }}
                    {{ destinatario_clube }}
                    {{ dupla_categoria }}
                </div>
            </div>
            <div class="ms-auto d-flex flex-row gap-2 align-items-center h-100">
                <button class="btn btn-danger">
                    <i class="bi bi-x"></i>
                    Cancelar
                </button>
            </div>
        </div>
    ';

    $remetente    = $atletas[$solicitacao->idAtletaRemetente];
    $destinatario = $atletas[$solicitacao->idAtletaDestinatario];

    $categoria = $categorias[$solicitacao->idCategoria];

    $retorno = fill_template($template, [
        'destinatario_foto'      => Html::imgAtleta($destinatario->foto(), 80),
        'destinatario_descricao' => Html::campoDescricaoAtleta($destinatario),
        'destinatario_tecnico'   => Html::campo('Técnico', $destinatario->tecnico()->nomeCompleto()),  // TODO abbr informações
        'destinatario_clube'     => Html::campo('Clube', $destinatario->tecnico()->clube()->nome()),
        'remetente_foto'         => Html::imgAtleta($remetente->foto(), 80),
        'remetente_descricao'    => Html::campoDescricaoAtleta($remetente),
        'dupla_categoria'        => Html::campo('Categoria', $categoria->descricao()),
    ]);
    return $retorno;
}

// TODO mensagens caso não tenham solicitações enviadas/recebidas
// "Nenhuma solicitação enviada" div.alert.alert-warning
?>

<main class="container">
    <h1>Solicitações pendentes</h1>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-recebidas">Recebidas</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-enviadas">Enviadas</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane show active" id="tab-recebidas">
            <?php
            if (empty($recebidas)) {
                echo Html::alerta('warning', 'Nenhuma solicitação recebida pendente');
            }
            foreach ($recebidas as $solicitacao) {
                echo htmlSolicitacaoRecebida($solicitacao);
            }
            ?>
        </div>
        <div class="tab-pane" id="tab-enviadas">
            <?php
            if (empty($enviadas)) {
                echo Html::alerta('warning', 'Nenhuma solicitação enviada pendente');
            }
            foreach ($enviadas as $solicitacao) {
                echo htmlSolicitacaoEnviada($solicitacao);
            }
            ?>
        </div>
    </div>
</main>

<?php Template::scripts() ?>

<?php Template::footer() ?>