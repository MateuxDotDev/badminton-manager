<?php

require __DIR__ . '/../../../../vendor/autoload.php';

use App\Competicoes\CompeticaoRepository;
use App\Categorias\CategoriaRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();

Template::head('Atletas precisando de dupla');
Template::nav($session);

$idCompeticao = null;
if (array_key_exists('competicao', $_GET)) {
    $idCompeticao = $_GET['competicao'];
}

$pdo = Connection::getInstance();
$competicaoRepo = new CompeticaoRepository($pdo);
$categoriaRepo  = new CategoriaRepository($pdo);

$competicao = null;
if ($idCompeticao != null) {
    $competicao = $competicaoRepo->getViaId($idCompeticao);
}

if ($competicao == null) {
    ?>
        <div class="container">
            <div class="alert alert-danger d-flex flex-row gap-2 align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    Competição não encontrada.
                </div>
            </div>
        </div>
        <script>
            setTimeout(history.back, 3000);
        </script>
    <?php
    Template::footer();

    return;
}

$categorias = $categoriaRepo->buscarCategorias();

$inputsCategorias = [];
foreach ($categorias as $categoria) {
    $id        = $categoria->id();
    $descricao = $categoria->descricao();

    $inputsCategorias[] = "
        <div class='form-check'>
            <input name='categoria[]' checked class='form-check-input input-categoria' type='checkbox' id='categoria-$id'>
            <label for='categoria-$id' class='form-check-label'>$descricao</label>
        </div>
    ";
}

?>

<div class="container">
    <span class="titulo-pagina">Consulta de atletas precisando de duplas</span>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Filtros</h5>

            <div class="mb-3">
                <label class="form-label">Competição</label>
                <input type="text" class="form-control" readonly disabled
                    value="<?=$competicao->nome()?>">
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Nome do atleta</label>
                    <input class="form-control" id="nome-atleta" type="text">
                </div>
                <div class="col">
                    <label class="form-label">Nome do técnico</label>
                    <input class="form-control" id="nome-tecnico" type="text">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 col-md-6 mb-3 mb-md-0">
                    <label class="form-label">Idade</label>
                    <div class="input-group">
                        <div class="input-group-text">Entre</div>
                        <input class="form-control" type="number" min=0 inputmode="numeric" pattern="[0-9]*" id="idade-minimo"/>
                        <div class="input-group-text">e</div>
                        <input class="form-control" type="number" min=0 inputmode="numeric" pattern="[0-9]*" id="idade-maximo"/>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label">Clube</label>
                    <input type="text" class="form-control" id="clube"/>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 col-md-6 mb-3 mb-md-0">
                    <label class="form-label">Categorias</label>
                    <div class="d-flex flex-row gap-5">
                        <div>
                            <?= implode('', array_slice($inputsCategorias, 0, 7)) ?>
                        </div>
                        <div>
                            <?= implode('', array_slice($inputsCategorias, 7)) ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3 mb-md-0">
                    <label class="form-label">Sexo</label>
                    <div class='form-check'>
                        <input checked class='form-check-input' type='checkbox' id='sexo-masculino'>
                        <label for='sexo-masculino' class='form-check-label'>Masculino</label>
                    </div>
                    <div class='form-check'>
                        <input checked class='form-check-input' type='checkbox' id='sexo-feminino'>
                        <label for='sexo-feminino' class='form-check-label'>Feminino</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3 mb-md-0">
                    <label class="form-label">Buscando dupla</label>
                    <div class='form-check'>
                        <input checked class='form-check-input' type='checkbox' id='dupla-masculina'>
                        <label for='dupla-masculina' class='form-check-label'>Masculina</label>
                    </div>
                    <div class='form-check'>
                        <input checked class='form-check-input' type='checkbox' id='dupla-feminina'>
                        <label for='dupla-feminina' class='form-check-label'>Feminina</label>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-row justify-content-end">
                <button id="btn-filtrar" class="btn btn-outline-success">
                    <i class="bi bi-filter"></i>&nbsp;
                    Filtrar
                </button>
            </div>

        </div>
    </div>
</div>

<?php Template::scripts(); ?>

<?php Template::footer(); ?>
