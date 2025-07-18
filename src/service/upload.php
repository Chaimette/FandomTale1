<?php

namespace App\Service;

use DateTime;

class Upload
{
    public function uploadFile($file): array|string
    {
        $errors = $this->validate($file);
        if (empty($errors)) {
            $fileName = (new DateTime())->format('Y-m-d-H-i-s') . '-' . uniqid() .
            "." . pathinfo($file['name'], PATHINFO_EXTENSION);
            $uploadFile = dirname(__DIR__, 2) . '/public/upload/' . basename($fileName);
            move_uploaded_file($file['tmp_name'], $uploadFile);
            return $fileName;
        }
        return $errors;
    }



    public function validate($file): array
    {
        $errors = [];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $authorizedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2000000;

        if ((file_exists($file['tmp_name']) && !in_array($extension, $authorizedExtensions))) {
            $errors[] = 'Veuillez sélectionner une image de type Jpg ou Jpeg ou Png ou Gif !';
        }

        if (file_exists($file['tmp_name']) && filesize($file['tmp_name']) > $maxFileSize) {
            $errors[] = "Votre fichier doit faire moins de 2Mo !";
        }

        if (file_exists($file['tmp_name']) && strlen($file['name']) + 20 > 100) {
            $errors[] = "Le nom de votre fichier doit faire moins de 50 caractères ";
        }

        return $errors;
    }
}
