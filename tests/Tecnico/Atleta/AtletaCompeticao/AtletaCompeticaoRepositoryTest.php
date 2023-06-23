<?php

namespace Tests\Tecnico\Atleta\AtletaCompeticao;

use App\Categorias\Categoria;
use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticao;
use App\Tecnico\Atleta\AtletaCompeticao\AtletaCompeticaoRepository;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Tecnico;
use App\Util\General\Dates;
use ArrayIterator;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AtletaCompeticaoRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private AtletaCompeticaoRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repository = new AtletaCompeticaoRepository($this->pdo);
    }

    public function testGetAtletasForaCompeticao(): void
    {
        $linha = [
            'id' => 1,
            'nome_completo' => 'Nome Teste',
            'sexo' => 'M',
            'data_nascimento' => '2023-01-01',
            'informacoes' => 'Informações Teste',
            'path_foto' => 'path/teste.jpg',
            'criado_em' => '2023-01-01 00:00:00.000000',
            'alterado_em' => '2023-01-01 00:00:00.000000',
        ];

        $this->pdo->expects($this->once())
            ->method('query')
            ->willReturn($this->stmt);

        $this->stmt->method('getIterator')
            ->willReturn(new ArrayIterator([$linha]));

        $atletas = $this->repository->getAtletasForaCompeticao(1, 1);

        $this->assertIsArray($atletas);
        $this->assertInstanceOf(Atleta::class, $atletas[0]);
    }

    public function testIncluirAtletaCompeticao(): void
    {
        $tecnico = (new Tecnico())
            ->setId(1);
        $sexo = Sexo::MASCULINO;
        $dataNascimento = Dates::parseMicro('2023-01-01 00:00:00.000000');

        $atleta = (new Atleta())
            ->setId(1)
            ->setTecnico($tecnico)
            ->setNomeCompleto('Nome Atleta')
            ->setSexo($sexo)
            ->setDataNascimento($dataNascimento)
            ->setInformacoesAdicionais('Informações Adicionais')
            ->setFoto('foto.jpg');

        $categorias = [
            new Categoria(1, 'Categoria 1', 10, 20),
            new Categoria(2, 'Categoria 2', 15, 30)
        ];
        $sexoDupla = [Sexo::MASCULINO, Sexo::FEMININO];

        $atletaCompeticao = (new AtletaCompeticao())
            ->setAtleta($atleta)
            ->addCategoria(...$categorias)
            ->addSexoDupla(...$sexoDupla)
            ->setInformacao('Informações')
            ->setCompeticao((new Competicao())->setId(1));

        $this->pdo->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt->method('execute')
            ->willReturn(true);

        $result = $this->repository->incluirAtletaCompeticao($atletaCompeticao);

        $this->assertTrue($result);
    }

    public function testGet(): void
    {
        $atleta = (new Atleta())->setId(1);
        $competicao = (new Competicao())->setId(1);

        $row = [
            'informacoes' => 'Informações Teste',
            'criado_em' => '2023-01-01 00:00:00.000000',
            'alterado_em' => '2023-01-01 00:00:00.000000',
            'sexo_dupla' => json_encode(['M', 'F']),
            'categorias' => json_encode([
                ['id' => 1, 'descricao' => 'Categoria 1', 'idade_maior_que' => 10, 'idade_menor_que' => 20],
                ['id' => 2, 'descricao' => 'Categoria 2', 'idade_maior_que' => 15, 'idade_menor_que' => 30]
            ]),
        ];

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->with([
                'atleta_id' => $atleta->id(),
                'competicao_id' => $competicao->id()
            ])
            ->willReturn(true);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $atletaCompeticao = $this->repository->get($atleta, $competicao);

        $this->assertInstanceOf(AtletaCompeticao::class, $atletaCompeticao);
    }

    public function testGetReturnsNull(): void
    {
        $atleta = (new Atleta())->setId(1);
        $competicao = (new Competicao())->setId(1);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->with([
                'atleta_id' => $atleta->id(),
                'competicao_id' => $competicao->id()
            ])
            ->willReturn(true);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $atletaCompeticao = $this->repository->get($atleta, $competicao);

        $this->assertNull($atletaCompeticao);
    }

    public function testViaId(): void
    {
        $atleta = (new Atleta())->setId(1);
        $competicao = (new Competicao())->setId(1);

        $row = [
            'sexo' => 'M',
            'sexo_duplas' => json_encode(['M', 'F']),
            'categorias' => json_encode([1, 2]),
        ];

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->with([
                'atleta_id' => $atleta->id(),
                'competicao_id' => $competicao->id()
            ])
            ->willReturn(true);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $atleta = $this->repository->getViaId($atleta->id(), $competicao->id());

        $this->assertIsArray($atleta);
        $this->assertSame(Sexo::MASCULINO, $atleta['sexo']);
        $this->assertSame([Sexo::MASCULINO, Sexo::FEMININO], $atleta['sexoDuplas']);
        $this->assertSame([1, 2], $atleta['categorias']);
    }


    public function testViaIdNull(): void
    {
        $atleta = (new Atleta())->setId(1);
        $competicao = (new Competicao())->setId(1);

        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->with([
                'atleta_id' => $atleta->id(),
                'competicao_id' => $competicao->id()
            ])
            ->willReturn(true);

        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $atleta = $this->repository->getViaId($atleta->id(), $competicao->id());

        $this->assertNull($atleta);
    }
}
