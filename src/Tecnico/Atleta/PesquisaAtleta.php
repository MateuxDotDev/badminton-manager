<?php

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Ordenacao;

readonly class PesquisaAtleta
{
    public function __construct(
        public int $idCompeticao,
        public ?string $nomeAtleta,
        public ?string $nomeTecnico,
        public ?string $clube,
        public ?int $idadeMaiorQue,
        public ?int $idadeMenorQue,
        public array $idCategorias,
        public array $sexoAtleta,
        public array $sexoDupla,
        public string $colunaOrdenacao,
        public Ordenacao $ordenacao,
    ) {}

    /**
     * @throws ValidatorException
     */
    public static function parse(array $req): PesquisaAtleta
    {
        if (!array_key_exists('idCompeticao', $req)) {
            throw new ValidatorException('É obrigatório informar a competição que está sendo pesquisada');
        }

        $idCompeticao = (int) filter_var($req['idCompeticao'], FILTER_SANITIZE_NUMBER_INT);

        $nomeAtleta = array_key_exists('nomeAtleta', $req) ? $req['nomeAtleta'] : null;
        $nomeTecnico = array_key_exists('nomeTecnico', $req) ? $req['nomeTecnico'] : null;

        $clube = array_key_exists('clube', $req) ? $req['clube'] : null;

        $idadeMaiorQue = array_key_exists('idadeMaiorQue', $req)
                       ? (int) filter_var($req['idadeMaiorQue'], FILTER_SANITIZE_NUMBER_INT)
                       : null;

        $idadeMenorQue = array_key_exists('idadeMaiorQue', $req)
                       ? (int) filter_var($req['idadeMenorQue'], FILTER_SANITIZE_NUMBER_INT)
                       : null;

        $idCategorias = array_key_exists('categorias', $req)
                      ? array_map(
                           fn($id) => (int) filter_var($id, FILTER_SANITIZE_NUMBER_INT),
                           $req['categorias'])
                      : [];
    
        $sexoAtleta = array_key_exists('sexoAtleta', $req)
                    ? array_filter(array_map(fn(string $s): Sexo => Sexo::tryFrom($s), $req['sexoAtleta']))
                    : [];
    
        $sexoDupla = array_key_exists('sexoDupla', $req)
                   ? array_filter(array_map(fn(string $s): Sexo => Sexo::tryFrom($s), $req['sexoDupla']))
                   : [];

        if (!array_key_exists('ordenacao', $req) || !array_key_exists('colunaOrdenacao', $req)) {
            throw new ValidatorException('É obrigatório informar a ordenação ao pesquisar atletas');
        }
        $ordenacao = Ordenacao::fromString($req['ordenacao']);
        if ($ordenacao === null) {
            throw new ValidatorException('Ordenação inválida');
        }

        $colunaOrdenacao = $req['colunaOrdenacao'];

        static $colunasOrdenacao = ['nomeAtleta', 'nomeTecnico', 'clube', 'idade', 'dataAlteracao'];

        if (!in_array($colunaOrdenacao, $colunasOrdenacao)) {
            throw new ValidatorException('Coluna de ordenação inválida, deve ser uma dentre: ' . implode(', ', $colunasOrdenacao));
        }

        return new self(
            $idCompeticao,
            $nomeAtleta,
            $nomeTecnico,
            $clube,
            $idadeMaiorQue,
            $idadeMenorQue,
            $idCategorias,
            $sexoAtleta,
            $sexoDupla,
            $colunaOrdenacao,
            $ordenacao,
        );
    }
}