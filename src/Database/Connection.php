<?php

namespace App\Database;

use PDO;

interface Connection
{
    public static function getInstance(): PDO;

    public function setInstance(PDO $instance): void;
}
