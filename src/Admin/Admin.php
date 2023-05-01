<?php
namespace App\Admin;

use App\SenhaCriptografada;
use App\TemDataCriacao;
use App\TemDataAlteracao;

class Admin
{
    use TemDataCriacao, TemDataAlteracao;

    private string $nome;
    private ?SenhaCriptografada $senhaCripto;

    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    public function setSenhaCriptografada(?SenhaCriptografada $senhaCripto): self
    {
        $this->senhaCripto = $senhaCripto;
        return $this;
    }

    public function nome(): string
    {
        return $this->nome;
    }

    public function senhaCriptografada(): ?SenhaCriptografada
    {
        return $this->senhaCripto;
    }
}