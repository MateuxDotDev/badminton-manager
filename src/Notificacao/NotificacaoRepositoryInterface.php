<?php

namespace App\Notificacao;

interface NotificacaoRepositoryInterface
{
    function criar(Notificacao $notificacao): ?int;
}
