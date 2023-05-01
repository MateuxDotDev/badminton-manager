<?php

require_once('../../../vendor/autoload.php');

use App\Util\SessionOld;

SessionOld::destruir();
header('Location: /admin');
