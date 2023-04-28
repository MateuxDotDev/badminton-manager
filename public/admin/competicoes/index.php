<?php

require_once('../../../vendor/autoload.php');

use App\Admin\Competicoes\CompeticaoRepository;
use App\Util\Database\Connection;
use App\Util\Session;
use App\Util\Template\Template;

Session::iniciar();

$template = new Template();

$template->head('Administrador | Competições');
if (!Session::isAdmin()) {
    $template->naoAutorizado();
}

$template->navAdmin();
$repository = new CompeticaoRepository(Connection::getInstance());
$competicoes = $repository->todasAsCompeticoes();
?>

<main class="container">
    <h4 id="competicoes-title" class="mb-3 mt-3">Competições</h4>
    <button class="mb-3 btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-nova-competicao">
        <i class="bi bi-plus-circle"></i>&nbsp;
        Nova competição
    </button>
    <?php if (!empty($competicoes)): ?>
        <table id="tabela-competicoes" class="table" aria-describedby="competicoes-title">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Prazo</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($competicoes as $competicao): ?>
                <tr>
                    <td><?= $competicao->id() ?></td>
                    <td><?= $competicao->nome() ?></td>
                    <td><?= $competicao->prazo()->format('d/m/Y') ?></td>
                    <td class="td-botao">
                        <button
                            class="btn btn-primary btn-modal-alterar-competicao"
                            data-id-competicao=<?= $competicao->id() ?>>
                            <i class="bi bi-pencil"></i>&nbsp;
                            Alterar
                        </button>
                    </td>
                    <td class="td-botao">
                        <button
                            class="btn btn-danger btn-excluir-competicao"
                            data-id-competicao=<?= $competicao->id() ?>>
                            <i class="bi bi-trash"></i>&nbsp;
                            Excluir
                        </button>
                    </td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            Nenhuma competição cadastrada
        </div>
    <?php endif; ?>
</main>

<div id="modal-nova-competicao" class="modal">
    <div class="modal-dialog">
        <form name="form-nova-competicao">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova competição</h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input class="form-control" type="text" name="nome" required/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prazo</label>
                        <input class="form-control" type="date" name="prazo" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Criar competição</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="modal-alterar-competicao" class="modal">
    <div class="modal-dialog">
        <form name="form-alterar-competicao">
            <input type="hidden" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alterar competição</h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome</label>
                        <input class="form-control" type="text" id="nome" name="nome" required/>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="prazo">Prazo</label>
                        <input class="form-control" type="date" id="prazo" name="prazo" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        id="btn-alterar-competicao"
                        type="submit"
                        class="btn btn-primary"
                        disabled
                    >Alterar competição</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
    $competicoesJson = [];
    foreach ($competicoes as $competicao) {
        $competicoesJson[$competicao->id()] = $competicao->toJson();
    }
?>

<script>
    const competicoes = <?= json_encode($competicoesJson) ?>;

    const formNovaCompeticao = document.forms['form-nova-competicao'];

    const alterar = {
        elemento: document.getElementById('modal-alterar-competicao'),
        form: document.forms['form-alterar-competicao'],
    }
    Object.assign(alterar, {
        modal: new bootstrap.Modal(alterar.elemento),
        botaoSubmit: alterar.form.querySelector('#btn-alterar-competicao'),
    })

    formNovaCompeticao.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = formNovaCompeticao;
        criarCompeticao(form.nome.value, form.prazo.value);
    });

    {
        const botoesExcluir = document.getElementsByClassName('btn-excluir-competicao');
        for (const botao of botoesExcluir) {
            const id = botao.getAttribute('data-id-competicao');
            botao.addEventListener('click', event => {
                event.preventDefault();
                confirmarExcluirCompeticao(id);
            });
        }
    }

    {
        const botoesAlterar = document.getElementsByClassName('btn-modal-alterar-competicao');
        for (const botao of botoesAlterar) {
            const id = botao.getAttribute('data-id-competicao');
            botao.addEventListener('click', event => {
                event.preventDefault();
                abrirModalAlterarCompeticao(id);
            });
        }
    }

    async function criarCompeticao(nome, prazo) {
        const dados = {
            acao: 'criarCompeticao',
            nome,
            prazo
        };
        const response = await fetch('/competicoes/controller.php', {
            method: 'POST',
            body: JSON.stringify(dados),
        });
        const texto = await response.text();
        try {
            const { mensagem } = JSON.parse(texto);
            if (response.ok) {
                location.reload();
                agendarAlertaSucesso(mensagem);
            } else {
                Toast.fire({
                    icon: 'error',
                    text: mensagem,
                });
            }
        } catch (err) {
            console.error('retorno', texto, 'err', err);
        }
    }

    async function confirmarExcluirCompeticao(id) {
        const msg = `A competição "${competicoes[id].nome}" será excluída`;
        const remover = await confirmarExclusao(msg)
        if (remover) {
            excluirCompeticao(id);
        }
    }

    async function excluirCompeticao(id) {
        const dados = {
            acao: 'excluirCompeticao',
            id,
        };
        const response = await fetch('/competicoes/controller.php', {
            method: 'DELETE',
            body: JSON.stringify(dados)
        })
        const texto = await response.text();
        try {
            if (response.ok) {
                location.reload();
                agendarAlertaSucesso('Competição excluída com sucesso');
            } else {
                const { mensagem } = JSON.parse(texto);
                Toast.fire({
                    icon: 'error',
                    text: mensagem,
                });
            }
        } catch (err) {
            console.error('retorno', texto, 'err', err)
        }
    }

    function abrirModalAlterarCompeticao(id) {
        const { nome, prazo } = competicoes[id];
        const { form, botaoSubmit, modal } = alterar;
        form.id.value = id;
        form.nome.value = nome;
        form.prazo.value = prazo;
        botaoSubmit.removeAttribute('disabled');
        modal.show();
    }

    alterar.elemento.addEventListener('hidden.bs.modal', () => {
        alterar.form.id.value = '';
        alterar.botaoSubmit.setAttribute('disabled', 'disabled');
    });

    alterar.form.addEventListener('submit', event => {
        event.preventDefault();
        const {form} = alterar;
        const id    = form.id.value;
        const nome  = form.nome.value;
        const prazo = form.prazo.value;
        alterarCompeticao(id, nome, prazo);
    });

    async function alterarCompeticao(id, nome, prazo) {
        const dados = {
            acao: 'alterarCompeticao',
            id,
            nome,
            prazo,
        };
        const response = await fetch('/competicoes/controller.php', {
            method: 'PUT',
            body: JSON.stringify(dados)
        });
        const texto = await response.text();
        try {
            const { mensagem } = JSON.parse(texto);
            if (response.ok) {
                location.reload();
                agendarAlertaSucesso(mensagem);
            } else {
                Toast.fire({
                    icon: 'error',
                    text: mensagem,
                })
            }
        } catch (err) {
            console.error('RETORNO', texto, 'ERRO', err)
        }
    }
</script>

<?php $template->footer() ?>
