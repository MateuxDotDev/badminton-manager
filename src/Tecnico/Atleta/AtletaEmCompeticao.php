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

    public function jogaNaCategoria(int $idCategoria): bool
    {
        foreach ($this->categorias as $categoria) {
            if ($categoria->id() == $idCategoria) {
                return true;
            }
        }
        return false;
    }

    public function sexoDuplasNecessarias(): array
    {
        return $this->sexoDupla;
    }

    public static function podemFormarDupla(
        AtletaEmCompeticao $a,
        AtletaEmCompeticao $b,
        int $idCategoria,
    ): ResultadoCompatibilidade
    {
        if (!$a->jogaNaCategoria($idCategoria)) return ResultadoCompatibilidade::CATEGORIA_INCOMPATIVEL;
        if (!$b->jogaNaCategoria($idCategoria)) return ResultadoCompatibilidade::CATEGORIA_INCOMPATIVEL;
        if (!in_array($a->sexoAtleta, $b->sexoDupla)) return ResultadoCompatibilidade::SEXO_INCOMPATIVEL;
        if (!in_array($b->sexoAtleta, $a->sexoDupla)) return ResultadoCompatibilidade::SEXO_INCOMPATIVEL;
        if ($a->idTecnico == $b->idTecnico) return ResultadoCompatibilidade::MESMO_TECNICO;
        return ResultadoCompatibilidade::OK;
    }
}