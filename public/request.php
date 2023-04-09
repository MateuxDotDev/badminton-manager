<?

class Response {
  public function __construct(
    private readonly int $code = 200,
    private readonly string $mensagem = '',
    private readonly array $dados = [],
  ) {}

  public static function justOk(): Response {
    return new Response(200);
  }

  public static function ok(string $mensagem='', array $dados=[]): Response {
    return new Response(200, $mensagem, $dados);
  }

  public static function okExcluido(): Response {
    return new Response(204);
  }

  public static function erro(string $mensagem='', array $dados=[]): Response {
    return new Response(400, $mensagem, $dados);
  }

  public static function erroException(Exception $e): Response {
    return new Response(500, 'Ocorreu um erro inesperado', ['exception' => $e]);
  }

  public static function erroNaoAutorizado(): Response {
    return new Response(401, 'Usuário não autorizado para essa ação');
  }

  public static function notFound(): Response {
    return new Response(404, 'Recurso não encontrado');
  }

  public function statusCode(): int {
    return $this->code;
  }

  public function mensagem(): string {
    return $this->mensagem;
  }

  public function array(): array {
    $a = [];
    if ($this->mensagem !== '') {
      $a['mensagem'] = $this->mensagem;
    }
    foreach ($this->dados as $chave => $valor) {
      $a[$chave] = $valor;
    }
    return $a;
  }
}

function getJson(): array {
  $json = file_get_contents('php://input');
  if ($json === false) die('Erro ao ler JSON da request');
  return json_decode($json, true);
}

function retornarResponse(Response $r): void {
  http_response_code($r->statusCode());
  header('Content-Type: application/json');
  die(json_encode(
    $r->array(),
    JSON_PRETTY_PRINT
  ));
}

function validarCamposPresentes(array $req, array $camposRequeridos): Response|false {
  foreach ($camposRequeridos as $campo) {
    if (!array_key_exists($campo, $req)) {
      return Response::erro("Campo faltando na requisição", ['campo' => $campo]);
    }
  }
  return false;
}