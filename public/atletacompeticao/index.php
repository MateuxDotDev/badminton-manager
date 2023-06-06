<?php

require_once(__DIR__.'/../../vendor/autoload.php');

use App\Util\Template\Template;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Http\Request;
use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Categorias\CategoriaRepository;

$session = UserSession::obj();

Template::head('Cadastrar atleta competição');

if ($session->isTecnico()) {
    Template::navTecnicoLogado();
} else {
    Template::naoAutorizado();
}

$tecnico = $session->getTecnico();

?>

<style>
    .nav-tabs .nav-link.active {
        font-weight: 400 !important;
    }

    .atleta-busca_modal {
        transition: .1s ease-in-out;
    }
    
    .atleta-busca_modal:hover {
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
        background-color: rgba(var(--bs-light-rgb),var(--bs-bg-opacity)) !important;
    }
</style>

<?php 
    Template::scripts();

    $GetDados = Request::getDados();
    $codigoCompeticao = 0;
    $htmlCompeticao = '';
    if(is_array($GetDados) && array_key_exists('competicao',$GetDados)){
        $codigoCompeticao = $GetDados['competicao'];
    }

    $competicao = null;
    if($codigoCompeticao && $codigoCompeticao != 0){
        $repo = new CompeticaoRepository(Connection::getInstance());
        $competicao = $repo->buscarCompeticao($codigoCompeticao);

        $repoAtleta = new AtletaCompeticaoRepository(Connection::getInstance());
        $atletas = [];
        foreach($repoAtleta->getAtletasForaCompeticao($tecnico->id(), $codigoCompeticao) as $atleta){
            $atletas[] = $atleta->toJson();
        }

        $repoCategoria = new CategoriaRepository(Connection::getInstance());
        $categorias = $repoCategoria->buscarCategorias();

        $inputsCategorias = [];
        foreach ($categorias as $categoria) {
          $id        = $categoria->id();
          $descricao = $categoria->descricao();
        
          $inputsCategorias[] = "
            <div class='form-check'>
              <input class='form-check-input input-categoria' type='checkbox' name='categoria-$id' id='categoria-$id' value='$id'>
              <label for='categoria-$id' class='form-check-label'>$descricao</label>
            </div>
          ";
        }
        
?>

<main class="container">
    <h2>Incluir atleta na competição</h2>
    <div class="card mb-5">
        <form name="form-atletacompeticao">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Competição</label>
                    <input  disabled readonly type="text" class="form-control"
                        value="<?= $competicao->nome() ?>"/>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body pt-2">
                        <ul class="nav mb-3 nav-tabs">
                            <li class="nav-item">
                                <button id="btn-selecionar-atleta" class="nav-link active" data-bs-toggle="tab" data-bs-target="#selecionar_atleta" type="button">
                                    Selecionar atleta cadastrado
                                </button>
                            </li>
                            <li class="nav-item">
                                <button id="btn-cadastrar-atleta" class="nav-link" data-bs-toggle="tab" data-bs-target="#cadastrar_atleta" type="button">
                                    Cadastrar novo atleta
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="selecionar_atleta" class="tab-pane show active">
                                <div class="input-group mb-3 elementos-sem-atleta">
                                    <button id="btn-pesquisar" class="btn btn-outline-primary" type="button">
                                        Consultar Atleta
                                    </button>
                                </div>

                                <div id="atleta-selecionado" class="atleta-busca border rounded p-3 flex-row gap-3 align-items-center" style="display: none;">
                                    <div class="flex-shrink">
                                        <div class="rounded-circle" style="height: 60px; width: 60px;">
                                            <img id="img-atleta-selecionado" src="" alt="" style="height: 60px; width: 60px;">
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span id="nome-atleta-selecionado" class="fw-bold"></span>
                                        <span id="idade-atleta-selecionado"></span>
                                    </div>
                                    <div class="ms-auto"></div>
                                    <button id="btn-remover-selecionado" class="btn btn-danger">
                                        Selecionar Outro
                                    </button>
                                </div>
                            </div>

                            <div id="cadastrar_atleta" class="tab-pane">
                                <div class="mb-3">
                                    <label class="form-label" for="cadastrar_nomeCompleto">Nome completo</label>
                                    <input id="cadastrar_nomeCompleto" name="cadastrar_nomeCompleto" class="form-control" type="text"/>
                                </div>
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="form-label" for="cadastrar_sexo">Sexo</label>
                                        <select class="form-control" id="cadastrar_sexo" name="cadastrar_sexo">
                                            <option value="M">Masculino</option>
                                            <option value="F">Feminino</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label" for="cadastrar_dataNascimento">Data de nascimento</label>
                                        <!-- TODO: descobrir a necessidade do 1.2rem em type date -->
                                        <input style="line-height: 1.2rem" class="form-control" type="date" id="cadastrar_dataNascimento" name="cadastrar_dataNascimento"/>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="cadastrar_observacoes">Observações</label>
                                        <textarea class="form-control" id="cadastrar_observacoes" name="cadastrar_observacoes"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="cadastrar_foto">Foto</label>
                                        <input class="form-control" type="file" id="cadastrar_foto" name="cadastrar_foto" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">
                            Joga nas categorias
                        </label>
                        <div>
                            <?= implode('', array_slice($inputsCategorias, 0, 7)) ?>
                        </div>
                        <div>
                            <?= implode('', array_slice($inputsCategorias, 7)) ?>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label">
                            Precisa de dupla
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="check-masculina" name="check-masculina">
                            <label class="form-check-label" for="check-masculina">Masculina</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="check-feminina" name="check-feminina">
                            <label class="form-check-label" for="check-feminina">Feminina</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="informacao" class="form-label">Observações</label>
                    <textarea name="informacao" class="form-control"></textarea>
                </div>
            </div>
            <div class="card-footer d-flex flex-row justify-content-center">
                <button class="btn btn-success" type="submit">
                    <i class="bi bi-person-plus"></i>&nbsp;
                    Incluir
                </button>
            </div>
        </form>
    </div>

</main>

<div id="consulta-atleta" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <input id="pesquisa-atleta-modal" type="text" class="form-control" placeholder="Digite o nome do atleta...">
            <button id="btn-close-modal" type="button" class="btn btn-outline-primary close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">
            <div class="tab-content">
                <div id="selecionar_atleta_modal" class="tab-pane show active">
                    <div id="lista_atleta_modal" class="d-flex flex-column gap-2">

                    </div>
                </div>
            </div>
        </div>
            
    </div>
  </div>
</div>

<?php } else { ?>
    <div class="alert alert-info">
        Não foi informado o código da competição!
    </div>
<?php } ?>

