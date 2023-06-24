<?php
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

Template::head('Cadastre-se');
?>

<main class="container-sm d-flex flex-column justify-content-center m-auto" style="max-width: 512px;">
    <header class="d-flex flex-column text-center mt-5">
        <img src="/assets/images/brand/favicon.svg" alt="MatchPoint" class="mb-4" height="48vw">
        <h1 class="fs-2">Crie sua conta</h1>
        <p class="text-muted mb-4">Encontre parceiros de jogo e participe de competições emocionantes com nossas funcionalidades exclusivas</p>
    </header>

    <div>
        <div id="container-form" class="mb-3"></div>

        <div class="d-flex flex-column gap-3 text-center mb-4">
            <button type="button" class="btn btn-success" id="btn-criar-conta">Criar conta</button>
            <small>Já é um técnico cadastrado? <a href="/login">Entre aqui</a>.</small>
        </div>
    </div>

</main>

<template id="template-opcao-clube">
    <div class="form-check">
        <input type="radio" class="form-check-input">
        <label class="form-check-label"></label>
    </div>
</template>

<?php Template::scripts() ?>

<script>
    const form = new FormCadastroTecnico();

    qs('#container-form').append(form.elemento);

    qs('#btn-criar-conta').addEventListener('click', async () => {
        const {email, senha, nome, informacoes, clube} = form.valores;
        await criarContaTecnico(email, senha, nome, informacoes, clube);
    });

    async function criarContaTecnico(email, senha, nome, informacoes, clube) {
        const dados = { acao: 'cadastro', email, senha, nome, informacoes, clube };
        const response = await fetch('/cadastro/acao.php', {
            method: 'POST',
            body: JSON.stringify(dados)
        });
        const text = await response.text();
        try {
            const retorno = JSON.parse(text);
            if (response.ok) {
                agendarAlertaSucesso('Conta criada com sucesso, agora você já pode fazer login!');
                location.assign('/login');
            } else {
                Toast.fire({
                    icon: 'error',
                    text: retorno.mensagem,
                });
            }
        } catch(err) {
            console.error('response', response)
            console.error('response text', text)
        }
    }
</script>

<?php Template::footer() ?>
