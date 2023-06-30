<?php

namespace App\Util\Mail\Service\Actions;

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Mail\SolicitacaoRejeitadaMail;
use App\Notificacao\Notificacao;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Tecnico\TecnicoRepository;
use App\Util\General\Dates;
use App\Util\Mail\Mailer;
use PDO;

class MailSolicitacaoRejeitadaAction implements MailActionInterface
{
    public function enviarDeNotificacao(Notificacao $notificacao, PDO $pdo): void
    {
        $mailRepo = new MailRepository($pdo);
        $tecnicoRepo = new TecnicoRepository($pdo);
        $atletaRepo = new AtletaRepository($pdo);
        $categoriaRepo = new CategoriaRepository($pdo);
        $competicaoRepo = new CompeticaoRepository($pdo);
        $solicitacaoRepo = new SolicitacaoConcluidaRepository($pdo);

        $solicitacao = $solicitacaoRepo->getViaId($notificacao->id1);

        $atletaDest = $atletaRepo->getViaId($solicitacao->atletaDestinoId());
        $atletaRem = $atletaRepo->getViaId($solicitacao->atletaOrigemId());
        $tecnicoDest = $tecnicoRepo->getViaAtleta($atletaDest->id());
        $categoria = $categoriaRepo->getById($solicitacao->categoriaId());
        $competicao = $competicaoRepo->getViaId($solicitacao->competicaoId());

        $mail = new SolicitacaoRejeitadaMail(new Mailer());

        $mail->fillTemplate([
            'dest_tecnico' => $tecnicoDest->nomeCompleto(),
            'dest_nome' => $atletaDest->nomeCompleto(),
            'rem_nome' => $atletaRem->nomeCompleto(),
            'competicao' => $competicao->nome(),
            'dest_sexo' => $atletaDest->sexo()->toString(),
            'rem_sexo' => $atletaRem->sexo()->toString(),
            'dest_idade' => Dates::age($atletaDest->dataNascimento()),
            'rem_idade' => Dates::age($atletaRem->dataNascimento()),
            'dest_nascimento' => Dates::formatDayBr($atletaDest->dataNascimento()),
            'rem_nascimento' => Dates::formatDayBr($atletaRem->dataNascimento()),
            'dest_info' => $atletaDest->informacoesAdicionais(),
            'rem_info' => $atletaRem->informacoesAdicionais(),
            'categoria' => $categoria->descricao(),
            'observacoes' => $solicitacao->informacoes(),
            'ano_atual' => Dates::currentYear(),
        ]);

        $mailDto = new EmailDTO(
            $tecnicoDest->nomeCompleto(),
            $tecnicoDest->email(),
            $mail->getSubject(),
            $mail->getBody(),
            $mail->getAltBody(),
            $notificacao->id
        );

        $mailRepo->criar($mailDto);
    }
}
