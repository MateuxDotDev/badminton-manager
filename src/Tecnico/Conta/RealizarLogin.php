<?php

namespace App\Tecnico\Conta;

use App\Tecnico\TecnicoRepository;

use App\Result;
use App\Session;
use Exception;

// implementa somente login quando técnico tem senha
// para técnico sem senha, requerindo e-mail de confirmação, é (será) feito num lugar diferente
readonly class RealizarLogin
{
    public function __construct(
        private TecnicoRepository $repo,
        private Session           $session,
    ) {}

    public function __invoke(LoginDTO $login): Result
    {
        $repo    = $this->repo;
        $session = $this->session;

        try {
            $tecnico = $repo->getViaEmail($login->email);
            if ($tecnico === null) {
                return Result::error('Técnico não encontrado');
            }
        } catch (Exception $e) {
            return Result::error($e);
        }

        $senha = $tecnico->senhaCriptografada();
        if ($senha === null) {
            return Result::error('Técnico não tem senha');
        }
        $ok = $senha->validar($login->email, $login->senha);
        if (!$ok) {
            return Result::error('Senha incorreta');
        }

        $session->setTecnico($tecnico);

        return Result::ok();
    }
}
