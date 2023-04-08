<?php

class DBConfig {
    public function __construct(
        public readonly string $host,
        public readonly string $port,
        public readonly string $name,
        public readonly string $user,
        public readonly string $password,
    ) {}

    public function dsn(): string {
        return "pgsql:host=$this->host;port=$this->port;dbname=$this->name;user=$this->user;password=$this->password";
    }
}

function _getenv($s): array|string {
    $v = getenv($s);
    if ($v === false) {
        die("Falta $s no .env");
    }
    return $v;
}

$_host = _getenv('POSTGRES_HOST');
$_port = _getenv('POSTGRES_PORT');
$_name = _getenv('POSTGRES_DB');
$_user = _getenv('POSTGRES_USER');
$_password = _getenv('POSTGRES_PASSWORD');

return new DBConfig($_host, $_port, $_name, $_user, $_password);