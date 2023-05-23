<?php /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Tecnico;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use App\Util\General\SenhaCriptografada;
use App\Util\Http\HttpStatus;
use Exception;
use PDO;

class TecnicoRepository implements TecnicoRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * Chaves válidas: 'email', 'id'
     *
     * @throws Exception
     */
    public function getViaChave(string $chave, string $valor): ?Tecnico
    {
        if ($chave != 'email' && $chave != 'id') {
            throw new ValidatorException("Chave de técnico '$chave' inválida", HttpStatus::UNAUTHORIZED);
        }

        $sql = <<<SQL
            SELECT t.id,
                   t.email,
                   t.nome_completo,
                   t.informacoes,
                   t.hash_senha,
                   t.salt_senha,
                   c.id as clube_id,
                   c.nome as clube_nome,
                   c.criado_em as clube_criado_em,
                   t.criado_em,
                   t.alterado_em
              FROM tecnico t
              JOIN clube c
                ON c.id = t.clube_id
        SQL;

        if ($chave == 'email') {
            $sql .= ' WHERE email = :email';
            $params = ['email' => $valor];
        } else {
            $sql .= ' WHERE id = :id';
            $params = ['id' => $valor];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll();
        if (count($rows) != 1) {
            return null;
        }

        $row = $rows[0];

        $dataCriacao   = Dates::parseMicro($row['criado_em']);
        $dataAlteracao = Dates::parseMicro($row['alterado_em']);

        $clube = (new Clube)
            ->setId((int) $row['clube_id'])
            ->setNome($row['clube_nome'])
            ->setDataCriacao(Dates::parseMicro($row['clube_criado_em']));

        $senha = SenhaCriptografada::existente($row['hash_senha'], $row['salt_senha']);

        return (new Tecnico)
            ->setId((int) $row['id'])
            ->setEmail($row['email'])
            ->setSenhaCriptografada($senha)
            ->setNomeCompleto($row['nome_completo'])
            ->setInformacoes($row['informacoes'])
            ->setDataCriacao($dataCriacao)
            ->setDataAlteracao($dataAlteracao)
            ->setClube($clube);
    }

    /**
     * @throws Exception
     */
    public function getViaEmail(string $email): ?Tecnico
    {
        return $this->getViaChave('email', $email);
    }

    /**
     * @throws Exception
     */
    public function getViaId(int $id): ?Tecnico
    {
        return $this->getViaChave('id', (string) $id);
    }

    /**
     * Caso exista um clube com o nome informado, esse clube é retornado; caso contrário, o clube é criado e retornado.
     *
     * @param string $nome
     * @return Clube
     */
    public function buscarOuCriarClube(string $nome): Clube
    {
        $pdo  = $this->pdo;

        $sql  = "SELECT id FROM clube WHERE nome ILIKE :nome";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nome' => $nome]);

        $rows = $stmt->fetchAll();

        $clube = (new Clube)->setNome($nome);

        if (!empty($rows)) {
            $id = $rows[0]['id'];

            $clube->setId((int) $id);
        } else {
            $sql  = "INSERT INTO clube (nome) VALUES (:nome)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['nome' => $nome]);

            $clube->setId((int) $pdo->lastInsertId());
        }

        return $clube;
    }

    /**
     * @throws Exception
     *
     * @param Tecnico $tecnico Técnico a ser cadastrado, sem atributo clube necessário
     * @param string $nomeClube Nome do clube do técnico.
     * Caso já existe um clube com esse nome, o técnico será vinculado e esse clube;
     * Caso contrário, iremos criar um clube com esse nome e vincular o técnico ao clube criado.
     * A instância de técnico passada terá o clube buscado ou criado vinculado a ela.
     */
    public function criarTecnico(Tecnico $tecnico, string $nomeClube): void
    {
        $pdo = $this->pdo;

        $pdo->beginTransaction();
        try {
            $tecnico->setClube($this->buscarOuCriarClube($nomeClube));

            $sql = <<<SQL
                INSERT INTO tecnico (email, nome_completo, informacoes, clube_id, hash_senha, salt_senha)
                VALUES (:email, :nomeCompleto, :informacoes, :idClube, :hashSenha, :saltSenha)
            SQL;
            $pdo->prepare($sql)->execute([
                'email' => $tecnico->email(),
                'nomeCompleto' => $tecnico->nomeCompleto(),
                'informacoes' => $tecnico->informacoes(),
                'idClube' => $tecnico->clube()->id(),
                'hashSenha' => $tecnico->senhaCriptografada()?->hash(),
                'saltSenha' => $tecnico->senhaCriptografada()?->salt(),
            ]);

            $tecnico->setId($pdo->lastInsertId());
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }
}
