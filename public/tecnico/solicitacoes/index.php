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

$idCompeticaoSelecionada = array_key_exists('competicao', $_GET) ? $_GET['competicao'] : -1;

$atletas     = array_index_by((new AtletaRepository($pdo))->getViaIds($idAtletas),         fn($a) => $a->id());
$competicoes = array_index_by((new CompeticaoRepository($pdo))->getViaIds($idCompeticoes), fn($c) => $c->id());
$categorias  = array_index_by((new CategoriaRepository($pdo))->buscarCategorias(), fn($c) => $c->id());


$enviadas = [];
$recebidas = [];

foreach ($solicitacoes as $solicitacao) {
    $idTecnicoRemetente = $atletas[$solicitacao->idAtletaRemetente]->tecnico()->id();
    if ($idTecnicoRemetente == $tecnicoLogado->id()) {
        $enviadas[] = $solicitacao;
    } else {
        $recebidas[] = $solicitacao;
    }
}

function htmlSolicitacaoRecebida(SolicitacaoPendente $solicitacao)
{
    global $atletas, $competicoes, $categorias;

    // O elemento com .solicitacao-recebida não pode ter d-flex na classe porque o atributo display inline é trocado dinamicamente conforme a competição muda
    static $template = <<<HTML
        <div class="solicitacao-recebida hover-shadow border rounded mt-3 flex-column"
             style="row-gap: 0.8rem; column-gap: 3rem;"
             data-competicao="{{ competicao_id }}"
             >
            <div class="d-flex flex-row flex-wrap p-3 gap-5 align-items-center">
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
                    </div>
                </div>
            </div>

            <div class="border-top d-flex flex-row flex-wrap align-items-center gap-3 p-3">
                {{ dupla_categoria }}
                {{ observacao }}

                <div class="ms-auto d-flex flex-row gap-2 align-items-center h-100">
                    <button class="btn btn-success btn-aceitar" data-id="{{ solicitacao_id }}" data-remetente="{{ remetente_nome }}" data-destinatario="{{ destinatario_nome }}">
                        <i class="bi bi-people"></i>
                        Aceitar
                    </button>
                    <button class="btn btn-danger btn-rejeitar" data-id="{{ solicitacao_id }}">
                        <i class="bi bi-x"></i>
                        Rejeitar
                    </button>
                </div>
            </div>
        </div>
    HTML;

    $remetente    = $atletas[$solicitacao->idAtletaRemetente];
    $destinatario = $atletas[$solicitacao->idAtletaDestinatario];

    $categoria = $categorias[$solicitacao->idCategoria];

    $campoObservacao = '';
    if (!empty($solicitacao->informacoes)) {
        $campoObservacao = Html::campo('Observações', $solicitacao->informacoes);
    }

    $tecnicoRemetente = $remetente->tecnico();

    $retorno = fill_template($template, [
        'solicitacao_id'         => $solicitacao->id,
        'remetente_nome'         => $remetente->nomeCompleto(),
        'destinatario_nome'      => $destinatario->nomeCompleto(),
        'competicao_id'          => $solicitacao->idCompeticao,
        'remetente_foto'         => Html::imgAtleta($remetente->foto(), 80),
        'remetente_descricao'    => Html::campoDescricaoAtleta($remetente),
        'remetente_tecnico'      => Html::campoAbbr('Técnico', $tecnicoRemetente->nomeCompleto(), $tecnicoRemetente->informacoes()),
        'remetente_clube'        => Html::campo('Clube', $tecnicoRemetente->clube()->nome()),
        'destinatario_foto'      => Html::imgAtleta($destinatario->foto(), 80),
        'destinatario_descricao' => Html::campoDescricaoAtleta($destinatario),
        'dupla_categoria'        => Html::campo('Categoria', $categoria->descricao()),
        'observacao'             => $campoObservacao
    ]);
    return $retorno;
}


