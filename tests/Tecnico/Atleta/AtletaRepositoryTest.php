<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Tecnico;
use App\Tecnico\Atleta\Sexo;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemServiceInterface;
use DateTime;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class AtletaRepositoryTest extends TestCase
{
    private AtletaRepository $atletaRepository;
    private Atleta $atleta;
    private Tecnico $tecnico;
    private PDO $pdo;
    private PDOStatement $pdoStatement;
    private UploadImagemServiceInterface $uploadImagemService;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->tecnico = (new Tecnico())
            ->setId(1)
            ->setNomeCompleto('Teste Tecnico');

        $this->atleta = (new Atleta())
            ->setTecnico($this->tecnico)
            ->setNomeCompleto('Teste Atleta')
            ->setSexo(Sexo::MASCULINO)
            ->setDataNascimento(new DateTime('2000-01-01'))
            ->setInformacoesAdicionais('Teste Informacoes')
            ->setFoto('Teste Foto');

        $this->pdo = $this->createMock(PDO::class);
        $this->pdoStatement = $this->createMock(PDOStatement::class);
        $this->uploadImagemService = $this->createMock(UploadImagemServiceInterface::class);

        $this->atletaRepository = new AtletaRepository($this->pdo, $this->uploadImagemService);
    }

    /**
     * @throws Exception
     */
    public function testCriarAtleta(): void
    {
        $this->pdo->method('beginTransaction')->willReturn(true);
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdo->method('lastInsertId')->willReturn('1');
        $this->pdo->method('commit')->willReturn(true);

        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn([['id' => 1]]);

        $this->uploadImagemService->expects($this->never())->method('removerImagem');

        $this->assertNull($this->atleta->id());

        $id = $this->atletaRepository->criarAtleta($this->atleta);

        $this->assertSame(1, $id);
    }

    /**
     * @throws Exception
     */
    public function testCriarAtletaThrowsValidatorException(): void
    {
        $this->pdo->method('beginTransaction')->willReturn(true);
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);

        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn([]);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Técnico '{$this->atleta->tecnico()->nomeCompleto()}' não existe");
        $this->expectExceptionCode(HttpStatus::NOT_FOUND->value);

        $this->atletaRepository->criarAtleta($this->atleta);
    }

    public function testCriarAtletaCallsRemoverImagemOnException(): void
    {
        $this->pdo->method('beginTransaction')->willReturn(true);
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdo->method('commit')->willThrowException(new PDOException());

        $this->uploadImagemService->expects($this->once())->method('removerImagem')->with('Teste Foto');

        $this->expectException(Exception::class);

        $this->atletaRepository->criarAtleta($this->atleta);
    }

    public function testGetViaTecnico(): void
    {
        $expectedData = [
            [
                'id' => 1,
                'nome_completo' => 'Teste Atleta',
                'sexo' => Sexo::MASCULINO->value,
                'data_nascimento' => '2000-01-01',
                'informacoes' => 'Teste Informacoes',
                'path_foto' => 'Teste Foto',
                'criado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                'alterado_em' => (new DateTime())->format('Y-m-d H:i:s.u')
            ]
        ];

        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->with(['tecnico_id' => $this->tecnico->id()])->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn($expectedData);

        $atletas = $this->atletaRepository->getViaTecnico($this->tecnico->id());

        $this->assertCount(1, $atletas);

        /** @var Atleta $atleta */
        $atleta = $atletas[0];
        $this->assertSame($expectedData[0]['id'], $atleta->id());
        $this->assertSame($expectedData[0]['nome_completo'], $atleta->nomeCompleto());
        $this->assertSame(Sexo::from($expectedData[0]['sexo']), $atleta->sexo());
        $this->assertSame($expectedData[0]['data_nascimento'], $atleta->dataNascimento()->format('Y-m-d'));
        $this->assertSame($expectedData[0]['informacoes'], $atleta->informacoesAdicionais());
        $this->assertSame($expectedData[0]['path_foto'], $atleta->foto());
        $this->assertSame($expectedData[0]['criado_em'], $atleta->dataCriacao()->format('Y-m-d H:i:s.u'));
        $this->assertSame($expectedData[0]['alterado_em'], $atleta->dataAlteracao()->format('Y-m-d H:i:s.u'));
    }

    public function testGetViaTecnicoThrowsExceptionOnQueryError(): void
    {
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->with(['tecnico_id' => $this->tecnico->id()])
            ->will($this->throwException(new PDOException('Error Message')));

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Error Message');

        $this->atletaRepository->getViaTecnico($this->tecnico->id());
    }

    public function testRemoverAtleta(): void
    {
        $this->atleta->setId(1);

        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->with(['id' => $this->atleta->id()])->willReturn(true);
        $this->pdoStatement->method('rowCount')->willReturn(1);

        $result = $this->atletaRepository->removerAtleta($this->atleta->id());

        $this->assertTrue($result);
    }

    public function testRemoverAtletaReturnsFalseIfNoRowIsDeleted(): void
    {
        $this->atleta->setId(1);

        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->with(['id' => $this->atleta->id()])->willReturn(true);
        $this->pdoStatement->method('rowCount')->willReturn(0);

        $result = $this->atletaRepository->removerAtleta($this->atleta->id());

        $this->assertFalse($result);
    }

    public function testAtualizarAtleta(): void
    {
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('rowCount')->willReturn(1);

        $result = $this->atletaRepository->atualizarAtleta($this->atleta);

        $this->assertTrue($result);
    }

    public function testAtualizarAtletaReturnsFalseIfNoRowIsUpdated(): void
    {
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('rowCount')->willReturn(0);

        $result = $this->atletaRepository->atualizarAtleta($this->atleta);

        $this->assertFalse($result);
    }
}
