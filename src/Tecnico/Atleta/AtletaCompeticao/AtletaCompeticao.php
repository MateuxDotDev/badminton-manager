<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;

class AtletaCompeticao
{
    private string $informacao;
    private Atleta $atleta;
    private Competicao $competicao;

    public function setAtleta(Atleta $atleta): self
    {
        $this->atleta = $atleta;
        return $this;
    }
    
    public function setCompeticao(Competicao $competicao): self
    {
        $this->competicao = $competicao;
        return $this;
    }
    
    public function setInformacao(string $informacao): self
    {
        $this->informacao = $informacao;
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
}
