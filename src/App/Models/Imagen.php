<?php

namespace Paw\App\Models;
use Exception;
use Paw\Core\Model;

class Imagen extends Model
{
    const UPLOADDIRECTORY = '../uploads/';
    const MAX_FILE_SIZE = 1 * 1024 * 1024;

    private $fileName;
    private $fileType;
    private $fileSize;
    private $fileTmpName;
    private $error;

    private $path_imagen;
    private $nombre_imagen;

    public function __construct($fileName, $fileType, $fileSize, $fileTmpName, $error)
    {   
        $this->fileName = $fileName;
        $this->fileType = $fileType;
        $this->fileSize = $fileSize;
        $this->fileTmpName = $fileTmpName;
        $this->error = $error;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function verificarTipoValido()
    {
        global $log;
        // Verificar el tipo de archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        
        if ($finfo === false) {
            return [
                'exito' => false,
                'description' => "No se pudo abrir el recurso de fileinfo."
            ];
        }

        if (!file_exists($this->fileTmpName) || !is_readable($this->fileTmpName)) {
            return [
                'exito' => false,
                'description' => "El archivo {$this->fileName} no existe o no se puede leer."
            ];
        }
        
        $fileMimeType = finfo_file($finfo, $this->fileTmpName);

        $this->fileTmpName = $fileMimeType;

        finfo_close($finfo);          

        if ($fileMimeType !== 'image/jpeg' && $fileMimeType !== 'image/png') {
            return [
                'exito' => self::ERROR_TIPO_NO_PERMITIDO,
                'description' => "El tipo de archivo $this->fileName no está permitido."
            ];
        }else{
            return [
                'exito' => true,
                'description' => "Validacion de TIPO del archivo $this->fileName (tipo: $this->fileTmpName), superada."
            ];
        }        
    }

    public function verificarTamanio()
    {

        if ($this->fileSize > self::MAX_FILE_SIZE) {
            return [
                'exito' => false,
                'description' => "El archivo $this->fileName no debe exceder {self::MAX_FILE_SIZE} bytes."
            ];
        }else{
            return [
                'exito' => true,
                'description' => "Validacion de TAMANIO del archivo $this->fileName (tamaño: $this->fileSize), superada."
            ];            
        }        
    }

    public function verificarErrorDeSubida()
    {
        if ($this->error != UPLOAD_ERR_OK) {
            return [
                'exito' => false,
                'description' => "El archivo $this->fileName tuvo un error de subida en el formulario."
            ];
        }else{
            return [
                'exito' => true,
                'description' => "Validacion de SUBIDA del archivo $this->fileName, superada."
            ];            
        }     
    }

    public function subirArchivo()
    {
        global $log;

        // Generar un nombre de archivo único para evitar colisiones
        $newFileName = uniqid() . "." . pathinfo($this->fileName, PATHINFO_EXTENSION);
        $uploadPath = self::UPLOADDIRECTORY . $newFileName;

        $log->info("uploadPath: ",[$uploadPath]);
        // Intentar mover el archivo subido al directorio de destino
        if (move_uploaded_file($this->fileTmpName, $uploadPath)) {
            $this->path_imagen = $uploadPath;
            $this->nombre_imagen = $newFileName;
            return [
                'exito' => true,
                'path_imagen' => $uploadPath,
                'nombre_imagen' => $newFileName
            ];
        } else {
            return [
                'exito' => false,
                'description' => "Error al subir el archivo $this->fileName."
            ];
        }
    }

    public function getMimeType($filePath) {
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