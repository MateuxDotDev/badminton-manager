<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Util\Traits\TemDataAlteracao;
use App\Util\Traits\TemDataCriacao;

class AtletaCompeticao
{

    private Atleta $atleta;
    private Competicao $competicao;
    private string $informacao;

    public function __construct(){
        $this->atleta = new Atleta();
        $this->competicao = new Competicao();
    }
    
    public function setAtleta(Atleta $a){
        $this->atleta = $a;
        return $this;
    }
    
    public function setCompeticao(Competicao $c){
        $this->competicao = $c;
        return $this;
    }
    
    public function setInformacao(string $s){
        $this->informacao = $s;
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

    public function toJson(): array
    {
        return [
            'atleta_id' => $this->atleta()->id(),
            'competicao_id' => $this->competicao()->id(),
            'informacao' => $this->informacao(),
            'dataCriacao' => Dates::formatBr($this->dataCriacao()),
            'dataAlteracao' => Dates::formatBr($this->dataAlteracao())
        ];
    }
}