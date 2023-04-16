<?php
use App\Pagina;

require_once(__DIR__.'/../../vendor/autoload.php');

$pag = new Pagina();
?>

<?php $pag->header('Login - Administrador') ?>

<div class="m-auto mt-5 card" style="width: 50%">
  <form name="form_entrar_admin">
    <div class="card-header">
      Entrar como administrador
    </div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Senha</label>
        <input class="form-control" type="password" name="senha"/>
      </div>
    </div>
    <div class="card-footer text-center">
      <button class="btn btn-primary" type="submit">Entrar</button>
    </div>
  </form>
</div>

<?php $pag->scripts() ?>

<script>
  const form       = document.forms["form_entrar_admin"];
  const inputSenha = document.getElementsByName('senha')[0];

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
      const response = await fetch('/login_admin/controller.php', {
        method: 'POST',
        body: JSON.stringify({
          acao: 'login',
          senha: inputSenha.value
        })
      });
      const texto = await response.text();
      console.log('texto', texto)
      const json = JSON.parse(texto);

      if (response.ok) {
        location.assign('/competicoes');
      } else {
        Toast.fire({
          icon: 'error',
          text: json.mensagem,
        });
      }
    } catch (err) {
      console.error(err);
      Toast.fire({
        icon: 'error',
        text: 'Ocorreu um erro ao realizar o login',
      })
    }
  });
</script>

<?php $pag->footer() ?>