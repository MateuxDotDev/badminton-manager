<?php

namespace App\Util\Mail\Service\Actions;

use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Mail\SolicitacaoAceitaEnviadaMail;
use App\Mail\SolicitacaoAceitaRecebidaMail;
use App\Notificacao\Notificacao;
use App\Tecnico\Dupla\DuplaRepository;
use App\Tecnico\TecnicoRepository;
use App\Token\TokenRepository;
use App\Util\Environment\Environment;
use App\Util\General\Dates;
use App\Util\Mail\Mailer;
use App\Util\Services\TokenService\AcoesToken;
use PDO;

class MailSolicitacaoEnviadaRecebidaAction implements MailActionInterface
{
    public function enviarDeNotificacao(Notificacao $notificacao, PDO $pdo): void
    {
        $duplaRepo = new DuplaRepository($pdo);
        $tokenRepo = new TokenRepository($pdo);
        $tecnicoRepo = new TecnicoRepository($pdo);
        $mailRepo = new MailRepository($pdo);


        // FIX de última hora
        $sql = "SELECT categoria_id FROM solicitacao_dupla_concluida WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([ $notificacao->id1 ]);
        $categoria = $stmt->fetchColumn();


        $dupla = $duplaRepo->getViaAtletas($notificacao->id2, $notificacao->id3, $categoria);

        $atletaDest = $dupla->atletaFromTecnico($notificacao->idTecnico);
        $atletaRem = $dupla->other($atletaDest->id());

        $mail = new SolicitacaoAceitaRecebidaMail(
            new Mailer(),
            $atletaDest->nomeCompleto(),
            $atletaRem->nomeCompleto(),
            $dupla->competicao()->nome()
        );

        $token = $tokenRepo->createToken(
            7,
            10,
            [
                'acao' => AcoesToken::DESFAZER_DUPLA,
                'tecnico' => json_encode(serialize($tecnicoRepo->getViaAtleta($atletaDest->id()))),
            ]
        );

        $linkDesfazer = Environment::getBaseUrl() . '/tecnico/competicoes/atletas/duplas/?competicao=' . $dupla->competicao()->id() . '&acao=desfazer&dupla=' . $dupla->id() . '&token=' . $token['token'];

        $mail->fillTemplate([
            'dest_tecnico' => $atletaDest->tecnico()->nomeCompleto(),
            'dest_nome' => $atletaDest->nomeCompleto(),
            'rem_nome' => $atletaRem->nomeCompleto(),
            'competicao' => $dupla->competicao()->nome(),
            'dest_sexo' => $atletaDest->sexo()->toString(),
            'rem_sexo' => $atletaRem->sexo()->toString(),
            'dest_idade' => $atletaDest->idade(),
            'rem_idade' => $atletaRem->idade(),
            'dest_nascimento' => $atletaDest->dataNascimento()->format('d/m/Y'),
            'rem_nascimento' => $atletaRem->dataNascimento()->format('d/m/Y'),
            'dest_info' => $atletaDest->informacoesAdicionais(),
            'rem_info' => $atletaRem->informacoesAdicionais(),
            'categoria' => $dupla->categoria()->descricao(),
            'link_desfazer' => $linkDesfazer,
            'ano_atual' => Dates::currentYear(),
        ]);

        $mailDto = new EmailDTO(
            $atletaDest->tecnico()->nomeCompleto(),
            $atletaDest->tecnico()->email(),
            $mail->getSubject(),
            $mail->getBody(),
            $mail->getAltBody(),
            $notificacao->id
        );

        $mailRepo->criar($mailDto);
    }
}
