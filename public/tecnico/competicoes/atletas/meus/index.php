<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Template\Template;

$hasError = false;

try {
    $session = UserSession::obj();

    if (!$session->isTecnico()) {
        Response::erroNaoAutorizado()->enviar();
    }

    $idCompeticao = $_GET['competicao'] ?? null;

    if (!$idCompeticao) {
        Response::erro('É necessário informar a competição.')->enviar();
    }

    $pdo = Connection::getInstance();

    $compRepo = new CompeticaoRepository($pdo);
    $competicao = $compRepo->getViaId($idCompeticao);

    if ($competicao == null) {
        Response::erro('Competição não encontrada.')->enviar();
    }

    $idTecnico = $session->getTecnico()->id();

    $atletaCompRepo = new AtletaCompeticaoRepository($pdo);
    $atletas = $atletaCompRepo->getViaTecnico($idTecnico, $idCompeticao);
} catch (Exception $e) {
    $hasError = true;
}

Template::head('Meus Atletas');
Template::nav($session);

?>

<style>
    #conteudo {
        display: grid;
        gap: 1rem;
    }

    #pesquisa {
        max-width: 512px;
    }
</style>

<main class="container">
    <section class="d-flex justify-content-between align-items-center mb-4">
        <h1 id="titulo mb-0">Meus Atletas</h1>
    </section>

    <div class="mb-3">
        <label class="form-label">Competição</label>
        <input type="text" class="form-control" readonly disabled value="<?= $competicao->nome() ?>">
    </div>

    <section class="input-group my-4 d-flex justify-content-center">
        <span class="input-group-text input-group-prepend">
            <i class="bi bi-search"></i><label for="pesquisa" class="d-none d-md-inline-block ms-2">Pesquisar</label>
        </span>
        <input class="form-control" type="search" id="pesquisa" placeholder="Digite aqui informações do atleta que deseja buscar..." />
    </section>

    <section id="sem-atletas" class="d-none alert alert-info">
        <p class="mb-0">
            <i class="bi bi-info-circle"></i> Nenhum atleta na competição. <a href="../incluir/?competicao=<?= $competicao->id() ?>">Clique aqui</a> para incluir um novo atleta.
        </p>
    </section>

    <?php if ($hasError) : ?>
        <section class="alert alert-danger">
            <p class="mb-0">
                <i class="bi bi-exclamation-circle"></i> Ocorreu um erro ao carregar os atletas. Tente novamente mais tarde.
            </p>
        </section>
    <?php endif ?>

    <section id="conteudo">
    </section>
</main>

<?php Template::scripts() ?>

<?php require_once './atleta-card.html'; ?>

<script>
    const atletas = <?= json_encode($atletas) ?>;

    const conteudo = document.querySelector('#conteudo');
    const inputPesquisa = document.querySelector('#pesquisa');
    const componentesAtletas = [];
    const template = document.querySelector('#atleta-card-template');
    const semAtletas = document.querySelector('#sem-atletas');

    const createAtletaCard = (atleta) => {
        let card = template.content.cloneNode(true);

        card.querySelector('.atleta-card').id = `atleta-${atleta.id}`;
        card.querySelector('.nome_completo').textContent = atleta.nome_completo;
        card.querySelector('.idade').textContent = `${atleta.idade} anos`;
        card.querySelector('.data_nascimento').textContent = atleta.data_nascimento;
        card.querySelector('.sexo').textContent = atleta.sexo;
        card.querySelectorAll('.botao-acao').forEach(botao => botao.setAttribute('data-atleta-id', atleta.id));

        const atletaInfos = `${atleta.informacoes && `${atleta.informacoes}. `}${atleta.informacoes_atleta_competicao}`
        if (atletaInfos) {
            card.querySelector('.informacoes_adicionais').textContent = atletaInfos;
        } else {
            card.querySelector('.informacoes_adicionais').classList.add('d-none');
            card.querySelector('.info-adicional-titulo').innerText = 'Sem informações adicionais';
        }
        const img = card.querySelector('.profile-pic');

        img.setAttribute('src', `/assets/images/profile/${atleta.path_foto}`);
        img.setAttribute('alt', `Foto de perfil de ${atleta.nome_completo}`);

        const buscaDuplas = card.querySelector('.sexo_duplas')
        for (const sexo of atleta.sexo_duplas) {
            buscaDuplas.append(iconeSexo(sexo));
        }

        const buscaCategorias = card.querySelector('.categorias');
        buscaCategorias.innerText = atleta.categorias.join(', ');

        return card;
    }

    window.addEventListener('load', () => {
        if (atletas.length === 0) {
            semAtletas.classList.remove('d-none');
            return;
        }
        atletas.forEach(atleta => {
            const card = createAtletaCard(atleta);
            conteudo.appendChild(card);
        });

        componentesAtletas.push(...document.querySelectorAll('.atleta-card'));
        inputPesquisa.addEventListener('keydown', debounce(300, () => {
            pesquisar(inputPesquisa.value.trim() ?? '');
        }));
    });

    const chavesPesquisa = ['nome_completo', 'idade', 'data_nascimento', 'sexo', 'informacoes', 'informacoes_atleta_competicao', 'categorias'];

    function pesquisar(pesquisa) {
        const atletasFiltrados = filterByMultipleKeys(atletas, chavesPesquisa, pesquisa).map(atleta => atleta.id);
        componentesAtletas.forEach(card => {
            const id = card.id.match(/\d+/g).map(Number)[0];
            if (atletasFiltrados.includes(id)) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }
</script>

<?php Template::footer() ?>
