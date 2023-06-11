<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoDupla;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoDuplaRepository;
use App\Tecnico\Atleta\Sexo;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoDuplaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private AtletaCompeticaoDuplaRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new AtletaCompeticaoDuplaRepository($this->pdo);
    }

    public function testCadastrarAtletaCompeticaoDupla(): void
    {
        $atleta = (new Atleta())->setId(1);
        $competicao = (new Competicao())->setId(2);
        $atletaCompeticao = (new AtletaCompeticao())->setAtleta($atleta)->setCompeticao($competicao)->setInformacao("info");
        $atletaCompeticaoDupla = (new AtletaCompeticaoDupla())->setAtletaCompeticao($atletaCompeticao)->setTipoDupla(Sexo::MASCULINO);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->with([
                'atleta_id' => $atletaCompeticaoDupla->atletaCompeticao()->atleta()->id(),
                'competicao_id' => $atletaCompeticaoDupla->atletaCompeticao()->competicao()->id(),
                'sexo_dupla' => $atletaCompeticaoDupla->tipoDupla()->value
            ])
            ->willReturn(true);

        $this->assertTrue($this->repository->cadastrarAtletaCompeticaoDupla($atletaCompeticaoDupla));
    }
}
