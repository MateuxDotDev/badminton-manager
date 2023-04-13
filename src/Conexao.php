<?php

namespace App;

use \PDO;

class Conexao {
  public static function criar(): PDO {
    return require('db_connect.php');
  }
}