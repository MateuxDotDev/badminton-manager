<?php

namespace App\Tecnico\Atleta;

use App\Tecnico\Tecnico;
use App\Util\General\Dates;
use App\Util\Traits\TemDataAlteracao;
use App\Util\Traits\TemDataCriacao;
use DateTime;
use DateTimeInterface;

class Atleta
{
    use TemDataCriacao, TemDataAlteracao;

    private ?int $id = null;
    private Tecnico $tecnico;
    private string $nomeCompleto;
    private Sexo $sexo;
    private DateTimeInterface $dataNascimento;
    private string $informacoesAdicionais = '';
    private string $foto = '';

    public function setId(int $id): Atleta
    {
        $this->id = $id;
        return $this;
    }

    public function setTecnico(Tecnico $tecnico): Atleta
    {
        $this->tecnico = $tecnico;
        return $this;
    }

    public function setNomeCompleto(string $nomeCompleto): Atleta
    {
        $this->nomeCompleto = $nomeCompleto;
        return $this;
    }

    public function setSexo(Sexo $sexo): Atleta
    {
        $this->sexo = $sexo;
        return $this;
    }

    public function setDataNascimento(DateTimeInterface $dataNascimento): Atleta
    {
        $this->dataNascimento = $dataNascimento;
        return $this;
    }

    public function setInformacoesAdicionais(string $informacoesAdicionais): Atleta
    {
        $this->informacoesAdicionais = $informacoesAdicionais;
        return $this;
    }

    public function setFoto(string $foto): Atleta
    {
        $this->foto = $foto;
        return $this;
    }

    public function id(): ?int
    {
        return $this->id;
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

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'tecnico' => serialize($this->tecnico),
            'nomeCompleto' => $this->nomeCompleto,
            'sexo' => $this->sexo->value,
            'dataNascimento' => $this->dataNascimento->format('Y-m-d'),
            'informacoesAdicionais' => $this->informacoesAdicionais,
            'foto' => $this->foto,
            'criadoEm' => Dates::formatMicro($this->dataCriacao),
            'alteradoEm' => Dates::formatMicro($this->dataAlteracao),
        ];
    }

    public function __unserialize(array $data): void
    {
        $tecnico = unserialize($data['tecnico']);
        $dataNascimento = DateTime::createFromFormat('Y-m-d', $data['dataNascimento']);
        $dataCriacao = Dates::parseMicro($data['criadoEm']);
        $dataAlteracao = Dates::parseMicro($data['alteradoEm']);

        $this
            ->setId($data['id'])
            ->setTecnico($tecnico)
            ->setNomeCompleto($data['nomeCompleto'])
            ->setSexo(Sexo::from($data['sexo']))
            ->setDataNascimento($dataNascimento)
            ->setInformacoesAdicionais($data['informacoesAdicionais'])
            ->setFoto($data['foto'])
            ->setDataCriacao($dataCriacao)
            ->setDataAlteracao($dataAlteracao);
    }
}

