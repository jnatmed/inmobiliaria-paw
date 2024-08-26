<?php

namespace Paw\App\Models;
use Exception;
use Paw\Core\Model;
use Paw\App\Models\Imagen;

class ImagenCollection extends Model
{
    private $imagenesCollection = [];
    private $erroresCollection = [];
    private $erroresCollectionSubida = [];
    private $id_publicacion;
    private $id_usuario;

    public function __construct($imagenes)
    {   

        for ($i = 0; $i < count($imagenes['name']); $i++) 
        {
            if ($imagenes['name'][$i] != "") {
                $this->imagenesCollection[] = new Imagen(
                    $imagenes['name'][$i],
                    $imagenes['type'][$i],
                    $imagenes['tmp_name'][$i],
                    $imagenes['error'][$i],
                    $imagenes['size'][$i],
                );         
            }
        }        
    }

    public function getErroresCollection(){
        return $this->erroresCollection;
    }

    public function getErroresCollectionSubida()
    {
        return $this->erroresCollectionSubida;
    }

    public function getImagenesCollection()
    {
        return $this->imagenesCollection;
    }

    /**
     * verifico primero si las imagenes no tienen errores
     * e informo la lista de errores, si no las hay devuelvo
     * exito true, contrario false y la lista de errores
     */
    public function verificarCollectionImagenes()
    {
        foreach ($this->imagenesCollection as $imagen){

            $controlSubida = $imagen->verificarErrorDeSubida();
            if (!$controlSubida['exito']) {
                $this->erroresCollection[$imagen->getFileName()][] = $controlSubida['description'];
            }
            $controlTipo = $imagen->verificarTipoValido();
            if (!$controlTipo['exito']) {
                $this->erroresCollection[$imagen->getFileName()][] = $controlTipo['description'];
            }
            $controlTamanio = $imagen->verificarTamanio();
            if (!$controlTamanio['exito']) {
                $this->erroresCollection[$imagen->getFileName()][] = $controlTamanio['description'];
            }
        }  

        if(!empty($this->erroresCollection)){
            return [
                'exito' => true,
                'description' => "Imágenes Sin Errores Listas para ser subidas",
            ];
        } else {
            return [
                'exito' => false,
                'description' => "Una o más imágenes no pudieron ser subidas.",
                'errores' => $this->erroresCollection
            ];        
       }
    }
    /**
     * si las imagenes pasaron la etapa de verificacion
     * entonces procedo a subirlas
     */
    public function guardarImagenes($id_publicacion, $id_usuario)
    {
        foreach($this->imagenesCollection as $imagen){
            $controlUpload = $imagen->subirArchivo();
            if (!$controlUpload['exito']){
                $this->erroresCollectionSubida[$imagen->getFileName()][] = $controlUpload['description'];
            }else{
                $imagen->setIdPublicacion($id_publicacion);
                $imagen->setIdUsuario($id_usuario);    
            }
        }
       if(empty($this->erroresCollectionSubida)){
             return [
                'exito' => true,
                'description' => "Imagenes Subidas con exito",
            ];
        }else{
            return [
                'exito' => false,
                'description' => "No se pudo abrir el recurso de fileinfo.",
                'errores' => $this->erroresCollectionSubida
            ];            
        }
    }
}