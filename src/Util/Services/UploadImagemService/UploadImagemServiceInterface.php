<?php

namespace App\Util\Services\UploadImagemService;

interface UploadImagemServiceInterface
{
    function upload(array $file): string;

    function removerImagem(string $nomeImagem): void;
}
