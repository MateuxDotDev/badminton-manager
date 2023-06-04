<?php

namespace App\Tecnico\Solicitacao;
use App\Util\Exceptions\ValidatorException;

readonly class EnviarSolicitacaoDTO
{
    private function __construct(
        public int $idCompeticao,
        public int $idAtletaRemetente,
        public int $idAtletaDestinatario,
        public int $idCategoria,
        public ?string $informacoes,
    ) {}

    public static function parse(array $a): EnviarSolicitacaoDTO
    {
        $campos = ['competicao', 'atletaRemetente', 'atletaDestinatario', 'categoria'];
        foreach ($campos as $campo) {
            if (!array_key_exists($campo, $a)) {
                throw new ValidatorException("Campo '$campo' faltando");
            }
        }

        $idCompeticao = filter_var($a['competicao'], FILTER_VALIDATE_INT);
        if (!$idCompeticao) {
            throw new ValidatorException("Campo 'competicao' inválido: deve ser um inteiro");
        }

        $idAtletaRemetente = filter_var($a['atletaRemetente'], FILTER_VALIDATE_INT);
        if (!$idAtletaRemetente) {
            throw new ValidatorException("Campo 'atletaRemetente' inválido: deve ser um inteiro");
        }

        $idAtletaDestinatario = filter_var($a['atletaDestinatario'], FILTER_VALIDATE_INT);
        if (!$idAtletaDestinatario) {
            throw new ValidatorException("Campo 'atletaDestinatario' inválido: deve ser um inteiro");
        }

        $idCategoria = filter_var($a['categoria'], FILTER_VALIDATE_INT);
        if (!$idCategoria) {
            throw new ValidatorException("Campo 'categoria' inválido: deve ser um inteiro");
        }
        
        $informacoes = '';
        if (array_key_exists('informacoes', $a)) {
            $informacoes = $a['informacoes'];
        }

        return new self($idCompeticao, $idAtletaRemetente, $idAtletaDestinatario, $idCategoria, $informacoes);
    }
}