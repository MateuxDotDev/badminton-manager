<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\EnviarSolicitacaoDTO;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Util\Exceptions\ValidatorException;
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
    public function testGetEnvolvendo(): void
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

        $solicitacao = $repo->getEnvolvendo(2, 3, 4, 5);

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
}
