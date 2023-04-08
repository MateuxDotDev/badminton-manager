<?

class Response {
  public function __construct(
    private readonly int $code = 200,
    private readonly string $mensagem = '',
    private readonly array $dados = [],
  ) {}

  public static function erro(string $mensagem='', array $dados=[]) {
    return new Response(400, $mensagem, $dados);
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