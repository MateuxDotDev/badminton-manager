<?php

/**
 * This script provides functionality to manage and execute database migrations.
 *
 * Available actions:
 * 1. Create a new migration file: php migrate.php new
 *    - This will create a new empty migration file with the current timestamp in the "migrations" folder.
 *
 * 2. Run the most recent migration: php migrate.php latest
 *    - This will execute the most recent migration file that hasn't been executed yet.
 *
 * 3. Run a specific migration: php migrate.php migration=migration_2023_04_19_000001
 *    - Replace "migration_2023_04_19_000001" with the desired migration file's name (without the .sql extension).
 *    - This will execute the specified migration file if it hasn't been executed yet.
 *
 * 4. Force run a specific migration: php migrate.php migration=migration_2023_04_19_000001 force
 *    - Replace "migration_2023_04_19_000001" with the desired migration file's name (without the .sql extension).
 *    - This will execute the specified migration file even if it has already been executed before.
 *
 * 5. Run all pending migrations: php migrate.php
 *    - This will execute all pending migrations in chronological order.
 *    - If a migration fails, the script will stop and won't execute the remaining migrations.
 */


declare(strict_types=1);

require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

$pdo = initPdo();
$migrationName = '';
$force = false;
$action = 'migrate'; // Default action is to run migrations
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
    $filePath = "files/$migrationName.sql";
    if (file_put_contents($filePath, '') !== false) {
        logMigration("Migration file created: $filePath");
    } else {
        logMigration("Error creating migration file: $filePath");
    }
}

function runLatestMigration(): void
{
    $files = glob('files/*.sql');
    if (empty($files)) {
        die("No migration files found");
    }

    usort($files, function ($a, $b) {
        return strcmp($a, $b);
    });

    $latestFile = end($files);
    runMigrations(pathinfo($latestFile, PATHINFO_FILENAME), false);
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
        $files = glob('files/*.sql');
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
    $stmt = $pdo->prepare("SELECT 1 FROM migrations WHERE migration = :migration");
    $stmt->bindParam('migration', $nomeMigration);
    $stmt->execute();
    return count($stmt->fetchAll()) > 0;
}

function saveRunMigration(PDO $pdo, string $nomeMigration): void
{
    $stmt = $pdo->prepare("
        INSERT INTO migrations (migration)
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

function initPdo(): PDO
{
    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        getenv('POSTGRES_HOST'),
        getenv('POSTGRES_PORT'),
        getenv('POSTGRES_DB')
    );

    $pdo = new PDO(
        $dsn,
        getenv('POSTGRES_USER'),
        getenv('POSTGRES_PASSWORD'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set time zone -3");

    return $pdo;
}