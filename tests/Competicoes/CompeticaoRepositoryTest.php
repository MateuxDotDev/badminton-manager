<?php

namespace Tests\Competicoes;

use App\Competicoes\Competicao;
use App\Competicoes\CompeticaoRepository;
use ArrayIterator;
use \DateTimeImmutable;
use \PDO;
use \PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CompeticaoRepositoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testTodasAsCompeticoes()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $pdo->method('query')
            ->willReturn($stmt);

        $data = [
            ['id' => '1', 'nome' => 'Competicao 1', 'prazo' => '2023-01-01', 'criado_em' => '2022-12-12 01:01:01.123456', 'alterado_em' => '2023-12-12 02:02:03.121233', 'descricao' => 'teste descrição 1'],
            ['id' => '2', 'nome' => 'Competicao 2', 'prazo' => '2023-02-01', 'criado_em' => '2022-11-11 11:11:11.111111', 'alterado_em' => '2022-12-25 12:34:56.789012', 'descricao' => '']
        ];

        $stmt->method('getIterator')
            ->willReturn(new ArrayIterator($data));

        $repo = new CompeticaoRepository($pdo);
        $competicoes = $repo->todasAsCompeticoes();

        $this->assertCount(2, $competicoes);

        $this->assertEquals(1, $competicoes[0]->id());
        $this->assertEquals('Competicao 1', $competicoes[0]->nome());
        $this->assertEquals('2023-01-01', $competicoes[0]->prazo()->format('Y-m-d'));
        $this->assertEquals('teste descrição 1', $competicoes[0]->descricao());

        $this->assertEquals(2, $competicoes[1]->id());
        $this->assertEquals('Competicao 2', $competicoes[1]->nome());
        $this->assertEquals('2023-02-01', $competicoes[1]->prazo()->format('Y-m-d'));
        $this->assertEquals('', $competicoes[1]->descricao());
    }

    /**
     * @throws Exception
     */
    public function testCriarCompeticao()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')
            ->willReturn($stmt);

        $pdo->method('lastInsertId')
            ->willReturn('10');

        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'nome' => 'Teste',
                'prazo' => '2023-01-01',
                'descricao' => 'Competição de teste'
            ]);

        $competicao = (new Competicao)
            ->setNome('Teste')
            ->setDescricao('Competição de teste')
            ->setPrazo(DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-01'));

        $repo = new CompeticaoRepository($pdo);
        $id = $repo->criarCompeticao($competicao);

        $this->assertEquals(10, $id);
        $this->assertEquals(10, $competicao->id());
    }

    /**
     * @throws Exception
     */
    public function testAlterarCompeticao()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')
            ->willReturn($stmt);

        $stmt->method('rowCount')
            ->willReturn(1);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'id' => 10,
                'nome' => 'Teste',
                'prazo' => '2023-01-01',
                'descricao' => 'Descrição atualizada'
            ]);

        $competicao = (new Competicao)
            ->setId(10)
            ->setNome('Teste')
            ->setDescricao('Descrição atualizada')
            ->setPrazo(DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-01'));

        $repo = new CompeticaoRepository($pdo);
        $result = $repo->alterarCompeticao($competicao);

        $this->assertTrue($result);
    }

    /**
     * @throws Exception
     */
    public function testExcluirCompeticao()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 10]);

        $repo = new CompeticaoRepository($pdo);
        $repo->excluirCompeticao(10);
    }

    /**
     * @throws Exception
     */
    public function testCompeticoesAbertas()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $pdo->method('query')
            ->willReturn($stmt);

        $data = [
            ['id' => '1', 'nome' => 'Competicao 1', 'prazo' => '2023-01-01', 'descricao' => 'teste descrição 1'],
            ['id' => '2', 'nome' => 'Competicao 2', 'prazo' => '2023-02-01', 'descricao' => '']
        ];

        $stmt->method('getIterator')
            ->willReturn(new ArrayIterator($data));

        $repo = new CompeticaoRepository($pdo);
        $competicoes = $repo->competicoesAbertas();

        $this->assertCount(2, $competicoes);

        $this->assertEquals(1, $competicoes[0]->id());
        $this->assertEquals('Competicao 1', $competicoes[0]->nome());
        $this->assertEquals('2023-01-01', $competicoes[0]->prazo()->format('Y-m-d'));
        $this->assertEquals('teste descrição 1', $competicoes[0]->descricao());

        $this->assertEquals(2, $competicoes[1]->id());
        $this->assertEquals('Competicao 2', $competicoes[1]->nome());
        $this->assertEquals('2023-02-01', $competicoes[1]->prazo()->format('Y-m-d'));
        $this->assertEquals('', $competicoes[1]->descricao());
    }

    /**
     * @throws Exception
     */
    public function testGetViaId()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')
            ->willReturn($stmt);

        $data = [
            'id' => '1',
            'nome' => 'Competicao 1',
            'descricao' => 'Descrição da Competição 1',
            'prazo' => '2023-01-01',
            'criado_em' => '2022-12-12 01:01:01.123456',
            'alterado_em' => '2023-12-12 02:02:03.121233'
        ];

        $stmt->method('fetchAll')
            ->willReturn([$data]);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 1]);

        $repo = new CompeticaoRepository($pdo);
        $competicao = $repo->getViaId(1);

        $this->assertInstanceOf(Competicao::class, $competicao);
        $this->assertEquals(1, $competicao->id());
        $this->assertEquals('Competicao 1', $competicao->nome());
        $this->assertEquals('Descrição da Competição 1', $competicao->descricao());
        $this->assertEquals('2023-01-01', $competicao->prazo()->format('Y-m-d'));
    }

    /**
     * @throws Exception
     */
    public function testGetViaIdNotFound()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')
            ->willReturn($stmt);

        $stmt->method('fetchAll')
            ->willReturn([]);

        $stmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 999]);

        $repo = new CompeticaoRepository($pdo);
        $competicao = $repo->getViaId(999);

        $this->assertNull($competicao);
    }
}
