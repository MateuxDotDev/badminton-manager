<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\Sexo;

class AtletaCompeticaoDupla
{

    private AtletaCompeticao $atletaCompeticao;
    private Sexo $tipoDupla;

    public function __construct(){
        $this->atletaCompeticao = new AtletaCompeticao();
    }

    public function setAtletaCompeticao(AtletaCompeticao $ac){
        $this->atletaCompeticao = $ac;
    }

    public function setTipoDupla(Sexo $td){
        $this->tipoDupla = $td;
    }

    public function atletaCompeticao(): AtletaCompeticao
    {
        return $this->atletaCompeticao;
    }

    public function tipoDupla(): Sexo
    {
        return $this->tipoDupla;
    }
}