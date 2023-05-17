<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();

Template::head('Cadastrar atleta');

if ($session->isTecnico()) {
    Template::navTecnico();
} else {
    Template::naoAutorizado();
}
?>

<div class="container">
    <h1>Cadastrar atleta</h1>
    <div class="card">
        <form name="form-cadastro" action="#" method="GET">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="nomeCompleto">Nome completo</label>
                    <input id="nomeCompleto" name="nomeCompleto" class="form-control" type="text" required />
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label" for="sexo">Sexo</label>
                        <select class="form-control" id="sexo" name="sexo" required>
                            <option value="M">Masculino</option>
                            <option value="F">Feminino</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label" for="dataNascimento">Data de nascimento</label>
                        <input class="form-control" type="date" id="dataNascimento" name="dataNascimento" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="foto">Foto</label>
                    <input class="form-control" type="file" id="foto" name="foto" />
                </div>
            </div>
            <div class="card-footer d-flex flex-row justify-content-center">
                <button class="btn btn-success" type="submit">
                    <i class="bi bi-person-plus"></i>&nbsp;
                    Cadastrar
                </button>
            </div>
        </form>
    </div>
</div>


<?php Template::scripts() ?>

<script>
    const form = document.forms['form-cadastro'];

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        formData.append('acao', 'cadastrar');
        cadastrarConta(formData);
    });

    async function cadastrarConta(dados) {
        try {
            const response = await fetch('/tecnico/atletas/cadastrar/acao.php', {
                method: 'POST',
                body: dados
            });
            const text = await response.text();
            const retorno = JSON.parse(text);
            if (response.ok) {
                agendarAlertaSucesso('Atleta criado com sucesso! Você já pode adicioná-lo em uma competição.');
                location.assign('/tecnico/atletas');
            } else {
                Toast.fire({
                    icon: 'error',
                    text: retorno.mensagem,
                });
            }
        } catch (error) {
            console.error('erro', error)
        }
    }
</script>

<?php Template::footer() ?>
