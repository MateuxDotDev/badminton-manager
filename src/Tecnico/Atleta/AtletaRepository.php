<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico\Atleta;

use App\Tecnico\Clube;
use App\Tecnico\Tecnico;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\Http\HttpStatus;
use App\Util\Services\UploadImagemService\UploadImagemService;
use App\Util\Services\UploadImagemService\UploadImagemServiceInterface;
use \Exception;
use \PDO;

class AtletaRepository implements AtletaRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly UploadImagemServiceInterface $uploadImagemService = new UploadImagemService()
    ) {}

    /**
     * @throws Exception
     */
    public function criarAtleta(Atleta $atleta): int
    {
        try {
            $sql = <<<SQL
                SELECT id
                  FROM tecnico
                 WHERE id = :id
            SQL;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $atleta->tecnico()->id()]);
            $rows = $stmt->fetchAll();
            if (count($rows) != 1) {
                throw new ValidatorException(
                    "Técnico '{$atleta->tecnico()->nomeCompleto()}' não existe",
                    HttpStatus::NOT_FOUND
                );
            }

            $sql = <<<SQL
                INSERT INTO atleta (
                    tecnico_id,
                    nome_completo,
                    sexo,
                    data_nascimento,
                    informacoes,
                    path_foto
                )
                VALUES (
                    :tecnico_id,
                    :nome_completo,
                    :sexo,
                    :data_nascimento,
                    :informacoes,
                    :path_foto
                )
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'tecnico_id' => $atleta->tecnico()->id(),
                'nome_completo' => $atleta->nomeCompleto(),
                'sexo' => $atleta->sexo()->value,
                'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
                'informacoes' => $atleta->informacoesAdicionais(),
                'path_foto' => $atleta->foto()
            ]);

            $atleta->setId($this->pdo->lastInsertId());

            return $atleta->id();
        } catch (Exception $e) {
            $this->uploadImagemService->removerImagem($atleta->foto());
            throw $e;
        }
    }

    private function get(array $filtros=[]): array
    {
        $condicoes  = [];
        $parametros = [];

        if (array_key_exists('tecnico', $filtros)) {
            $condicoes[]  = 'a.tecnico_id = ?';
            $parametros[] = (int) $filtros['tecnico'];
        }

        if (array_key_exists('id', $filtros)) {
            $condicoes[]  = 'a.id = ?';
            $parametros[] = (int) $filtros['id'];
        }

        if (array_key_exists('id_in', $filtros)) {
            $ids = $filtros['id_in'];
            if (empty($ids)) {
                return [];
            }
            $condicoes[] = 'a.id in ('.implode(',', array_fill(0, count($ids), '?')).')';
            foreach ($ids as $id) {
                $parametros[] = $id;
            }
        }

        if (empty($condicoes)) {
            throw new Exception('Condições não foram informadas');
        }

        $where = implode(' AND ', $condicoes);

        $sql = <<<SQL
            SELECT a.id                  a_id
                 , a.nome_completo       a_nome_completo
                 , a.sexo                a_sexo
                 , a.data_nascimento     a_data_nascimento
                 , a.informacoes         a_informacoes
                 , a.path_foto           a_path_foto
                 , a.criado_em           a_criado_em
                 , a.alterado_em         a_alterado_em

                 , t.id                  t_id
                 , t.nome_completo       t_nome_completo
                 , t.email               t_email
                 , t.informacoes         t_informacoes
                 , t.criado_em           t_criado_em
                 , t.alterado_em         t_alterado_em

                 , c.id                  c_id
                 , c.nome                c_nome
                 , c.criado_em           c_criado_em
              FROM atleta a
              JOIN tecnico t ON t.id = a.tecnico_id
              JOIN clube c   ON c.id = t.clube_id
             WHERE $where
          ORDER BY a.nome_completo
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        $rows = $stmt->fetchAll();

        $atletas = [];
        foreach ($rows as $row) {
            $atletas[] = (new Atleta())
                ->setId($row['a_id'])
                ->setNomeCompleto($row['a_nome_completo'])
                ->setSexo(Sexo::from($row['a_sexo']))
                ->setDataNascimento(Dates::parseDay($row['a_data_nascimento']))
                ->setInformacoesAdicionais($row['a_informacoes'])
                ->setFoto($row['a_path_foto'])
                ->setDataCriacao(Dates::parseMicro($row['a_criado_em']))
                ->setDataAlteracao(Dates::parseMicro($row['a_alterado_em']))
                ->setTecnico(
                    (new Tecnico)
                    ->setNomeCompleto($row['t_nome_completo'])
                    ->setEmail($row['t_email'])
                    ->setInformacoes($row['t_informacoes'])
                    ->setId((int) $row['t_id'])
                    ->setDataCriacao(Dates::parseMicro($row['t_criado_em']))
                    ->setDataAlteracao(Dates::parseMicro($row['t_alterado_em']))
                    ->setClube(
                        (new Clube)
                        ->setDataCriacao(Dates::parseMicro($row['c_criado_em']))
                        ->setId((int) $row['c_id'])
                        ->setNome($row['c_nome'])));
        }

        return $atletas;
    }

    public function removerAtleta(int $atletaId): bool
    {
        $sql = <<<SQL
            DELETE FROM atleta
                  WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $atletaId]);

        return $stmt->rowCount() === 1;
    }

    public function atualizarAtleta(Atleta $atleta): bool
    {
        $sql = <<<SQL
            UPDATE atleta
               SET nome_completo = :nome_completo,
                   sexo = :sexo,
                   data_nascimento = :data_nascimento,
                   informacoes = :informacoes,
                   path_foto = :path_foto,
                   alterado_em = NOW()
             WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nome_completo' => $atleta->nomeCompleto(),
            'sexo' => $atleta->sexo()->value,
            'data_nascimento' => $atleta->dataNascimento()->format('Y-m-d'),
            'informacoes' => $atleta->informacoesAdicionais(),
            'path_foto' => $atleta->foto(),
            'id' => $atleta->id()
        ]);

        return $stmt->rowCount() === 1;
    }

    public function getViaTecnico(int $tecnicoId): array
    {
        return $this->get(['tecnico' => $tecnicoId]);
    }

    public function getViaId(int $idAtleta): ?Atleta
    {
        $atletas = $this->get(['id' => $idAtleta]);
        return empty($atletas) ? null : $atletas[0];
    }

    public function getViaIds(array $idAtletas): array
    {
        return $this->get(['id_in' => $idAtletas]);
    }
}
