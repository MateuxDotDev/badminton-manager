<?php

namespace Tests\Tecnico;

use App\Tecnico\Clube;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepositoryArray;
use App\Util\General\SenhaCriptografada;
use Exception;
use PHPUnit\Framework\TestCase;

class TecnicoRepositoryArrayTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetViaEmail(): void
    {
        $tecnicoRepository = new TecnicoRepositoryArray();

        $tecnico1 = (new Tecnico())
            ->setId(1)
            ->setEmail('tecnico1@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('tecnico1@example.com', 'senha1'))
            ->setNomeCompleto('Tecnico 1');
        $tecnico2 = (new Tecnico())
            ->setId(2)
            ->setEmail('tecnico2@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('tecnico2@example.com', 'senha2'))
            ->setNomeCompleto('Tecnico 2');

        $tecnicoRepository->criarTecnico($tecnico1, 'Clube A');
        $tecnicoRepository->criarTecnico($tecnico2, 'Clube B');

        $tecnicoEncontrado = $tecnicoRepository->getViaEmail('tecnico2@example.com');

        $this->assertEquals($tecnico2, $tecnicoEncontrado);
    }

    /**
     * @throws Exception
     */
    public function testGetViaId(): void
    {
        $tecnicoRepository = new TecnicoRepositoryArray();

        $tecnico1 = (new Tecnico())
            ->setId(1)
            ->setEmail('tecnico1@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('tecnico1@example.com', 'senha1'))
            ->setNomeCompleto('Tecnico 1');
        $tecnico2 = (new Tecnico())
            ->setId(2)
            ->setEmail('tecnico2@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('tecnico2@example.com', 'senha2'))
            ->setNomeCompleto('Tecnico 2');

        $tecnicoRepository->criarTecnico($tecnico1, 'Clube A');
        $tecnicoRepository->criarTecnico($tecnico2, 'Clube B');

        $tecnicoEncontrado = $tecnicoRepository->getViaId(2);

        $this->assertEquals($tecnico2, $tecnicoEncontrado);
    }

    /**
     * @throws Exception
     */
    public function testCriarTecnicoClubeNovo()
    {
        $tecnico = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('john@example.com', 'senha123'))
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico');

        $repo = new TecnicoRepositoryArray();
        $repo->criarTecnico($tecnico, 'Clube A');

        $this->assertEquals($tecnico, $repo->getViaId(1));
        $this->assertEquals($tecnico, $repo->getViaEmail('john@example.com'));
        $this->assertNotNull($tecnico->clube());
        $this->assertEquals(1, $tecnico->clube()->id());
        $this->assertEquals('Clube A', $tecnico->clube()->nome());
    }

    /**
     * @throws Exception
     */
    public function testCriarTecnicoClubeExistente()
    {
        $repo = new TecnicoRepositoryArray();

        $t1 = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('john@example.com', 'senha123'))
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico');
        $repo->criarTecnico($t1, 'Clube A');

        $t2 = (new Tecnico)
            ->setEmail('jane@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('jane@example.com', 'senhaExtremamenteSegura'))
            ->setNomeCompleto('Jane Doe')
            ->setInformacoes('Informações sobre a técnica');
        $repo->criarTecnico($t2, 'Clube A');

        $this->assertNotNull($t2->clube());
        $this->assertEquals(1, $t2->clube()->id());
        $this->assertEquals('Clube A', $t2->clube()->nome());
    }

    public function testGetViaIdReturnsNull(): void
    {
        $tecnicoRepository = new TecnicoRepositoryArray();

        $tecnicoEncontrado = $tecnicoRepository->getViaId(1);

        $this->assertNull($tecnicoEncontrado);
    }
}
