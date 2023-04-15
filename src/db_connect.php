<?php

require_once('util/env.php');

$_host = $GLOBALS['env']['postgres']['host'];
$_port = $GLOBALS['env']['postgres']['port'];
$_name = $GLOBALS['env']['postgres']['name'];
$_user = $GLOBALS['env']['postgres']['user'];
$_password = $GLOBALS['env']['postgres']['password'];

$_dsn = "pgsql:host=$_host;port=$_port;dbname=$_name;user=$_user;password=$_password";

$_pdo = new PDO($_dsn);
$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$_pdo->exec("set time zone -3");

return $_pdo;