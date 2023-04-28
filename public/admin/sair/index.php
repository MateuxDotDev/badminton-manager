<?php

require_once('../../../vendor/autoload.php');

use App\Util\Session;

Session::destruir();
header('Location: /admin');
