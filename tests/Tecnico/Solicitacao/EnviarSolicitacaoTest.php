<?php

namespace Tests\Tecnico\Solicitacao;

use App\Competicoes\Competicao;
use App\Competicoes\CompeticaoRepository;
use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Dupla\DuplaRepository;
use App\Tecnico\Solicitacao\EnviarSolicitacao;
use App\Tecnico\Solicitacao\EnviarSolicitacaoDTO;
use App\Tecnico\Solicitacao\SolicitacaoPendente;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Tecnico\Tecnico;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Mail\Service\MailService;
use DateTime;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class EnviarSolicitacaoTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private UserSession $session;
    private CompeticaoRepository $competicaoRepository;
    private SolicitacaoPendenteRepository $solicitacaoPendenteRepository;
    private NotificacaoRepository $notificacaoRepository;
    private DuplaRepository $duplaRepository;
    private MailService $mailService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->session = $this->createMock(UserSession::class);
        $this->competicaoRepository = $this->createMock(CompeticaoRepository::class);
        $this->solicitacaoPendenteRepository = $this->createMock(SolicitacaoPendenteRepository::class);
        $this->notificacaoRepository = $this->createMock(NotificacaoRepository::class);
        $this->duplaRepository = $this->createMock(DuplaRepository::class);
        $this->mailService = $this->createMock(MailService::class);
    }

    /**
     * @throws ValidatorException
     */
    public function testInvokeSuccessfully(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->competicaoRepository
            ->method('getViaId')
            ->willReturn($competicao);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->method('execute')
            ->willReturn(true);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $this->stmt
            ->method('fetchColumn')
            ->willReturn('categoria para o teste funcionar');

        $this->solicitacaoPendenteRepository
            ->method('getViaIds')
            ->willReturn(null);

        $this->solicitacaoPendenteRepository
            ->expects($this->once())
            ->method('enviar')
            ->willReturn(1);

        $this->notificacaoRepository->expects($this->exactly(2))
            ->method('criar')
            ->willReturnOnConsecutiveCalls(1, 2);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testCompetitionNotFound(): void
    {
        $this->competicaoRepository->method('getViaId')->willReturn(null);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => 1,
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Competição não encontrada');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testCompetitionDeadlinePassed(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('-1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('O prazo da competição já passou');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testSenderNotFound(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 999,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([]);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Atleta não encontrado (id 999)');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testRecipientNotFound(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 999,
            'categoria' => 1,
        ]);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], []);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Atleta não encontrado (id 999)');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testUnauthorizedCoach(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 999,
            'categoria' => 1,
        ]);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturn([$remetenteGet]);

        $tecnico = (new Tecnico())
            ->setId(2);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Técnico não autorizado a enviar solicitações envolvendo o atleta de ID 1');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testAlreadySent(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->competicaoRepository
            ->method('getViaId')
            ->willReturn($competicao);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->method('execute')
            ->willReturn(true);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $solicitacaoPendente = new SolicitacaoPendente(
            1,
            new DateTime(),
            new DateTime(),
            1,
            1,
            1,
            1,
            'test'
        );

        $this->solicitacaoPendenteRepository
            ->method('getViaIds')
            ->willReturn($solicitacaoPendente);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Essa solicitação já foi enviada');

        $enviarSolicitacao($dto);
    }
    /**
     * @throws ValidatorException
     */
    public function testAthletesCategoriesDoNotMatch(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 3,
        ]);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Atletas não são compatíveis: Não jogam ambos na categoria selecionada');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testDoublesSexesDoNotMatch(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "M"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Atletas não são compatíveis: Um dos atletas não precisa formar dupla mista');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testSelfRequest(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $this->competicaoRepository->method('getViaId')->willReturn($competicao);

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 1,
            'categoria' => 1,
        ]);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $remetenteGet = [
            'sexo' => 'M',
            'tecnico_id' => 1,
            'nome_completo' => 'Atleta 1',
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Atletas não são compatíveis: Ambos têm o mesmo técnico');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testCategoriaInvalida(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->competicaoRepository
            ->method('getViaId')
            ->willReturn($competicao);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->method('execute')
            ->willReturn(true);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $this->stmt
            ->method('fetchColumn')
            ->willReturn(false);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Categoria inválida');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testAtletaDestJaFormouDupla(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->competicaoRepository
            ->method('getViaId')
            ->willReturn($competicao);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->method('execute')
            ->willReturn(true);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $this->stmt
            ->method('fetchColumn')
            ->willReturn('1');

        $this->duplaRepository
            ->expects($this->once())
            ->method('temDupla')
            ->willReturn(true);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('O atleta Atleta 2 já formou tem uma dupla mista 1');

        $enviarSolicitacao($dto);
    }

    /**
     * @throws ValidatorException
     */
    public function testAtletaRemJaFormouDupla(): void
    {
        $competicao = (new Competicao())
            ->setId(1)
            ->setPrazo((new DateTime())->modify('+1 day'));

        $dto = EnviarSolicitacaoDTO::parse([
            'competicao' => $competicao->id(),
            'atletaRemetente' => 1,
            'atletaDestinatario' => 2,
            'categoria' => 1,
        ]);

        $this->competicaoRepository
            ->method('getViaId')
            ->willReturn($competicao);

        $tecnico = (new Tecnico())
            ->setId(1);

        $this->session
            ->method('getTecnico')
            ->willReturn($tecnico);

        $this->pdo
            ->method('prepare')
            ->willReturn($this->stmt);

        $this->stmt
            ->method('execute')
            ->willReturn(true);

        $remetenteGet = [
            'sexo' => 'M',
            'nome_completo' => 'Atleta 1',
            'tecnico_id' => 1,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $destinatarioGet = [
            'sexo' => 'F',
            'nome_completo' => 'Atleta 2',
            'tecnico_id' => 2,
            'categorias' => '[1, 2]',
            'sexo_dupla' => '["M", "F"]',
        ];

        $this->stmt
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([$remetenteGet], [$destinatarioGet]);

        $this->stmt
            ->method('fetchColumn')
            ->willReturn('1');

        $this->duplaRepository
            ->expects($this->exactly(2))
            ->method('temDupla')
            ->willReturnOnConsecutiveCalls(false, true);

        $enviarSolicitacao = new EnviarSolicitacao(
            $this->pdo,
            $this->session,
            $this->competicaoRepository,
            $this->solicitacaoPendenteRepository,
            $this->notificacaoRepository,
            $this->duplaRepository,
            $this->mailService,
        );

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('O seu atleta Atleta 1 já tem uma dupla mista 1');

        $enviarSolicitacao($dto);
    }
}
