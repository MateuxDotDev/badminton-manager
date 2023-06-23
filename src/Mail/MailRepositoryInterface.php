<?php

namespace App\Mail;

use App\Util\Exceptions\ValidatorException;

interface MailRepositoryInterface
{
    /**
     * @throws ValidatorException
     */
    function criar(EmailDTO $dto): ?int;

    /**
     * @return EmailDTO[]
     */
    function ativas(): array;

    /**
     * @param int[] $ids
     */
    function enviadas(array $ids): int;
}
