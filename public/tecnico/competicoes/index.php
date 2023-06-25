<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Competicoes\CompeticaoRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Template\Template;

try {
    $pdo = Connection::getInstance();
    $repo = new CompeticaoRepository($pdo);
    $competicoes = $repo->competicoesAbertas();
    $session = UserSession::obj();

    $displayAlerta = empty($competicoes) ? 'block' : 'none';
    $displayTabela = empty($competicoes) ? 'none' : 'table';
} catch (Exception $e) {
    Response::erroException($e);
}

Template::head('Competições abertas');

Template::nav($session);

?>

<main class="container">
    <h1 class="mt-3">Competições abertas</small></h1>
    <div class="input-group mt-5">
        <input class="form-control" type="search" id="pesquisa" placeholder="Digite o nome ou descrição de uma competição..."/>
        <span class="input-group-text input-group-prepend">
            <i class="bi bi-search"></i>
        </span>
    </div>

    <div id="nenhuma-competicao-encontrada" class="alert alert-info mt-3 d-none">
        Nenhuma competição encontrada
    </div>

    <section id="competicoes" class="row mt-3"></section>

    <template id="template-competicao">
        <div class="col-12 col-lg-6 competicao-col gy-4">
            <article class="d-flex competicao-card card card-body bg-light border-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Liga Nacional de Badminton</h4>
                    <span>
                        <i class="bi bi-calendar2-event me-2"></i>
                        <small class="help-cursor"></small>
                    </span>
                </div>
                <p class="my-3"></p>
                <div class="row gy-2">
                    <div class="col-12 col-xxl-6">
                        <a class="btn btn-outline-secondary btn-solicitacoes w-100" title="Solicitações pendentes">
                            <i class="bi bi-send"></i>
                            <span>Solicitações pendentes</span>
                        </a>
                    </div>
                    <div class="col-12 col-xxl-6">
                        <a class="btn btn-outline-success btn-cadastrar w-100" title="Cadastrar atleta">
                            <i class="bi bi-person-plus"></i>
                            <span>Cadastrar atleta</span>
                        </a>
                    </div>
                    <div class="col-12 col-xxl-6 offset-xxl-3">
                        <a class="btn btn-outline-primary btn-atletas  w-100" title="Ver atletas">
                            <i class="bi bi-person"></i>
                            <span>Ver atletas</span>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </template>
</main>

<?php Template::scripts() ?>

<script>
    const competicoes = <?= json_encode(array_map(fn($c) => $c->toJson(), $competicoes)) ?>;

    const inputPesquisa = qs('#pesquisa');
    const nenhumaEncontrada = qs('#nenhuma-competicao-encontrada');
    const containerCompeticoes = qs('#competicoes');
    const template = qs('#template-competicao');

    window.addEventListener('load', () => {
        const cards = competicoes.map(competicao => {
           const card = template.content.cloneNode(true);

           eqs(card, 'article').setAttribute('data-id', competicao.id);
           eqs(card, 'h4').textContent = competicao.nome;
           eqs(card, 'span > small').textContent = dataBr(new Date(competicao.prazo));
           eqs(card, 'span > small').title = `Faltam ${diasFaltando(new Date(competicao.prazo))} dias.`;
           eqs(card, 'p').textContent = competicao.descricao;
           eqs(card, '.btn-cadastrar')
               .setAttribute('href', `/tecnico/competicoes/atletas/incluir/?competicao=${competicao.id}`);
           eqs(card, '.btn-solicitacoes')
               .setAttribute('href', `/tecnico/solicitacoes?competicao?competicao=${competicao.id}`);
           eqs(card, '.btn-atletas')
               .setAttribute('href', `/tecnico/competicoes/atletas/?competicao=${competicao.id}`);

          return card;
        });

        containerCompeticoes.append(...cards);
    });

    inputPesquisa.addEventListener('keydown', debounce(300, () => {
        const termos = (inputPesquisa.value ?? '');
        pesquisar(termos)
    }));


    const chavesPesquisa = ['nome', 'descricao', 'prazo'];

    function pesquisar(pesquisa) {
        if (pesquisa.length === 0) {
            qsa('.competicao-col').forEach(card => card.classList.remove('d-none'));
            nenhumaEncontrada.classList.add('d-none');
        } else {
            const competicoesFiltradas = filterByMultipleKeys(competicoes, chavesPesquisa, pesquisa).map(c => c.id);
            if (competicoesFiltradas.length > 0) {
                nenhumaEncontrada.classList.add('d-none');
            } else {
                nenhumaEncontrada.classList.remove('d-none')
            }
            qsa('.competicao-col').forEach(card => {
                const id = eqs(card, 'article').getAttribute('data-id').match(/\d+/g).map(Number)[0];
                if (competicoesFiltradas.includes(id)) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
        }
    }
</script>

<?php Template::footer() ?>
