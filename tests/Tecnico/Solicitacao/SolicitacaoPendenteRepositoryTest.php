<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\EnviarSolicitacaoDTO;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\Http\HttpStatus;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class SolicitacaoPendenteRepositoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testgetViaIds(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => '1',
                    'competicao_id' => '2',
                    'atleta_origem_id' => '3',
                    'atleta_destino_id' => '4',
                    'categoria_id' => '5',
                    'criado_em' => '2023-06-15 13:45:30.123456',
                    'alterado_em' => '2023-06-15 13:45:30.123456',
                    'informacoes' => 'Teste',
                ]
            ]);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $solicitacao = $repo->getViaIds(2, 3, 4, 5);

        $this->assertSame(1, $solicitacao->id);
        $this->assertSame(2, $solicitacao->idCompeticao);
        $this->assertSame(3, $solicitacao->idAtletaRemetente);
        $this->assertSame(4, $solicitacao->idAtletaDestinatario);
        $this->assertSame(5, $solicitacao->idCategoria);
        $this->assertSame('Teste', $solicitacao->informacoes);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testEnviar(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $pdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        $repo = new SolicitacaoPendenteRepository($pdo);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => 2,
            'atletaRemetente' => 3,
            'atletaDestinatario' => 4,
            'categoria' => 5,
            'informacoes' => 'Teste',
        ]);
        $id = $repo->enviar($dto);

        $this->assertSame(1, $id);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testgetViaIdsMesmosAtletasECategoria(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => '1',
                    'competicao_id' => '2',
                    'atleta_origem_id' => '3',
                    'atleta_destino_id' => '4',
                    'categoria_id' => '5',
                    'criado_em' => '2023-06-15 13:45:30.123456',
                    'alterado_em' => '2023-06-15 13:45:30.123456',
                    'informacoes' => 'Teste',
                ],
                [
                    'id' => '1',
                    'competicao_id' => '2',
                    'atleta_origem_id' => '3',
                    'atleta_destino_id' => '4',
                    'categoria_id' => '5',
                    'criado_em' => '2023-06-15 13:45:30.123456',
                    'alterado_em' => '2023-06-15 13:45:30.123456',
                    'informacoes' => 'Teste',
                ],
            ]);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Mais de uma solicitação envolvendo os mesmos atleta e a mesma categoria dentro da mesma competição.');
        $repo->getViaIds(2, 3, 4, 5);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testgetViaIdsRetornaNull(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $solicitacao = $repo->getViaIds(2, 3, 4, 5);

        $this->assertNull($solicitacao);
    }
    /**
     * @throws Exception
     */
    public function testGetViaTecnicoReturnsSolicitacoes(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                'tecnico_id' => 1,
            ])
            ->willReturn(true);

        $stmt->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => '1',
                    'competicao_id' => '2',
                    'atleta_origem_id' => '3',
                    'atleta_destino_id' => '4',
                    'informacoes' => 'Teste 1',
                    'categoria_id' => '5',
                    'criado_em' => '2023-06-15 13:45:30.123456',
                    'alterado_em' => '2023-06-15 13:45:30.123456',
                ],
                [
                    'id' => '2',
                    'competicao_id' => '2',
                    'atleta_origem_id' => '3',
                    'atleta_destino_id' => '4',
                    'informacoes' => 'Teste 2',
                    'categoria_id' => '5',
                    'criado_em' => '2023-06-15 13:45:30.123456',
                    'alterado_em' => '2023-06-15 13:45:30.123456',
                ],
                false
            );

        $repo = new SolicitacaoPendenteRepository($pdo);

        $solicitacoes = $repo->getViaTecnico(1);

        $this->assertCount(2, $solicitacoes);

        $this->assertSame(1, $solicitacoes[0]->id);
        $this->assertSame('Teste 1', $solicitacoes[0]->informacoes);

        $this->assertSame(2, $solicitacoes[1]->id);
        $this->assertSame('Teste 2', $solicitacoes[1]->informacoes);
    }

    /**
     * @throws Exception
     */
    public function testGetViaTecnicoReturnsEmptyWhenNoMatches(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $solicitacoes = $repo->getViaTecnico(1);

        $this->assertEmpty($solicitacoes);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testgetViaId(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => '1',
                    'competicao_id' => '2',
                    'atleta_origem_id' => '3',
                    'atleta_destino_id' => '4',
                    'categoria_id' => '5',
                    'criado_em' => '2023-06-15 13:45:30.123456',
                    'alterado_em' => '2023-06-15 13:45:30.123456',
                    'informacoes' => 'Teste',
                ]
            ]);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $solicitacao = $repo->getViaId(1);

        $this->assertSame(1, $solicitacao->id);
        $this->assertSame(2, $solicitacao->idCompeticao);
        $this->assertSame(3, $solicitacao->idAtletaRemetente);
        $this->assertSame(4, $solicitacao->idAtletaDestinatario);
        $this->assertSame(5, $solicitacao->idCategoria);
        $this->assertSame('Teste', $solicitacao->informacoes);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testgetViaIdInvalida(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([[], []]);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Mais de uma solicitação envolvendo os mesmos atleta e a mesma categoria dentro da mesma competição.');
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);
        $repo->getViaId(1);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testgetViaIdNull(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $repo = new SolicitacaoPendenteRepository($pdo);

        $solicitacao = $repo->getViaId(1);

        $this->assertNull($solicitacao);
    }
}