function htmlSolicitacaoEnviada(SolicitacaoPendente $solicitacao)
{
    global $atletas, $competicoes, $categorias;

    // O elemento com .solicitacao-enviada não pode ter d-flex na classe porque o atributo display inline é trocado dinamicamente conforme a competição muda
    static $template = <<<HTML
        <div class="solicitacao-enviada hover-shadow border rounded mt-3 flex-column"
             style="row-gap: 0.8rem; column-gap: 3rem;"
         >

            <div class="d-flex flex-row flex-wrap gap-5 p-3 align-items-center">
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
                    </div>
                </div>
            </div>

            <div class="border-top d-flex flex-row flex-wrap gap-3 p-3 align-items-center">
                {{ dupla_categoria }}
                {{ observacao }}
                <div class="ms-auto d-flex flex-row gap-2 align-items-center h-100">
                    <button class="btn btn-danger btn-cancelar" data-id="{{ solicitacao_id }}">
                        <i class="bi bi-x"></i>
                        Cancelar
                    </button>
                </div>
            </div>

        </div>
    HTML;

    $remetente    = $atletas[$solicitacao->idAtletaRemetente];
    $destinatario = $atletas[$solicitacao->idAtletaDestinatario];

    $categoria = $categorias[$solicitacao->idCategoria];

    $campoObservacao = '';
    if (!empty($solicitacao->informacoes)) {
        $campoObservacao = Html::campo('Observações', $solicitacao->informacoes);
    }

    $tecnicoDest = $destinatario->tecnico();

    $retorno = fill_template($template, [
        'solicitacao_id'         => $solicitacao->id,
        'competicao_id'          => $solicitacao->idCompeticao,
        'destinatario_foto'      => Html::imgAtleta($destinatario->foto(), 80),
        'destinatario_descricao' => Html::campoDescricaoAtleta($destinatario),
        'destinatario_tecnico'   => Html::campoAbbr('Técnico', $tecnicoDest->nomeCompleto(), $tecnicoDest->informacoes()),
        'destinatario_clube'     => Html::campo('Clube', $tecnicoDest->clube()->nome()),
        'remetente_foto'         => Html::imgAtleta($remetente->foto(), 80),
        'remetente_descricao'    => Html::campoDescricaoAtleta($remetente),
        'dupla_categoria'        => Html::campo('Categoria', $categoria->descricao()),
        'observacao'             => $campoObservacao,
    ]);
    return $retorno;
}

?>

<main class="container">
    <div class="d-flex flex-row align-items-center">
        <h1>Solicitações pendentes</h1>
        <div class="ms-auto d-flex flex-row gap-3 align-items-center <?= empty($competicoes) ? 'd-none' : ''?>">
            <span>Competição</span>
            <select class="form-control" id="select-competicao">
                <?php
                    foreach ($competicoes as $competicao) {
                        $selected = $competicao->id() == $idCompeticaoSelecionada ? 'selected' : '';
                        printf(
                            '<option %s value=%d>%s</option>',
                            $selected,
                            $competicao->id(),
                            $competicao->nome(),
                        );
                    }
                ?>
            </select>
        </div>
    </div>
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
            echo Html::alerta('warning', 'Nenhuma solicitação recebida pendente', ['id' => 'nenhuma-solicitacao-recebida']);
            foreach ($recebidas as $solicitacao) {
                echo htmlSolicitacaoRecebida($solicitacao);
            }
            ?>
        </div>
        <div class="tab-pane" id="tab-enviadas">
            <?php
            echo Html::alerta('warning', 'Nenhuma solicitação enviada pendente', ['id' => 'nenhuma-solicitacao-enviada']);
            foreach ($enviadas as $solicitacao) {
                echo htmlSolicitacaoEnviada($solicitacao);
            }
            ?>
        </div>
    </div>
</main>

<?php Template::scripts() ?>

