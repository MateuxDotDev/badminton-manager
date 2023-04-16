<?php

namespace App\Competicoes;

use \DateTimeInterface;
use \DateTimeImmutable;

class Competicao {
  private int $id;
  private string $nome;
  private DateTimeInterface $prazo;

  public function setId(int $id): Competicao {
    $this->id = $id;
    return $this;
  }

  public function setNome(string $nome): Competicao {
    $this->nome = $nome;
    return $this;
  }

  public function setPrazo(DateTimeInterface $prazo): Competicao {
    $this->prazo = $prazo;
    return $this;
  }

  public function id(): int { return $this->id; }
  public function nome(): string { return $this->nome; }
  public function prazo(): DateTimeInterface { return $this->prazo; }

  public function prazoPassou(?DateTimeInterface $data=null) {
    $data ??= new DateTimeImmutable('now');
    return $data->getTimestamp() >= $this->prazo->getTimestamp();
  }

  public function toJson(): array {
    return [
      'id' => $this->id,
      'nome' => $this->nome,
      'prazo' => $this->prazo->format('Y-m-d')
    ];
  }
}