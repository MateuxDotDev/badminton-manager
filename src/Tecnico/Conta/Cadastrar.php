<?php

namespace App\Tecnico\Conta;

use App\Util\Http\HttpStatus;
use App\Tecnico\TecnicoRepositoryInterface;
use App\Tecnico\Tecnico;
use App\Tecnico\Clube;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\SenhaCriptografada;
use \Exception;

readonly class Cadastrar
{
    public function __construct(
        private TecnicoRepositoryInterface $repo,
    ) {}

    /**
     * @throws Exception
     */
    public function __invoke(CadastroDTO $dto): array
    {
        $repo = $this->repo;

        $jaExiste = null !== $repo->getViaEmail($dto->email);
        if ($jaExiste) {
            throw new ValidatorException('Esse e-mail já está sendo usado por outro técnico', HttpStatus::FORBIDDEN);
        }
    
        $senha = SenhaCriptografada::criptografar($dto->email, $dto->senha);

        $tecnico = (new Tecnico)
            ->setEmail($dto->email)
            ->setNomeCompleto($dto->nomeCompleto)
            ->setInformacoes($dto->informacoes)
            ->setSenhaCriptografada($senha)
            ;
    
        $repo->criarTecnico($tecnico, $dto->nomeClube);
    
        return [
            'id' => $tecnico->id(),
            'idClube' => $tecnico->clube()->id(),
        ];
    }
}