<script>
    const form = document.forms['form-atletacompeticao'];
    const btnPesquisar = document.getElementById("btn-pesquisar");
    const btnCloseModal = document.getElementById("btn-close-modal");
    const inpPesquisaModal = document.getElementById("pesquisa-atleta-modal");
    const btnRemoverAtletaSelecionado = document.getElementById("btn-remover-selecionado");
    const idTecnico = <?= $tecnico->id() ?>;
    const idCompeticao = <?= $competicao->id() ?>;
    const atletas = Object.values(<?= json_encode($atletas) ?>);
    var atletaSelecionado;

    form.addEventListener('submit', (event)=>{
        event.preventDefault();
        let formData = new FormData(form);
        if(validaFormulario(formData)){
            let userChoice = $("#btn-selecionar-atleta").hasClass("active") ? 1 : 2;
            formData.append('acao', 'cadastrar');
            formData.append('tecnico', idTecnico);
            formData.append('competicao', idCompeticao);
            if(atletaSelecionado){
                formData.append('atleta', atletaSelecionado.id);
            }
            formData.append('userChoice', userChoice);

            submitAtletaCompeticao(formData);
        }
    });

    btnPesquisar.addEventListener('click', (event) =>{
        event.preventDefault();
        loadModalAtleta();
    });

    inpPesquisaModal.addEventListener('keydown', debounce(300, () => {
        const termos = (inpPesquisaModal.value ?? '');
        reloadModalListaAtleta(termos)
        var listaAtletaElement = document.getElementById("lista_atleta_modal");
        if(!listaAtletaElement.children.length){
            alertaErro("Nenhum atleta encontrado");
        }
    }));

    btnRemoverAtletaSelecionado.addEventListener('click', (event) =>{
        event.preventDefault();

        atletaSelecionado = null;
        limpaAtletaSelecionado();
    });

    function validaFormulario(form){
        var sucesso = true;
        var userChoice = $("#btn-selecionar-atleta").hasClass("active") ? 1 : 2;
        if(userChoice == 1){
            sucesso = validaFormularioAtletaSelecionado();
        }else {
            sucesso = validaFormularioAtletaCadastro(form);
        }

        return sucesso && validaFormularioCategoriaSelecionada(form) && validaFormularioTipoDuplaSelecionado(form);
    }

    function validaFormularioAtletaSelecionado(){
        var sucesso = true;

        if(typeof atletaSelecionado !== 'object'){
            sucesso = false;
            alertaErro('Não foi selecionado um atleta para a competição');
        }

        return sucesso;
    }

    function validaFormularioAtletaCadastro(form){
        var sucesso = true;

        if(!form.get("cadastrar_nomeCompleto")){
            sucesso = false;
            alertaErro("Informe o nome do atleta");
        }
        if(sucesso && !form.get("cadastrar_dataNascimento")){
            sucesso = false;
            alertaErro("Informe a data de nascimento do atleta");
        }

        return sucesso;
    }

    function validaFormularioCategoriaSelecionada(form){
        var sucesso = false
        for(const chave of form.keys()){
            if(chave.includes('categoria')){
                sucesso = true;
                break;
            }
        }

        if(!sucesso){
            alertaErro("Selecione uma categoria para poder continuar");
        }

        return sucesso;
    }

    function validaFormularioTipoDuplaSelecionado(form){
        var sucesso = false
        for(const chave of form.keys()){
            if(chave.includes('check-')){
                sucesso = true;
                break;
            }
        }

        if(!sucesso){
            alertaErro("Selecione uma opção de dupla para poder continuar");
        }

        return sucesso;
    }

    async function submitAtletaCompeticao(dados){
        try{
            const response = await fetch('/atletacompeticao/acao.php', {
                method: 'POST',
                body: dados
            });
            const text = await response.text();
            const retorno = JSON.parse(text);
            if (response.ok) {
                agendarAlertaSucesso('Atleta inserido na competição');
                location.assign('/tecnico/index.php');
            } else {
                Toast.fire({
                    icon: 'error',
                    text: retorno.mensagem,
                });
            }
        }catch(error){
            console.error('erro', error);
        }
    }

    
    /**Iterar o array retornado afim de montar cada card da lista */
    /**Todo card deve possuir um evento duplo-clique com o objetivo de selecionar o atleta consultado */
    /**No final o atleta consultado deve ficar disponivel num card na tela principal */
    function loadModalAtleta(){
        esvaziar(document.getElementById("lista_atleta_modal"));
        reloadModalListaAtleta('');
        $('#consulta-atleta').modal('show');
    }

    function reloadModalListaAtleta(nomeAtleta){
        var atletasFiltrados = getAtletasFiltradosModal(nomeAtleta);
        var listaAtletaElement = document.getElementById("lista_atleta_modal");
        esvaziar(listaAtletaElement);
        reloadElementListaAtletaModal(listaAtletaElement, atletasFiltrados);
    }

    function getAtletasFiltradosModal(nomeAtleta){
        let atletasFiltrados = [];
        if(nomeAtleta != ''){
            atletas.forEach(function(atleta){
                if(atleta.nomeCompleto.includes(nomeAtleta)){
                    atletasFiltrados.push(atleta);
                }
            });
        }
        else{
            atletasFiltrados = atletas;
        }
        return atletasFiltrados;
    }

    /**Adicionar evento no btn-close-modal */
    btnCloseModal.addEventListener('click', (event) =>{
        closeModal();
    });
    

    function reloadElementListaAtletaModal(listaAtletaElement, atletasFiltrados){
        for(atleta of atletasFiltrados){
            let atletaBuscaElement = document.createElement("div");
            let classList = "atleta-busca_modal border rounded p-3 flex-row gap-3 align-items-center elementos-sem-atleta".split(" ");
            atletaBuscaElement.classList.add(...classList);
            atletaBuscaElement.style.display = "flex";

            let flexShrinkElement = document.createElement("div");
            flexShrinkElement.classList.add("flex-shrink");

            let roundedCircle = document.createElement("div");
            roundedCircle.classList.add("rounded-circle");
            roundedCircle.style.height = "60px";
            roundedCircle.style.width = "60px";
            if(atleta.foto != ''){
                let imgRoundedCircle = document.createElement("img");
                imgRoundedCircle.src = "/assets/images/profile/" + atleta.foto;
                imgRoundedCircle.style.height = "60px";
                imgRoundedCircle.style.width = "60px";
                roundedCircle.appendChild(imgRoundedCircle);
            }
            flexShrinkElement.appendChild(roundedCircle);
            atletaBuscaElement.appendChild(flexShrinkElement);

            let dFlexColumn = document.createElement("div");
            classList = "d-flex flex-column".split(" ");
            dFlexColumn.classList.add(...classList);

            let spanNome = document.createElement("span");
            spanNome.classList.add("fw-bold");
            spanNome.appendChild(document.createTextNode(atleta.nomeCompleto));
            dFlexColumn.appendChild(spanNome);

            let spanIdade = document.createElement("span");
            spanIdade.appendChild(document.createTextNode(atleta.idade + " ano(s)"));
            dFlexColumn.appendChild(spanIdade);
            atletaBuscaElement.appendChild(dFlexColumn);

            let msAutoEmpurra = document.createElement("div");
            msAutoEmpurra.classList.add("ms-auto");
            atletaBuscaElement.appendChild(msAutoEmpurra);

            let btnSelecionar = document.createElement("button");
            classList = "btn btn-primary".split(" ");
            btnSelecionar.classList.add(...classList);
            btnSelecionar.appendChild(document.createTextNode("Selecionar"));
            btnSelecionar.id = "btn-selecionar-" + atleta.id;
            btnSelecionar.addEventListener('click', (event)=>{
                let idSelecionado = event.target.id.split("-")[2];
                atletasFiltrados.forEach(function(atleta){
                    if(idSelecionado == atleta.id){
                        atletaSelecionado = atleta;
                    }
                });
                showAtletaSelecionado();
                closeModal();
            });
            atletaBuscaElement.appendChild(btnSelecionar);

            listaAtletaElement.appendChild(atletaBuscaElement);
        }
    }

    function closeModal(){
        document.getElementById("pesquisa-atleta-modal").value = "";
        esvaziar(document.getElementById("lista_atleta_modal"));
        $('#consulta-atleta').modal('hide');
    }

    function showAtletaSelecionado(){
        limpaAtletaSelecionado();
        document.getElementById('img-atleta-selecionado').src = "/assets/images/profile/" + atletaSelecionado.foto;
        document.getElementById('nome-atleta-selecionado').appendChild(document.createTextNode(atletaSelecionado.nomeCompleto));
        document.getElementById('idade-atleta-selecionado').appendChild(document.createTextNode(atletaSelecionado.idade + " ano(s)"));
        document.getElementById('atleta-selecionado').style.display = "flex";
    }

    function limpaAtletaSelecionado(){
        document.getElementById('img-atleta-selecionado').src = '';
        document.getElementById('nome-atleta-selecionado').textContent = "";
        document.getElementById('idade-atleta-selecionado').textContent = "";
        document.getElementById('atleta-selecionado').style.display = "none";
    }
</script>

<!-- Importando o jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<?php Template::footer() ?>