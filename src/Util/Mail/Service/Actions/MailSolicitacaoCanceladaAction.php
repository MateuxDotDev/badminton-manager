<?php

namespace App\Util\Mail\Service\Actions;

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Mail\SolicitacaoCanceladaMail;
use App\Notificacao\Notificacao;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Solicitacao\SolicitacaoConcluidaRepository;
use App\Util\Mail\Mailer;
use Exception;
use PDO;

class MailSolicitacaoCanceladaAction implements MailActionInterface
{
    /**
     * @throws Exception
     */
    public function enviarDeNotificacao(Notificacao $notificacao, PDO $pdo): void
    {
        $solicitacaoRepo = new SolicitacaoConcluidaRepository($pdo);
        $atletaRepo = new AtletaRepository($pdo);
        $competicaoRepo = new CompeticaoRepository($pdo);
        $categoriaRepo = new CategoriaRepository($pdo);

        $solicitacao = $solicitacaoRepo->getViaId($notificacao->id1);

        $competicao = $competicaoRepo->getViaId($solicitacao->competicaoId());

        $atletasSolicitacao = [];
        $atletasSolicitacao[] = $atletaRepo->getViaId($solicitacao->atletaOrigemId());
        $atletasSolicitacao[] = $atletaRepo->getViaId($solicitacao->atletaDestinoId());

        foreach ($atletasSolicitacao as $atletaSolicitacao) {
            if ($atletaSolicitacao->tecnico()->id() == $notificacao->idTecnico) {
                $atletaDest = $atletaSolicitacao;
            } else {
                $atletaRem = $atletaSolicitacao;
            }
        }

        $tecnicoDest = $atletaDest->tecnico();
        $categoria = $categoriaRepo->getById($solicitacao->categoriaId());

        $mail = new SolicitacaoCanceladaMail(
            new Mailer(),
            $atletaDest->nomeCompleto(),
            $atletaRem->nomeCompleto(),
            $competicao->nome()
        );

        $mail->fillTemplate([
            'dest_tecnico' => $tecnicoDest->nomeCompleto(),
            'dest_nome' => $atletaDest->nomeCompleto(),
            'rem_nome' => $atletaRem->nomeCompleto(),
            'competicao' => $competicao->nome(),
            'dest_sexo' => $atletaDest->sexo()->toString(),
            'rem_sexo' => $atletaRem->sexo()->toString(),
            'dest_idade' => $atletaDest->idade(),
            'rem_idade' => $atletaRem->idade(),
            'dest_nascimento' => $atletaDest->dataNascimento()->format('d/m/Y'),
            'rem_nascimento' => $atletaRem->dataNascimento()->format('d/m/Y'),
            'dest_info' => $atletaDest->informacoesAdicionais(),
            'rem_info' => $atletaRem->informacoesAdicionais(),
            'categoria' => $categoria->descricao(),
            'observacoes' => $solicitacao->informacoes(),
            'ano_atual' => date('Y'),
        ]);

        $emailDto = new EmailDTO(
            $tecnicoDest->nomeCompleto(),
            $tecnicoDest->email(),
            $mail->getSubject(),
            $mail->getBody(),
            $mail->getAltBody(),
            $notificacao->id,
        );

        $mailRepo = new MailRepository($pdo);
        $mailRepo->criar($emailDto);
    }
}
