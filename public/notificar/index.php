<?php

require_once __DIR__ . '/.././../vendor/autoload.php';

use App\Mail\MailRepository;
use App\Util\Database\Connection;
use App\Util\Environment\Environment;
use App\Util\Exceptions\MailException;
use App\Util\Http\HttpStatus;
use App\Util\Http\Response;
use App\Util\Mail\Mailer;

$token = $_GET['token'] ?? null;

$horario = [
    'horario' => date('Y-m-d H:i:s')
];

if (!$token) {
    $response = new Response(
        HttpStatus::BAD_REQUEST,
        'Token não informado',
        $horario
    );
    $response->enviar();
} elseif ($token != Environment::getCronToken()) {
    $response = new Response(
        HttpStatus::UNAUTHORIZED,
        'Token inválido',
        ['token' => $token, ...$horario]
    );
    $response->enviar();
}

try {
    $pdo = Connection::getInstance();
    $pdo->beginTransaction();
    $repo = new MailRepository($pdo);
    $emails = $repo->ativas();

    if (empty($emails)) {
        $response = new Response(
            HttpStatus::OK,
            'Nenhum email a ser enviado',
            $horario
        );
        $response->enviar();
    }
    $enviadas = [];
    $erros = [];
    $mailer = new Mailer();

    foreach ($emails as $email) {
        try {
            if ($mailer->sendEmail(
                $email->toEmail,
                $email->toName,
                $email->subject,
                $email->body,
                $email->altBody
            )) {
                $enviadas[] = $email->idNotificacao;
            }
        } catch (MailException $e) {
            $erros[] = $email->idNotificacao;
        }
    }

    $totalEnviadas = $repo->enviadas($enviadas);

    $response = new Response(
        empty($erros) ? HttpStatus::OK : HttpStatus::INTERNAL_SERVER_ERROR,
        "Envio de email concluído.",
        [
            'enviadas' => [
                'total' => $totalEnviadas,
                'ids' => $enviadas
            ],
            'erros' => $erros,
            ...$horario
        ]
    );
    $pdo->commit();
    $response->enviar();
} catch (Throwable $th) {
    $pdo->rollBack();
    $response = new Response(
        HttpStatus::INTERNAL_SERVER_ERROR,
        'Erro ao enviar email',
        [
            'erro' => $th->getMessage(),
            ...$horario
        ]
    );
    $response->enviar();
}
