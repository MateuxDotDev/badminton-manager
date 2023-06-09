<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();

if ($session->isTecnico()) {
    header('Location: /tecnico');
    exit;
}

Template::head('Login');

?>

<?php Template::scripts() ?>

<main class="container-sm d-flex flex-column justify-content-center m-auto" style="max-width: 360px;">
    <header class="d-flex flex-column text-center">
        <img src="/assets/images/brand/favicon.svg" alt="MatchPoint" class="mb-4" height="48vw">
        <h1 class="fs-2">Acesse a plataforma</h1>
        <p class="text-muted mb-4">Encontre parceiros de jogo e participe de competições emocionantes com nossas funcionalidades exclusivas</p>
    </header>
    </h1>

    <div id="etapa-email" style="display: none">
        <form name="form-email">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input name="email" type="email" class="form-control" required/>
            </div>
            <div class="d-flex flex-column gap-3 text-center">
                <button id="btn-continuar" type="submit" class="btn btn-success">Continuar</button>
                <small>Ainda não possui cadastro? <a href="/cadastro">Crie sua conta aqui</a>.</small>
            </div>
        </form>
    </div>

    <div id="etapa-senha" style="display: none">
        <form name="form-login">
            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input name="email" type="text" class="form-control" id="email" readonly disabled>
            </div>
            <div class="mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control" type="password" id="senha" name="senha" required/>
            </div>
            <div class="d-flex flex-row gap-3">
                <button id="link-voltar" type="button" class="btn btn-outline-secondary" style="width: 48px; height: 48px;" title="Voltar"><i class="bi bi-arrow-left"></i></button>
                <button id="btn-entrar" type="submit" class="btn btn-success w-100">Entrar</button>
            </div>
        </form>
    </div>
</main>

<script>
    const formEmail = document.forms['form-email'];
    const formLogin = document.forms['form-login'];

    const inputSenha = formLogin.senha;

    const btnContinuar = document.querySelector('#btn-continuar')
    const btnEntrar    = document.querySelector('#btn-entrar')

    const etapas = {
        email: {
            elemento: document.querySelector('#etapa-email'),
            focus: formEmail.email,
        },
        senha: {
            elemento: document.querySelector('#etapa-senha'),
            focus: formLogin.senha,
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
        // TODO animação de carregando em algum lugar (por enquanto o botão fica meio 'apagado' com o disabled)
        btnContinuar.setAttribute('disabled', '');

        event.preventDefault();
        const email = formEmail.email.value;
        const dadosConta = await getDadosConta(email);
        if (dadosConta == null) {
            alertaErro('Ocorreu um erro inesperado');
        } else {
            const {existe, temSenha} = dadosConta;
            if (!existe) {
                alertaErro('Não existe técnico cadastrado com esse e-mail');
            } else if (temSenha) {
                formLogin.email.value = email;
                trocarEtapa('senha');
            } else {
                // TODO quando implementarmos login sem precisar de senha
                // enviar e-mail de confirmação...
                // trocarEtapa('emailConfirmacao')
            }
        }

        btnContinuar.removeAttribute('disabled');
    });

    document.querySelector('#link-voltar').onclick = (event) => {
        event.preventDefault();
        trocarEtapa('email');
    }

    formLogin.addEventListener('submit', async (event) => {
        event.preventDefault();
        btnEntrar.setAttribute('disabled', '');
        await fazerLogin(
            formLogin.email.value,
            formLogin.senha.value,
        );
        btnEntrar.removeAttribute('disabled');
    });

    async function getDadosConta(email) {
        email = encodeURI(email);
        const response = await fetch(`/login/acao.php?acao=getDadosConta&email=${email}`);
        const texto = await response.text();
        try {
            return JSON.parse(texto);
        } catch (err) {
            console.error({ err, texto, response })
            return null;
        }
    }

    async function fazerLogin(email, senha) {
        const dados = { acao: 'login', email, senha };
        const response = await fetch('/login/acao.php', {
            method: 'POST',
            body: JSON.stringify(dados),
        });
        const texto = await response.text();
        try {
            const retorno = JSON.parse(texto);
            if (response.ok) {
                location.assign('/tecnico');
            } else {
                alertaErro(retorno.mensagem);
                inputSenha.value = '';
            }
        } catch (err) {
            console.error({ err, texto, response })
        }
    }
</script>

<?php Template::footer() ?>
