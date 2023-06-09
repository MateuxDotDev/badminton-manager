<?php


require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Tecnico\Atleta\AtletaRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\Response;
use App\Util\Services\TokenService\TokenService;
use App\Util\Services\UploadImagemService\UploadImagemService;
use App\Util\Template\Template;

$urlToken = $_GET['token'] ?? null;
$hasError = false;

try {
    $session = UserSession::obj();

    if ($session->getTecnico() === null && $urlToken === null) {
        Response::erroNaoAutorizado()->enviar();
    }

    if ($session->getTecnico() !== null) {
        $idTecnico = $session->getTecnico()->id();
    } else {
        $decodedToken = TokenService::decodeToken($urlToken);
        $idTecnico = $decodedToken->tecnico->id;

        if ($idTecnico === null) {
            Response::erroNaoAutorizado()->enviar();
        }
    }

    $repo = new AtletaRepository(Connection::getInstance(), new UploadImagemService());
    $atletas = $repo->getViaTecnico($idTecnico);
} catch (ResponseException|ValidatorException $e) {
    Response::erroException($e)->enviar();
} catch (Exception $e) {
    $hasError = true;
}

Template::head('Atletas');
Template::nav($session);

?>

<style>
    #conteudo {
        display: grid;
        gap: 1rem;
    }

    #pesquisa {
        max-width: 512px;
    }
</style>

<main class="container">
    <section class="d-flex justify-content-between align-items-center">
        <h1 id="titulo">Atletas</h1>
        <a href="cadastrar" class="btn btn-outline-success"><i class="bi bi-person-add"></i> Cadastrar atleta</a>
    </section>

    <section class="input-group my-4 d-flex justify-content-center">
        <span class="input-group-text input-group-prepend">
            <i class="bi bi-search"></i><label for="pesquisa" class="d-none d-md-inline-block ms-2">Pesquisar</label>
        </span>
        <input class="form-control" type="search" id="pesquisa" placeholder="Digite aqui informações do atleta que deseja buscar..."/>
    </section>

    <?php if (empty($atletas)): ?>
        <section id="sem-atletas" class="d-none alert alert-info">
            <p class="mb-0">
                <i class="bi bi-info-circle"></i> Nenhum atleta cadastrado. <a href="/tecnico/atletas/cadastrar">Clique aqui</a> para cadastrar um novo atleta.
            </p>
        </section>
    <?php endif ?>

    <?php if ($hasError): ?>
        <section class="alert alert-danger">
            <p class="mb-0">
                <i class="bi bi-exclamation-circle"></i> Ocorreu um erro ao carregar os atletas. Tente novamente mais tarde.
            </p>
        </section>
    <?php endif ?>

    <section id="conteudo">
    </section>
</main>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="form-alterar" method="get" action="#" >
            <input type="hidden" id="id" name="id" />
            <input type="hidden" id="acao" name="acao" value="alterar" />
            <input type="hidden" id="fotoPerfil" name="fotoPerfil" />
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editModalLabel">Alterar usuário</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <section class="modal-body">
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
                        <!-- TODO: descobrir a necessidade do 1.2rem em type date -->
                        <input style="line-height: 1.2rem" class="form-control" type="date" id="dataNascimento" name="dataNascimento" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes"></textarea>
                </div>
                <div class="mb-3 d-flex flex-column w-100">
                    <label class="form-label" for="foto">Foto de perfil</label>
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="ratio ratio-1x1 align-self-center me-3" style="max-width: 64px;">
                            <img class="img-fluid rounded-circle profile-pic" id="alterar-foto-atual" />
                        </div>
                        <input class="form-control" type="file" id="foto" name="foto" />
                    </div>
                </div>
            </section>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-arrow-counterclockwise"></i> Cancelar</button>
                <button type="submit" class="btn btn-success class"><i class="bi bi-pencil"></i> Alterar</button>
            </div>
        </form>
    </div>
</div>

<?php Template::scripts() ?>

<?php require_once './atleta-card.html'; ?>