<script>
    const nenhumaRecebida = qs('#nenhuma-solicitacao-recebida');
    const nenhumaEnviada  = qs('#nenhuma-solicitacao-enviada');

    const solicitacoesRecebidas = qsa('.solicitacao-recebida');
    const solicitacoesEnviadas  = qsa('.solicitacao-enviada');

    const selectCompeticao = qs('#select-competicao');

    selectCompeticao.addEventListener('change', competicaoSelecionadaMudou);
    competicaoSelecionadaMudou();

    document.querySelectorAll('.btn-rejeitar').forEach(btn => {
        const id = btn.getAttribute('data-id');
        btn.id = `btn-rejeitar-${id}`;

        btn.addEventListener('click', async () => {
            const ok = await confirmarExclusao('Essa solicitação para formar dupla será rejeitada.', {
                confirmButtonText: 'Rejeitar'
            });
            if (!ok) return;
            await realizarAcao(id, 'rejeitar');
        })
    });

    document.querySelectorAll('.btn-aceitar').forEach(btn => {
        const id = btn.getAttribute('data-id');
        btn.id = `btn-aceitar-${id}`;
        const remetenteNome    = btn.getAttribute('data-remetente');
        const destinatarioNome = btn.getAttribute('data-destinatario');

        btn.addEventListener('click', async () => {
            const ok = await confirmarSucesso(`Aceitando essa solicitação, você irá formar uma dupla entre ${remetenteNome} e ${destinatarioNome}`, {
                confirmButtonText: 'Formar dupla',
            });
            if (!ok) return;
            await realizarAcao(id, 'aceitar');
        });
    });


    document.querySelectorAll('.btn-cancelar').forEach(btn => {
        btn.addEventListener('click', async () => {
            const ok = await confirmarExclusao('Essa solicitação para formar dupla será cancelada.', {
                confirmButtonText: 'Cancelar'
            });
            if (!ok) return;
            const id = btn.getAttribute('data-id');
            await realizarAcao(id, 'cancelar');
        })
    });

    async function realizarAcao(id, acao) {
        const req = { acao, id };
        const resp = await fetch('/tecnico/solicitacoes/acao.php', {
            method: 'POST',
            body: JSON.stringify(req),
        });
        const text = await resp.text();

        try {
            const json = JSON.parse(text);
            if (resp.ok) {
                agendarAlertaSucesso(json.mensagem);
                location.reload();
            } else {
                Toast.fire({
                    icon: 'error',
                    text: json.mensagem,
                });
            }
        } catch (err) {
            console.error('Erro parse', { err, text });
        }
    }

    function competicaoSelecionadaMudou() {
        const competicaoSelecionada = selectCompeticao.value;
        
        let enviadasMostradas = 0;
        for (const card of solicitacoesEnviadas) {
            const competicao = card.getAttribute('data-competicao');
            const mostrar    = competicao == competicaoSelecionada;
            card.style.display = mostrar ? 'flex' : 'none';
            enviadasMostradas += mostrar ? 1 : 0;
        }
        nenhumaEnviada.style.display = enviadasMostradas == 0 ? 'block' : 'none';

        let recebidasMostradas = 0;
        for (const card of solicitacoesRecebidas) {
            const competicao = card.getAttribute('data-competicao');
            const mostrar    = competicao == competicaoSelecionada;
            card.style.display  = mostrar ? 'flex' : 'none';
            recebidasMostradas += mostrar ? 1 : 0;
        }
        nenhumaRecebida.style.display = recebidasMostradas == 0 ? 'block' : 'none';
    }

    window.addEventListener('load', () => {
        const getParams = new URLSearchParams(window.location.search);
        const idSolicitacao = getParams.get('solicitacao');
        const acao = getParams.get('acao');

        if (idSolicitacao && acao) {
            const btn = qs(`#btn-${acao}-${idSolicitacao}`);
            if (btn && btn.click) {
                btn.click();
            } else {
                alertaErro('Solicitação não encontrada');
            }
        }
    });
</script>

<?php Template::footer() ?>
