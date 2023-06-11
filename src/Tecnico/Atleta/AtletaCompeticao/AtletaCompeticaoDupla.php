<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Tecnico\Atleta\Sexo;

class AtletaCompeticaoDupla
{
    private AtletaCompeticao $atletaCompeticao;
    private Sexo $tipoDupla;

    public function setAtletaCompeticao(AtletaCompeticao $atletaCompeticao): self
    {
        $this->atletaCompeticao = $atletaCompeticao;
        return $this;
    }

    public function setTipoDupla(Sexo $sexo): self
    {
        $this->tipoDupla = $sexo;
        return $this;
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
