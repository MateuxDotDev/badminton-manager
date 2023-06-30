<?php

namespace Tests\Tecnico\Dupla;

use App\Tecnico\Dupla\Dupla;
use PHPUnit\Framework\TestCase;

class DuplaTest extends TestCase
{
    public function testDupla()
    {
        $row = [
            'id' => 1,
            'idSolicitacao' => 1,
            'criadoEm' => '2021-01-01 00:00:00.000000',
            'categoria' => 'Sub 17',
            'categoriaId' => 1,
            'competicao' => 'Campeonato Mundial',
            'competicaoId' => 1,
            'atletas' => json_encode([
                [
                    'id' => 1,
                    'nome' => 'Atleta 1',
                    'sexo' => 'M',
                    'dataNascimento' => '2000-01-01',
                    'foto' => 'foto.jpg',
                    'informacoes' => 'Informações',
                    'tecnico' => [
                        'id' => 1,
                        'nome' => 'Tecnico 1',
                        'email' => 'tecnico1@mail.com',
                        'informacoes' => 'Informações',
                        'clubeId' => 1,
                        'clube' => 'Clube 1'
                    ]
                ],
                [
                    'id' => 2,
                    'nome' => 'Atleta 2',
                    'sexo' => 'F',
                    'dataNascimento' => '2000-01-01',
                    'foto' => 'foto.jpg',
                    'informacoes' => 'Informações',
                    'tecnico' => [
                        'id' => 2,
                        'nome' => 'Tecnico 2',
                        'email' => 'tecnico2@mail.com',
                        'informacoes' => 'Informações',
                        'clubeId' => 2,
                        'clube' => 'Clube 2'
                    ]
                ]
            ])
        ];

        $dupla = Dupla::fromRow($row);

        $this->assertEquals(1, $dupla->id());
        $this->assertEquals(1, $dupla->idSolicitacao());
        $this->assertEquals('Sub 17', $dupla->categoria()->descricao());
        $this->assertEquals(1, $dupla->categoria()->id());
        $this->assertEquals('Campeonato Mundial', $dupla->competicao()->nome());
        $this->assertEquals(1, $dupla->atleta1()->id());
        $this->assertEquals(2, $dupla->atleta2()->id());
        $this->assertEquals(1, $dupla->atletaFromTecnico(1)->id());
        $this->assertEquals(2, $dupla->other(1)->id());
    }
}
