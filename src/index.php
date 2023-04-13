<?
use App\Pagina;

require_once('../vendor/autoload.php');

$pag = new Pagina();
$pag->header('MatchPoint');
phpinfo();
$pag->scripts();
$pag->footer();
?>