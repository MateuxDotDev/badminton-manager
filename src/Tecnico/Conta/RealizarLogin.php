<?php

namespace App\Tecnico\Conta;

use App\Tecnico\TecnicoRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use App\Util\Http\HttpStatus;
use Exception;

/**
 * Implementa somente login quando técnico tem senha
 * Para técnico sem senha, requerendo e-mail de confirmação, é (será) feito num lugar diferente
 */
readonly class RealizarLogin
{
    public function __construct(
        private TecnicoRepository $repo,
        private UserSession       $session,
    ) {}

    /**
     * @throws Exception
     */
    public function __invoke(LoginDTO $login): void
    {
        $tecnico = $this->repo->getViaEmail($login->email);
        if ($tecnico === null) {
            throw new ValidatorException('Técnico não encontrado', HttpStatus::NOT_FOUND);
        }

        $senha = $tecnico->senhaCriptografada();
        if ($senha === null) {
            throw new ValidatorException('Técnico não tem senha', HttpStatus::FORBIDDEN);
        }
        $ok = $senha->validar($login->email, $login->senha);
        if (!$ok) {
            throw new ValidatorException('Senha incorreta', HttpStatus::UNAUTHORIZED);
        }

        $this->session->setTecnico($tecnico);
    }
}
