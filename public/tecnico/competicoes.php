<?php
use App\Competicoes\CompeticaoRepository;
use App\Util\Database\Connection;
use App\Util\Template\Template;

require_once __DIR__ . '/../../vendor/autoload.php';

$pdo = Connection::getInstance();
$repo = new CompeticaoRepository($pdo);
$competicoes = $repo->competicoesAbertas();

$displayAlerta = empty($competicoes) ? 'block' : 'none';
$displayTabela = empty($competicoes) ? 'none' : 'table';

Template::head('Competições abertas');

// TODO if tecnico logado { mostrar nav de técnico }

$json = array_map(fn($c) => $c->toJson(), $competicoes);
?>

<main class="container">
    <h1 class="mt-3">MatchPoint <small>| Competições abertas</small></h1>
    <div class="input-group mt-5">
        <input class="form-control" type="search" id="pesquisa" placeholder="Digite o nome ou descrição de uma competição..."/>
        <span class="input-group-text input-group-prepend">
            <i class="bi bi-search"></i>
        </span>
    </div>
    <div
        id="nenhuma-competicao-encontrada"
        class="alert alert-info mt-3"
        style="display: <?= $displayAlerta?>"
    >
        Nenhuma competição encontrada
    </div>
    <table id="tabela-competicoes" class="table mt-3" style="display: <?= $displayTabela ?>">
        <thead>
            <th>Nome</th>
            <th>Prazo</th>
            <th></th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach ($competicoes as $competicao): ?>
                <tr data-id=<?= $competicao->id() ?>>
                    <td>
                        <div class="d-flex flex-column">
                            <span><?= $competicao->nome() ?></span>
                            <small><?= $competicao->descricao() ?></small>
                        </div>
                    </td>
                    <td>
                        <?= $competicao->prazo()->format('d/m/Y') ?>
                    </td>

                    <!-- TODO após implementar essas telas, colocar os links aqui -->
                    <td class="td-botao">
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-person"></i>
                            Ver atletas
                        </button>
                    </td>
                    <td class="td-botao">
                        <button class="btn btn-outline-success">
                            <i class="bi bi-person-plus"></i>
                            Cadastrar atleta
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php Template::scripts() ?>

<script>
    // TODO fazer num script competicoes.js separado?
    // intellisense funciona um pouco melhor

    const inputPesquisa = document.querySelector('#pesquisa');
    const nenhumaEncontrada = document.querySelector('#nenhuma-competicao-encontrada');
    const tabelaCompeticoes = document.querySelector('#tabela-competicoes');
    const competicoes = <?= json_encode($json) ?>;

    const linhaCompeticao = new Map();
    for (const tr of tabelaCompeticoes.rows) {
        const id = Number(tr.getAttribute('data-id'))
        linhaCompeticao.set(id, tr);
    }

    // algoritmo ineficiente
    // mas ok enquanto o sistema não tiver muitas competições abertas a cada momento

    /**
     * @param {array} termos
     * @param {string} texto
     */
    function match(termos, texto) {
        for (const termo of termos) {
            if (texto.includes(termo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param {array} termos
     */
    function pesquisar(termos) {
        let algumaEncontrada = false; 
        for (const competicao of competicoes) {
            const ok = match(termos, competicao.nome) || match(termos, competicao.descricao);
            linha = linhaCompeticao.get(competicao.id);
            linha.style.display = ok ? 'table-row' : 'none';
            algumaEncontrada ||= ok;
        }
        tabelaCompeticoes.style.display = algumaEncontrada ? 'table' : 'none';
        nenhumaEncontrada.style.display = algumaEncontrada ? 'none' : 'block';
    }

    // debounce
    let timeoutPesquisa = null;
    inputPesquisa.addEventListener('keydown', () => {
        if (timeoutPesquisa) {
            clearTimeout(timeoutPesquisa);
        }
        timeoutPesquisa = setTimeout(() => {
            const termos = (inputPesquisa.value ?? '').split(/\s+/);
            pesquisar(termos)
        }, 200);
    });
</script>

<?php Template::footer() ?>