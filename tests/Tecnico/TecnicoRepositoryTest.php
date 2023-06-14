<?php

namespace Tests\Tecnico;

use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\SenhaCriptografada;
use App\Util\Http\HttpStatus;
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


    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testGetViaIdReturnsNull()
    {
        $valor = 1;

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['id' => $valor]);

        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $tecnico = $this->tecnicoRepository->getViaId($valor);

        $this->assertNull($tecnico);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCriarTecnico()
    {
        $senha = SenhaCriptografada::existente('hashed_password', 'salt');

        $tecnico = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico');

        $nomeClube = 'Clube A';

        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');

        $this->pdoMock->expects($this->once())
            ->method('commit');

        $stmtSelectClube = $this->createMock(PDOStatement::class);
        $stmtSelectClube->method('execute')->willReturn(true); 
        $stmtSelectClube->method('fetchAll')->willReturn([
            [
                'id' => '1',
                'nome' => 'Clube A'
            ]
        ]);

        $stmtInsertTecnico = $this->createMock(PDOStatement::class);
        $stmtInsertTecnico->method('execute')->willReturn(true);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn(
                $stmtSelectClube,
                $stmtInsertTecnico,
            );

        $this->pdoMock->expects($this->exactly(1))
            ->method('lastInsertId')
            ->willReturn('1');

        try {
            $this->tecnicoRepository->criarTecnico($tecnico, $nomeClube);
            $this->assertEquals(1, $tecnico->id());
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown: ' . $e->getMessage());
        }
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCriarTecnicoComNovoClube()
    {
        $senha = SenhaCriptografada::existente('hashed_password', 'salt');

        $tecnico = (new Tecnico)
            ->setEmail('jane@example.com')
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto('Jane Doe')
            ->setInformacoes('Informações sobre a técnica');

        $nomeClube = 'Clube B';

        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');

        $this->pdoMock->expects($this->once())
            ->method('commit');

        $stmtSelectClube = $this->createMock(PDOStatement::class);
        $stmtSelectClube->method('execute')->willReturn(true);
        $stmtSelectClube->method('fetchAll')->willReturn([]);

        $stmtInsertClube = $this->createMock(PDOStatement::class);
        $stmtInsertClube->method('execute')->willReturn(true);

        $stmtInsertTecnico = $this->createMock(PDOStatement::class);
        $stmtInsertTecnico->method('execute')->willReturn(true);
        
        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturn(
                $stmtSelectClube,
                $stmtInsertClube,
                $stmtInsertTecnico
            );

        $this->pdoMock->expects($this->exactly(2))
            ->method('lastInsertId')
            ->willReturn('1', '2');

        try {
            $this->tecnicoRepository->criarTecnico($tecnico, $nomeClube);
            $this->assertEquals(1, $tecnico->clube()->id());
            $this->assertEquals(2, $tecnico->id());
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testGetViaChaveInvalida()
    {
        $chave = 'invalida';
        $valor = 'teste';

        $repo = $this->tecnicoRepository;

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Chave de técnico '$chave' inválida");
        $this->expectExceptionCode(HttpStatus::UNAUTHORIZED->value);

        $repo->getViaChave($chave, $valor);
    }

    public function testCriarTecnicoException()
    {
        $senha = SenhaCriptografada::existente('hashed_password', 'salt');

        $tecnico = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico');

        $nomeClube = 'Clube A';

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

        $this->tecnicoRepository->criarTecnico($tecnico, $nomeClube);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testGetViaAtleta(): void
    {
        $idAtleta = 1;

        $stmtMock = $this->createMock(PDOStatement::class);
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock
            ->expects($this->once())
            ->method('execute')
            ->with(['atleta' => $idAtleta]);

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

        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$row]);

        $tecnico = $this->tecnicoRepository->getViaAtleta($idAtleta);

        $this->assertEquals($row['id'], $tecnico->id());
    }
}
