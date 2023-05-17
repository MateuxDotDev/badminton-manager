<?php

namespace App\Util\Services\UploadImagemService;

use App\Util\Exceptions\ValidatorException;

class UploadImagemService implements UploadImagemServiceInterface
{
    private const MAX_FILE_SIZE_BYTES = 500000;
    private string $imagesFolder;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    public function __construct()
    {
        $this->imagesFolder = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/profile/';
    }

    /**
     * @throws ValidatorException
     */
    public function upload(array $file): string
    {
        $this->validate($file);

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $extension;
        $targetFile = $this->imagesFolder . $nomeImagem;

        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $nomeImagem;
        } else {
            throw new ValidatorException('Erro ao processar arquivo.');
        }
    }

    /**
     * @throws ValidatorException
     */
    private function validate(array $file): void
    {
        if (!isset($file["tmp_name"]) || !is_uploaded_file($file["tmp_name"])) {
            throw new ValidatorException('Arquivo inválido.');
        }

        if (getimagesize($file["tmp_name"]) === false) {
            throw new ValidatorException('Arquivo não é uma imagem.');
        }

        if ($file["size"] > self::MAX_FILE_SIZE_BYTES) {
            throw new ValidatorException('Arquivo muito grande. Limite: ' . self::MAX_FILE_SIZE_BYTES / 1024 . 'Kb.');
        }

        $targetFile = $this->imagesFolder . basename($file["name"]);
        if (!in_array(pathinfo($targetFile, PATHINFO_EXTENSION), self::ALLOWED_EXTENSIONS)) {
            throw new ValidatorException(
                'Formato de arquivo inválido. Permitidos' . implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }
    }

    public function removerImagem(string $nomeImagem): void
    {
        if ($nomeImagem == 'default.png') {
            return;
        }

        $targetFile = $this->imagesFolder . $nomeImagem;
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
    }
}
