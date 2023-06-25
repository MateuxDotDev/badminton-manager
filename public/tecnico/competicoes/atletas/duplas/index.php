<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Dupla\DuplaRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Services\TokenService\TokenService;
use App\Util\Services\UploadImagemService\UploadImagemService;
use App\Util\Template\Template;

$hasError = false;

try {
    $session = UserSession::obj();
    if (!$session->isTecnico()) {
        Response::erroNaoAutorizado()->enviar();
    }

    $competicaoId = $_GET['competicao'] ?? null;
    if (is_null($competicaoId)) {
        Response::erro('Competição não informada')->enviar();
    }

    $pdo = Connection::getInstance();
    $competicaoRepo = new CompeticaoRepository($pdo);
    $competicao = $competicaoRepo->getViaId($competicaoId);
    if (is_null($competicao)) {
        Response::erro('Competição não encontrada')->enviar();
    }

    $duplasRepo = new DuplaRepository($pdo);
    $duplas = $duplasRepo->formadas($competicaoId);
} catch (Exception $e) {
    $hasError = true;
}

Template::head('Duplas');
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

    .tecnico span {
        text-overflow: ellipsis;
        line-clamp: 1;
    }
</style>

<main class="container">
    <section class="d-flex justify-content-between align-items-center">
        <h1 id="titulo">Duplas</h1>
    </section>

    <section class="input-group my-4 d-flex justify-content-center">
        <span class="input-group-text input-group-prepend">
            <i class="bi bi-search"></i><label for="pesquisa" class="d-none d-md-inline-block ms-2">Pesquisar</label>
        </span>
        <input class="form-control" type="search" id="pesquisa" placeholder="Digite aqui informações das duplas que deseja buscar..." />
    </section>

    <section id="sem-atletas" class="d-none alert alert-info">
        <p class="mb-0">
            <i class="bi bi-info-circle"></i> Nenhuma dupla formada. <a href="/tecnico/competicoes/atletas/?competicao=<?= $competicao->id() ?>">Clique aqui</a> para procurar atletas e formar duplas.
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

<?php
Template::scripts();

$rowTemplate = <<<HTML
<template id="dupla-card-template">
    <div class="card card-body bg-light border-0 dupla-card">
        <div class="row mb-2">
            <h4 class="fw-normal text-secondary text-center text-lg-start">Categoria: <span class="categoria fw-bold text-black"></span></h4>
        </div>
        <div class="row gy-4 dupla-card-cols">
        </div>
    </div>
</template>
HTML;

print($rowTemplate);

$colTemplate = <<<HTML
<template id="dupla-card-col-template">
    <div class="col-12 col-lg-6 atleta-col">
        <div class="row gy-4">
            <div class="col-12 col-md-4 col-lg-4 d-flex justify-content-center">
                <div class="ratio ratio-1x1" style="max-width: 128px;">
                    <img class="img-fluid rounded-circle profile-pic" />
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4 d-flex justify-content-center align-items-center">
                <h6 class="mb-0 nomeCompleto"></h6>
                <h6 class="mb-0 sexo ms-2"></h6>
            </div>
            <div class="col-12 col-md-2 col-lg-4 d-flex flex-column justify-content-center align-items-center">
                <h6 class="mb-0 idade"></h6>
                <h6 class="mb-0 dataNascimento mt-1 text-secondary"></h6>
            </div>
            <div class="col-12 col-md-6 col-lg-6 d-flex flex-column justify-content-center align-items-center text-secondary text-break">
                <h6 class="mb-0 tecnico">Técnico: <span class="fw-normal text-black"></span></h6>
            </div>
            <div class="col-12 col-md-6 col-lg-6 d-flex flex-column justify-content-center align-items-center text-secondary text-break">
                <h6 class="mb-0 clube">Clube: <span class="fw-normal text-black"></span></h6>
            </div>
        </div>
    </div>
</template>
HTML;

print($colTemplate);

?>

<script>
    const duplas = <?= json_encode($duplas) ?>;

    const conteudo = qs('#conteudo');
    const inputPesquisa = qs('#pesquisa');
    const componentesDuplas = [];
    const template = qs('#dupla-card-template');
    const templateCol = qs('#dupla-card-col-template');

    const createDuplaCard = (dupla) => {
        let card = template.content.cloneNode(true);

        dupla.atletas.forEach(atleta => {
            const col = templateCol.content.cloneNode(true);

            const img = eqs(col, '.profile-pic');
            img.setAttribute('src', `/assets/images/profile/${atleta.foto}`);
            img.setAttribute('alt', `Foto de perfil de ${atleta.nome}`);

            eqs(col, '.nomeCompleto').textContent = atleta.nome;
            eqs(col, '.sexo').append(iconeSexo(atleta.sexo));
            eqs(col, '.atleta-col').id = `atleta-${atleta.id}`;
            eqs(col, '.dataNascimento').textContent = atleta.dataNascimento;
            eqs(col, '.idade').textContent = `${atleta.idade} anos`;
            eqs(col, '.tecnico span').textContent = atleta.tecnico.nome;
            eqs(col, '.clube span').textContent = atleta.tecnico.clube;

            eqs(card, '.dupla-card-cols').appendChild(col);
        });

        eqs(card, '.categoria').textContent = dupla.categoria;

        eqs(card, '.dupla-card').id = `dupla-${dupla.id}`;
        componentesDuplas.push(eqs(card, '.dupla-card'));

        return card;
    }

    window.addEventListener('load', () => {
        if (duplas.length === 0) {
            qs('#sem-atletas').classList.remove('d-none');
            return;
        }

        duplas.forEach(dupla => {
            conteudo.appendChild(createDuplaCard(dupla));
        });

        inputPesquisa.addEventListener('keydown', debounce(300, () => {
            if (inputPesquisa.value.trim() === '') {
                componentesDuplas.forEach(componente => componente.classList.remove('d-none'));
                return;
            }

            pesquisar(inputPesquisa.value.trim() ?? '');
        }));
    });

    const chavesPesquisa = ['dataNascimento', 'nome', 'tecnico', 'clube', 'idade', 'categoria'];

    function pesquisar(pesquisa) {
        const atletas = duplas.map(dupla => dupla.atletas.map(atleta => {
            const tecnico = atleta.tecnico.nome;
            const clube = atleta.tecnico.clube;
            return {
                ...atleta,
                tecnico,
                clube,
                idDupla: dupla.id,
                categoria: dupla.categoria
            }
        })).flat();

        const duplasFiltradas = filterByMultipleKeys(atletas, chavesPesquisa, pesquisa).map(dupla => dupla.idDupla);
        componentesDuplas.forEach(card => {
            const id = card.id.match(/\d+/g).map(Number)[0];
            if (duplasFiltradas.includes(id)) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }
</script>

<?php Template::footer() ?>
