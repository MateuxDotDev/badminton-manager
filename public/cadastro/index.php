<?php
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

// TODO para clube a implementação inicial é sempre criar o clube
// só depois fazer a funcionalidade de escolher clube existente

Template::head('Cadastre-se');
?>

<style>
    @media (max-width: 768px) {
        .btn {
            width: 100%;
        }
    }
        
</style>

<main class="container">
    <h1 class="my-5">
        <!-- TODO logo -->
        MatchPoint
        <small> | Cadastro de técnico</small>
    </h1>
    <form name="form-cadastro" action="#" method="GET">
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" class="form-control" name="email" required/>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" class="form-control" name="senha" required/>
        </div>
        <div class="mb-3">
            <label class="form-label">Nome completo</label>
            <input type="text" class="form-control" name="nome" required/>
        </div>
        <div class="mb-3">
            <label class="form-label">Informações de contato</label>
            <textarea class="form-control" name="informacoes"></textarea>
        </div>
        <div class="mb-5">
            <label class="form-label">Clube</label>
            <input type="text" class="form-control" name="clube" required/>
        </div>
        <div class="d-flex flex-row gap-3 align-items-center">
            <button type="submit" class="btn btn-success" id="btn-criar-conta">Criar conta</button>
            <small>Já tem uma conta? faça o <a href="/login">login</a>.</small>
        </div>
    </form>
</main>

<?php Template::scripts() ?>

<script>
    const form = document.forms['form-cadastro'];

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const clube = {
            novo: true,
            nome: form.clube.value,
        }
        criarContaTecnico(
            form.email.value,
            form.senha.value,
            form.nome.value,
            form.informacoes.value,
            clube,
        );
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