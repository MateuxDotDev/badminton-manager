<?php

namespace Tests\Tecnico\Solicitacao;

use App\Tecnico\Solicitacao\EnviarSolicitacaoDTO;
use App\Util\Exceptions\ValidatorException;
use PHPUnit\Framework\TestCase;

class EnviarSolicitacaoDTOTest extends TestCase
{
    /**
     * @throws ValidatorException
     */
    public function testParse(): void
    {
        $dto = EnviarSolicitacaoDTO::parse(
            [
                'competicao' => 1,
                'atletaRemetente' => 1,
                'atletaDestinatario' => 2,
                'categoria' => 1,
                'informacoes' => 'test'
            ]
        );

        $this->assertNotNull($dto);
        $this->assertSame(1, $dto->idCompeticao);
        $this->assertSame(1, $dto->idAtletaRemetente);
        $this->assertSame(2, $dto->idAtletaDestinatario);
        $this->assertSame(1, $dto->idCategoria);
        $this->assertSame('test', $dto->informacoes);
    }

    public function testParseWithoutArgument(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Campo 'competicao' faltando");

        EnviarSolicitacaoDTO::parse([]);
    }

    public function testParseWithInvalidArgumentType(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Campo 'competicao' inválido: deve ser um inteiro");

        EnviarSolicitacaoDTO::parse(
            [
                'competicao' => 'um',
                'atletaRemetente' => 1,
                'atletaDestinatario' => 2,
                'categoria' => 1,
            ]
        );
    }
}
