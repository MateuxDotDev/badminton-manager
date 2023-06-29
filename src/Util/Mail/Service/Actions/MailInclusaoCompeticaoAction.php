<?php

namespace App\Util\Mail\Service\Actions;

use App\Mail\EmailDTO;
use App\Mail\InclusaoCompeticaoMail;
use App\Mail\MailRepository;
use App\Notificacao\Notificacao;
use App\Notificacao\NotificacaoRepository;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepository;
use App\Token\TokenRepository;
use App\Util\Environment\Environment;
use App\Util\Exceptions\ValidatorException;
use App\Util\Mail\Mailer;
use App\Util\Services\TokenService\AcoesToken;
use PDO;

class MailInclusaoCompeticaoAction implements MailActionInterface
{
    function enviarDeNotificacao(Notificacao $notificacao, PDO $pdo): void
    {
        $competicao = $dados->competicao();
        $atleta = $dados->atleta();

        $tecnicoRepo = new TecnicoRepository($pdo);
        $tecnico = $tecnicoRepo->getViaAtleta($atleta->id());

        $mail = (new InclusaoCompeticaoMail(new Mailer(), $atleta->nomeCompleto(), $competicao->nome()))
            ->setToName($tecnico->nomeCompleto())
            ->setToEmail($tecnico->email())
            ->setAltBody('Você recentemente incluiu um novo atleta em uma competição!');

        $tokenAlterar = gerarToken($pdo, $tecnico, AcoesToken::ALTERAR_ATLETA->value)['token'];
        $tokenRemover = gerarToken($pdo, $tecnico, AcoesToken::REMOVER_ATLETA->value)['token'];

        $baseUrl = Environment::getBaseUrl();
        $linkAlterar = sprintf('%s/tecnico/atletas/?id=%d&acao=alterar&token=%s', $baseUrl, $atleta->id(), $tokenAlterar);
        $linkRemover = sprintf('%s/tecnico/atletas/?id=%d&acao=remover&token=%s', $baseUrl, $atleta->id(), $tokenRemover);
        $linkBuscar = sprintf('%s/tecnico/competicoes/atletas/?competicao=%d&atleta=%d', $baseUrl, $competicao->id(), $atleta->id());

        $mail->fillTemplate([
            'nome_tecnico' => $tecnico->nomeCompleto(),
            'nome_atleta' => $atleta->nomeCompleto(),
            'nome_competicao' => $competicao->nome(),
            'nome_clube' => $tecnico->clube()->nome(),
            'atleta_sexo' => $atleta->sexo()->toString(),
            'atleta_observacoes' => $atleta->informacoesAdicionais(),
            'link_alterar' =>  $linkAlterar,
            'link_remover' => $linkRemover,
            'link_buscar' => $linkBuscar,
            'ano_atual' => date('Y')
        ]);

        $mailRepo = new MailRepository($pdo);

        $emailDto = new EmailDTO(
            $tecnico->nomeCompleto(),
            $tecnico->email(),
            $mail->getSubject(),
            $mail->getBody(),
            $mail->getAltBody(),
            $notificacao->id
        );

        $mailRepo->criar($emailDto);
    }

    public function gerarToken(PDO $pdo, Tecnico $tecnico, string $acao): array
    {
        $tokenRepo = new TokenRepository($pdo);

        return $tokenRepo->createToken(
            7,
            10,
            ['acao' => $acao, 'tecnico' => json_encode(serialize($tecnico))]
        );
    }
}
