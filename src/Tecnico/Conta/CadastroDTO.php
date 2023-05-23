<?php

namespace App\Tecnico\Conta;

use App\Util\Exceptions\ValidatorException;

readonly class CadastroDTO
{
    public function __construct(
        public string $email,
        public string $nomeCompleto,
        public string $senha,
        public string $nomeClube,
        public ?string $informacoes,
    ) {}

    /**
     * @throws ValidatorException
     */
    public static function parse(array $req): CadastroDTO
    {
        foreach (['email', 'senha', 'clube', 'nome'] as $campoObrigatorio) {
            if (!array_key_exists($campoObrigatorio, $req)) {
                throw new ValidatorException("Campo '$campoObrigatorio' está faltando");
            }
        }
        
        $nomeClube    = $req['clube'];
        $senha        = $req['senha'];
        $informacoes  = array_key_exists('informacoes', $req) ? $req['informacoes'] : '';

        if (false === ($email = filter_var($req['email'], FILTER_VALIDATE_EMAIL))) {
            throw new ValidatorException('E-mail inválido');
        }

        return new self($email, $req['nome'], $senha, $nomeClube, $informacoes);
    }
}
