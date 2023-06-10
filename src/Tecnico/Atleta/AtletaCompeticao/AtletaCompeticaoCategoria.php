<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Categorias\Categoria;

class AtletaCompeticaoCategoria
{
    private AtletaCompeticao $atletaCompeticao;
    private Categoria $categoria;

    public function __construct()
    {
        $this->atletaCompeticao = new AtletaCompeticao();
        $this->categoria = new Categoria(null, null, null, null);
    }

    public function setAtletaCompeticao(AtletaCompeticao $ac): self
    {
        $this->atletaCompeticao = $ac;
        return $this;
    }

    public function setCategoria(Categoria $c): self
    {
        $this->categoria = $c;
        return $this;
    }

    public function atletaCompeticao(): AtletaCompeticao
    {
        return $this->atletaCompeticao;
    }

    public function categoria(): Categoria
    {
        return $this->categoria;
    }
}