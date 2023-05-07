<?php

namespace Tests\Tecnico;

use App\Tecnico\Clube;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepository;
use App\Util\General\SenhaCriptografada;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TecnicoRepositoryTest extends TestCase
{
    private MockObject|PDO $pdoMock;
    private TecnicoRepository $tecnicoRepository;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->tecnicoRepository = new TecnicoRepository($this->pdoMock);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testGetViaEmail()
    {
        $valor = 'john@example.com';

        $row = [
            'id' => 1,
            'email' => 'john@example.com',
            'nome_completo' => 'John Doe',
            'informacoes' => 'Informações sobre o técnico',
            'hash_senha' => 'hashed_password',
            'salt_senha' => 'salt',
            'clube_id' => 1,
            'clube_nome' => 'Clube A',
            'clube_criado_em' => '2021-01-01 00:00:00.000000',
            'criado_em' => '2021-01-01 00:00:00.000000',
            'alterado_em' => '2021-01-01 00:00:00.000000'
        ];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['email' => $valor]);

        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $tecnico = $this->tecnicoRepository->getViaEmail($valor);

        $this->assertInstanceOf(Tecnico::class, $tecnico);
        $this->assertEquals($row['email'], $tecnico->email());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testGetViaId()
    {
        $valor = 1;

        $row = [
            'id' => 1,
            'email' => 'john@example.com',
            'nome_completo' => 'John Doe',
            'informacoes' => 'Informações sobre o técnico',
            'hash_senha' => 'hashed_password',
            'salt_senha' => 'salt',
            'clube_id' => 1,
            'clube_nome' => 'Clube A',
            'clube_criado_em' => '2021-01-01 00:00:00.000000',
            'criado_em' => '2021-01-01 00:00:00.000000',
            'alterado_em' => '2021-01-01 00:00:00.000000'
        ];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['id' => $valor]);

        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $tecnico = $this->tecnicoRepository->getViaId($valor);

        $this->assertInstanceOf(Tecnico::class, $tecnico);
        $this->assertEquals($row['id'], $tecnico->id());
    }

    public function testCriarTecnico()
    {
        $clube = (new Clube)
            ->setId(1)
            ->setNome('Clube A');
        $senha = SenhaCriptografada::existente('hashed_password', 'salt');

        $tecnico = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico')
            ->setClube($clube);

        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');

        $this->pdoMock->expects($this->once())
            ->method('commit');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function () {
                $stmtMock = $this->createMock(PDOStatement::class);
                $stmtMock->expects($this->any())
                    ->method('execute')
                    ->willReturn(true);
                return $stmtMock;
            });

        $this->pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        try {
            $this->tecnicoRepository->criarTecnico($tecnico);
            $this->assertEquals(1, $tecnico->id());
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown: ' . $e->getMessage());
        }
    }

    public function testCriarTecnicoException()
    {
        $clube = (new Clube)
            ->setId(1)
            ->setNome('Clube A');
        $senha = SenhaCriptografada::existente('hashed_password', 'salt');

        $tecnico = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico')
            ->setClube($clube);

        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');

        $this->pdoMock->expects($this->never())
            ->method('commit');

        $this->pdoMock->expects($this->once())
            ->method('rollback');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new Exception('Erro ao preparar a query'));

        $this->expectException(Exception::class);

        $this->tecnicoRepository->criarTecnico($tecnico);
    }
}
