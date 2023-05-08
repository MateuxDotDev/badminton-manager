<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Util\Mail\Mailer;
use App\Util\Exceptions\MailException;

$subject = 'Você recebeu uma nova solicitação de Dupla!';
$content = file_get_contents(__DIR__ . '/../../src/Util/Mail/Template/nova-solicitacao.html');
$content = str_replace('{{ assunto }}', $subject, $content);
$content = str_replace('{{ nome_tecnico }}', 'Pedro Augusto', $content);
$content = str_replace('{{ convite_atleta }}', 'Carlos Alberto', $content);
$content = str_replace('{{ convite_clube }}', 'Clube A', $content);
$content = str_replace('{{ convite_tecnico }}', 'Pedro Augusto', $content);
$content = str_replace('{{ convite_sexo }}', 'Masculino', $content);
$content = str_replace('{{ convite_categoria }}', 'Sub-21', $content);
$content = str_replace('{{ convite_modalidade }}', 'Futebol', $content);
$content = str_replace('{{ convite_observacoes }}', 'Observações do atela', $content);
$content = str_replace('{{ link_aceite }}', 'https://matchpoint.mateux.dev', $content);
$content = str_replace('{{ link_recusa }}', 'https://matchpoint.mateux.dev', $content);
$content = str_replace('{{ ano_atual }}', date('Y'), $content);

try {
    $mailer = new Mailer();
    $sentEmail = $mailer->sendEmail(
        'mateuxlucax@gmail.com',
        'Mateus Lucas',
        $subject,
        $content,
        'Você recebeu uma nova solicitação de Dupla!'
    );
    echo $sentEmail ? 'Email enviado com sucesso!' : 'Email não enviado!';
} catch (MailException $e) {
    echo $e;
}
