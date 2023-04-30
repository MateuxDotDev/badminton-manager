<?php

// TODO testes......

namespace App\Tecnico;

use DateTimeInterface;

class Tecnico
{
    private ?int $id = null;
    private string $email;
    private string $nomeCompleto;
    private string $informacoes;
    private Clube $clube;
    private ?DateTimeInterface $dataCriacao = null;
    private ?DateTimeInterface $dataAlteracao = null;

    private bool $temSenha = false;

    public function setId(int $id): Tecnico
    {
        $this->id = $id;
        return $this;
    }

    public function setEmail(string $email): Tecnico
    {
        $this->email = $email;
        return $this;
    }

    public function setNomeCompleto(string $nomeCompleto): Tecnico
    {
        $this->nomeCompleto = $nomeCompleto;
        return $this;
    }

    public function setInformacoes(string $informacoes): Tecnico
    {
        $this->informacoes = $informacoes;
        return $this;
    }

    public function setClube(Clube $clube): Tecnico
    {
        $this->clube = $clube;
        return $this;
    }

    public function setDataCriacao(DateTimeInterface $data): Tecnico
    {
        $this->dataCriacao = $data;
        return $this;
    }

    public function setDataAlteracao(DateTimeInterface $data): Tecnico
    {
        $this->dataAlteracao = $data;
        return $this;
    }

    public function settemSenha(bool $temSenha): Tecnico
    {
        $this->temSenha = $temSenha;
        return $this;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function nomeCompleto(): string
    {
        return $this->nomeCompleto;
    }

    public function informacoes(): string
    {
        return $this->informacoes;
    }

    public function clube(): Clube
    {
        return $this->clube;
    }

    public function dataCriacao(): ?DateTimeInterface
    {
        return $this->dataCriacao;
    }

    public function dataAlteracao(): ?DateTimeInterface
    {
        return $this->dataAlteracao;
    }

    public function temSenha(): bool
    {
        return $this->temSenha;
    }

}