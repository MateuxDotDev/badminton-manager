<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Util\Template\Template;

$template = new Template();

$template->head('Administrador | Login');
?>

<div class="m-auto mt-5 card" style="width: 50%">
    <form name="form_entrar_admin">
        <div class="card-header">
            Entrar como administrador
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="usuario">Usuário</label>
                <input class="form-control" type="text" id="usuario" name="usuario"/>
            </div>
            <div class="mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control" type="password" id="senha" name="senha"/>
            </div>
        </div>
        <div class="card-footer text-center">
            <button class="btn btn-primary" type="submit">Entrar</button>
        </div>
    </form>
</div>
<script>
    const form       = document.forms["form_entrar_admin"];
    const inputSenha = form.senha;
    const inputUsuario = form.usuario;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const response = await fetch('/admin/acao.php', {
                method: 'POST',
                body: JSON.stringify({
                    acao: 'login',
                    senha: inputSenha.value,
                    usuario: inputUsuario.value,
                })
            });
            const texto = await response.text();
            console.log('texto', texto)
            const json = JSON.parse(texto);

            if (response.ok) {
                location.assign('/admin/competicoes');
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
        } finally {
            inputSenha.value = '';
            inputUsuario.value = '';
        }
    });
</script>

<?php $template->footer() ?>
