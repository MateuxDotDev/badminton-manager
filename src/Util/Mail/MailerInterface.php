<?php

namespace App\Util\Mail;

interface MailerInterface
{
    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        string $altBody,
        bool $isHTML = true
    ): bool;
}
