<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Util\Mail\Mailer;
use App\Util\Exceptions\MailException;

$content = file_get_contents(__DIR__ . '/mail-template.html');

try {
    $mailer = new Mailer();
    $sentEmail = $mailer->sendEmail('mateuxlucax@gmail.com', 'Mateus Lucas', 'Você recebeu uma nova solicitação de Dupla!', $content, 'Você recebeu uma nova solicitação de Dupla!', true);
    echo $sentEmail ? 'Email enviado com sucesso!' : 'Email não enviado!';
} catch (MailException $e) {
    echo $e;
}
