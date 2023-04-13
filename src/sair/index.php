<?

require_once('../../vendor/autoload.php');

use App\Sessao;

Sessao::iniciar();
Sessao::destruir();

header('Location: /');