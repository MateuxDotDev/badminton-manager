<?

declare(strict_types=1);

require "../request.php";
$pdo = require "../db_connect.php";
$req = getJson();

retornarResponse(loginAdminController($pdo, $req));

function loginAdminController(PDO $pdo, array $req): Response {
  $acao = $req['acao'];
  return match ($acao) {
    'login' => acaoLogin($pdo, $req),
    default => Response::erro('Ação inválida', ['acao' => $acao])
  };
}

function acaoLogin(PDO $pdo, array $req): Response {
  $senha = $req['senha'];
  try {
    $linhas = $pdo->query("SELECT hash_senha FROM senhas_administrador")->fetchAll();
    foreach ($linhas as $linha) {
      $hash = $linha['hash_senha'];
      if (password_verify($senha, $hash)) {
        // TODO token jwt etc
        $token = 'placeholder123';
        return new Response(mensagem: 'Login realizado com sucesso', dados: ['token' => $token]);
      }
    }
    return Response::erro('Senha incorreta');
  } catch (Exception $e) {
    return Response::erro('Erro inesperado', ['exception' => $e]);
  }
}

