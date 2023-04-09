<?

class Competicao {
  private int $id;
  private string $nome;
  private DateTimeImmutable $prazo;

  public function setId(int $id): Competicao {
    $this->id = $id;
    return $this;
  }

  public function setNome(string $nome): Competicao {
    $this->nome = $nome;
    return $this;
  }

  public function setPrazo(DateTimeImmutable $prazo): Competicao {
    $this->prazo = $prazo;
    return $this;
  }

  public function id(): int { return $this->id; }
  public function nome(): string { return $this->nome; }
  public function prazo(): DateTimeImmutable { return $this->prazo; }

  public function passouPrazo(DateTimeImmutable $data) {
    return $data->getTimestamp() >= $this->prazo->getTimestamp();
  }
}