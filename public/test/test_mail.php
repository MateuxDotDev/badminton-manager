<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Mail\NovaSolicitacaoMail;
use App\Util\Mail\Mailer;
use App\Util\Exceptions\MailException;

try {
    $mail = (new NovaSolicitacaoMail(new Mailer()))
        ->setToName('Mateus Lucas')
        ->setToEmail('mateuxlucax@gmail.com')
        ->setAltBody('Você recebeu uma nova solicitação de Dupla!');

    $mail->fillTemplate([
        'assunto' => 'Você recebeu uma nova solicitação de Dupla!',
        'nome_tecnico' => 'Pedro Augusto',
        'convite_atleta' => 'Carlos Alberto',
        'convite_clube' => 'Clube A',
        'convite_tecnico' => 'Pedro Augusto',
        'convite_sexo' => 'Masculino',
        'convite_categoria' => 'Sub-21',
        'convite_modalidade' => 'Futebol',
        'convite_observacoes' => 'Observações do atela',
        'link_aceite' => 'https://matchpoint.mateux.dev',
        'link_recusa' => 'https://matchpoint.mateux.dev',
        'ano_atual' => date('Y'),
    ]);

    echo $mail->send() ? 'Email enviado com sucesso!' : 'Email não enviado!';
} catch (MailException $e) {
    echo $e;
}
