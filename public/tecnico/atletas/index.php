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


<script>
    const atletas = <?= json_encode(array_map(fn($a) => $a->toJson(), $atletas)) ?>;

    const conteudo = document.querySelector('#conteudo');
    const inputPesquisa = document.querySelector('#pesquisa');
    const componentesAtletas = [];

    // On load page, show all atletas
    window.addEventListener('load', () => {
        atletas.forEach(atleta => {
            conteudo.insertAdjacentHTML('beforeend', createAtletaCard(atleta));
            if (!atleta.informacoesAdicionais) {
                const infoAdicionais = document.querySelector(`#atleta-${atleta.id} .info-adicional-titulo`);
                infoAdicionais.innerText = 'Sem informações adicionais';
            }
        });

        componentesAtletas.push(...document.querySelectorAll('.atleta-card'));

        inputPesquisa.addEventListener('keydown', debounce(300, () => {
            pesquisar(inputPesquisa.value.trim() ?? '');
        }));
    });

    const atletaCardString = `<?= file_get_contents('./atleta-card.html') ?>`;

    function createAtletaCard(atleta) {
        let newCard = structuredClone(atletaCardString);

        newCard = replaceKeyInString(newCard, 'id', atleta.id);
        newCard = replaceKeyInString(newCard, 'imagem_perfil', atleta.foto);
        newCard = replaceKeyInString(newCard, 'nome_completo', atleta.nomeCompleto);
        newCard = replaceKeyInString(newCard, 'idade', atleta.idade);
        newCard = replaceKeyInString(newCard, 'data_nascimento', atleta.dataNascimento);
        newCard = replaceKeyInString(newCard, 'sexo', atleta.sexo);
        newCard = replaceKeyInString(newCard, 'informacoes_adicionais', atleta.informacoesAdicionais);
        newCard = replaceKeyInString(newCard, 'criado_em', atleta.dataCriacao);
        newCard = replaceKeyInString(newCard, 'ultima_alteracao', atleta.dataAlteracao);

        return newCard;
    }

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
