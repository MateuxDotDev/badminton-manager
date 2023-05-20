<?php
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

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
            <div class="mb-3">
                <label class="form-label">Clube</label>
                <input type="text" class="form-control" name="clube" autocomplete="off"/>
            </div>
            <div class="card">
                <div class="card-header">Clubes encontrados</div>
                <div class="card-body">
                    <div id="alerta-digitar" class="alert alert-info mb-0">
                        Digite o nome de um clube.
                    </div>


                    <div id="container-opcao-clube-digitado">
                    </div>
                    
                    <div id="container-opcoes-clubes">
                    </div>

                </div>
            </div>
        </div>
        <div class="d-flex flex-row gap-3 align-items-center" style="margin-bottom: 500px">
            <button type="submit" class="btn btn-success" id="btn-criar-conta">Criar conta</button>
            <small>Já tem uma conta? faça o <a href="/login">login</a>.</small>
        </div>
    </form>
</main>

<template id="template-opcao-clube">
    <div class="form-check">
        <input type="radio" class="form-check-input">
        <label class="form-check-label"></label>
    </div>
</template>

<?php Template::scripts() ?>

<script>
    const baseURL = new URL(location).origin;

    const form = document.forms['form-cadastro'];

    const alertaDigitar         = document.getElementById('alerta-digitar');
    const templateOpcaoClube    = document.getElementById('template-opcao-clube');
    const containerOpcoesClubes = document.getElementById('container-opcoes-clubes');

    let opcaoClubeDigitado;
    {
        const elem = opcaoClube(0, '');
        opcaoClubeDigitado = {
            elem,
            input: elem.querySelector('.form-check-input'),
            label: elem.querySelector('.form-check-label'),
        };
    }

    let timeoutPesquisa = null;


    document.getElementById('container-opcao-clube-digitado').append(opcaoClubeDigitado.elem);


    form.clube.addEventListener('input', handleInputClube);
    handleInputClube();

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        criarContaTecnico(
            form.email.value,
            form.senha.value,
            form.nome.value,
            form.informacoes.value,
            form.clube.value,
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


    function limparResultadosPesquisa() {
        let x;
        while (x = containerOpcoesClubes.firstChild) {
            x.remove();
        }
    }


    function getPesquisaClube() {
        return (form.clube.value ?? '').trim();
    }


    function opcaoClube(id, nome) {
        const elem  = templateOpcaoClube.content.cloneNode(true).firstElementChild;
        const input = elem.querySelector('.form-check-input');
        const label = elem.querySelector('.form-check-label');

        input.name  = 'clube-radio';
        input.value = nome;

        const attrId = `clube-${id}`;
        input.setAttribute('id', attrId);
        label.setAttribute('for', attrId);

        label.innerText = nome;

        input.addEventListener('change', () => {
            if (input.checked) {
                form.clube.value = (input.value ?? '').trim()
            }
        });

        return elem;
    }


    function handleInputClube() {
        const value  = getPesquisaClube();
        const termos = value.split(/\s+/);

        if (value == '') {
            alertaDigitar.style.display = 'block';
            opcaoClubeDigitado.elem.style.display = 'none';
            limparResultadosPesquisa();
        } else {
            alertaDigitar.style.display = 'none';
            opcaoClubeDigitado.elem.style.display = 'block';

            opcaoClubeDigitado.input.value = value;
            opcaoClubeDigitado.label.innerText = value;
            opcaoClubeDigitado.input.dispatchEvent(new Event('click'));

            if (timeoutPesquisa) clearTimeout(timeoutPesquisa);

            timeoutPesquisa = setTimeout(pesquisarClubes, 200, termos, (clubes) => {
                // caso o usuário faça uma pesquisa mas apague ela antes de termos buscado,
                // para evitar que os resultados apareçam
                if (getPesquisaClube() == '') return;
                if (!clubes) return;

                limparResultadosPesquisa();
                for (const clube of clubes) {
                    if (clube.nome == value) continue;
                    containerOpcoesClubes.append(opcaoClube(clube.id, clube.nome));
                }
            });
        }
    }


    function pesquisarClubes(termos, callback) {
        if (termos.length == 0 || (termos.length == 1 && termos[0] == '')) {
            callback([]);
            return;
        }

        const url = new URL('cadastro/acao.php', baseURL);
        url.searchParams.append('acao', 'pesquisarClubes');
        for (const termo of termos) {
            url.searchParams.append('termos[]', termo)
        }

        fetch(url)
        .then(r => r.text())
        .then(JSON.parse)
        .then(o => o.resultados)
        .then(callback)
        .catch(console.error);
    }
</script>

<?php Template::footer() ?>