<?php

/**
 * @throws Exception
 */
function generateSalt(): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $salt = '';

    for ($i = 0; $i < random_int(32, 64); $i++) {
        $salt .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $salt;
}

/**
 * @throws Exception
 */
function generateAdmin(string $username, string $password): bool
{
    $pdo = initPdo();
    $salt = generateSalt();
    $hash = password_hash($username . $password . $salt, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('
        INSERT INTO admin ("user", hash_senha, salt_senha)
                   VALUES (:user, :hash_senha, :salt_senha)
     ');
    $stmt->bindParam(':user', $username);
    $stmt->bindParam(':hash_senha', $hash);
    $stmt->bindParam(':salt_senha', $salt);

    return $stmt->execute();
}

if (empty($argv[1]) || empty($argv[2])) {
    echo "Error: Username and password cannot be empty.\n";
    exit(1);
}

$username = $argv[1];
$password = $argv[2];

try {
    if (generateAdmin($username, $password)) {
        echo "Admin user '{$username}' created successfully.\n";
    } else {
        echo "Error: Failed to create admin user '{$username}'.\n";
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
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
