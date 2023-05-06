<?php

namespace App\Tecnico\Conta;

use App\Tecnico\TecnicoRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\UserSession;
use Exception;

// TODO copiar classe com códigos HTTP de algum lugar
// para não termos que ficar escrevendo 401 mas Http::UNAUTHORIZED etc.

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
            throw new ValidatorException('Técnico não encontrado', 404);
        }

        $senha = $tecnico->senhaCriptografada();
        if ($senha === null) {
            throw new ValidatorException('Técnico não tem senha', 403);
        }
        $ok = $senha->validar($login->email, $login->senha);
        if (!$ok) {
            throw new ValidatorException('Senha incorreta', 401);
        }

        $this->session->setTecnico($tecnico);
    }
}
