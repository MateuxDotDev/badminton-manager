<?

function _getenv($s): string|array {
  $x = getenv($s);
  if ($x === false) {
    die("Falta $s no .env");
  }
  return $x;
} 

$_host = _getenv('POSTGRES_HOST');
$_port = _getenv('POSTGRES_PORT');
$_name = _getenv('POSTGRES_DB');
$_user = _getenv('POSTGRES_USER');
$_password = _getenv('POSTGRES_PASSWORD');

$_dsn = "pgsql:host=$_host;port=$_port;dbname=$_name;user=$_user;password=$_password";

$_pdo = new PDO($_dsn);
$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$_pdo->exec("set time zone -3");

return $_pdo;