<?php

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;

class AtletaCompeticaoCategoria
{
    private AtletaCompeticao $atletaCompeticao;
    private Categoria $categoria;

    public function setAtletaCompeticao(AtletaCompeticao $atletaCompeticao): self
    {
        $this->atletaCompeticao = $atletaCompeticao;
        return $this;
    }

    public function setCategoria(Categoria $categoria): self
    {
        $this->categoria = $categoria;
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
