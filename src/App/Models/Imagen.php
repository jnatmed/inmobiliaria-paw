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
    private $id_publicacion;
    private $id_usuario;

    public function __construct(
                                $fileName, 
                                $fileType, 
                                $fileTmpName, 
                                $fileSize, 
                                $error
                                )
    {   
        global $log;

        $this->fileName = $fileName;
        $this->fileType = $fileType;
        $this->fileTmpName = $fileTmpName;
        $this->fileSize = $fileSize;
        $this->error = $error;

        $log->debug("this->name: ". $this->fileName);
        $log->debug("this->type: ". $this->fileType);
        $log->debug("this->tmpname: ". $this->fileTmpName);
        $log->debug("this->size: ". $this->fileSize);
        $log->debug("this->error: ". $this->error);
    }

    public function setIdPublicacion($id_publicacion)
    {
        $this->id_publicacion = $id_publicacion;
    }

    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function load() {
        return [
            'id_publicacion' => $this->id_publicacion,
            'path_imagen' => $this->path_imagen,
            'nombre_imagen' => $this->nombre_imagen,
            'id_usuario' => $this->id_usuario,
        ];
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

        // $log->debug("fileTmpName: ". $this->fileTmpName);
        if (!file_exists($this->fileTmpName) || !is_readable($this->fileTmpName)) {
            return [
                'exito' => false,
                'description' => "El archivo {$this->fileName} no existe o no se puede leer."
            ];
        }
        
        $fileMimeType = finfo_file($finfo, $this->fileTmpName);

        $this->fileType = $fileMimeType;

        finfo_close($finfo);          

        if ($fileMimeType !== 'image/jpeg' && $fileMimeType !== 'image/png') {
            return [
                'exito' => false,
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
                'description' => "El archivo $this->fileName no debe exceder ".self::MAX_FILE_SIZE." bytes."
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
        global $log;
        $log->debug("this->error: " . $this->error);
        if ($this->error !== UPLOAD_ERR_OK) {
            $mensajeError = $this->obtenerMensajeError($this->error); // Obtener un mensaje detallado basado en el código de error
            return [
                'exito' => false,
                'description' => "El archivo {$this->fileName} tuvo un error de subida: {$mensajeError}"
            ];
        } else {
            return [
                'exito' => true,
                'description' => "Validación de subida del archivo {$this->fileName} superada."
            ];
        }
    }
    
    private function obtenerMensajeError($codigoError)
    {
        switch ($codigoError) {
            case UPLOAD_ERR_INI_SIZE:
                return "El archivo excede el tamaño máximo permitido por la directiva upload_max_filesize en php.ini.";
            case UPLOAD_ERR_FORM_SIZE:
                return "El archivo excede el tamaño máximo permitido por la directiva MAX_FILE_SIZE especificada en el formulario HTML.";
            case UPLOAD_ERR_PARTIAL:
                return "El archivo se ha subido parcialmente.";
            case UPLOAD_ERR_NO_FILE:
                return "No se subió ningún archivo.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Falta una carpeta temporal.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Error al escribir el archivo en el disco.";
            case UPLOAD_ERR_EXTENSION:
                return "Una extensión de PHP detuvo la subida del archivo.";
            default:
                return "Error desconocido.";
        }
    }

    public function subirArchivo()
    {
        global $log;
    
        // Generar un nombre de archivo único para evitar colisiones
        $newFileName = uniqid() . "." . pathinfo($this->fileName, PATHINFO_EXTENSION);
        $uploadPath = self::UPLOADDIRECTORY . $newFileName;
    
        $log->info("fileName: ", [$this->fileName]);
        $log->info("uploadPath: ", [$uploadPath]);
        $log->info("newFileName: ", [$newFileName]);
        $log->info("fileTmpName: ", [$this->fileTmpName]);
    
        // Verificar si el archivo temporal existe
        if (!file_exists($this->fileTmpName)) {
            $log->error("El archivo temporal no existe: ", [$this->fileTmpName]);
            return [
                'exito' => false,
                'description' => "El archivo temporal no existe o no es accesible."
            ];
        }
    
        // Verificar si el directorio de destino tiene permisos de escritura
        if (!is_writable(self::UPLOADDIRECTORY)) {
            $log->error("El directorio de destino no tiene permisos de escritura: ", [self::UPLOADDIRECTORY]);
            return [
                'exito' => false,
                'description' => "El directorio de destino no tiene permisos de escritura."
            ];
        }
    
        // Intentar mover el archivo subido al directorio de destino
        if (move_uploaded_file($this->fileTmpName, $uploadPath)) {
            $this->path_imagen = $uploadPath;
            $this->nombre_imagen = $newFileName;
            $log->debug("Éxito de subida");
            return [
                'exito' => true,
                'path_imagen' => $newFileName,
                'nombre_imagen' => $newFileName
            ];
        } else {
            $error_message = error_get_last(); // Capturar el último error
            $log->error("Fallo de subida", ['error' => $error_message]);
            return [
                'exito' => false,
                'description' => "Error al subir el archivo $this->fileName. " . $error_message['message']
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

        // Extraer la extensión del tipo MIME
        $extension = substr(strrchr($mimeType, '/'), 1);

        return $extension;
    }

}