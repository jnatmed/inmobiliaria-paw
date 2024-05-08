<?php

namespace Paw\App\Utils;

class Uploader
{
    public string $path_promociones = '/';
    const ERROR_TIPO_NO_PERMITIDO = false;
    const ERROR_TAMANIO_NO_PERMITIDO = false;
    const ERROR_AL_SUBIR_ARCHIVO = false;
    const ERROR_DE_CARGA = false;
    const UPLOAD_COMPLETED = true;
    const UPLOADDIRECTORY = '../uploads/';

    public function verificar_imagen($archivo_imagen, $newPlato){

        global $log;
        // Verifica si el archivo se ha subido correctamente

        if (isset($archivo_imagen['imagen_plato']) && $archivo_imagen['imagen_plato']['error'] === UPLOAD_ERR_OK) {
            // Obtén información sobre el archivo subido
            $log->info("fileSize: " , [$archivo_imagen['imagen_plato'], $archivo_imagen['imagen_plato']['error']]);
            $file = $archivo_imagen['imagen_plato'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileTmpName = $file['tmp_name'];
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $fileTmpName);
            finfo_close($finfo);
            
            $log->info("fileSize: " . $fileSize);
            $log->info("fileSize: " . $fileType);
            // Verifica el tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png'];
            if (!in_array($fileType, $allowedTypes)) {
                return [
                    'exito' => self::ERROR_TIPO_NO_PERMITIDO,
                    'description' => "El archivo debe ser una imagen JPEG o PNG."
                ];
            }
            
            // Verifica el tamaño del archivo (no mayor a 1 MB)
            $maxFileSize = 1 * 1024 * 1024; // 1 MB en bytes
            if ($fileSize > $maxFileSize) {
                return [
                    'exito' => self::ERROR_TAMANIO_NO_PERMITIDO,
                    'description' => "El archivo no debe exceder 1 MB."
                ];
            }
            
            // Si todo es correcto, guarda el archivo en el servidor
            // Establece el directorio donde se guardará el archivo

            // Genera un nombre de archivo único para evitar colisiones
            $newFileName = uniqid().".".pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadPath = self::UPLOADDIRECTORY . $newFileName;

            $newPlato->setPathImg($newFileName);
            
            // Mueve el archivo del directorio temporal a su ubicación final
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                return [
                    'exito' => self::UPLOAD_COMPLETED,
                    'description' => "El archivo se ha subido correctamente.",
                    'path_imagen' => $uploadPath,
                    'nombreArchivo' => $newFileName,
                    'nombre_comida' => $newPlato->getNombrePlato(), 
                    'ingredientes_comida' => $newPlato->getIngredientes(),
                    'tipo_plato' => $newPlato->getTipoPlato()
                ];                
            } else {
                return [
                    'exito' => self::ERROR_AL_SUBIR_ARCHIVO,
                    'description' => "Hubo un problema al subir el archivo."
                ];                
            }
        } else {
            return [
                'exito' => self::ERROR_DE_CARGA,
                'description' => "No se ha subido ningún archivo o ocurrió un error."
            ];            
        }        
    }



}