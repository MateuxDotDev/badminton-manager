<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Atleta\Sexo;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();

Template::head('Atletas precisando de dupla');
Template::nav($session);

$idCompeticao = null;
if (array_key_exists('competicao', $_GET)) {
  $idCompeticao = $_GET['competicao'];
}

$pdo = Connection::getInstance();
$competicaoRepo = new CompeticaoRepository($pdo);
$categoriaRepo  = new CategoriaRepository($pdo);

$competicao = null;
if ($idCompeticao != null) {
  $competicao = $competicaoRepo->getViaId($idCompeticao);
}

$atletaCompeticao = null;
if (array_key_exists('atleta', $_GET) && intval($_GET['atleta'])) {
  $idAtleta = intval($_GET['atleta']);
  $aRepo = new AtletaRepository($pdo);
  $atleta = $aRepo->getViaId($idAtleta);

  if ($atleta !== null) {
    $acRepo = new AtletaCompeticaoRepository($pdo);
    $atletaCompeticao = $acRepo->get($atleta, $competicao);
  }
}

function sexoAtletaChecked(Sexo $sexo): bool
{
  global $atletaCompeticao;
  return $atletaCompeticao == null || in_array($sexo, $atletaCompeticao->sexoDupla());
}

function sexoBuscadoChecked(Sexo $sexo): bool
{
  global $atletaCompeticao;
  return $atletaCompeticao == null || $sexo == $atletaCompeticao->atleta()->sexo();
}

if ($competicao == null) {
?>
  <div class="container">
    <div class="alert alert-danger d-flex flex-row gap-2 align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div>
        Competição não encontrada.
      </div>
    </div>
  </div>
  <script>
    setTimeout(history.back, 3000);
  </script>
<?php
  Template::footer();

  return;
}

?>

<style>
</style>

<div class="container">
    <article class="d-flex mb-4 justify-content-between align-items-center">
        <h1 class="mb-0">Atletas na competição</h1>
        <?php if ($session->isTecnico()): ?>
            <div class="d-flex gap-2">
                <a href="./tecnico/competicoes/atletas/incluir/?competicao=<?= $competicao->id() ?>" class="btn btn-outline-primary h-100">
                    <i class="bi bi-person-plus"></i> Cadastrar atleta
                </a>
                <a href="./duplas/?competicao=<?= $competicao->id() ?>" class="btn btn-outline-secondary h-100">
                    <i class="bi bi-person-lock"></i> Duplas formadas
                </a>
                <a href="./meus/?competicao=<?= $competicao->id() ?>" class="btn btn-outline-secondary h-100">
                    <i class="bi bi-person-lines-fill"></i> Meus atletas
                </a>
            </div>
        <?php endif; ?>
    </article>


  <div class="mb-3">
    <label class="form-label">Competição</label>
    <input type="text" class="form-control" readonly disabled value="<?= $competicao->nome() ?>">
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex flex-row gap-2 align-items-baseline mb-3">
        <h5 class="card-title">Filtros</h5>
        <button class="btn btn-link" data-click-switch="Ver menos" data-bs-toggle="collapse" data-bs-target="#container-mais-filtros">Ver mais</button>
        <button class="ms-auto btn btn-outline-danger" id="btn-limpar">
          <i class="bi bi-eraser-fill"></i>
          Limpar
        </button>
      </div>

      <div id="container-mais-filtros" class="collapse">

        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Nome do atleta</label>
            <input class="form-control" id="nome-atleta" type="text">
          </div>
          <div class="col">
            <label class="form-label">Nome do técnico</label>
            <input class="form-control" id="nome-tecnico" type="text">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12 col-md-6 mb-3 mb-md-0">
            <label class="form-label">Idade</label>
            <div class="input-group">
              <div class="input-group-text">Entre</div>
              <input class="form-control" type="number" min=0 inputmode="numeric" pattern="[0-9]*" id="idade-maior-que" />
              <div class="input-group-text">e</div>
              <input class="form-control" type="number" min=0 inputmode="numeric" pattern="[0-9]*" id="idade-menor-que" />
            </div>
          </div>
          <div class="col">
            <label class="form-label">Clube</label>
            <input type="text" class="form-control" id="clube" />
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12 col-md-6 mb-3 mb-md-0" id="container-input-categorias">
          </div>
          <div class="col-12 col-md-3 mb-3 mb-md-0">
            <label class="form-label">Sexo</label>
            <div class='form-check'>
              <input class='form-check-input input-sexo-atleta' type='checkbox' value='M' id='sexo-masculino' <?= sexoAtletaChecked(Sexo::from('M')) ? 'checked' : '' ?>>
              <label for='sexo-masculino' class='form-check-label'>Masculino</label>
            </div>
            <div class='form-check'>
              <input class='form-check-input input-sexo-atleta' type='checkbox' value='F' id='sexo-feminino' <?= sexoAtletaChecked(Sexo::from('F')) ? 'checked' : '' ?>>
              <label for='sexo-feminino' class='form-check-label'>Feminino</label>
            </div>
          </div>
          <div class="col-12 col-md-3 mb-3 mb-md-0">
            <label class="form-label">Buscando dupla</label>
            <div class='form-check'>
              <input class='form-check-input input-sexo-dupla' type='checkbox' value='M' id='dupla-masculina' <?= sexoBuscadoChecked(Sexo::from('M')) ? 'checked' : '' ?>>
              <label for='dupla-masculina' class='form-check-label'>Masculina</label>
            </div>
            <div class='form-check'>
              <input class='form-check-input input-sexo-dupla' type='checkbox' value='F' id='dupla-feminina' <?= sexoBuscadoChecked(Sexo::from('F')) ? 'checked' : '' ?>>
              <label for='dupla-feminina' class='form-check-label'>Feminina</label>
            </div>
          </div>
        </div>

      </div>


      <div class="d-flex flex-column flex-md-row gap-3">
        <div class="d-flex flex-row gap-3 align-items-center">
          <span>
            Ordenação
          </span>
          <div>
            <button id="btn-ordenacao-tipo" class="btn btn-outline-secondary" data-ordenacao="asc" style="width: 120px">
              Crescente
            </button>
          </div>
          <div>
            <select id="ordenacao-campo" class="form-control">
              <option value="nomeAtleta">Nome do atleta</option>
              <option value="nomeTecnico">Nome do técnico</option>
              <option value="clube">Clube</option>
              <option value="idade">Idade</option>
              <option value="dataAlteracao">Data da última atualização</option>
            </select>
          </div>
        </div>
        <div class="ms-auto">
          <div class="d-flex flex-row gap-3 align-items-center">
            <span id="carregando" class="d-none">Carregando...</span>
            <button id="btn-filtrar" class="btn btn-outline-success">
              <i class="bi bi-filter"></i>&nbsp;
              Filtrar
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="pt-3">
    <div class="alert alert-warning d-none" id="nenhum-encontrado">
      Nenhum atleta encontrado
    </div>
    <div id="container-atletas" class="d-flex flex-column gap-3"></div>
  </div>


