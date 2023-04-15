<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\util\Environment;

$_host = Environment::getPostgresHost();
$_port = Environment::getPostgresPort();
$_name = Environment::getPostgresDb();
$_user = Environment::getPostgresUser();
$_password = Environment::getPostgresPassword();

$_dsn = "pgsql:host=$_host;port=$_port;dbname=$_name;user=$_user;password=$_password";

$_pdo = new PDO($_dsn);
$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$_pdo->exec("set time zone -3");

return $_pdo;