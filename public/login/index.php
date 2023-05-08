<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Util\Template\Template;

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
                <button id="btn-continuar" type="submit" class="btn btn-primary">Continuar</button>
                <small>Não tem uma conta? <a href="/cadastro">cadastre-se</a>.</small>
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
            <div class="d-flex flex-column gap-3">
                <button id="btn-entrar" type="submit" class="btn btn-primary">Entrar</button>
                <small><a href="#" id="link-voltar">Voltar</a>.</small>
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
