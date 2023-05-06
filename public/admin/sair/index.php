<?php

require_once('../../../vendor/autoload.php');

use App\Util\General\OldSession;

OldSession::destruir();
header('Location: /admin');
