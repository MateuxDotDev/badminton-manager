<?php

namespace App\Tecnico\Conta;

use App\Tecnico\TecnicoRepository;
use App\Util\Exceptions\ValidatorException;
use App\Util\General\Result;
use App\Util\General\UserSession;
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

    public function __invoke(LoginDTO $login): Result
    {
        try {
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
            return Result::ok();
        } catch (ValidatorException $e) {
            return Result::error($e->getMessage());
        } catch (Exception $e) {
            return Result::error($e);
        }
    }
}
