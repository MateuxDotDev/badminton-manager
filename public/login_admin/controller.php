<?

declare(strict_types=1);

// TODO usar autoload pra esses arquivos? intellisense vai relacionar?
require "../session.php";
require "../request.php";
$pdo = require "../db_connect.php";
$req = getJson();

retornarResponse(loginAdminController($pdo, $req));

function loginAdminController(PDO $pdo, array $req): Response {
  $acao = array_key_exists('acao', $req) ? $req['acao'] : '';
  return match ($acao) {
    'login' => acaoLogin($pdo, $req),
    default => Response::erro('Ação inválida', ['acao' => $acao])
  };
}

function acaoLogin(PDO $pdo, array $req): Response {
  $senha = $req['senha'];
  try {
    $ret = Response::erro('Senha incorreta');
    $linhas = $pdo->query("SELECT hash_senha FROM senhas_administrador")->fetchAll();
    foreach ($linhas as $linha) {
      $hash = $linha['hash_senha'];
      if (password_verify($senha, $hash)) {
        $ret = Response::justOk();
        criarSessaoAdmin();
      }
    }
    return $ret;
  } catch (Exception $e) {
    return Response::erroException($e);
  }
}

