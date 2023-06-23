<?php

namespace App\Notificacao;

interface NotificacaoRepositoryInterface
{
    function criar(Notificacao $notificacao): ?int;

    function getViaId1(int $id1, TipoNotificacao $tipo): array;
}
