<?php

namespace App\Mail;

use App\Util\Exceptions\MailException;
use App\Util\Mail\MailerInterface;

abstract class MailTemplate
{
    private MailerInterface $mailer;
    private string $body;
    private string $subject;
    private string $toName;
    private string $toEmail;
    private string $altBody;

    public function __construct(MailerInterface $mailer, string $body, string $subject = '', $altBody = '')
    {
        $this->mailer = $mailer;
        $this->body = $body;
        $this->subject = $subject;
        $this->altBody = $altBody;
    }

    public function fillTemplate(array $data): void
    {
        $this->body = str_replace(
            array_map(fn ($key) => "{{ $key }}", array_keys($data)),
            array_values($data),
            $this->body
        );
    }

    /**
     * @throws MailException
     */
    public function send(): bool
    {
        return $this->mailer->sendEmail(
            $this->toEmail,
            $this->toName,
            $this->subject,
            $this->body,
            $this->altBody
        );
    }

    public function setToName(string $toName): MailTemplate
    {
        $this->toName = $toName;
        return $this;
    }

    public function setToEmail(string $toEmail): MailTemplate
    {
        $this->toEmail = $toEmail;
        return $this;
    }

    public function setAltBody(string $altBody): MailTemplate
    {
        $this->altBody = $altBody;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getAltBody(): string
    {
        return $this->altBody;
    }
}
