<?php

namespace App\Util\Mail;

use App\Util\Exceptions\MailException;

interface MailerInterface
{
    /**
     * @throws MailException
     */
    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        string $altBody,
        bool $isHTML = true
    ): bool;
}
