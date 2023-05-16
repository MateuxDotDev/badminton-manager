<?php

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;

class UploadImagemService implements UploadImagemServiceInterface
{
    private const MAX_FILE_SIZE = 500000;
    private string $imagesFolder;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    private string $nomeImagem;

    public function __construct()
    {
        $this->imagesFolder = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/profile/';
    }

    /**
     * @throws ValidatorException
     */
    public function upload(array $file): bool
    {
        $this->validate($file);

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $this->nomeImagem = uniqid() . '.' . $extension;
        $targetFile = $this->imagesFolder . $this->nomeImagem;
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            if ($this->resizeImage($targetFile)) {
                return true;
            }
            throw new ValidatorException('Erro ao redimensionar imagem.');
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

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $this->nomeImagem = uniqid() . '.' . $extension;
        $targetFile = $this->imagesFolder . $this->nomeImagem;
        if (file_exists($targetFile)) {
            throw new ValidatorException('Arquivo já existe.');
        }

        if ($file["size"] > self::MAX_FILE_SIZE) {
            throw new ValidatorException('Arquivo muito grande. Limite: ' . self::MAX_FILE_SIZE / 1024 . 'Kb.');
        }

        $targetFile = $this->imagesFolder . basename($file["name"]);
        if (!in_array(pathinfo($targetFile, PATHINFO_EXTENSION), self::ALLOWED_EXTENSIONS)) {
            throw new ValidatorException(
                'Formato de arquivo inválido. Permitidos' . implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }
    }

    private function resizeImage($filename): bool
    {
        $maxWidth = 512;
        $maxHeight = 512;

        list($origWidth, $origHeight) = getimagesize($filename);

        $ratio = $origWidth / $origHeight;

        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }

        $maxWidth = round($maxWidth);
        $maxHeight = round($maxHeight);
        $imageResized = imagecreatetruecolor($maxWidth, $maxHeight);
        $imageTmp = imagecreatefromjpeg($filename);
        $resampled = imagecopyresampled(
            $imageResized,
            $imageTmp,
            0,
            0,
            0,
            0,
            $maxWidth,
            $maxHeight,
            $origWidth,
            $origHeight
        );

        if ($resampled) {
            imagejpeg($imageResized, $filename);
            return true;
        } else {
            return false;
        }
    }

    public function getNomeImagem(): string
    {
        return $this->nomeImagem;
    }

    public function removerImagem(): bool
    {
        $targetFile = $this->imagesFolder . $this->nomeImagem;
        if (file_exists($targetFile)) {
            return unlink($targetFile);
        }
        return false;
    }
}
