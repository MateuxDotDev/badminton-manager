<?php /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta\AtletaCompeticao;

use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Util\General\Dates;
use PDO;

class AtletaCompeticaoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function getAtletasForaCompeticao(int $idTecnico, int $idCompeticao): array
    {
        $sql = <<<SQL
            SELECT a.id as id,
                   a.nome_completo as nome_completo,
                   a.sexo as sexo,
                   a.data_nascimento as data_nascimento,
                   a.informacoes as informacoes,
                   a.path_foto as path_foto,
                   a.criado_em as criado_em,
                   a.alterado_em as alterado_em
              FROM atleta a
             WHERE a.tecnico_id = $idTecnico
               AND a.id NOT IN (SELECT ac.atleta_id
                                  FROM atleta_competicao ac
                                 WHERE ac.competicao_id = $idCompeticao)
        SQL;
        $query = $this->pdo->query($sql);
        $atletas = [];
        foreach ($query as $linha) {
            $atletas[] = $this->linhaParaAtleta($linha);
        }
        return $atletas;
    }


    private function salvarCategorias(int $idAtleta, int $idCompeticao, array $categorias): void
    {
        // Deletar categorias existentes (para quando o técnico estiver alterando o cadastro de um atleta na competição)

        $sql = <<<SQL
            DELETE FROM atleta_competicao_categoria WHERE atleta_id = ? AND competicao_id = ?
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ $idAtleta, $idCompeticao ]);


        // Inserir categorias

        $valuesArray = [];

        $parametros = [
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao,
        ];

        for ($i = 0; $i < count($categorias); $i++) {
            $categoria = $categorias[$i];
            $parametro = "categoria_id_$i";

            $parametros[$parametro] = $categoria->id();

            $valuesArray[] = "(:atleta_id, :competicao_id, :$parametro)";
        }

        $values = implode(',', $valuesArray);

        $sql = <<<SQL
            INSERT INTO atleta_competicao_categoria (atleta_id, competicao_id, categoria_id)
            VALUES $values
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
    }


    private function salvarSexoDupla(int $idAtleta, int $idCompeticao, array $sexoDupla): void
    {
        // Deletar existentes (para quando o técnico estiver alterando o cadastro de um atleta na competição)

        $sql = <<<SQL
            DELETE FROM atleta_competicao_sexo_dupla WHERE atleta_id = ? AND competicao_id = ?
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ $idAtleta, $idCompeticao ]);


        // Inserir

        $parametros = [
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao
        ];

        $valuesArray = [];

        for ($i = 0; $i < count($sexoDupla); $i++) {
            $parametro = "sexo_id_$i";
            $parametros[$parametro] = $sexoDupla[$i]->value;

            $valuesArray[] = "(:atleta_id, :competicao_id, :$parametro)";
        }

        $values = implode(',', $valuesArray);

        $sql = <<<SQL
            INSERT INTO atleta_competicao_sexo_dupla (atleta_id, competicao_id, sexo_dupla)
            VALUES $values
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
    }

    public function incluirAtletaCompeticao(AtletaCompeticao $atletaCompeticao) : bool
    {
        $idAtleta     = $atletaCompeticao->atleta()->id();
        $idCompeticao = $atletaCompeticao->competicao()->id();

        $sql = <<<SQL
            INSERT INTO atleta_competicao (atleta_id, competicao_id, informacoes)
            VALUES (:atleta_id, :competicao_id, :informacoes)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'atleta_id'     => $idAtleta,
            'competicao_id' => $idCompeticao,
            'informacoes'   => $atletaCompeticao->informacao()
        ]);

        $this->salvarCategorias($idAtleta, $idCompeticao, $atletaCompeticao->categorias());

        $this->salvarSexoDupla($idAtleta, $idCompeticao, $atletaCompeticao->sexoDupla());

        return true;
    }

    private function linhaParaAtleta(array $linha): Atleta
    {
        return (new Atleta())
            ->setId($linha['id'])
            ->setNomeCompleto($linha['nome_completo'])
            ->setSexo(Sexo::from($linha['sexo']))
            ->setDataNascimento(Dates::parseDay($linha['data_nascimento']))
            ->setInformacoesAdicionais($linha['informacoes'])
            ->setDataCriacao(Dates::parseMicro($linha['criado_em']))
            ->setDataAlteracao(Dates::parseMicro($linha['alterado_em']))
            ->setFoto($linha['path_foto']);
    }
}
