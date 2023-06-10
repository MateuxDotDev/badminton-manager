<?php

namespace App\Tecnico\Atleta;

use App\Tecnico\Tecnico;
use App\Util\General\Dates;
use App\Util\Traits\TemDataAlteracao;
use App\Util\Traits\TemDataCriacao;
use App\Util\Traits\TemId;
use DateTime;
use DateTimeInterface;

class Atleta
{
    use TemDataCriacao, TemDataAlteracao, TemId;

    private Tecnico $tecnico;
    private string $nomeCompleto;
    private Sexo $sexo;
    private DateTimeInterface $dataNascimento;
    private string $informacoesAdicionais = '';
    private string $foto = '';

    public function setTecnico(Tecnico $tecnico): self
    {
        $this->tecnico = $tecnico;
        return $this;
    }

    public function setNomeCompleto(string $nomeCompleto): self
    {
        $this->nomeCompleto = $nomeCompleto;
        return $this;
    }

    public function setSexo(Sexo $sexo): self
    {
        $this->sexo = $sexo;
        return $this;
    }

    public function setDataNascimento(DateTimeInterface $dataNascimento): self
    {
        $this->dataNascimento = $dataNascimento;
        return $this;
    }

    public function setInformacoesAdicionais(string $informacoesAdicionais): self
    {
        $this->informacoesAdicionais = $informacoesAdicionais;
        return $this;
    }

    public function setFoto(string $foto): self
    {
        $this->foto = $foto;
        return $this;
    }

    public function tecnico(): Tecnico
    {
        return $this->tecnico;
    }

    public function nomeCompleto(): string
    {
        return $this->nomeCompleto;
    }

    public function sexo(): Sexo
    {
        return $this->sexo;
    }

    public function dataNascimento(): DateTimeInterface
    {
        return $this->dataNascimento;
    }

    public function informacoesAdicionais(): string
    {
        return $this->informacoesAdicionais;
    }

    public function foto(): string
    {
        return $this->foto;
    }

    public function idade(): int
    {
        return Dates::age($this->dataNascimento());
    }

    public function toJson(): array
    {
        return [
            'id' => $this->id(),
            'nomeCompleto' => $this->nomeCompleto(),
            'sexo' => $this->sexo()->toString(),
            'dataNascimento' => $this->dataNascimento()->format('d/m/Y'),
            'informacoesAdicionais' => $this->informacoesAdicionais(),
            'foto' => $this->foto(),
            'idade' => $this->idade(),
            'dataCriacao' => Dates::formatBr($this->dataCriacao()),
            'dataAlteracao' => Dates::formatBr($this->dataAlteracao())
        ];
    }
}