</div>


<div id="modal-enviar-solicitacao" class="modal modal-lg">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Formar dupla</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>
            Selecione um atleta para formar dupla com o(a) <strong id="nome-atleta-clicado"></strong>
          </label>
          <select id="select-atleta-modal" class="form-control"></select>
        </div>
        <div id="container-input-categorias-modal" class="mb-3">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" id="btn-enviar-solicitacao">
          Enviar solicitação
        </button>
      </div>
    </div>
  </div>
</div>


<?php require_once 'template-atleta.html' ?>

<?php
Template::scripts();

$categoriasMarcadas = [];
if ($atletaCompeticao !== null) {
  $categoriasMarcadas = array_map(fn ($c) => $c->id(), $atletaCompeticao->categorias());
}
?>

<script>
  const idAtletaRemetente = <?= $atletaCompeticao?->atleta()?->id() ?? 'null' ?>;

  let inputCategorias = null;
  const categoriasMarcadas = <?= json_encode($categoriasMarcadas) ?>;

  fetchCategorias().then(categorias => {
    inputCategorias = new InputCategorias(categorias);
    inputCategorias.marcadas = categoriasMarcadas;
    qs('#container-input-categorias').append(inputCategorias.elemento());
  });


  const baseUrl = location.origin;

  const btnOrdenacaoTipo = qs('#btn-ordenacao-tipo');

  const idCompeticao = <?= $_GET['competicao'] ?>;

  const templateAtleta = qs('#template-atleta');
  const containerAtletas = qs('#container-atletas');

  btnOrdenacaoTipo.addEventListener('click', () => {
    const btn = btnOrdenacaoTipo;
    if (btn.getAttribute('data-ordenacao') == 'asc') {
      btn.setAttribute('data-ordenacao', 'desc');
      btn.innerText = 'Decrescente';
    } else {
      btn.setAttribute('data-ordenacao', 'asc');
      btn.innerText = 'Crescente';
    }
  });

  qs('#btn-limpar').addEventListener('click', limparFiltros);

  qs('#btn-filtrar').addEventListener('click', clicouFiltrar);
  clicouFiltrar();


  async function clicouFiltrar() {
    const filtros = getFiltros();

    const carregando = qs('#carregando');
    carregando.classList.remove('d-none');

    const atletas = await pesquisarAtletas(filtros);

    carregando.classList.add('d-none');

    esvaziar(containerAtletas);

    const alertaNenhumEncontrado = qs('#nenhum-encontrado');
    if (atletas.length == 0) {
      alertaNenhumEncontrado.classList.remove('d-none');
    } else {
      alertaNenhumEncontrado.classList.add('d-none');
      for (const atleta of atletas) {
        containerAtletas.append(criarElementoAtleta(atleta));
      }
    }
  }

  function criarElementoAtleta(atleta) {
    const elem = templateAtleta.content.firstElementChild.cloneNode(true);

    {
      const foto = eqs(elem, '.atleta-foto')
      foto.src = `/assets/images/profile/${atleta.pathFoto}`;
      foto.alt = `Foto de perfil do atleta '${atleta.nome}'`;
    }

    {
      const nome = eqs(elem, '.atleta-nome');
      nome.innerText = `${atleta.nome}`;
      nome.append(iconeSexo(atleta.sexo));

      adicionarTooltip(nome, atleta.informacoes);
    }

    {
      const idade = atleta.idade;
      const nascimento = new Date(atleta.dataNascimento);
      const html = `${pluralizar(idade, 'ano', 'anos')} <small>(${dataBr(nascimento)})</small>`;
      eqs(elem, '.atleta-idade-e-nascimento').innerHTML = html;
    }

    eqs(elem, '.atleta-categorias').innerText = (atleta.categorias ?? []).map(cat => cat.descricao).join(', ');

    {
      const buscaDuplas = eqs(elem, '.atleta-busca-duplas');
      for (const sexo of atleta.sexoDupla) {
        buscaDuplas.append(iconeSexo(sexo));
      }
    }

    {
      const tecnico = eqs(elem, '.atleta-tecnico');
      tecnico.innerText = atleta.tecnico.nome;

      adicionarTooltip(tecnico, atleta.tecnico.informacoes);
    }

    eqs(elem, '.atleta-clube').innerText = atleta.tecnico.clube.nome;

    {
      const containerInformacoes = eqs(elem, '.atleta-container-informacoes');
      const elementoInformacoes = eqs(elem, '.atleta-informacoes');

      const informacoes = atleta.informacoesCompeticao.trim();
      if (informacoes.length == 0) {
        containerInformacoes.classList.add('d-none');
      } else {
        elementoInformacoes.innerText = informacoes;
      }
    }

    {
        const formarDupla = eqs(elem, '.atleta-link-formar-dupla');

        const url = new URL(baseUrl + '/tecnico/competicoes/atletas/duplas/formar/');
        url.searchParams.append('destino', atleta.id);
        url.searchParams.append('competicao', idCompeticao);
        if (idAtletaRemetente != null) {
            url.searchParams.append('remetente', idAtletaRemetente);
        }

        formarDupla.setAttribute('href', url.toString());
    }

    return elem;
  }


  function getFiltros() {
    const filtros = {};

    function addFiltroText(nome, input) {
      if (!input) return
      if (!input.value) return
      const value = input.value.trim();
      if (!value) return
      filtros[nome] = value;
    }

    function addFiltroCheckbox(nome, inputs) {
      const selecionados = inputs.filter(x => x.checked).map(x => x.value)
      const todos = inputs.map(x => x.value)
      filtros[nome] = selecionados.length == 0 ? todos : selecionados;
    }

    addFiltroText('nomeAtleta', qs('#nome-atleta'));
    addFiltroText('nomeTecnico', qs('#nome-tecnico'));
    addFiltroText('clube', qs('#clube'));
    addFiltroText('idadeMaiorQue', qs('#idade-maior-que'));
    addFiltroText('idadeMenorQue', qs('#idade-menor-que'));

    filtros.categorias = inputCategorias?.marcadas ?? [];

    addFiltroCheckbox('sexoAtleta', Array.from(qsa('.input-sexo-atleta')));
    addFiltroCheckbox('sexoDupla', Array.from(qsa('.input-sexo-dupla')));

    filtros.idCompeticao = idCompeticao;
    filtros.ordenacao = qs('#btn-ordenacao-tipo').getAttribute('data-ordenacao');
    filtros.colunaOrdenacao = qs('#ordenacao-campo').selectedOptions[0].value;

    return filtros;
  }


  async function pesquisarAtletas(filtros) {
    const url = new URL(baseUrl + '/tecnico/competicoes/atletas/controller.php');

    for (const chave in filtros) {
      const valor = filtros[chave];
      if (Array.isArray(valor)) {
        const chaveArray = chave + '[]';
        for (const elem of valor) {
          url.searchParams.append(chaveArray, elem)
        }
      } else {
        url.searchParams.append(chave, valor)
      }
    }

    url.searchParams.append('acao', 'pesquisar');

    const response = await fetch(url);
    const text = await response.text();

    try {
      const retorno = JSON.parse(text);
      return retorno.resultados ?? [];
    } catch (err) {
      console.error('text', text);
      console.error('err', err);
    }
  }


  function limparFiltros() {
    qs('#nome-atleta').value = '';
    qs('#nome-tecnico').value = '';
    qs('#idade-maior-que').value = '';
    qs('#idade-menor-que').value = '';
    qs('#clube').value = '';

    const uncheck = it => {
      it.checked = false
    };
    qsa('.input-categoria').forEach(uncheck);
    qsa('.input-sexo-atleta').forEach(uncheck)
    qsa('.input-sexo-dupla').forEach(uncheck);
  }

  window.addEventListener('load', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const idAtleta = urlParams.get('atleta');
    if (idAtleta) {
      clicouFiltrar();
    }
  });
</script>

<?php Template::footer(); ?>

