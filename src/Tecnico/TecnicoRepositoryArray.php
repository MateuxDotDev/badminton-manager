<?php

namespace App\Tecnico;

class TecnicoRepositoryArray implements TecnicoRepositoryInterface
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

    public function criarTecnico(Tecnico $tecnico, string $nomeClube): void
    {
        $clube = null;
        foreach ($this->tecnicos as $t) {
            if ($t->clube()->nome() == $nomeClube) {
                $clube = $t->clube();
                break;
            }
        }
        $clube ??= (new Clube)->setNome($nomeClube)->setId($this->proximoIdClube++);

        $tecnico->setId($this->proximoIdTecnico++);
        $tecnico->setClube($clube);
        $this->tecnicos[] = $tecnico;
    }
}
