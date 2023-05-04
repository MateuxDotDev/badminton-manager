<?php

namespace App\Tecnico;

use App\SenhaCriptografada;
use App\TemDataAlteracao;
use App\TemDataCriacao;
use App\Util\Dates;

class Tecnico
{
    use TemDataCriacao, TemDataAlteracao;

    private ?int $id = null;
    private string $email;
    private string $nomeCompleto;
    private string $informacoes = '';
    private Clube $clube;
    private ?SenhaCriptografada $senhaCriptografada = null;

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

    public function setSenhaCriptografada(?SenhaCriptografada $senhaCriptografada): Tecnico
    {
        $this->senhaCriptografada = $senhaCriptografada;
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

    public function senhaCriptografada(): ?SenhaCriptografada
    {
        return $this->senhaCriptografada;
    }

    public function __serialize(): array
    {
        // Não inclui senhaCriptografada
        return [
            'id' => $this->id,
            'email' => $this->email,
            'nomeCompleto' => $this->nomeCompleto,
            'informacoes' => $this->informacoes,
            'clube' => serialize($this->clube),
            'dataCriacao' => Dates::formatMicro($this->dataCriacao),
            'dataAlteracao' => Dates::formatMicro($this->dataAlteracao),
        ];
    }

    public function __unserialize(array $a): void {
        $id = $a['id'] === null ? null : (int) $a['id'];

        $clube = unserialize($a['clube']);

        $dataCriacao = Dates::parseMicro($a['dataCriacao']);
        $dataAlteracao = Dates::parseMicro($a['dataAlteracao']);

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
