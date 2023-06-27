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
    $duplas = array_map(function ($dupla) use ($session) {
        $tecnicos =  array_map(fn ($tecnico) => $tecnico['tecnico']['id'], $dupla['atletas']);
        $dupla['mine'] = in_array($session->getTecnico()->id(), $tecnicos);
        return $dupla;
    }, $duplas);
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
            <h4 class="fw-normal text-secondary text-center text-lg-start d-flex align-items-center dupla-card-header">
                Categoria: <span class="categoria fw-bold text-black"></span>
            </h4>
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

        if (dupla.mine) {
            eqs(card, '.dupla-card-header').appendChild(botaoDesfazer(dupla));
        }

        eqs(card, '.categoria').textContent = dupla.categoria;
        eqs(card, '.dupla-card').id = `dupla-${dupla.id}`;
        componentesDuplas.push(eqs(card, '.dupla-card'));

        return card;
    }

    function botaoDesfazer(dupla) {
        const botao = document.createElement('button');
        botao.classList.add('btn', 'btn-danger', 'btn-sm', 'ms-auto', 'ms-4');
        botao.setAttribute('type', 'button');
        botao.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Desfazer';
        botao.title = 'Desfazer dupla';
        const nomeAtletas = dupla.atletas.map(atleta => atleta.nome).join(' e ');
        botao.addEventListener('click', async () => {
            if (await confirmarExclusao(`Tem certeza que deseja desfazer a dupla entre os atletas ${nomeAtletas}?`)) {
                await desfazerDupla(dupla.id);
            }
        });

        return botao;
    }

    async function desfazerDupla(idDupla) {
        try {
            const payload = {
                idDupla,
                acao: 'desfazer'
            };
            console.log(payload)
            const response = await fetch(`/tecnico/competicoes/atletas/duplas/acao.php`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                // POG -> Programacao Orientada a Gambiarra!
                agendarAlertaSucesso('Dupla desfeita com sucesso!');
                location.assign('/tecnico/competicoes/atletas/duplas/?competicao=<?= $competicao->id() ?>');
            } else {
                const mensagem = (await response.json()).mensagem;
                if (mensagem) {
                    alertaErro(mensagem);
                } else {
                    alertaErro('Não foi possível desfazer a dupla. Tente novamente mais tarde.');
                }
            }
        } catch (error) {
            alertaErro('Não foi possível desfazer a dupla. Tente novamente mais tarde.');
        }
    }

    window.addEventListener('load', () => {
        if (duplas.length === 0) {
            qs('#sem-atletas').classList.remove('d-none');
            return;
        }

        duplas.forEach(dupla => {
            conteudo.appendChild(createDuplaCard(dupla));
        });

        console.log(componentesDuplas);
        checkUrl();

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

    function checkUrl() {
        const getParams = new URLSearchParams(location.search);
        const dupla = getParams.get('dupla');
        const acao = getParams.get('acao');
        if (dupla && acao) {
            const idDupla = Number(dupla);
            if (acao === 'desfazer' && idDupla) {
                const dupla = componentesDuplas.find(dupla => {
                    const id = dupla.id.match(/\d+/g).map(Number)[0];
                    return id === idDupla;
                });
                if (dupla) {
                    dupla.querySelector('button').click();
                } else {
                    alertaErro('Não foi possível encontrar a dupla.');
                }
            }
        }
    }
</script>

<?php Template::footer() ?>
