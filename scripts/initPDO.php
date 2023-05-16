<?php


function initPdo($maxAttempts = 10): PDO
{
    for ($i = 0; $i < $maxAttempts; $i++) {
        try {
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
        } catch (PDOException $e) {
            if ($i === $maxAttempts - 1) {
                echo $e->getMessage();
                exit(1);
            } else {
                echo "Waiting for postgres...\n";
            }
        }
    }

    echo "Max attempts reached\n";
    exit(1);
}
