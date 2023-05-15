<?php

namespace App\Util\Mail;

use App\Util\Environment\Environment;
use App\Util\Exceptions\MailException;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer implements MailerInterface
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer();

        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = Environment::getMailUsername();
        $this->mail->Password   = Environment::getMailPassword();
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        $this->mail->encodeHeader('UTF-8');
        $this->mail->CharSet = 'UTF-8';
    }

    /**
     * @throws MailException
     */
    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        string $altBody = '',
        bool $isHTML = true
    ) : bool {
        try {
            $this->mail->setFrom($this->mail->Username, 'Suporte | MatchPoint');
            $this->mail->addAddress($toEmail, $toEmail);

            $this->mail->isHTML($isHTML);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody;

            $this->mail->send();
            return true;
        } catch (Exception $exception) {
            throw new MailException(
                $exception,
                [
                    'from' => $this->mail->Username,
                    'to' => "{$toName} <{$toEmail}>",
                    'subject' => $subject,
                    'body' => $body,
                    'altBody' => $altBody,
                    'isHTML' => $isHTML,
                    'mailerError' => $this->mail->ErrorInfo
                ],
            );
        }
    }
}
