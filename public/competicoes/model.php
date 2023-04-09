<?

require 'Competicao.php';

function buscarCompeticoes(PDO $pdo): array {
  $sql = "SELECT id, nome, prazo FROM competicao ORDER BY prazo DESC";
  $qry = $pdo->query($sql);
  $competicoes = [];
  foreach ($qry as $linha) {
    $competicoes[] = (new Competicao)
      ->setId((int) $linha['id'])
      ->setNome($linha['nome'])
      ->setPrazo(DateTimeImmutable::createFromFormat('Y-m-d', $linha['prazo']));
  }
  return $competicoes;
}