<?
require '../session.php';
require '../pagina.php';

$sessionOk = validaSessaoAdmin();

htmlHeader('..', 'Competições - Administrador');
if (!$sessionOk) {
  htmlNaoAutorizado();
}

require 'model.php';
$pdo = require '../db_connect.php';
$competicoes = buscarCompeticoes($pdo);

// TODO fazer menu/nav
?>

<main class="container">
  <h4 class="mb-3 mt-3">Competições</h4>
  <button class="mb-3 btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-nova-competicao">
    <i class="bi bi-plus-circle"></i>&nbsp;
    Nova competição
  </button>
  <table class="table">
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
      <? foreach ($competicoes as $competicao): ?>
        <tr>
          <td><?= $competicao->id() ?></td>
          <td><?= $competicao->nome() ?></td>
          <td><?= $competicao->prazo()->format('d/m/Y') ?></td>
          <td class="td-botao">
            <button
              class="btn btn-primary btn-alterar-competicao"
              data-bs-toggle="tooltip"
              data-bs-title="Alterar"
              data-id-competicao=<?= $competicao->id() ?>>
              <i class="bi bi-pencil"></i>
            </button>
          </td>
          <td class="td-botao">
            <button
              class="btn btn-danger btn-excluir-competicao"
              data-bs-toggle="tooltip"
              data-bs-title="Excluir"
              data-id-competicao=<?= $competicao->id() ?>>
              <i class="bi bi-trash"></i>
            </button>
          </td>
          <td></td>
        </tr>
      <? endforeach; ?>
    </tbody>
  </table>
</main>

<div id="modal-nova-competicao" class="modal">
  <div class="modal-dialog">
    <form name="form_nova_competicao">
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

<? htmlScripts('..') ?>

<script>
  const formNovaCompeticao = document.forms['form_nova_competicao'];

  formNovaCompeticao.addEventListener('submit', (event) => {
    event.preventDefault();
  });
</script>

<? htmlFooter() ?>