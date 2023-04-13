<?

require('../../vendor/autoload.php');

use App\Response;
use App\RequestUtils;

require '../session.php';

$pdo = require '../db_connect.php';
$req = RequestUtils::getJson();

competicaoController($pdo, $req)->enviar();

function competicaoController(PDO $pdo, array $req): Response {
  if (!validaSessaoAdmin()) {
    return Response::erroNaoAutorizado();
  }
  $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
  $ret = match ($acao) {
    'criarCompeticao' => criarCompeticao($pdo ,$req),
    'excluirCompeticao' => excluirCompeticao($pdo, $req),
    'alterarCompeticao' => alterarCompeticao($pdo, $req),
    default => Response::erro('Ação inválida', ['acao' => $acao])
  };
  return $ret;
}

function criarCompeticao(PDO $pdo, array $req): Response {
  if ($resp = RequestUtils::validarCamposPresentes($req, ['nome', 'prazo'])) {
    return $resp;
  }
  $nome = $req['nome'];
  $prazo = DateTimeImmutable::createFromFormat('Y-m-d', $req['prazo']);
  if ($prazo === false) {
    return Response::erro("Prazo inválido");
  }
  if ($prazo->getTimestamp() < time()) {
    return Response::erro("Prazo deve ser no futuro");
  }
  try {
    $stmt = $pdo->prepare("INSERT INTO competicao (nome, prazo) VALUES (:nome, :prazo)");
    $ok = $stmt->execute([
      'nome' => $nome,
      'prazo' => $prazo->format('Y-m-d'),
    ]);
    if ($ok) {
      $id = $pdo->lastInsertId();
      return Response::ok('Competição criada com sucesso', ['id' => $id]);
    } else {
      return Response::erro('Erro ao salvar a competição');
    }
  } catch (Exception $e) {
    return Response::erroException($e);
  }
}

function excluirCompeticao(PDO $pdo, array $req): Response {
  if ($resp = RequestUtils::validarCamposPresentes($req, ['id'])) {
    return $resp;
  }

  // TODO
  // caso a competição já tenha inscrições, não pode ser excluída

  $id = $req['id'];
  try {
    $stmt = $pdo->prepare("DELETE FROM competicao WHERE id = :id");
    $ok = $stmt->execute(['id' => $id]);
    if ($ok) {
      return Response::okExcluido();
    } else {
      return Response::erro('Erro ao excluir a competição');
    }
  } catch (Exception $e) {
    return Response::erroException($e);
  }
}

function alterarCompeticao(PDO $pdo, array $req): Response {
  if ($resp = RequestUtils::validarCamposPresentes($req, ['id', 'nome', 'prazo'])) {
    return $resp;
  }
  $id = $req['id'];
  $nome = $req['nome'];
  $prazo = $req['prazo'];
  try {
    $stmt = $pdo->prepare("UPDATE competicao SET nome = :nome, prazo = :prazo WHERE id = :id");
    $stmt->execute([
      'id' => $id,
      'nome' => $nome,
      'prazo' => $prazo
    ]);
    if ($stmt->rowCount() == 0) {
      return Response::notFound();
    } else {
      return Response::ok('Competição alterada com sucesso');
    }
  } catch (Exception $e) {
    return Response::erroException($e);
  }
}