<script>
    const atletas = <?= json_encode(array_map(fn($a) => $a->toJson(), $atletas)) ?>;

    const conteudo = document.querySelector('#conteudo');
    const inputPesquisa = document.querySelector('#pesquisa');
    const componentesAtletas = [];
    const template = document.querySelector('#atleta-card-template');
    const token = new URLSearchParams(window.location.search).get('token');

    const createAtletaCard = (atleta) => {
        let card = template.content.cloneNode(true);

        card.querySelector('.atleta-card').id = `atleta-${atleta.id}`;
        card.querySelector('.nome_completo').textContent = atleta.nomeCompleto;
        card.querySelector('.idade').textContent = `${atleta.idade} anos`;
        card.querySelector('.data_nascimento').textContent = atleta.dataNascimento;
        card.querySelector('.sexo').textContent = atleta.sexo;
        card.querySelectorAll('.botao-acao').forEach(botao => botao.setAttribute('data-atleta-id', atleta.id));
        if (atleta.informacoesAdicionais) {
            card.querySelector('.informacoes_adicionais').textContent = atleta.informacoesAdicionais;
        } else {
            card.querySelector('.informacoes_adicionais').classList.add('d-none');
            card.querySelector('.info-adicional-titulo').innerText = 'Sem informações adicionais';
        }
        const botaoInfo = card.querySelector('.botao-info');
        const img = card.querySelector('.profile-pic');

        botaoInfo.setAttribute('data-bs-content', `Criado em: ${atleta.dataCriacao}.\nAlterado em: ${atleta.dataAlteracao}.`);
        img.setAttribute('src', `/assets/images/profile/${atleta.foto}`);
        img.setAttribute('alt', `Foto de perfil de ${atleta.nomeCompleto}`);

        card.querySelector('.btn-excluir-atleta').addEventListener('click', event => {
            event.preventDefault();
            excluirAtleta(atleta);
        });

        return card;
    }

    window.addEventListener('load', () => {
        atletas.forEach(atleta => {
            const card = createAtletaCard(atleta);
            conteudo.appendChild(card);
        });

        [...document.querySelectorAll('[data-bs-toggle="popover"]')]
            .map(el => new bootstrap.Popover(el))

        componentesAtletas.push(...document.querySelectorAll('.atleta-card'));
        inputPesquisa.addEventListener('keydown', debounce(300, () => {
            pesquisar(inputPesquisa.value.trim() ?? '');
        }));

        verificaRequest();
    });

    const chavesPesquisa = ['nomeCompleto', 'idade', 'dataNascimento', 'sexo', 'informacoesAdicionais'];

    function pesquisar(pesquisa) {
        const atletasFiltrados = filterByMultipleKeys(atletas, chavesPesquisa, pesquisa).map(atleta => atleta.id);
        componentesAtletas.forEach(card => {
            const id = card.id.match(/\d+/g).map(Number)[0];
            if (atletasFiltrados.includes(id)) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }

    const editModal = qs('#editModal');
    editModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (button) {
            const idAtleta = parseInt(button.getAttribute('data-atleta-id'));
            const atleta = atletas.find(a => a.id === idAtleta);
            prepararModalEditar(atleta);
        }
    });

    function prepararModalEditar(atleta) {
        editModal.querySelector('#id').value = atleta.id;
        editModal.querySelector('#fotoPerfil').value = atleta.foto;
        editModal.querySelector('#nomeCompleto').value = atleta.nomeCompleto;
        editModal.querySelector('#sexo').value = atleta.sexo.split('')[0];
        editModal.querySelector('#observacoes').value = atleta.informacoesAdicionais;
        const fotoEl = qs('#alterar-foto-atual');
        fotoEl.setAttribute('src', `/assets/images/profile/${atleta.foto}`);
        fotoEl.setAttribute('alt', `Foto de perfil de ${atleta.nomeCompleto}`);
        editModal.querySelector('#dataNascimento').value = brDateToYmd(atleta.dataNascimento);
    }

    const formAlterar = qs('#form-alterar');
    formAlterar.addEventListener('submit', event => {
        event.preventDefault();
        const atletaId = parseInt(formAlterar.querySelector('#id').value);
        const atleta = atletas.find(a => a.id === atletaId);
        alterarAtleta(atleta);
    });

    async function alterarAtleta(atleta) {
        if (!atleta) return;
        const formData = new FormData(formAlterar);
        if (token) {
            formData.append('token', token);
        }

        try {
            const retorno = await fetch('/tecnico/atletas/acao.php', {
                method: 'POST',
                body: formData
            });

            if (retorno.ok) {
                agendarAlertaSucesso('Atleta alterado com sucesso.');
                location.assign(token ? '/tecnico/competicoes/' : '/tecnico/atletas');
            } else {
                const body = await retorno.json();
                Toast.fire({
                    icon: 'error',
                    text: body.mensagem ?? 'Ocorreu um erro ao alterar o atleta.',
                });
            }
        } catch (e) {
            console.error(e);
            Toast.fire({
                icon: 'error',
                text: 'Ocorreu um erro ao alterar o atleta.',
            });
        }
    }

    async function excluirAtleta(atleta) {
        if (!atleta) return;
        if (await confirmarExclusao(`Tem certeza que deseja excluir o atleta ${atleta.nomeCompleto}? Essa ação não poderá ser desfeita.`)) {
            try {
                const retorno = await fetch(`/tecnico/atletas/acao.php`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        acao: 'remover',
                        id: atleta.id,
                        token: token,
                    })
                });

                if (retorno.ok) {
                    agendarAlertaSucesso('Atleta removido com sucesso.');
                    location.assign(token ? '/tecnico/competicoes/' : '/tecnico/atletas');
                } else {
                    const body = await retorno.json();
                    Toast.fire({
                        icon: 'error',
                        text: body.mensagem ?? 'Ocorreu um erro ao remover o atleta.',
                    });
                }
            } catch (e) {
                console.error(e);
                Toast.fire({
                    icon: 'error',
                    text: 'Ocorreu um erro ao remover o atleta.',
                })
            }
        }
    }

    function verificaRequest() {
        const urlParams = new URLSearchParams(window.location.search);
        const idAtleta = urlParams.get('id');
        if (!idAtleta) return;
        const atleta = atletas.find(a => a.id === parseInt(idAtleta));
        const acao = urlParams.get('acao');
        if (acao === 'alterar') {
            prepararModalEditar(atleta);
            const modalAlterar = new bootstrap.Modal(editModal, {
                keyboard: false,
            });
            modalAlterar.show();
        } else if (acao === 'remover') {
            excluirAtleta(atleta);
        } else {
            console.warn('Ação não reconhecida.');
        }
    }
</script>

<?php Template::footer() ?>
