<?php

use App\Util\General\OldSession;
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

OldSession::iniciar();
if (!OldSession::isTecnico()) {
    Template::naoAutorizado();
}

Template::head('Tela inicial');

$tecnico = OldSession::getTecnico();
echo '<pre>';
dump($tecnico);
echo '</pre>';

Template::head('Tela inicial');

Template::scripts();

Template::footer();
