<?php

namespace App\Mail;

use App\Util\Exceptions\ValidatorException;
use App\Util\General\Dates;
use PDO;

class MailRepository implements MailRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws ValidatorException
     */
    public function criar(EmailDTO $dto): ?int
    {
        if ($dto->idNotificacao === null) {
            throw new ValidatorException('O id da notificação não pode ser nulo.');
        }

        $sql = <<<SQL
            INSERT INTO email_notificacao
            (conteudo, assunto, destinatario, email_destino, alt_conteudo, notificacao_id)
            VALUES
            (:conteudo, :assunto, :destinatario, :emailDestino, :altConteudo, :notificacaoId)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $okEmailNotificacao = $stmt->execute([
            'conteudo' => $dto->body,
            'assunto' => $dto->subject,
            'destinatario' => $dto->toName,
            'emailDestino' => $dto->toEmail,
            'altConteudo' => $dto->altBody,
            'notificacaoId' => $dto->idNotificacao,
        ]);

        $sql = <<<SQL
            INSERT INTO envio_email_notificacao
            (notificacao_id)
            VALUES
            (:notificacaoId)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $okEnvioEmailNotificacao = $stmt->execute([
            'notificacaoId' => $dto->idNotificacao,
        ]);

        $ok = $okEmailNotificacao && $okEnvioEmailNotificacao;

        return $ok ? $this->pdo->lastInsertId() : null;
    }

    /**
     * @return EmailDTO[]
     */
    public function ativas(): array
    {
        $sql = <<<SQL
            SELECT en.id,
                   en.conteudo,
                   en.assunto,
                   en.destinatario,
                   en.email_destino,
                   en.alt_conteudo,
                   en.notificacao_id,
                   en.criado_em
              FROM email_notificacao en
              JOIN notificacao n on en.notificacao_id = n.id
              JOIN envio_email_notificacao een on n.id = een.notificacao_id
             WHERE een.enviado_em IS NULL
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $emails = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $emails[] = (new EmailDTO(
                $row['destinatario'],
                $row['email_destino'],
                $row['assunto'],
                $row['conteudo'],
                $row['alt_conteudo'],
                $row['notificacao_id'],
            ))->setUuid($row['id'])
              ->setDataCriacao(Dates::parseMicro($row['criado_em']));
        }

        return $emails;
    }


    /**
     * @param int[] $ids
     */
    public function enviadas(array $ids): int
    {
        $in  = str_repeat('?,', count($ids) - 1) . '?';

        $sql = <<<SQL
            UPDATE envio_email_notificacao
               SET enviado_em = NOW()
             WHERE notificacao_id IN ($in)
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);

        return $stmt->rowCount();
    }
}
