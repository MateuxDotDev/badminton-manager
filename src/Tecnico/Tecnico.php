<?php

// TODO testes......

namespace App\Tecnico;

use App\Senha;
use DateTimeImmutable;
use DateTimeInterface;

class Tecnico
{
    private ?int $id = null;
    private string $email;
    private string $nomeCompleto;
    private string $informacoes = '';
    private Clube $clube;
    private ?Senha $senha;
    private ?DateTimeInterface $dataCriacao = null;
    private ?DateTimeInterface $dataAlteracao = null;

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

    public function setSenha(?Senha $senha): Tecnico
    {
        $this->senha = $senha;
        return $this;
    }

    public function setDataCriacao(?DateTimeInterface $data): Tecnico
    {
        $this->dataCriacao = $data;
        return $this;
    }

    public function setDataAlteracao(?DateTimeInterface $data): Tecnico
    {
        $this->dataAlteracao = $data;
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

    public function senha(): ?Senha
    {
        return $this->senha;
    }

    public function dataCriacao(): ?DateTimeInterface
    {
        return $this->dataCriacao;
    }

    public function dataAlteracao(): ?DateTimeInterface
    {
        return $this->dataAlteracao;
    }

    public function __serialize(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'nomeCompleto' => $this->nomeCompleto,
            'informacoes' => $this->informacoes,
            'clube' => serialize($this->clube),
            'dataCriacao' => $this->dataCriacao?->format('Y-m-d H:i:s.u'),
            'dataAlteracao' => $this->dataAlteracao?->format('Y-m-d H:i:s.u'),
        ];
    }

    public function __unserialize(array $a): void {
        $id = $a['id'] === null ? null : (int) $a['id'];

        $clube = unserialize($a['clube']);

        $dataCriacao   = $a['dataCriacao']   == null ? null : DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $a['dataCriacao']);
        $dataAlteracao = $a['dataAlteracao'] == null ? null : DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $a['dataAlteracao']);

        $this
            ->setId($id)
            ->setEmail($a['email'])
            ->setNomeCompleto($a['nomeCompleto'])
            ->setInformacoes($a['informacoes'])
            ->setClube($clube)
            ->setDataCriacao($dataCriacao)
            ->setDataAlteracao($dataAlteracao)
            ;
    }
}