<?php  /** @noinspection PhpClassCanBeReadonlyInspection */

namespace App\Notificacao;

use PDO;

class NotificacaoRepository implements NotificacaoRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    public function criar(Notificacao $notificacao): ?int
    {
        $pdo = $this->pdo;

        $sql = <<<SQL
            INSERT INTO notificacao
            (tipo, tecnico_id, id_1, id_2, id_3)
            VALUES
            (:tipo, :idTecnico, :id1, :id2, :id3)
        SQL;

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            'tipo' => $notificacao->tipo->value,
            'idTecnico' => $notificacao->idTecnico,
            'id1' => $notificacao->id1,
            'id2' => $notificacao->id2,
            'id3' => $notificacao->id3,
        ]);

        return $ok ? $pdo->lastInsertId() : null;
    }
}
