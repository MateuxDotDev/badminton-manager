<?php

/**
 * Modo de uso:
 *
 * Executar todas as migrations que ainda não foram executadas
 * $ php migrate.php
 *
 * Executar uma migration específica que ainda não foi executada
 * $ php migrate.php?migration=2023-04-07-01-senhas-admin
 *
 * Executar uma migration específica, mesmo se já foi executada
 * $ php migrate.php?migration=2023-04-07-01-senhas-admin&force
 */

declare(strict_types=1);

// coloquei o script e as migrations na /public porque atualmente é o único jeito de rodar no servidor
// mas seria melhor fora da /public (não é público, é interno)

$config = require 'db_config.php';
$pdo = new PDO($config->dsn());
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("set time zone -3");

$nomeMigration = '';
$force         = false;

foreach ($argv as $arg) {
  if ($arg === 'force') {
    $force = true;
  } else if (str_starts_with($arg, 'migration')) {
    [,$nomeMigration] = explode('=', $arg);
  }
}

migrationLog("Executando migrations...");

if ($nomeMigration !== '') {
  $diretorio = "migrations/$nomeMigration.sql";
  if ($force || !migrationJaFoiExecutada($pdo, $nomeMigration)) {
    executarMigration($pdo, $diretorio);
  }
}
else {
  $diretorios = glob('migrations/*.sql');
  if ($diretorios === false) {
    die("Erro ao buscar arquivos de migration");
  }
  foreach ($diretorios as $diretorio) {
    $nomeMigration = pathinfo($diretorio, PATHINFO_FILENAME);
    if (preg_match('/^0/', $nomeMigration)) {
      continue;
    }
    if (migrationJaFoiExecutada($pdo, $nomeMigration)) {
      continue;
    }
    if (!executarMigration($pdo, $diretorio)) {
      // se uma falhou, não executar as outras
      break;
    }
  }

}
migrationLog("Fim");



function migrationLog(string $s): void {
  $datahora = date('d/m/Y H:i:s');
  echo "[$datahora] => $s\n\n";
}

function migrationJaFoiExecutada(PDO $pdo, string $nomeMigration): bool {
  $stmt = $pdo->prepare("SELECT 1 FROM migrations_executadas WHERE nome = :nome");
  $stmt->bindParam('nome', $nomeMigration, PDO::PARAM_STR);
  $stmt->execute();
  return count($stmt->fetchAll()) > 0;
}

function salvarMigrationExecutada(PDO $pdo, string $nomeMigration): void {
  $stmt = $pdo->prepare("
    INSERT INTO migrations_executadas (nome)
    VALUES (:nome)
    ON CONFLICT (nome) DO UPDATE SET datahora = NOW()
  ");
  $stmt->bindParam('nome', $nomeMigration, PDO::PARAM_STR);
  $stmt->execute();
}

function executarMigration(PDO $pdo, string $diretorio): bool {
  $conteudo = file_get_contents($diretorio);
  if ($conteudo === false) {
    die("Erro ao abrir migration '$diretorio'");
  }

  // separa comandos via linha somente com --
  // pode ter espaços antes e depois
  $comandos = preg_split("/\n\s*\-\-\s*\n/", $conteudo);

  $pdo->beginTransaction();
  try {
    migrationLog("Executando comandos do script $diretorio");
    foreach ($comandos as $comando) {
      migrationLog("Executando comando:\n$comando");
      $pdo->query($comando);
    }

    $nomeMigration = pathinfo($diretorio, PATHINFO_FILENAME);
    salvarMigrationExecutada($pdo, $nomeMigration);

    $pdo->commit();
    migrationLog("Migration $nomeMigration executada com sucesso");
    
    return true;

  } catch (Exception $e) {
    $pdo->rollBack();
    $msg = $e->getMessage();
    migrationLog("Erro ao rodar migration $diretorio ($msg)");

    return false;
  }

}


