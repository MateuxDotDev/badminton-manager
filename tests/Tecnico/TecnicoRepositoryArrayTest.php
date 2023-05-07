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
            ->setNomeCompleto('Tecnico 1')
            ->setClube((new Clube())
                ->setId(1)
                ->setNome('Clube A')
            );
        $tecnico2 = (new Tecnico())
            ->setId(2)
            ->setEmail('tecnico2@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('tecnico2@example.com', 'senha2'))
            ->setNomeCompleto('Tecnico 2')
            ->setClube((new Clube())
                ->setId(2)
                ->setNome('Clube B')
            );

        $tecnicoRepository->criarTecnico($tecnico1);
        $tecnicoRepository->criarTecnico($tecnico2);

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
            ->setNomeCompleto('Tecnico 1')
            ->setClube((new Clube())
                ->setId(1)
                ->setNome('Clube A')
            );
        $tecnico2 = (new Tecnico())
            ->setId(2)
            ->setEmail('tecnico2@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('tecnico2@example.com', 'senha2'))
            ->setNomeCompleto('Tecnico 2')
            ->setClube((new Clube())
                ->setId(2)
                ->setNome('Clube B')
            );

        $tecnicoRepository->criarTecnico($tecnico1);
        $tecnicoRepository->criarTecnico($tecnico2);

        $tecnicoEncontrado = $tecnicoRepository->getViaId(2);

        $this->assertEquals($tecnico2, $tecnicoEncontrado);
    }

    /**
     * @throws Exception
     */
    public function testCriarTecnico()
    {
        $clube = (new Clube)
            ->setId(1)
            ->setNome('Clube A');

        $tecnico = (new Tecnico)
            ->setEmail('john@example.com')
            ->setSenhaCriptografada(SenhaCriptografada::criptografar('john@example.com', 'senha123'))
            ->setNomeCompleto('John Doe')
            ->setInformacoes('Informações sobre o técnico')
            ->setClube($clube);

        $repo = new TecnicoRepositoryArray();
        $repo->criarTecnico($tecnico);

        $this->assertEquals($tecnico, $repo->getViaId(1));
        $this->assertEquals($tecnico, $repo->getViaEmail('john@example.com'));
    }
}
