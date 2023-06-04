<?php

namespace App\Tecnico\Atleta;
use App\Categorias\Categoria;
use App\Util\Traits\TemDataCriacao;
use App\Util\Traits\TemDataAlteracao;

class AtletaEmCompeticao
{
    use TemDataCriacao, TemDataAlteracao;

    private int $idAtleta;
    private Sexo $sexoAtleta;
    private int $idTecnico;
    private ?string $informacoes;
    private array $categorias = [];
    private array $sexoDupla = [];

    public function setIdAtleta(int $i): self
    {
        $this->idAtleta = $i;
        return $this;
    }

    public function setSexoAtleta(Sexo $s): self
    {
        $this->sexoAtleta = $s;
        return $this;
    }

    public function setIdTecnico(int $i): self
    {
        $this->idTecnico = $i;
        return $this;
    }

    public function setInformacoes(?string $s): self
    {
        $this->informacoes = $s;
        return $this;
    }

    public function addCategoria(Categoria $c): self
    {
        $this->categorias[] = $c;
        return $this;
    }

    public function addSexoDupla(Sexo $s): self
    {
        $this->sexoDupla[] = $s;
        return $this;
    }

    public function idAtleta(): int
    {
        return $this->idAtleta;
    }

    public function idTecnico(): int
    {
        return $this->idTecnico;
    }

    public function sexoAtleta(): Sexo
    {
        return $this->sexoAtleta;
    }

    public function informacoes(): ?string
    {
        return $this->informacoes;
    }

    public function categoriasEmQueJoga(): array
    {
        return $this->categorias;
    }

    public function sexoDuplasNecessarias(): array
    {
        return $this->sexoDupla;
    }

    public static function podemFormarDupla(
        AtletaEmCompeticao $a,
        AtletaEmCompeticao $b,
        int $idCategoria,
    ): bool
    {
        return in_array($idCategoria, $a->categorias)
            && in_array($idCategoria, $b->categorias)
            && in_array($a->sexoAtleta, $b->sexoDupla)
            && in_array($b->sexoAtleta, $a->sexoDupla)
            && $a->idTecnico != $b->idTecnico
            ;
    }
}