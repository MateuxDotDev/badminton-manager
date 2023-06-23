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

    public function getViaId1(int $id1, TipoNotificacao $tipo): array
    {
        $sql = <<<SQL
            SELECT n.id,
                   n.tipo,
                   n.tecnico_id,
                   n.id_1,
                   n.id_2,
                   n.id_3,
                   n.criado_em
              FROM notificacao n
             WHERE n.id_1 = :id_1
               AND n.tipo = :tipo
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_1' => $id1,
            'tipo' => $tipo->value
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
