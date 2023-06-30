<?php

namespace App\Tecnico\Dupla;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Clube;
use App\Tecnico\Tecnico;
use App\Util\General\Dates;
use App\Util\Traits\TemDataCriacao;
use App\Util\Traits\TemId;
use DateTimeInterface;

class Dupla
{
    use TemId, TemDataCriacao;

    private int $idSolicitacao;
    private Categoria $categoria;
    private Competicao $competicao;
    private Atleta $atleta1;
    private Atleta $atleta2;

    public function __construct(
        int $id,
        int $idSolicitacao,
        Categoria $categoria,
        Competicao $competicao,
        Atleta $atleta1,
        Atleta $atleta2,
        DateTimeInterface $dataCriacao,
    ) {
        $this->id = $id;
        $this->idSolicitacao = $idSolicitacao;
        $this->categoria = $categoria;
        $this->competicao = $competicao;
        $this->atleta1 = $atleta1;
        $this->atleta2 = $atleta2;
        $this->dataCriacao = $dataCriacao;
    }

    public function idSolicitacao(): int
    {
        return $this->idSolicitacao;
    }

    public function categoria(): Categoria
    {
        return $this->categoria;
    }

    public function competicao(): Competicao
    {
        return $this->competicao;
    }

    public function atleta1(): Atleta
    {
        return $this->atleta1;
    }

    public function atleta2(): Atleta
    {
        return $this->atleta2;
    }

    public function atletaFromTecnico(int $idTecnico): Atleta
    {
        return $this->atleta1->tecnico()->id() === $idTecnico
            ? $this->atleta1
            : $this->atleta2;
    }

    public function other(int $idAtleta): Atleta
    {
        return $this->atleta1->id() === $idAtleta
            ? $this->atleta2
            : $this->atleta1;
    }

    public static function fromRow(array $row): self
    {
        $atletas = json_decode($row['atletas'], true);

        return new self(
            $row['id'],
            $row['idSolicitacao'],
            new Categoria($row['categoriaId'], $row['categoria']),
            (new Competicao())->setId($row['competicaoId'])->setNome($row['competicao']),
            self::atletaFromArray($atletas[0]),
            self::atletaFromArray($atletas[1]),
            Dates::parseMicro($row['criadoEm']),
        );
    }

    private static function atletaFromArray(array $array): Atleta
    {
        return (new Atleta())
            ->setId($array['id'])
            ->setNomeCompleto($array['nome'])
            ->setSexo(Sexo::from($array['sexo']))
            ->setDataNascimento(Dates::parseDay($array['dataNascimento']))
            ->setFoto($array['foto'])
            ->setInformacoesAdicionais($array['informacoes'])
            ->setTecnico((new Tecnico())
                ->setId($array['tecnico']['id'])
                ->setNomeCompleto($array['tecnico']['nome'])
                ->setEmail($array['tecnico']['email'])
                ->setInformacoes($array['tecnico']['informacoes'])
                ->setClube((new Clube())
                    ->setId($array['tecnico']['clubeId'])
                    ->setNome($array['tecnico']['clube'])
                )
            );
    }
}
