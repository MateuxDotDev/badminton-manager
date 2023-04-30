<?php
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

Template::head('Login');
?>

<style>
    @media (max-width: 700px) {
        #container-login {
            width: 90vw;
        }
    }
    @media not (max-width: 700px) {
        #container-login {
            width: 40vw;
        }
    }
    #container-login {
        margin: auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
</style>

<?php Template::scripts() ?>

<main id="container-login">
    <!-- TODO logo -->
    <h1 class="my-5">
        MatchPoint
        <small>| Login de técnico</small>
    </h1>

    <div id="etapa-email" style="display: none">
        <form name="form-email">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input name="email" type="email" class="form-control" required/>
            </div>
            <div class="d-flex flex-column gap-3">
                <button type="submit" class="btn btn-primary">Continuar</button>
                <small>Não tem uma conta? <a href="/cadastro">cadastre-se</a>.</small>
            </div>
        </form>
    </div>

    <div id="etapa-senha" style="display: none">
        <form name="form-senha">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input name="email" type="text" class="form-control" readonly disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input class="form-control" type="password" name="senha" required/>
            </div>
            <div class="d-flex flex-column gap-3">
                <button id="btn-entrar" type="submit" class="btn btn-primary">Entrar</button>
                <small><a href="#" id="link-voltar">Voltar</a>.</small>
            </div>
        </form>
    </div>

</main>

<script>
    const formEmail = document.forms['form-email'];
    const formSenha = document.forms['form-senha'];

    const etapas = {
        email: {
            elemento: document.querySelector('#etapa-email'),
            focus: formEmail.email,
        },
        senha: {
            elemento: document.querySelector('#etapa-senha'),
            focus: formSenha.senha,
        }
    };

    let etapaAtual = null;

    function trocarEtapa(novaEtapa) {
        if (etapaAtual) {
            etapas[etapaAtual].elemento.style.display = 'none';
        }
        etapas[novaEtapa].elemento.style.display = 'block';
        etapas[novaEtapa].focus.focus();
        etapaAtual = novaEtapa;
    }

    trocarEtapa('email');

    formEmail.addEventListener('submit', async (event) => {
        // TODO animação de carregando em algum lugar

        event.preventDefault();
        const email = formEmail.email.value;
        const dadosConta = await getDadosConta(email);
        if (dadosConta == null) {
            Toast.fire({
                icon: 'error',
                text: 'Ocorreu um erro inesperado',
            });
            return;
        }

        const {existe, temSenha} = dadosConta;
        if (!existe) {
            Toast.fire({
                icon: 'error',
                text: 'Não existe técnico cadastrado com esse e-mail',
            });
            return;
        }

        if (temSenha) {
            formSenha.email.value = email;
            trocarEtapa('senha');
        } else {
            // TODO quando implementarmos login sem precisar de senha
            // enviar e-mail de confirmação...
            // trocarEtapa('emailConfirmacao')
        }

    });

    document.querySelector('#link-voltar').onclick = (event) => {
        event.preventDefault();
        trocarEtapa('email');
    }

    formSenha.addEventListener('submit', (event) => {
        event.preventDefault();
        fazerLogin(
            formSenha.email.value,
            formSenha.senha.value,
        );
    });

    async function getDadosConta(email) {
        // TODO urlencode ou oq for necessário para passar com GET corretamente
        const response = await fetch(`/login/acao.php?acao=getDadosConta&email=${email}`);
        const text = await response.text();
        try {
            const json = JSON.parse(text);
            return json;
        } catch (err) {
            console.error(err);
            console.error('texto', text);
            console.error('response', response);
            return null;
        }
    }

    async function fazerLogin(email, senha) {
        const dados = { acao: 'login', email, senha };
        // TODO login
    }
</script>

<?php Template::footer() ?>