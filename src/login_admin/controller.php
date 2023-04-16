<?php

use App\Database\Connection;
use App\Response;
use App\RequestUtils;
use App\Sessao;

require('../../vendor/autoload.php');

$pdo = Connection::getInstance();
$req = RequestUtils::getJson();

loginAdminController($pdo, $req)->enviar();

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
        Sessao::iniciar();
        Sessao::setAdmin();
      }
    }
    return $ret;
  } catch (Exception $e) {
    return Response::erroException($e);
  }
}

