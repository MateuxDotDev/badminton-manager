<?php

namespace App\Tecnico;

class TecnicoRepositoryArray
implements TecnicoRepositoryInterface
{
    private array $tecnicos = [];
    private int $proximoIdTecnico = 1;
    private int $proximoIdClube = 1;

    public function getViaEmail(string $email): ?Tecnico
    {
        foreach ($this->tecnicos as $tecnico) {
            if ($tecnico->email() == $email) {
                return $tecnico;
            }
        }
        return null;
    }

    public function getViaId(int $id): ?Tecnico
    {
        foreach ($this->tecnicos as $tecnico) {
            if ($tecnico->id() == $id) {
                return $tecnico;
            }
        }
        return null;
    }

    public function criarTecnico(Tecnico $tecnico): void
    {
        $tecnico->setId($this->proximoIdTecnico++);
        if ($tecnico->clube()->id() == null) {
            $tecnico->clube()->setId($this->proximoIdClube++);
        }
        $this->tecnicos[] = $tecnico;
    }
}
