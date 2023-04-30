<?php
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

Template::head('Login');

if (!array_key_exists('email', $_GET)) {
    $etapa = 'email';
} else {
    // TODO sanitize
    $email = $_GET['email'];

    $temConta = true;
    if ($temConta) {
        $etapa = 'senha';
    } else {
        $etapa = 'confirmacao';
        // TODO enviar e-mail de confirmação
    }

    $etapa = $temConta ? 'senha' : 'confirmacao';
}
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
    <h1 class="my-5">MatchPoint</h1>

    <?php if ($etapa == 'email'): ?>

        <form name="form-email">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input name="email" type="text" class="form-control"/>
            </div>
            <div class="d-flex flex-column gap-3">
                <button type="submit" class="btn btn-primary">Continuar</button>
                <small>Não tem uma conta? <a href="/cadastro">cadastre-se</a>.</small>
            </div>
        </form>

    <?php elseif ($etapa == 'senha') : ?>

        <form name="form-login">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input name="email" type="text" class="form-control" value="<?= $email ?>" readonly disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input class="form-control" type="password" name="senha"/>
            </div>
            <div class="d-flex flex-column gap-3 align-items-center">
                <button type="submit" class="btn btn-primary">Entrar</button>
                <small><a href="/login">Voltar</a></small>
            </div>
        </form>

    <?php endif; ?>
</main>

<script>
    const formEmail = document.forms['form-email'];

    formEmail?.addEventListener('submit', (event) => {
        event.preventDefault();
        const email = formEmail.email.value;
    });
    
    const formLogin = document.forms['form-login'];

    formLogin?.addEventListener('submit', (event) => {
        event.preventDefault();
        const email = formLogin.email.value;
        const senha = formLogin.senha.value;
        fazerLogin(email, senha);
    });

    async function fazerLogin(email, senha) {
        const dados = { acao: 'login', email, senha };
        // TODO login
    }
</script>

<?php Template::footer() ?>