<?php

namespace App\Util\Mail\Service\Actions;

use App\Categorias\CategoriaRepository;
use App\Competicoes\CompeticaoRepository;
use App\Mail\EmailDTO;
use App\Mail\MailRepository;
use App\Mail\NovaSolicitacaoMail;
use App\Notificacao\Notificacao;
use App\Tecnico\Atleta\AtletaRepository;
use App\Tecnico\Solicitacao\SolicitacaoPendenteRepository;
use App\Tecnico\Tecnico;
use App\Tecnico\TecnicoRepository;
use App\Token\TokenRepository;
use App\Util\Environment\Environment;
use App\Util\Mail\Mailer;
use App\Util\Services\TokenService\AcoesToken;
use PDO;

class MailNovaSolicitacaoAction implements MailActionInterface
{
    public function enviarDeNotificacao(Notificacao $notificacao, PDO $pdo): void
    {
        $atletaRepo = new AtletaRepository($pdo);
        $tecnicoRepo = new TecnicoRepository($pdo);
        $solicitacaoRepo = new SolicitacaoPendenteRepository($pdo);
        $categoriaRepo = new CategoriaRepository($pdo);
        $competicoesRepo = new CompeticaoRepository($pdo);
        $mailRepo = new MailRepository($pdo);

        $solicitacao = $solicitacaoRepo->getViaId($notificacao->id1);

        $atletaDest = $atletaRepo->getViaId($solicitacao->idAtletaDestinatario);
        $atletaRem = $atletaRepo->getViaId($solicitacao->idAtletaRemetente);
        $tecnicoDest = $tecnicoRepo->getViaAtleta($atletaDest->id());
        $tecnicoRem = $tecnicoRepo->getViaAtleta($atletaRem->id());
        $categoria = $categoriaRepo->getById($solicitacao->idCategoria);
        $competicao = $competicoesRepo->getViaId($solicitacao->idCompeticao);

        $tokenAceitar = $this->gerarToken($pdo, $tecnicoDest, AcoesToken::ACEITAR_SOLICITACAO);
        $tokenRejeitar = $this->gerarToken($pdo, $tecnicoDest, AcoesToken::REJEITAR_SOLICITACAO);

        $baseUrl = Environment::getBaseUrl() . '/tecnico/solicitacoes/?solicitacao=' . $solicitacao->id;

        $mail = new NovaSolicitacaoMail(new Mailer());

        $mail->fillTemplate([
            'dest_tecnico' => $tecnicoDest->nomeCompleto(),
            'competicao' => $competicao->nome(),
            'rem_nome' => $atletaRem->nomeCompleto(),
            'dest_nome' => $atletaDest->nomeCompleto(),
            'dest_sexo' => $atletaDest->sexo()->toString(),
            'rem_sexo' => $atletaRem->sexo()->toString(),
            'dest_idade' => $atletaDest->idade(),
            'rem_idade' => $atletaRem->idade(),
            'dest_nascimento' => $atletaDest->dataNascimento()->format('d/m/Y'),
            'rem_nascimento' => $atletaRem->dataNascimento()->format('d/m/Y'),
            'dest_info' => $atletaDest->informacoesAdicionais(),
            'rem_info' => $atletaRem->informacoesAdicionais(),
            'categoria' => $categoria->descricao(),
            'observacoes' => $solicitacao->informacoes,
            'rem_tec_nome' => $tecnicoRem->nomeCompleto(),
            'rem_tec_clube' => $tecnicoRem->clube()->nome(),
            'rem_tec_info' => $tecnicoRem->informacoes(),
            'rem_tec_email' => $tecnicoRem->email(),
            'link_aceite' => $baseUrl . '&acao=aceitar&token=' . $tokenAceitar,
            'link_rejeicao' => $baseUrl . '&acao=rejeitar&token=' . $tokenRejeitar,
            'ano_atual' => date('Y'),
        ]);

        $mailDto = new EmailDTO(
            $tecnicoDest->nomeCompleto(),
            $tecnicoDest->email(),
            $mail->getSubject(),
            $mail->getBody(),
            $mail->getAltBody(),
            $notificacao->id,
        );

        $mailRepo->criar($mailDto);
    }

    private function gerarToken(PDO $pdo, Tecnico $tecnico, AcoesToken $acao): string
    {
        $tokenRepo = new TokenRepository($pdo);

        return $tokenRepo->createToken(
            7,
            10,
            ['acao' => $acao, 'tecnico' => json_encode(serialize($tecnico))]
        )['token'];
    }
}
