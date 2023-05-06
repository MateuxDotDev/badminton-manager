<?php

use App\Session;
use App\Util\SessionOld;
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

SessionOld::iniciar();
if (!Session::isTecnico()) {
    Template::naoAutorizado();
}

Template::head('Tela inicial');

$tecnico = SessionOld::getTecnico();
echo '<pre>';
dump($tecnico);
echo '</pre>';

Template::head('Tela inicial');

Template::scripts();

Template::footer();