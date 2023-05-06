<?php /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Admin;

use App\Util\General\Dates;
use App\Util\General\SenhaCriptografada;
use PDO;

class AdminRepository
{
    public function __construct(
        readonly private PDO $pdo
    ) {}

    public function getViaNome(string $nome): ?Admin
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            SELECT "user",
                   hash_senha,
                   salt_senha,
                   alterado_em,
                   criado_em
              FROM "admin"
             WHERE "user" = :nome
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nome' => $nome]);

        $rows = $stmt->fetchAll();
        if (count($rows) != 1) {
            return null;
        }
        $row = $rows[0];

        $senhaCripto = SenhaCriptografada::existente($row['hash_senha'], $row['salt_senha']);

        return (new Admin)
            ->setNome($row['user'])
            ->setSenhaCriptografada($senhaCripto)
            ->setDataCriacao(Dates::parseMicro($row['criado_em']))
            ->setDataAlteracao(Dates::parseMicro($row['alterado_em']));
    }
}
