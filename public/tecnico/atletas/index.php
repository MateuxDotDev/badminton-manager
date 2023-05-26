<?php


require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Tecnico\Atleta\AtletaRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Services\UploadImagemService\UploadImagemService;
use App\Util\Template\Template;
use function App\Util\Services\UploadImagemService\UploadImagemService;

$session = UserSession::obj();

Template::head('Atletas');

if ($session->isTecnico()) {
    Template::navTecnico();
} else {
    Template::naoAutorizado();
}

$hasError = false;
$atletas = [];

try {
    $repo = new AtletaRepository(Connection::getInstance(), new UploadImagemService());
    $atletas = $repo->getViaTecnico($session->getTecnico()->id());
} catch (Exception $e) {
    $hasError = true;
}
?>

<style>
    .profile-pic {
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        border: 2px solid var(--bs-success);
    }

    #conteudo {
        display: grid;
        gap: 1rem;
    }

    #pesquisa {
        max-width: 512px;
    }
</style>

<main class="container">
    <section class="d-flex justify-content-between align-items-center">
        <h1 id="titulo">Atletas</h1>
        <a href="cadastrar" class="btn btn-outline-success">Cadastrar atleta <i class="bi bi-person-add"></i></a>
    </section>

    <section class="input-group my-4 d-flex justify-content-center">
        <span class="input-group-text input-group-prepend">
            <i class="bi bi-search"></i><label for="pesquisa" class="d-none d-md-inline-block ms-2">Pesquisar</label>
        </span>
        <input class="form-control" type="search" id="pesquisa" placeholder="Digite aqui informações do atleta que deseja buscar..."/>
    </section>

    <?php if (empty($atletas)): ?>
        <section id="sem-atletas" class="d-none alert alert-info">
            <p class="mb-0">
                <i class="bi bi-info-circle"></i> Nenhum atleta cadastrado. <a href="/tecnico/atletas/cadastrar">Clique aqui</a> para cadastrar um novo atleta.
            </p>
        </section>
    <?php endif ?>

    <?php if ($hasError): ?>
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
    const atletas = <?= json_encode(array_map(fn($a) => $a->toJson(), $atletas)) ?>;

    const conteudo = document.querySelector('#conteudo');
    const inputPesquisa = document.querySelector('#pesquisa');
    const componentesAtletas = [];
    const template = document.querySelector('#atleta-card-template');

    const createAtletaCard = (atleta) => {
        let card = template.content.cloneNode(true);

        card.querySelector('.atleta-card').id = `atleta-${atleta.id}`;
        card.querySelector('.nome_completo').textContent = atleta.nomeCompleto;
        card.querySelector('.idade').textContent = `${atleta.idade} anos`;
        card.querySelector('.data_nascimento').textContent = atleta.dataNascimento;
        card.querySelector('.sexo').textContent = atleta.sexo;
        if (atleta.informacoesAdicionais) {
            card.querySelector('.informacoes_adicionais').textContent = atleta.informacoesAdicionais;
        } else {
            card.querySelector('.informacoes_adicionais').classList.add('d-none');
            card.querySelector('.info-adicional-titulo').innerText = 'Sem informações adicionais';
        }
        const botaoInfo = card.querySelector('.botao-info');
        const img = card.querySelector('.profile-pic');

        botaoInfo.setAttribute('data-bs-content', `Criado em: ${atleta.dataCriacao}.\nAlterado em: ${atleta.dataAlteracao}.`);
        img.setAttribute('src', `/assets/images/profile/${atleta.foto}`);
        img.setAttribute('alt', `Foto de perfil de ${atleta.nomeCompleto}`);

        return card;
    }

    window.addEventListener('load', () => {
        atletas.forEach(atleta => {
            const card = createAtletaCard(atleta);
            conteudo.appendChild(card);
        });

        [...document.querySelectorAll('[data-bs-toggle="popover"]')]
            .map(el => new bootstrap.Popover(el))

        componentesAtletas.push(...document.querySelectorAll('.atleta-card'));
        inputPesquisa.addEventListener('keydown', debounce(300, () => {
            pesquisar(inputPesquisa.value.trim() ?? '');
        }));
    });

    const chavesPesquisa = ['nomeCompleto', 'idade', 'dataNascimento', 'sexo', 'informacoesAdicionais'];

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
