<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Mail\InclusaoCompeticaoMail;
use App\Token\TokenRepository;
use App\Util\Database\Connection;
use App\Util\Exceptions\ResponseException;
use App\Util\Exceptions\ValidatorException;
use App\Util\Mail\Mailer;
use App\Util\Exceptions\MailException;
use App\Util\Services\TokenService\TokenService;

try {
    $mail = (new InclusaoCompeticaoMail(new Mailer(), 'João da Silva', 'Campeonato Rio Sulista'))
        ->setToName('Mateus Lucas')
        ->setToEmail('mateuxlucax@gmail.com')
        ->setAltBody('Você recentemente incluiu um novo atleta em uma competição!');

    $tokenRepo = new TokenRepository(Connection::getInstance(), new TokenService());

    $tokenAlterar = $tokenRepo->createToken(
        999,
        999,
        [
            'acao' => 'alterarAtleta',
            'tecnico' => [
                'id' => 1,
                'nome' => 'Mateus Lucas',
            ]
        ]
    );

    $tokenExcluir = $tokenRepo->createToken(
        999,
        999,
        [
            'acao' => 'removerAtleta',
            'tecnico' => [
                'id' => 1,
                'nome' => 'Mateus Lucas',
            ]
        ]
    );

    $baseUrl = $tokenAlterar['decodedToken']->iss;

    $mail->fillTemplate([
        'nome_tecnico' => 'Mateus Lucas',
        'nome_atleta' => 'João da Silva',
        'nome_competicao' => 'Campeonato de Futebol',
        'convite_atleta' => 'Carlos Alberto',
        'convite_clube' => 'Clube Zacarias',
        'convite_tecnico' => 'Bruno Henrique',
        'convite_sexo' => 'Masculino',
        'convite_observacoes' => 'Nenhuma observação',
        'link_alterar' =>  $baseUrl . '/tecnico/atletas/?id=2&acao=alterar&token=' . $tokenAlterar['token'],
        'link_remover' => $baseUrl . '/tecnico/atletas/?id=2&acao=remover&token=' . $tokenExcluir['token'],
        'link_buscar' => 'http://localhost:8080/tecnico/competicoes/',
        'ano_atual' => date('Y')
    ]);

    echo $mail->send() ? 'Email enviado com sucesso!' : 'Email não enviado!';
} catch (MailException|ResponseException|ValidatorException $e) {
    echo $e;
}
