<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Util\Traits\TemDataAlteracao;
use App\Util\Traits\TemDataCriacao;

class AtletaCompeticao
{
    use TemDataAlteracao, TemDataCriacao;

    private Atleta $atleta;
    private Competicao $competicao;
    private string $informacao = '';
    private array $categorias = [];
    private array $sexoDupla = [];

    public function __construct()
    {
        $this->atleta = new Atleta();
        $this->competicao = new Competicao();
    }
    
    public function setAtleta(Atleta $a): self
    {
        $this->atleta = $a;
        return $this;
    }
    
    public function setCompeticao(Competicao $c): self
    {
        $this->competicao = $c;
        return $this;
    }
    
    public function setInformacao(string $s): self
    {
        $this->informacao = $s;
        return $this;
    }

    public function jogaEmCategoria(int $idCategoria): bool
    {
        foreach ($this->categorias as $categoria) {
            if ($categoria->id() == $idCategoria) {
                return true;
            }
        }
        return false;
    }

    public function buscaDuplaDoSexo(Sexo $sexo): bool
    {
        foreach ($this->sexoDupla as $sexoRegistrado) {
            if ($sexoRegistrado == $sexo) {
                return true;
            }
        }
        return false;
    }

    public function addCategoria(Categoria ...$categorias): self
    {
        foreach ($categorias as $categoria) {
            // Evita itens duplicados (causaria erro na hora de fazer um INSERT)
            // Sim, isso é linear em vez de constante, mas como são sempre poucas categorias não faz diferença
            if ($this->jogaEmCategoria($categoria->id())) {
                continue;
            }
            $this->categorias[] = $categoria;
        }
        return $this;
    }

    public function addSexoDupla(Sexo ...$sexos): self
    {
        foreach ($sexos as $sexo) {
            if ($this->buscaDuplaDoSexo($sexo)) {
                continue;
            }
            $this->sexoDupla[] = $sexo;
        }
        return $this;
    }

    public function atleta(): Atleta
    {
        return $this->atleta;
    }

    public function competicao(): Competicao
    {
        return $this->competicao;
    }

    public function informacao(): string
    {
        return $this->informacao;
    }

    public function categorias(): array
    {
        return $this->categorias;
    }

    public function sexoDupla(): array
    {
        return $this->sexoDupla;
    }
}