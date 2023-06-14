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

    /**
     * @throws ValidatorException
     */
    public static function parse(array $a): EnviarSolicitacaoDTO
    {
        $idCompeticao         = self::validateAndExtractInt($a, 'competicao');
        $idAtletaRemetente    = self::validateAndExtractInt($a, 'atletaRemetente');
        $idAtletaDestinatario = self::validateAndExtractInt($a, 'atletaDestinatario');
        $idCategoria          = self::validateAndExtractInt($a, 'categoria');

        $informacoes = '';
        if (array_key_exists('informacoes', $a)) {
            $informacoes = $a['informacoes'];
        }

        return new self($idCompeticao, $idAtletaRemetente, $idAtletaDestinatario, $idCategoria, $informacoes);
    }

    /**
     * @throws ValidatorException
     */
    private static function validateAndExtractInt(array $data, string $field): int
    {
        if (!array_key_exists($field, $data)) {
            throw new ValidatorException("Campo '$field' faltando");
        }

        $value = filter_var($data[$field], FILTER_VALIDATE_INT);
        if (!$value) {
            throw new ValidatorException("Campo '$field' inválido: deve ser um inteiro");
        }

        return $value;
    }
}
