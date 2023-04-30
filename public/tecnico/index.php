<?php
use App\Util\Session;
use App\Util\Template\Template;

require_once(__DIR__.'/../../vendor/autoload.php');

Session::iniciar();
if (!Session::isTecnico()) {
    Template::naoAutorizado();
}

Template::head('Tela inicial');

$tecnico = Session::getTecnico();
echo '<pre>';
dump($tecnico);
echo '</pre>';

Template::head('Tela inicial');

Template::scripts();

Template::footer();