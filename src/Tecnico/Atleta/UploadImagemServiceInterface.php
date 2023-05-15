<?php

namespace App\Tecnico\Atleta;

interface UploadImagemServiceInterface
{
    function upload(array $file): bool;
}
