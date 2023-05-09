<?php

namespace Tests\Admin;

use App\Admin\Admin;
use App\Admin\AdminRepository;
use App\Util\General\SenhaCriptografada;
use Exception;
use PDO;
use PHPUnit\Framework\TestCase;

class AdminRepositoryTest extends TestCase
{
    private static PDO $pdo;

    /**
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->exec(<<<SQL
            CREATE TABLE "admin" (
                "user" TEXT NOT NULL,
                hash_senha TEXT NOT NULL,
                salt_senha TEXT NOT NULL,
                alterado_em TEXT NOT NULL,
                criado_em TEXT NOT NULL,
                PRIMARY KEY("user")
            )
        SQL);

        $nome = 'John Doe';
        $senhaCripto = SenhaCriptografada::criptografar($nome, 'senha123');

        $stmt = self::$pdo->prepare(<<<SQL
            INSERT INTO "admin" ("user", hash_senha, salt_senha, alterado_em, criado_em)
            VALUES (:nome, :hashSenha, :saltSenha, :alteradoEm, :criadoEm)
        SQL);
        $stmt->execute([
            'nome' => $nome,
            'hashSenha' => $senhaCripto->hash(),
            'saltSenha' => $senhaCripto->salt(),
            'alteradoEm' => '2023-05-05 10:00:00.000000',
            'criadoEm' => '2023-05-05 10:00:00.000000',
        ]);
    }

    public function testGetViaNome()
    {
        $repository = new AdminRepository(self::$pdo);

        $nome = 'John Doe';
        $admin = $repository->getViaNome($nome);

        $this->assertInstanceOf(Admin::class, $admin, 'O objeto retornado deve ser uma instância da classe Admin.');
        $this->assertSame($nome, $admin->nome(), 'O nome do administrador deve ser igual ao nome buscado.');

        $senhaCripto = $admin->senhaCriptografada();
        $this->assertInstanceOf(SenhaCriptografada::class, $senhaCripto, 'A senha criptografada do administrador deve ser uma instância da classe SenhaCriptografada.');
    }

    public function testGetViaNomeNotFound()
    {
        $repository = new AdminRepository(self::$pdo);

        $nome = 'Jane Doe';
        $admin = $repository->getViaNome($nome);

        $this->assertNull($admin, 'O método getViaNome deve retornar null quando o administrador não for encontrado.');
    }
}
