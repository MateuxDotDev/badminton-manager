<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Tecnico;
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
                'a_id' => 1,
                'a_nome_completo' => 'Teste Atleta',
                'a_sexo' => Sexo::MASCULINO->value,
                'a_data_nascimento' => '2000-01-01',
                'a_informacoes' => 'Teste Informacoes',
                'a_path_foto' => 'Teste Foto',
                'a_criado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                'a_alterado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                't_id' => 1,
                't_nome_completo' => 'Márcio Medeiros',
                't_email' => 'marcio@mail.com',
                't_criado_em' => '2023-02-02 12:12:12.012345',
                't_informacoes' => '',
                't_alterado_em' => '2023-12-02 12:12:12.012345',
                'c_id' => 1,
                'c_nome' => 'Clube X',
                'c_criado_em' => '2023-02-02 12:12:12.012345'
            ]
        ];

        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->with([0 => $this->tecnico->id()])->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn($expectedData);

        $atletas = $this->atletaRepository->getViaTecnico($this->tecnico->id());

        $this->assertCount(1, $atletas);

        /** @var Atleta $atleta */
        $atleta = $atletas[0];
        $this->assertSame($expectedData[0]['a_id'], $atleta->id());
        $this->assertSame($expectedData[0]['a_nome_completo'], $atleta->nomeCompleto());
        $this->assertSame(Sexo::from($expectedData[0]['a_sexo']), $atleta->sexo());
        $this->assertSame($expectedData[0]['a_data_nascimento'], $atleta->dataNascimento()->format('Y-m-d'));
        $this->assertSame($expectedData[0]['a_informacoes'], $atleta->informacoesAdicionais());
        $this->assertSame($expectedData[0]['a_path_foto'], $atleta->foto());
        $this->assertSame($expectedData[0]['a_criado_em'], $atleta->dataCriacao()->format('Y-m-d H:i:s.u'));
        $this->assertSame($expectedData[0]['a_alterado_em'], $atleta->dataAlteracao()->format('Y-m-d H:i:s.u'));
    }

    public function testGetViaTecnicoThrowsExceptionOnQueryError(): void
    {
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->with([0 => $this->tecnico->id()])
            ->will($this->throwException(new PDOException('Error Message')));

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Error Message');

        $this->atletaRepository->getViaTecnico($this->tecnico->id());
    }

    /**
     * @throws Exception
     */
    public function testGetAtletaViaId(): void
    {
        $expectedData = [
            'a_id' => 1,
            'a_nome_completo' => 'Teste Atleta',
            'a_sexo' => Sexo::MASCULINO->value,
            'a_data_nascimento' => '2000-01-01',
            'a_informacoes' => 'Teste Informacoes',
            'a_path_foto' => 'Teste Foto',
            'a_criado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
            'a_alterado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
            't_id' => 1,
            't_nome_completo' => 'Márcio Medeiros',
            't_email' => 'marcio@mail.com',
            't_informacoes' => '',
            't_criado_em' => '2023-02-02 12:12:12.012345',
            't_alterado_em' => '2023-12-02 12:12:12.012345',
            'c_id' => 1,
            'c_nome' => 'Clube X',
            'c_criado_em' => '2023-02-02 12:12:12.012345'
        ];

        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn([$expectedData]);

        $atleta = $this->atletaRepository->getViaId(1);

        $this->assertSame($expectedData['a_id'], $atleta->id());
        $this->assertSame($expectedData['a_nome_completo'], $atleta->nomeCompleto());
        $this->assertSame(Sexo::from($expectedData['a_sexo']), $atleta->sexo());
        $this->assertSame($expectedData['a_data_nascimento'], $atleta->dataNascimento()->format('Y-m-d'));
        $this->assertSame($expectedData['a_informacoes'], $atleta->informacoesAdicionais());
        $this->assertSame($expectedData['a_path_foto'], $atleta->foto());
        $this->assertSame($expectedData['a_criado_em'], $atleta->dataCriacao()->format('Y-m-d H:i:s.u'));
        $this->assertSame($expectedData['a_alterado_em'], $atleta->dataAlteracao()->format('Y-m-d H:i:s.u'));
    }

    public function testGetAtletaViaIdReturnsNullOnNoResults(): void
    {
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn([]);

        $atleta = $this->atletaRepository->getViaId(1);

        $this->assertNull($atleta);
    }

    public function testGetAtletaViaIdThrowsExceptionOnQueryError(): void
    {
        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')
            ->will($this->throwException(new PDOException('Error Message')));

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Error Message');

        $this->atletaRepository->getViaId(1);
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

    /**
     * @throws Exception
     */
    public function testGetViaIds(): void
    {
        $rows = [
            [
                'a_id' => 1,
                'a_nome_completo' => 'Teste Atleta',
                'a_sexo' => Sexo::MASCULINO->value,
                'a_data_nascimento' => '2000-01-01',
                'a_informacoes' => 'Teste Informacoes',
                'a_path_foto' => 'Teste Foto',
                'a_criado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                'a_alterado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                't_id' => 1,
                't_nome_completo' => 'Márcio Medeiros',
                't_email' => 'marcio@mail.com',
                't_informacoes' => '',
                't_criado_em' => '2023-02-02 12:12:12.012345',
                't_alterado_em' => '2023-12-02 12:12:12.012345',
                'c_id' => 1,
                'c_nome' => 'Clube X',
                'c_criado_em' => '2023-02-02 12:12:12.012345'
            ],
            [
                'a_id' => 2,
                'a_nome_completo' => 'Atleta 2',
                'a_sexo' => Sexo::FEMININO->value,
                'a_data_nascimento' => '2001-02-02',
                'a_informacoes' => 'Informações Atleta 2',
                'a_path_foto' => 'Foto Atleta 2',
                'a_criado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                'a_alterado_em' => (new DateTime())->format('Y-m-d H:i:s.u'),
                't_id' => 2,
                't_nome_completo' => 'Patrícia Silva',
                't_email' => 'patricia@mail.com',
                't_informacoes' => 'Informações Treinador 2',
                't_criado_em' => '2023-02-02 12:12:12.012345',
                't_alterado_em' => '2023-12-02 12:12:12.012345',
                'c_id' => 2,
                'c_nome' => 'Clube Y',
                'c_criado_em' => '2023-02-02 12:12:12.012345'
            ]
        ];

        $this->pdo->method('prepare')->willReturn($this->pdoStatement);
        $this->pdoStatement->method('execute')->willReturn(true);
        $this->pdoStatement->method('fetchAll')->willReturn($rows);

        $atletas = $this->atletaRepository->getViaIds([1, 2]);

        for ($i = 0; $i < count($atletas) - 1; $i++) {
            $atleta = $atletas[$i];
            $row = $rows[$i];

            $this->assertSame($row['a_id'], $atleta->id());
            $this->assertSame($row['a_nome_completo'], $atleta->nomeCompleto());
            $this->assertSame(Sexo::from($row['a_sexo']), $atleta->sexo());
            $this->assertSame($row['a_data_nascimento'], $atleta->dataNascimento()->format('Y-m-d'));
            $this->assertSame($row['a_informacoes'], $atleta->informacoesAdicionais());
            $this->assertSame($row['a_path_foto'], $atleta->foto());
            $this->assertSame($row['a_criado_em'], $atleta->dataCriacao()->format('Y-m-d H:i:s.u'));
            $this->assertSame($row['a_alterado_em'], $atleta->dataAlteracao()->format('Y-m-d H:i:s.u'));
        }
    }

    /**
     * @throws Exception
     */
    public function testGetViaIdsEmpty(): void
    {
        $atletas = $this->atletaRepository->getViaIds([]);

        $this->assertEmpty($atletas);
    }
}
