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
}
