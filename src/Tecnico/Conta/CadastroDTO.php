<?php

namespace App\Tecnico\Conta;

use App\Util\Exceptions\ValidatorException;

readonly class CadastroDTO
{
    public function __construct(
        public string $email,
        public string $nomeCompleto,
        public string $senha,
        public ?string $nomeClubeNovo,
        public ?int $idClubeExistente,
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
        
        $clube = $req['clube'];
        if (!array_key_exists('novo', $req['clube'])) {
            throw new ValidatorException("Campo 'novo' em 'clube' está faltando");
        }
        if ($clube['novo'] && !array_key_exists('nome', $clube)) {
            throw new ValidatorException("Nome do clube novo está faltando");
        }
        if (!$clube['novo'] && !array_key_exists('id', $clube)) {
            throw new ValidatorException("Id do clube existente está faltando");
        }

        $idClubeExistente = $clube['novo'] ? null : $clube['id'];
        $nomeClubeNovo    = $clube['novo'] ? $clube['nome'] : null;

        $senha        = $req['senha'];
        $informacoes  = array_key_exists('informacoes', $req) ? $req['informacoes'] : '';

        if (false === ($email = filter_var($req['email'], FILTER_VALIDATE_EMAIL))) {
            throw new ValidatorException('E-mail inválido');
        }

        return new self($email, $req['nome'], $senha, $nomeClubeNovo, $idClubeExistente, $informacoes);
    }
}
