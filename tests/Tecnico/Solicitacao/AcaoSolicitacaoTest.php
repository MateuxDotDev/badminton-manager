<?php

namespace Tests\Tecnico\Solicitacao;

use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Dupla\DuplaRepository;
use App\Tecnico\Solicitacao\AcaoSolicitacao;
use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Tecnico\Tecnico;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\HttpStatus;
use DateTime;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PDO;

class AcaoSolicitacaoTest extends TestCase
{
    private PDO $pdo;
    private UserSession $session;
    private NotificacaoRepository $notificacaoRepo;
    private SolicitacaoConcluidaRepository $concluidaRepo;
    private DuplaRepository $duplaRepo;
    private array $solicitacao;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->session = $this->createMock(UserSession::class);
        $this->notificacaoRepo = $this->createMock(NotificacaoRepository::class);
        $this->concluidaRepo = $this->createMock(SolicitacaoConcluidaRepository::class);
        $this->duplaRepo = $this->createMock(DuplaRepository::class);
        $this->solicitacao = [
            'id' => 1,
            'atleta_origem_id' => 1,
            'atleta_origem_nome' => 'Atleta 1',
            'atleta_origem_sexo' => 'M',
            'atleta_destino_id' => 2,
            'atleta_destino_nome' => 'Atleta 2',
            'atleta_destino_sexo' => 'F',
            'tecnico_origem_id' => 1,
            'tecnico_destino_id' => 2,
            'categoria_id' => 1,
            'categoria_descricao' => 'Categoria 1',
            'competicao_id' => 1,
            'competicao_prazo' => (new DateTime())->modify('+1 day')->format('Y-m-d'),
        ];
    }


    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testSolicitacaoNaoEncontrada(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $this->session->expects($this->never())
            ->method('getTecnico');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Não encontramos solicitação pendente de id 1');
        $this->expectExceptionCode(HttpStatus::NOT_FOUND->value);
        $acaoSolicitacao->rejeitar(1);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testRejeitar(): void
    {

        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')->willReturn([$this->solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->concluidaRepo->expects($this->once())
            ->method('concluirRejeitada')
            ->willReturn(1);

        $this->notificacaoRepo->expects($this->exactly(2))
            ->method('criar')
            ->willReturn(1);

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $acaoSolicitacao->rejeitar(1);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testRejeitarNaoAutorizado(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$this->solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(1));

        $this->concluidaRepo->expects($this->never())
            ->method('concluirRejeitada');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::FORBIDDEN->value);
        $this->expectExceptionMessage('Você não está autorizado a rejeitar essa solicitação');

        $acaoSolicitacao->rejeitar(1);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testRejeitarPrazoPassou(): void
    {
        $solicitacao = [
            ...$this->solicitacao,
            'competicao_prazo' => (new DateTime())->modify('-1 day')->format('Y-m-d'),
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->concluidaRepo->expects($this->never())
            ->method('concluirRejeitada');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('O prazo da competição já passou, duplas não podem mais ser formadas');
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);

        $acaoSolicitacao->rejeitar(1);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testRejeitarPrazoInvalido(): void
    {
        $solicitacao = [
            ...$this->solicitacao,
            'competicao_prazo' => null,
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->concluidaRepo->expects($this->never())
            ->method('concluirRejeitada');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Erro interno: prazo da competição é inválido');
        $this->expectExceptionCode(HttpStatus::INTERNAL_SERVER_ERROR->value);

        $acaoSolicitacao->rejeitar(1);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testCancelar(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$this->solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(1));

        $this->concluidaRepo->expects($this->once())
            ->method('concluirCancelada')
            ->willReturn(1);

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $acaoSolicitacao->cancelar(1);
    }

    /**
     * @throws Exception
     * @throws ValidatorException
     */
    public function testCancelarNaoAutorizado(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$this->solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->concluidaRepo->expects($this->never())
            ->method('concluirCancelada');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::FORBIDDEN->value);
        $this->expectExceptionMessage('Você não tem autorização para cancelar essa solicitação');

        $acaoSolicitacao->cancelar(1);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testAceitar(): void
    {
        $solicitacoesParaCancelar = [
            [
                'id' => 1,
                'atleta_origem_id' => 1,
                'atleta_destino_id' => 3,
                'tecnico_origem_id' => 4,
                'tecnico_destino_id' => 3,
            ],
            [
                'id' => 2,
                'atleta_origem_id' => 2,
                'atleta_destino_id' => 2,
                'tecnico_origem_id' => 2,
                'tecnico_destino_id' => 2,
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->exactly(2))
            ->method('prepare')->willReturn($stmt);
        $stmt->expects($this->exactly(2))
            ->method('execute')->willReturn(true);
        $stmt->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$this->solicitacao], $solicitacoesParaCancelar);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->duplaRepo->expects($this->exactly(2))
            ->method('temDupla')
            ->willReturnOnConsecutiveCalls(false, false);

        $idConcluidaAceita = 1;

        $this->concluidaRepo
            ->expects($this->once())
            ->method('concluirAceita')
            ->willReturn($idConcluidaAceita);

        $this->duplaRepo
            ->expects($this->once())
            ->method('criarDupla');

        $this->concluidaRepo
            ->expects($this->exactly(2))
            ->method('concluirCancelada')
            ->willReturn(1);

        $this->notificacaoRepo
            ->expects($this->exactly(3))
            ->method('criar')
            ->willReturnOnConsecutiveCalls(1, 2, 3);

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $acaoSolicitacao->aceitar(1);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testAceitarNaoAutorizado(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$this->solicitacao]);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(1));

        $this->concluidaRepo->expects($this->never())
            ->method('concluirAceita');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::FORBIDDEN->value);
        $this->expectExceptionMessage('Você não tem autorização para aceitar essa solicitação');

        $acaoSolicitacao->aceitar(1);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testAceitarDestinoJaFormouDupla(): void
    {
        $solicitacoesParaCancelar = [
            [
                'id' => 1,
                'atleta_origem_id' => 1,
                'atleta_destino_id' => 3,
                'tecnico_origem_id' => 4,
                'tecnico_destino_id' => 3,
            ],
            [
                'id' => 2,
                'atleta_origem_id' => 2,
                'atleta_destino_id' => 2,
                'tecnico_origem_id' => 2,
                'tecnico_destino_id' => 2,
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$this->solicitacao], $solicitacoesParaCancelar);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->duplaRepo->expects($this->once())
            ->method('temDupla')
            ->willReturn(true);

        $this->concluidaRepo
            ->expects($this->never())
            ->method('concluirAceita');

        $this->duplaRepo
            ->expects($this->never())
            ->method('criarDupla');

        $this->concluidaRepo
            ->expects($this->never())
            ->method('concluirCancelada');

        $this->notificacaoRepo
            ->expects($this->never())
            ->method('criar');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);
        $this->expectExceptionMessage('O seu atleta Atleta 2 já formou uma dupla mista Categoria 1');

        $acaoSolicitacao->aceitar(1);
    }

    /**
     * @throws ValidatorException
     * @throws Exception
     */
    public function testAceitarRemetenteJaFormouDupla(): void
    {
        $solicitacoesParaCancelar = [
            [
                'id' => 1,
                'atleta_origem_id' => 1,
                'atleta_destino_id' => 3,
                'tecnico_origem_id' => 4,
                'tecnico_destino_id' => 3,
            ],
            [
                'id' => 2,
                'atleta_origem_id' => 2,
                'atleta_destino_id' => 2,
                'tecnico_origem_id' => 2,
                'tecnico_destino_id' => 2,
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$this->solicitacao], $solicitacoesParaCancelar);

        $this->session->expects($this->once())
            ->method('getTecnico')
            ->willReturn((new Tecnico())->setId(2));

        $this->duplaRepo->expects($this->exactly(2))
            ->method('temDupla')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->concluidaRepo
            ->expects($this->never())
            ->method('concluirAceita');

        $this->duplaRepo
            ->expects($this->never())
            ->method('criarDupla');

        $this->concluidaRepo
            ->expects($this->never())
            ->method('concluirCancelada');

        $this->notificacaoRepo
            ->expects($this->never())
            ->method('criar');

        $acaoSolicitacao = new AcaoSolicitacao(
            $this->pdo,
            $this->session,
            new DateTime(),
            $this->notificacaoRepo,
            $this->concluidaRepo,
            $this->duplaRepo,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionCode(HttpStatus::BAD_REQUEST->value);
        $this->expectExceptionMessage('O atleta Atleta 1 já formou uma dupla mista Categoria 1');

        $acaoSolicitacao->aceitar(1);
    }
}
