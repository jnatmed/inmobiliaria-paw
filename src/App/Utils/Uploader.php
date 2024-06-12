<?php

namespace Paw\App\Utils;
use Exception;

class Uploader
{
    // src\App\Utils\Uploader.php
    // uploads\casa-foto-1.png
    const UPLOADDIRECTORY = '../uploads/';
    const UPLOAD_COMPLETED = 0;
    const ERROR_TIPO_NO_PERMITIDO = 1;
    const ERROR_TAMANIO_NO_PERMITIDO = 2;
    const ERROR_SUBIDA = 3;

    public static function uploadFile($file)
    {
        global $log;

        $log->info("file uploadFile: ", $file);

        $fileName = $file['name'];
        $fileType = $file['type'];
        $fileSize = $file['size'];
        $fileTmpName = $file['tmp_name'];

        // Verificar el tipo de archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMimeType = finfo_file($finfo, $fileTmpName);
        finfo_close($finfo);

        if ($fileMimeType !== 'image/jpeg' && $fileMimeType !== 'image/png') {
            return [
                'exito' => self::ERROR_TIPO_NO_PERMITIDO,
                'description' => "El tipo de archivo no está permitido."
            ];
        }

        // Verificar el tamaño del archivo
        $maxFileSize = 1 * 1024 * 1024; // 1 MB en bytes
        if ($fileSize > $maxFileSize) {
            return [
                'exito' => self::ERROR_TAMANIO_NO_PERMITIDO,
                'description' => "El archivo no debe exceder 1 MB."
            ];
        }

        // Generar un nombre de archivo único para evitar colisiones
        $newFileName = uniqid() . "." . pathinfo($fileName, PATHINFO_EXTENSION);
        $uploadPath = self::UPLOADDIRECTORY . $newFileName;

        $log->info("uploadPath: ",[$uploadPath]);
        // Intentar mover el archivo subido al directorio de destino
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            return [
                'exito' => self::UPLOAD_COMPLETED,
                'path_imagen' => $uploadPath,
                'nombre_imagen' => $newFileName
            ];
        } else {
            return [
                'exito' => self::ERROR_SUBIDA,
                'description' => "Error al subir el archivo."
            ];
        }
    }

    public static function getMimeType($filePath) {
        // Verificar si el archivo existe
        if (!file_exists(self::UPLOADDIRECTORY.$filePath)) {
            throw new Exception("El archivo no existe: $filePath");
        }

        // Obtener el tipo MIME del archivo
        $mimeType = mime_content_type(self::UPLOADDIRECTORY.$filePath);

        // Verificar si se pudo obtener el tipo MIME
        if ($mimeType === false) {
            throw new Exception("No se pudo determinar el tipo MIME del archivo: $filePath");
        }

        return $mimeType;
    }

}