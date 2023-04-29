<?php

/*
O arquivo migrate.php é um script responsável pela gestão de migrações de banco de dados. Aqui estão as funções que o script pode realizar:

1. Criação de uma nova migração: Este script pode criar um novo arquivo de migração, com um nome baseado na data atual e opcionalmente um sufixo fornecido pelo usuário. O arquivo de migração é um arquivo SQL vazio que é colocado na pasta "migrations".

Exemplo de uso:
php migrate.php new nome_da_migracao
Este comando cria um arquivo SQL vazio chamado migration_YYYY_MM_DD_HHMMSS_nome_da_migracao.sql na pasta "migrations".

2. Execução de migrações: O migrate.php pode executar migrações de banco de dados que ainda não foram executadas. As migrações são armazenadas como arquivos SQL na pasta "migrations". Cada arquivo é lido e os comandos SQL nele contidos são executados no banco de dados.

Exemplo de uso:
php migrate.php new AdicionarDescricaoACompeticao
Cria uma nova migration com o nome informado.

php migrate.php migrate
Este comando executa todas as migrações que ainda não foram executadas.

php migrate.php migrate=migration_2023_04_26_153002_nome_da_migracao
Este comando executa a migração especificada, se ela ainda não tiver sido executada.

php migrate.php migrate=migration_2023_04_26_153002_nome_da_migracao force
Este comando força a execução da migração especificada, mesmo se ela já tiver sido executada.

php migrate.php latest
Este comando executa a migração mais recente que ainda não foi executada.
*/

declare(strict_types=1);

require_once __DIR__ . '/initPDO.php';

$pdo = initPdo();
$migrationName = '';
$force = false;
$action = 'migrate'; // Default action is to run scripts
$newMigrationName = '';

foreach ($argv as $index => $arg) {
    if ($arg === 'force') {
        $force = true;
    } elseif (str_starts_with($arg, 'migration')) {
        [, $migrationName] = explode('=', $arg);
    } elseif (in_array($arg, ['new', 'latest', 'migrate'])) {
        $action = $arg;
        if ($action === 'new' && isset($argv[$index + 1])) {
            $newMigrationName = $argv[$index + 1];
        }
    }
}

switch ($action) {
    case 'new':
        createNewMigration($newMigrationName);
        break;
    case 'latest':
        runLatestMigration();
        break;
    case 'migrate':
    default:
        runMigrations($migrationName, $force);
        break;
}

function createNewMigration($name): void
{
    $namePart = $name !== '' ? '_' . $name : '';
    $migrationName = 'migration_' . date('Y_m_d_His'). $namePart;
    $filePath = "migrations/$migrationName.sql";
    if (file_put_contents($filePath, '') !== false) {
        logMigration("Migration file created: $filePath");
    } else {
        logMigration("Error creating migration file: $filePath");
    }
}

function runLatestMigration(): void
{
    $files = glob('migrations/*.sql');
    if (empty($files)) {
        die("No migration files found");
    }

    usort($files, function ($a, $b) {
        return strcmp($a, $b);
    });

    $latestFile = end($files);
    runMigrations($latestFile, false);
}

function runMigrations(string $migrationName, bool $force): void
{
    $pdo = initPdo();
    logMigration("Running migrations...");

    if ($migrationName !== '') {
        if ($force || !migrationAlreadyRun($pdo, $migrationName)) {
            executeMigration($pdo, $migrationName);
        } else {
            logMigration("Migration $migrationName already run");
        }
    } else {
        $files = glob('migrations/*.sql');
        if ($files === false) {
            die("Error reading migrations folder");
        }
        if (empty($files)) {
            die("No migration files found");
        }
        foreach ($files as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            if (str_starts_with($migrationName, '0')) {
                continue;
            }
            if (migrationAlreadyRun($pdo, $migrationName)) {
                logMigration("Migration $migrationName already run");
                continue;
            }
            if (!executeMigration($pdo, $file)) {
                // If fails, stop execution
                break;
            }
        }
    }
    logMigration("End");
}

function executeMigration(PDO $pdo, string $directory): bool
{
    $content = file_get_contents($directory);
    if ($content === false) {
        die("Error opening migration '$directory'");
    }

    // Splits commands by lines containing only --
    // Spaces before and after are allowed
    $commands = preg_split("/\n\s*--\s*\n/", $content);

    $commands = array_filter($commands, function ($command) {
        return trim($command) !== '';
    });

    if (empty($commands)) {
        die("No commands found in migration '$directory'");
    }

    $pdo->beginTransaction();
    try {
        logMigration("Executing commands from script $directory");
        foreach ($commands as $command) {
            logMigration("Executing command:\n$command");
            $pdo->query($command);
        }

        $migrationName = pathinfo($directory, PATHINFO_FILENAME);
        saveRunMigration($pdo, $migrationName);

        $pdo->commit();
        logMigration("Migration $migrationName executed successfully");

        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = $e->getMessage();
        logMigration("Error running migration $directory ($msg)");

        return false;
    }
}

function migrationAlreadyRun(PDO $pdo, string $nomeMigration): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM migration WHERE migration = :migration");
    $stmt->bindParam('migration', $nomeMigration);
    $stmt->execute();
    return count($stmt->fetchAll()) > 0;
}

function saveRunMigration(PDO $pdo, string $nomeMigration): void
{
    $stmt = $pdo->prepare("
        INSERT INTO migration (migration)
        VALUES (:migration)
        ON CONFLICT (migration) DO UPDATE SET created_at = NOW()
    ");
    $stmt->bindParam('migration', $nomeMigration);
    $stmt->execute();
}


function logMigration(string $s): void
{
    $dateTime = date('d/m/Y H:i:s');
    echo "[$dateTime] => $s\n\n";
}
