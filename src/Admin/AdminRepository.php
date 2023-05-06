<?php
namespace App\Admin;

use App\SenhaCriptografada;
use App\Util\Dates;
use PDO;

readonly class AdminRepository
{
    public function __construct(
        private PDO $pdo
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
            ->setDataAlteracao(Dates::parseMicro($row['alterado_em']))
            ;
    }

    public function criar(Admin $admin)
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            INSERT INTO admin (user, hash_senha, salt_senha)
            VALUES (:nome, :hash, :salt)
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $admin->nome(),
            'hash' => $admin->senhaCriptografada()->hash(),
            'salt' => $admin->senhaCriptografada()->salt(),
        ]);
    }
}
