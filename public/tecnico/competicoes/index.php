<?php
use App\Competicoes\CompeticaoRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Template\Template;

require_once __DIR__ . '/../../../vendor/autoload.php';

$pdo = Connection::getInstance();
$repo = new CompeticaoRepository($pdo);
$competicoes = $repo->competicoesAbertas();
$session = UserSession::obj();

$displayAlerta = empty($competicoes) ? 'block' : 'none';
$displayTabela = empty($competicoes) ? 'none' : 'table';

Template::head('Competições abertas');

if ($session->isTecnico()) Template::navTecnicoLogado();
else Template::navTecnicoNaoLogado();

?>

<main class="container">
    <h1 class="mt-3">Competições abertas</small></h1>
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
                        <a href="/tecnico/competicoes/atletas/?competicao=<?= $competicao->id() ?>" class="btn btn-outline-primary">
                            <i class="bi bi-person"></i>
                            Ver atletas
                        </a>
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
    const competicoes = <?= json_encode(array_map(fn($c) => $c->toJson(), $competicoes)) ?>;
</script>

<script src="/tecnico/competicoes/competicoes.js"></script>

<?php Template::footer() ?>
