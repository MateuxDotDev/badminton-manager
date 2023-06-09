<?php

namespace App\Competicoes;

use App\Util\General\Dates;
use App\Util\Traits\TemDataAlteracao;
use App\Util\Traits\TemDataCriacao;
use DateTimeImmutable;
use DateTimeInterface;

class Competicao
{
    use TemDataAlteracao, TemDataCriacao;

    private int $id;
    private string $nome;
    private DateTimeInterface $prazo;
    private string $descricao;

    public function setId(int $id): Competicao
    {
        $this->id = $id;
        return $this;
    }

    public function setNome(string $nome): Competicao
    {
        $this->nome = $nome;
        return $this;
    }

    public function setDescricao(string $descricao): Competicao
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function setPrazo(DateTimeInterface $prazo): Competicao
    {
        $this->prazo = $prazo;
        return $this;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function nome(): string
    {
        return $this->nome;
    }

    public function prazo(): DateTimeInterface
    {
        return $this->prazo;
    }

    public function descricao(): string
    {
        return $this->descricao;
    }

    public function prazoPassou(?DateTimeInterface $data=null): bool
    {
        $data ??= new DateTimeImmutable('now');
        return $data->getTimestamp() >= $this->prazo->getTimestamp();
    }

    public static function fromRow(array $row): self
    {
        return (new Competicao)
            ->setId((int) $row['id'])
            ->setNome($row['nome'])
            ->setDescricao($row['descricao'])
            ->setPrazo(Dates::parseDay($row['prazo']))
            ->setDataCriacao(Dates::parseMicro($row['criado_em']))
            ->setDataAlteracao(Dates::parseMicro($row['alterado_em']))
            ;
    }

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'prazo' => $this->prazo->format('Y-m-d'),
            'descricao' => $this->descricao
        ];
    }
}
