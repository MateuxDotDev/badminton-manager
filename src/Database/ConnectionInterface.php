<?php

namespace App\Database;

use PDO;

interface ConnectionInterface
{
    public static function getInstance(): ?PDO;
    public static function setInstance(?PDO $instance): void;
}
