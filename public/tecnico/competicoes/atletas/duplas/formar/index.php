<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\AtletaRepository;
use App\Util\Database\Connection;
use App\Util\General\UserSession;
use App\Util\Template\Template;

$session = UserSession::obj();

Template::head('Solicitação para formar dupla');
if (!$session->isTecnico()) {
    // TODO (task futura) Por enquanto a parte para técnico não logado não está implementada
    Template::naoAutorizado();
}
Template::nav($session);

$pdo = Connection::getInstance();

if (!array_key_exists('destino', $_GET) || !array_key_exists('competicao', $_GET)) {
    Template::alerta('Link inválido');
}

$idAtletaDestinatario = (int) $_GET['destino'];
$idCompeticao         = (int) $_GET['competicao'];

// atleta que vem pré-selecionado (opcional)
$idAtletaRemetente    = array_key_exists('remetente', $_GET) ? $_GET['remetente'] : null;

$competicao = (new CompeticaoRepository($pdo))->getViaId($idCompeticao);
if (!$competicao) {
    Template::alerta('Competição não encontrada');
}

$atleta = (new AtletaRepository($pdo))->getViaId($idAtletaDestinatario);
if (!$atleta) {
    Template::alerta('Atleta não encontrado');
}

$atletaNaCompeticao = (new AtletaCompeticaoRepository($pdo))->get($atleta, $competicao);
if (!$atletaNaCompeticao) {
    Template::alerta('O atleta não foi incluído na competição');
}

$tecnicoLogado = $session->getTecnico();
$compativeis = buscarAtletasCompetiveisNaCompeticao($pdo, $tecnicoLogado->id(), $idCompeticao, $atletaNaCompeticao);

if (empty($compativeis)) {
    Template::alerta(
        'Você não tem nenhum atleta compatível para poder formar dupla com o(a) "'.
        $atletaNaCompeticao->atleta()->nomeCompleto() .
        '"',
    );
}

$categorias = (new CategoriaRepository($pdo))->buscarCategorias();

function buscarAtletasCompetiveisNaCompeticao(\PDO $pdo, int $idTecnico, int $idCompeticao, AtletaCompeticao $dest): array
{
    // "dest" porque é o atleta que será o destinatário da solicitação de formar dupla

    $destCategorias = implode(',', array_map(fn($c) => $c->id(), $dest->categorias()));
    $destSexoDupla  = implode(',', array_map(fn($s) => "'".$s->value."'", $dest->sexoDupla()));

    $sql = <<<SQL
              SELECT a.id
                   , a.nome_completo
                   , a.path_foto
                   , ac.informacoes
                   , jsonb_agg(acs.sexo_dupla) as sexo_dupla
                   , jsonb_agg(acc.categoria_id) as categorias
                FROM atleta a
                JOIN atleta_competicao ac
                  ON (ac.atleta_id, ac.competicao_id) = (a.id, :competicao_id)
        NATURAL JOIN atleta_competicao_categoria acc
        NATURAL JOIN atleta_competicao_sexo_dupla acs
               WHERE a.tecnico_id = :tecnico_id
                 AND acc.categoria_id IN ($destCategorias)
                 AND a.sexo IN ($destSexoDupla)
                 AND a.tecnico_id = :tecnico_id
            GROUP BY a.id, ac.atleta_id, ac.competicao_id
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'tecnico_id'    => $idTecnico,
        'competicao_id' => $idCompeticao,
    ]);

    $retorno = [];

    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $categorias = json_decode($row['categorias']);

        $emComum = [];
        foreach ($categorias as $idCategoria) {
            foreach ($dest->categorias() as $categoriaDest) {
                if ($categoriaDest->id() == $idCategoria) {
                    $emComum[] = $idCategoria;
                    break;
                }
            }
        }

        $retorno[] = [
            'id'                => $row['id'],
            'nome'              => $row['nome_completo'],
            'pathFoto'          => $row['path_foto'],
            'sexoDupla'         => json_decode($row['sexo_dupla']),
            'categorias'        => $categorias,
            'categoriasEmComum' => $emComum,
        ];

    }

    return $retorno;
}
?>

<main class="container">
    <h1>Formar dupla</h1>
    <div class="mb-3">
        <label>Selecione um(a) atleta para formar dupla com o(a) "<strong><?= $atleta->nomeCompleto() ?></strong>"</label>
        <select class="form-control" id="select-atleta-remetente">
            <?php foreach ($compativeis as $compativel) {
                $selected = $compativel['id'] == $idAtletaRemetente ? 'selected' : '';
                printf(
                    "<option %s value=%s>%s</option>",
                    $selected,
                    $compativel['id'],
                    $compativel['nome'],
                );
            } ?>
        </select>
    </div>
    <div id="container-input-categoria" class="mb-3">
    </div>
    <div class="mb-3">
        <label>Observações</label>
        <textarea class="form-control" id="observacoes"></textarea>
    </div>

    <div>
        <button class="btn btn-success" id="btn-enviar-solicitacao">
            <i class="bi bi-send"></i>
            Enviar solicitação
        </button>
    </div>

</main>

<?php Template::scripts() ?>

<script>
const urlBase = '/tecnico/competicoes/atletas/';

const idCompeticao = <?=$idCompeticao?>;
const idDestinatario = <?=$idAtletaDestinatario?>;

const categorias = <?=json_encode(array_map(fn($c) => $c->toJson(), $categorias))?>;
const inputCategorias = new InputCategorias(categorias, {
    botoes: false,
    radio: true,
    label: 'Selecione uma categoria',
});

const compativeis = <?=json_encode(array_index_by($compativeis, 'id'))?>;

qs('#container-input-categoria').append(inputCategorias.elemento());

const selectAtleta = qs('#select-atleta-remetente');

selectAtleta.addEventListener('change', mudouAtletaSelecionado);
mudouAtletaSelecionado();

qs('#btn-enviar-solicitacao').addEventListener('click', () => {
    const idRemetente = selectAtleta.value;
    const idCategoria = inputCategorias.marcadas?.at(0);
    if (!idCategoria) {
        Toast.fire({
            icon: 'warning',
            text: 'Selecione uma categoria'
        });
        return;
    }
    const informacoes = qs('#observacoes').value;
    enviarSolicitacao(idRemetente, idDestinatario, idCompeticao, idCategoria, informacoes);
});

function mudouAtletaSelecionado() {
    const selecionado = selectAtleta.value;
    const { categoriasEmComum } = compativeis[selecionado];
    inputCategorias.desmarcarTodas();
    inputCategorias.habilitadas = categoriasEmComum;
}

async function enviarSolicitacao(idRemetente, idDestinatario, idCompeticao, idCategoria, informacoes) {
    const dados = {
        acao: 'enviarSolicitacao',
        atletaRemetente: idRemetente,
        atletaDestinatario: idDestinatario,
        competicao: idCompeticao,
        categoria: idCategoria,
        informacoes: informacoes,
    };
    const response = await fetch(urlBase + 'controller.php', {
        body: JSON.stringify(dados),
        method: 'POST',
    });
    const texto = await response.text();
    try {
        const json = JSON.parse(texto);
        if (response.ok) {
            agendarAlerta({
                icon: 'success',
                text: 'Solicitação para formar dupla enviada com sucesso!'
            });
            location.assign(urlBase + `?competicao=${idCompeticao}`);
        } else {
            Toast.fire({
                icon: 'error',
                text: json.mensagem,
            });
        }
    } catch (err) {
        console.error('Erro json', { texto, response });
    }
}
</script>

<?php Template::footer() ?>