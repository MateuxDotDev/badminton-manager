<?php

namespace App\Tecnico\Atleta;

use App\Util\Exceptions\ValidatorException;

class UploadImagemService implements UploadImagemServiceInterface
{
    private const MAX_FILE_SIZE = 500000;
    private const IMAGES_FOLDER = 'assets/images/profile/';
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @throws ValidatorException
     */
    public function upload(array $file): bool
    {
        $this->validate($file);

        $targetFile = self::IMAGES_FOLDER . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
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
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new ValidatorException('Arquivo inválido.');
        }

        if (getimagesize($_FILES["image"]["tmp_name"]) === false) {
            throw new ValidatorException('Arquivo não é uma imagem.');
        }

        if (file_exists($targetFile)) {
            throw new ValidatorException('Arquivo já existe.');
        }

        if ($_FILES["image"]["size"] > self::MAX_FILE_SIZE) {
            throw new ValidatorException('Arquivo muito grande. Limite: ' . self::MAX_FILE_SIZE / 1024 . 'Kb.');
        }

        $targetFile = self::IMAGES_FOLDER . basename($_FILES["image"]["name"]);
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

        $imageResized = imagecreatetruecolor($maxWidth, $maxHeight);
        $imageTmp = imagecreatefromjpeg($filename);
        imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $maxWidth, $maxHeight, $origWidth, $origHeight);

        return $imageResized;
    }
}
