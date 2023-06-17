<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Solicitacao;

use App\Competicoes\Competicao;
use App\Tecnico\Atleta\Atleta;
use App\Tecnico\Atleta\Sexo;
use App\Tecnico\Clube;
use App\Tecnico\Tecnico;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use Exception;
use PDO;

class SolicitacaoPendenteRepository implements SolicitacaoPendenteRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * @throws Exception
     */
    public function getViaIds(
        int $idCompeticao,
        int $idAtleta1,
        int $idAtleta2,
        int $idCategoria
    ): ?SolicitacaoPendente
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            select id
                 , competicao_id
                 , atleta_origem_id
                 , atleta_destino_id
                 , categoria_id
                 , criado_em
                 , alterado_em
                 , informacoes
              from solicitacao_dupla_pendente
             where atleta_origem_id in (:id1, :id2)
               and atleta_destino_id in (:id1, :id2)
               and categoria_id = :idCategoria
               and competicao_id = :idCompeticao
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id1' => $idAtleta1,
            'id2' => $idAtleta2,
            'idCompeticao' => $idCompeticao,
            'idCategoria' => $idCategoria,
        ]);

        $rows = $stmt->fetchAll();

        if (count($rows) > 1) {
            throw new ValidatorException(
                'Mais de uma solicitação envolvendo os mesmos atleta e a mesma categoria dentro da mesma competição.'
            );
        }
        if (empty($rows)) {
            return null;
        }
        $row = $rows[0];

        return new SolicitacaoPendente(
            (int) $row['id'],
            Dates::parseMicro($row['criado_em']),
            Dates::parseMicro($row['alterado_em']),
            (int) $row['competicao_id'],
            (int) $row['atleta_origem_id'],
            (int) $row['atleta_destino_id'],
            (int) $row['categoria_id'],
            $row['informacoes'],
        );
    }

    public function enviar(EnviarSolicitacaoDTO $solicitacao): int
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            INSERT INTO solicitacao_dupla_pendente
            (competicao_id, atleta_origem_id, atleta_destino_id, informacoes, categoria_id)
            VALUES
            (:idCompeticao, :idAtletaRemetente, :idAtletaDestinatario, :informacoes, :idCategoria)
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'idCompeticao'         => $solicitacao->idCompeticao,
            'idAtletaRemetente'    => $solicitacao->idAtletaRemetente,
            'idAtletaDestinatario' => $solicitacao->idAtletaDestinatario,
            'idCategoria'          => $solicitacao->idCategoria,
            'informacoes'          => $solicitacao->informacoes,
        ]);

        return $pdo->lastInsertId();
    }

    public function getViaTecnico(int $idTecnico): array
    {
        $competicoes  = [];  // indexado por id
        $atletas      = [];  // indexado por id
        $solicitacoes = [];

        $sql = <<<SQL
            SELECT s.id           s_id
                 , s.informacoes  s_informacoes
                 , s.criado_em    s_criado_em
                 , s.alterado_em  s_alterado_em
                 , s.categoria_id s_categoria_id

                 , co.id        co_id
                 , co.descricao co_descricao
                 , co.prazo     co_prazo

                 , ori.id                ori_id
                 , ori.nome_completo     ori_nome
                 , ori.sexo              ori_sexo
                 , ori.path_foto         ori_path_foto
                 , ori.data_nascimento   ori_data_nascimento
                 , ori_tec.id            ori_tecnico_id
                 , ori_tec.nome_completo ori_tecnico_nome
                 , ori_tec.informacoes   ori_tecnico_informacoes
                 , ori_clu.id            ori_clube_id
                 , ori_clu.nome          ori_clube_nome

                 , dest.id                dest_id
                 , dest.nome_completo     dest_nome
                 , dest.sexo              dest_sexo
                 , dest.path_foto         dest_path_foto
                 , dest.data_nascimento   dest_data_nascimento
                 , dest_tec.id            dest_tecnico_id
                 , dest_tec.nome_completo dest_tecnico_nome
                 , dest_tec.informacoes   dest_tecnico_informacoes
                 , dest_clu.id            dest_clube_id
                 , dest_clu.nome          dest_clube_nome

              FROM solicitacao_dupla_pendente s
              JOIN competicao co    ON co.id = s.competicao_id
              JOIN atleta ori       ON ori.id = s.atleta_origem_id
              JOIN tecnico ori_tec  ON ori_tec.id = ori.tecnico_id
              JOIN clube ori_clu    ON ori_clu.id = ori_tec.clube_id
              JOIN atleta dest      ON dest.id = s.atleta_destino_id
              JOIN tecnico dest_tec ON dest_tec.id = dest.tecnico_id
              JOIN clube dest_clu   ON dest_clu.id = dest_tec.clube_id
             WHERE :tecnico_id IN (ori.tecnico_id, dest.tecnico_id)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['tecnico_id' => $idTecnico]);

        $addCompeticao = function($row) use (&$competicoes) {
            $id = (int) $row['co_id'];
            if (!array_key_exists($id, $competicoes)) {
                $competicoes[$id] = (new Competicao)
                    ->setDescricao($row['co_descricao'])
                    ->setPrazo(Dates::parseDay($row['co_prazo']))
                    ->setId((int) $row['co_id'])
                    ;
            }
        };

        $addAtleta = function($row, $pre) use (&$atletas) {
            $id = (int) $row[$pre.'_id'];
            if (!array_key_exists($id, $atletas)) {
                $atletas[$id] = (new Atleta)
                    ->setNomeCompleto($row[$pre.'_nome'])
                    ->setSexo(Sexo::from($row[$pre.'_sexo']))
                    ->setDataNascimento(Dates::parseDay($row[$pre.'_data_nascimento']))
                    ->setFoto($row[$pre.'_path_foto'])
                    ->setTecnico((new Tecnico)
                        ->setNomeCompleto($row[$pre.'_tecnico_nome'])
                        ->setInformacoes($row[$pre.'_tecnico_informacoes'])
                        ->setId((int) $row[$pre.'_tecnico_id'])
                        ->setClube((new Clube)
                            ->setNome($row[$pre.'_clube_nome'])
                            ->setId((int) $row[$pre.'_clube_id'])))
                    ->setId($id);
            }
        };

        while ($row = $stmt->fetch()) {
            $addCompeticao($row);
            $addAtleta($row, 'ori');
            $addAtleta($row, 'dest');

            $solicitacoes[] = new SolicitacaoPendente(
                id: (int) $row['s_id'],
                dataCriacao: Dates::parseMicro($row['s_criado_em']),
                dataAlteracao: Dates::parseMicro($row['s_alterado_em']),
                idCompeticao: (int) $row['co_id'],
                idAtletaRemetente: (int) $row['ori_id'],
                idAtletaDestinatario: (int) $row['dest_id'],
                idCategoria: (int) $row['s_categoria_id'],
                informacoes: $row['s_informacoes'],
            );
        }

        return [
            'competicoes'  => $competicoes,
            'atletas'      => $atletas,
            'solicitacoes' => $solicitacoes,
        ];
    }
}
