<?php

namespace Paw\App\Controllers;


use Paw\Core\Controller;
use Paw\App\Utils\Uploader;
use Paw\App\Utils\Utils;
use Paw\App\Models\PublicacionCollection;
use Paw\App\Models\ReservasCollection;
use Paw\App\Utils\Verificador;
use Paw\App\Models\Mailer;
use Paw\App\Models\Publicacion;
use Paw\App\Models\Imagen;
use Paw\App\Models\ImagenCollection;

use PDOException;
use Throwable;
use Exception;

class PublicacionController extends Controller
{
    public ?string $modelName = PublicacionCollection::class;
    public $usuario;
    public Verificador $verificador;
    public Uploader $uploader;
    public $utils;
    public $mailer;
    public $menuAndSession;
    public ReservasCollection $ReservasCollection;

    private const PUBLICACIONES_POR_PAGINA = 6;

    public function __construct()
    {
        global $config, $log;
        parent::__construct();

        $this->uploader = new Uploader;
        $this->verificador = new Verificador;
        $this->utils = new  Utils();
        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);
        $this->ReservasCollection = new ReservasCollection();
        $this->ReservasCollection->setQueryBuilder($this->qb);

        $this->usuario = new UsuarioController();
        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;
    }

    private function obtenerFiltrosListado(){

        /*Zona debe ser texto*/
        $zonaRecibida = $this->request->query('zona');

        $zona = is_string($zonaRecibida) ? trim($zonaRecibida) : null;

        $zona = $zona === '' ? null : $zona;

        /*Los tipos pueden llegar como tipo[]=1, tipo[]=2*/
        $tiposRecibidos = $this->request->query('tipo', []);

        if (!is_array($tiposRecibidos)) {
            $tiposRecibidos = [$tiposRecibidos];
        }

        $tipos = [];

        foreach ($tiposRecibidos as $tipoRecibido) {
            if (is_array($tipoRecibido)) {
                continue;
            }

            $tipoValidado = filter_var(
                $tipoRecibido,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($tipoValidado !== false) {
                $tipos[] = (int) $tipoValidado;
            }
        }

        $tipos = array_values(array_unique($tipos));

        /*El precio debe ser un entero positivo*/
        $precioRecibido = $this->request->query('precio');

        $precio = null;

        if (!is_array($precioRecibido) && $precioRecibido !== null && $precioRecibido !== '') {

            $precioValidado = filter_var(
                $precioRecibido,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($precioValidado !== false) {
                $precio = (int) $precioValidado;
            }
        }

        /*Instalaciones usa una lista blanca.Solo se aceptan esos cuatro nombres*/
        $instalacionesRecibidas = $this->request->query('instalaciones', []);

        if (!is_array($instalacionesRecibidas)) {
            $instalacionesRecibidas = [$instalacionesRecibidas];
        }

        $instalacionesPermitidas = [
            'cochera',
            'pileta',
            'aire_acondicionado',
            'wifi'
        ];

        $instalacionesRecibidas = array_filter(
            $instalacionesRecibidas,
            function ($instalacion) {
                return is_string($instalacion);
            }
        );

        $instalaciones = array_values(array_unique(array_intersect($instalacionesRecibidas, $instalacionesPermitidas)));

        return [
            'zona' => $zona,
            'tipos' => $tipos,
            'precio' => $precio,
            'instalaciones' => $instalaciones
        ];
    }

    private function obtenerPaginaSolicitada(){

        $paginaRecibida = $this->request->query('pagina', 1);

        /*Evita valores como pagina[]=2*/
        if (is_array($paginaRecibida)) {
            return 1;
        }

        $pagina = filter_var(
            $paginaRecibida,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        /*Si se recibe una pagina invalida se utiliza la pagina 1*/
        return $pagina === false ? 1 : (int) $pagina;
    }

    public function list(){

        try {
            
            $filtros = $this->obtenerFiltrosListado();

            $paginaSolicitada = $this->obtenerPaginaSolicitada();

            $porPagina = self::PUBLICACIONES_POR_PAGINA;

            /*Primero se cuentan cuantas propiedades cumplen los filtros*/
            $cantidadResultadosFiltrados = $this->model
                ->getCantidadPublicacionesFiltradas(
                    $filtros['zona'],
                    $filtros['tipos'],
                    $filtros['precio'],
                    $filtros['instalaciones'],
                    null
                );

            $totalPaginas = max(
                1,
                (int) ceil($cantidadResultadosFiltrados / $porPagina)
            );

            /*Si alguien solicita una pagina que no existe se utiliza la ultima pag existente*/
            $paginaActual = min($paginaSolicitada, $totalPaginas);

            $offset = ($paginaActual - 1) * $porPagina;

            $publicaciones = $this->model
                ->getAllFilter(
                    $filtros['zona'],
                    $filtros['tipos'],
                    $filtros['precio'],
                    $filtros['instalaciones'],
                    null,
                    $porPagina,
                    $offset
                );

            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales();

            $mayorPrecio = $this->model->getPublicacionMayorPrecio();

            $datos = [

                'zona' => $filtros['zona'],
                'tipos' => $filtros['tipos'],
                'precio' => $filtros['precio'],
                'instalaciones' => $filtros['instalaciones'],
                'mayorPrecio' => $mayorPrecio,
                'publicaciones' => $publicaciones,
                'cantidadMostrada' => count($publicaciones),
                'cantidadResultadosFiltrados' => $cantidadResultadosFiltrados,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'paginaActual' => $paginaActual,
                'totalPaginas' => $totalPaginas,
                'porPagina' => $porPagina,
                'rutaListado' => '/publicaciones/list',

                'id_usuario' => $this->menuAndSession['id_usuario'] ?? null,
                'titulo' => 'PAWPERTIES | PROPIEDADES',
                'subtitulo' => 'Propiedades en Alquiler'

            ];

            if ($this->request->isAjaxRequest()) {
                view(
                    'parts/lista-publicaciones.view',
                    $datos
                );

                return;
            }

            view(
                'publicaciones.list.view',
                array_merge(
                    $datos,
                    $this->menuAndSession,
                    $this->model->traerTipos()
                )
            );

        } catch (Throwable $e) {

            $error_message = 'Error al obtener las publicaciones: ' . $e->getMessage();

            $this->logger->error($error_message);

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message' => 'No se pudo cargar el listado de propiedades.'],
                    $this->menuAndSession
                )
            );
        }
    }

    public function verPublicacion(){

        $errores = [];

        //El ID llega por la URL: /publicacion/ver?id_pub=2 Por eso se usa query() y se comprueba que sea un entero positivo
        
        $idPublicacion = $this->verificador->entero(
            $this->request->query('id_pub'),
            'id_pub',
            $errores,
            1
        );


        if ($idPublicacion === null) {
            http_response_code(400);

            view(
                'errors/bads-request.view',
                array_merge(
                    ['error_message' => 'El identificador de la publicación no es válido.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $publicacion = $this->model->getOne($idPublicacion);

        //El ID tiene formato válido, pero la publicación no existe

        if (!$publicacion) {
            http_response_code(404);

            $this->logger->info(
                'Publicación no encontrada.',
                ['publicacion_id' => $idPublicacion]
            );

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' => 'La publicación solicitada no existe.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        //Una publicacion pendiente o rechazada solo puede ser vista por el usaurio que la creo o un empleado moderador

        $usuarioActual = (int) ($this->usuario->getUserId() ?? 0);

        $tipoUsuario = $this->usuario->getUserType();

        $esDuenio = $usuarioActual > 0 && $usuarioActual === (int) $publicacion['id_usuario'];

        $esEmpleado = $tipoUsuario === 2;

        $estaAceptada = (int) $publicacion['estado_id'] === 2;

        if (!$estaAceptada && !$esDuenio && !$esEmpleado) {
            http_response_code(404);

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' =>'La publicación solicitada no está disponible.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $reservas = $this->ReservasCollection->getReservas($idPublicacion);

        $comentarios = $this->model->getComentarios($idPublicacion);

        $periodosJson = json_encode(
            $reservas,
            JSON_UNESCAPED_SLASHES
        );

        //Recuperar los mensajes temporales antes de eliminarlos de la sesión
        
        $resultadoReserva = $this->request->getResultadoGuardado('resultadoReserva');

        $resultadoComentario = $this->request->getResultadoGuardado('resultadoComentario');

        $resultadoContacto = $this->request->getResultadoGuardado('resultadoContacto');

        $datos = [
            'publicacion' => $publicacion,
            'idUserSesion' => $this->usuario->getUserId(),
            'periodos_json' => $periodosJson,
            'reservas' => $reservas,
            'titulo' => 'PAWPERTIES | PROPIEDAD',
            'comentarios' => $comentarios,
            'resultadoReserva' => $resultadoReserva,
            'resultadoComentario' => $resultadoComentario,
            'resultadoContacto' => $resultadoContacto
        ];

        //Los mensajes se muestran una sola vez

        $this->request->eliminarResultadoEnSesion('resultadoReserva');

        $this->request->eliminarResultadoEnSesion('resultadoComentario');

        $this->request->eliminarResultadoEnSesion('resultadoContacto');

        view(
            'publicacion.details.view',
            array_merge(
                $datos,
                $this->menuAndSession
            )
        );
    }


    public function contactarAlDuenio(){

        $this->usuario->chequearCsrf();

        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->post('id_publicacion'),
            'id_publicacion',
            $errores,
            1
        );

        if ($idPublicacion === null) {

            http_response_code(400);

            view(
                'errors/bads-request.view',
                array_merge(
                    ['error_message' => 'El identificador de la publicación no es válido.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $publicacion = $this->model->getOne($idPublicacion);

        if (!$publicacion || (int) $publicacion['estado_id'] !== 2) {

            http_response_code(404);

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' => 'La publicación no está disponible.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $emailInteresado = $this->verificador->email(
            $this->request->post('email-interesado'),
            'email-interesado',
            $errores
        );

        $telefonoInteresado = $this->verificador->telefono(
            $this->request->post('telefono-interesado'),
            'telefono-interesado',
            $errores
        );

        $textoConsulta = $this->verificador->texto(
            $this->request->post('texto-consulta'),
            'texto-consulta',
            $errores,
            true,
            3,
            2000
        );

        if (!empty($errores)) {
            $this->request->setResultadoEnSesion(
                'resultadoContacto',
                [
                    'exito' => false,
                    'mensaje' => implode(' ', $errores)
                ]
            );

            redirect('publicacion/ver?id_pub=' . $idPublicacion);
            return;
        }

        //Datos recuperados directamente desde la base de datos para aumentar confiabilidad
        $emailDuenio = $publicacion['email'];

        $urlPublicacion = $this->request->host() . '/publicacion/ver?id_pub=' . $idPublicacion;

        $resultadoSend = $this->mailer->enviarMailAlDuenio(
            $emailInteresado,
            $telefonoInteresado,
            $textoConsulta,
            $urlPublicacion,
            $emailDuenio
        );

        if ($resultadoSend) {

            $this->request->setResultadoEnSesion(
                'resultadoContacto',
                [
                    'exito' => true,
                    'mensaje' => 'La consulta fue enviada correctamente.'
                ]
            );

            $this->logger->info(
                'Consulta enviada por correo.',
                ['publicacion_id' => $idPublicacion]
            );

        } else {

            $this->request->setResultadoEnSesion(
                'resultadoContacto',
                [
                    'exito' => false,
                    'mensaje' => 'No se pudo enviar la consulta. Intentá nuevamente más tarde.'
                ]
            );

            $this->logger->warning(
                'No se pudo enviar la consulta por correo.',
                ['publicacion_id' => $idPublicacion]
            );
        }

        redirect('publicacion/ver?id_pub=' . $idPublicacion);
    }

    public function listaPublicacionesPropietario(){

        try {

            $this->usuario->chequearTiposPermitidos([1, 3]);

            $idUser = $this->usuario->getUserId();

            $filtros = $this->obtenerFiltrosListado();

            $paginaSolicitada = $this->obtenerPaginaSolicitada();

            $porPagina = self::PUBLICACIONES_POR_PAGINA;

            $cantidadResultadosFiltrados = $this->model
                ->getCantidadPublicacionesFiltradas(
                    $filtros['zona'],
                    $filtros['tipos'],
                    $filtros['precio'],
                    $filtros['instalaciones'],
                    $idUser
                );

            $totalPaginas = max(
                1,
                (int) ceil($cantidadResultadosFiltrados / $porPagina)
            );

            $paginaActual = min($paginaSolicitada, $totalPaginas);

            $offset = ($paginaActual - 1) * $porPagina;

            $publicaciones = $this->model
                ->getAllFilter(
                    $filtros['zona'],
                    $filtros['tipos'],
                    $filtros['precio'],
                    $filtros['instalaciones'],
                    $idUser,
                    $porPagina,
                    $offset
                );

            /*Cuenta el total de propiedades de este usuario, no las propiedades de toda la plataforma*/
            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales($idUser);

            /*Se Obtiene el precio máximo de las propiedades del usuario*/
            $mayorPrecio = $this->model->getPublicacionMayorPrecio($idUser);

            $datos = [
                'idUser' => $idUser,
                'id_usuario' => $idUser,
                'zona' => $filtros['zona'],
                'tipos' => $filtros['tipos'],
                'precio' => $filtros['precio'],
                'instalaciones' => $filtros['instalaciones'],
                'mayorPrecio' => $mayorPrecio,
                'publicaciones' => $publicaciones,
                'cantidadMostrada' => count($publicaciones),
                'cantidadResultadosFiltrados' => $cantidadResultadosFiltrados,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'paginaActual' => $paginaActual,
                'totalPaginas' => $totalPaginas,
                'porPagina' => $porPagina,

                'rutaListado' => '/mis_publicaciones',
                'titulo' => 'PAWPERTIES | MIS PROPIEDADES',
                'subtitulo' => 'Mis Propiedades'
            ];

            if ($this->request->isAjaxRequest()) {
                view(
                    'parts/lista-publicaciones.view',
                    $datos
                );

                return;
            }

            view(
                'publicaciones.list.view',
                array_merge(
                    $datos,
                    $this->menuAndSession,
                    $this->model->traerTipos()
                )
            );
        } catch (Throwable $e) {

            $error_message = 'Error al obtener las publicaciones: ' . $e->getMessage();

            $this->logger->error($error_message);

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message' => 'No se pudo cargar el listado de propiedades.'],
                    $this->menuAndSession
                )
            );
        }
    }

    private function enviarImagenNoEncontrada(int $statusCode = 404): void {

        http_response_code($statusCode);

        header('X-Content-Type-Options: nosniff');

        header('Cache-Control: no-store');

        $placeholder = realpath(Imagen::UPLOADDIRECTORY . 'image-not-found.png');

        if ($placeholder !== false && is_file($placeholder)) {

            header('Content-Type: image/png');

            header('Content-Length: ' . filesize($placeholder));

            readfile($placeholder);

            return;

        }

        header('Content-Type: text/plain; ' . 'charset=UTF-8');

        echo 'Imagen no disponible.';

    }


    public function getImgPublicacion(){
        
        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->query('id_pub'),
            'id_pub',
            $errores,
            1
        );

        $idImagen = null;

        $idImagenRecibido = $this->request->query('id_img');

        if ($idImagenRecibido !== null && $idImagenRecibido !== '') {

            $idImagen = $this->verificador->entero(
                $idImagenRecibido,
                'id_img',
                $errores,
                1
            );

        }

        if (!empty($errores) || $idPublicacion === null) {
            
            $this->enviarImagenNoEncontrada(400);
            return;

        }

        try {
            $publicacion = $this->model->getOne($idPublicacion);

            if (!$publicacion) {

                $this->enviarImagenNoEncontrada(404);
                return;

            }

            $usuarioActual = (int) ($this->usuario->getUserId() ?? 0);

            $tipoUsuario = $this->usuario->getUserType();

            $esDuenio = $usuarioActual > 0 && $usuarioActual === (int) $publicacion['id_usuario'];

            $esEmpleado = $tipoUsuario === 2;

            $estaAceptada = (int) $publicacion['estado_id'] === 2;

            if (!$estaAceptada && !$esDuenio && !$esEmpleado) {

                $this->enviarImagenNoEncontrada(404);
                return;

            }

            $imagenPublicacion = $this->model->getImg($idPublicacion, $idImagen);

            if (!$imagenPublicacion) {

                $this->enviarImagenNoEncontrada(404);
                return;

            }

            $directorioBase = realpath(Imagen::UPLOADDIRECTORY);

            $archivo = realpath(Imagen::UPLOADDIRECTORY . $imagenPublicacion['path_imagen']);

            //Impedir que ../ salga del directorio uploads
            if ($directorioBase === false || $archivo === false || !is_file($archivo) || !str_starts_with($archivo, $directorioBase . DIRECTORY_SEPARATOR)) {

                $this->enviarImagenNoEncontrada(404);
                return;

            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $mimeType = $finfo !== false ? finfo_file($finfo, $archivo) : false;

            if ($finfo !== false) {
                finfo_close($finfo);
            }

            if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
                $this->logger->warning(
                    'Se rechazó una imagen con tipo MIME no permitido.',
                    ['publicacion_id' => $idPublicacion]
                );

                $this->enviarImagenNoEncontrada(404);
                
                return;
            }

            header('Content-Type: ' . $mimeType);

            header('Content-Length: ' . filesize($archivo));

            header('X-Content-Type-Options: nosniff');

            if ($estaAceptada) {
                header('Cache-Control: public, max-age=3600');
            } else {
                header('Cache-Control: private, no-store');
            }

            readfile($archivo);

        } catch (Exception $e) {
            $this->logger->error(
                'Error al obtener una imagen de publicación.',
                [
                    'publicacion_id' => $idPublicacion,
                    'mensaje' => $e->getMessage()
                ]
            );

            $this->enviarImagenNoEncontrada(500);
        }
    }

    public function new()
    {
        try {

            $this->usuario->chequearTiposPermitidos([1, 3]);

            if ($this->request->method() == 'POST') {

                $this->usuario->chequearCsrf();

                $errors = [];

                $formData = $this->obtenerDatosFormularioAlta();
                
                $idUser = $this->usuario->getUserId();

                $tiposResultado = $this->model->traerTipos();

                $tiposDisponibles = $tiposResultado['tipos_alojamiento'] ?? [];

                $tiposPermitidos = is_array($tiposDisponibles) ? array_column($tiposDisponibles, 'id') : [];

                $provincia = $this->verificador->texto(
                    $this->request->post('provincia'),
                    'provincia',
                    $errors,
                    true,
                    2,
                    100
                );

                $codigo_postal = $this->verificador->texto(
                    $this->request->post('codigo_postal'),
                    'codigo_postal',
                    $errors,
                    false,
                    2,
                    20
                );

                $direccion = $this->verificador->coordenadas(
                    $this->request->post('direccion'),
                    'direccion',
                    $errors
                );

                $direccion_completa = $this->verificador->texto(
                    $this->request->post('direccion_completa'),
                    'direccion_completa',
                    $errors,
                    true,
                    3,
                    255
                );

                
                if ($direccion_completa !== null) {
                    $direccionNormalizada = strtolower(trim($direccion_completa));

                    $valoresInvalidos = [
                        'undefined',
                        'null',
                        'direccion no disponible',
                        'dirección no disponible'
                    ];

                    if (in_array($direccionNormalizada, $valoresInvalidos, true)) {
                        $errors['direccion_completa'] = 'No se pudo determinar la dirección seleccionada.';

                        $direccion_completa = null;
                    }
                }

                $precio = $this->verificador->precioEntero(
                    $this->request->post('precio'),
                    'precio',
                    $errors
                );

                $nombreAlojamiento = $this->verificador->texto(
                    $this->request->post('nombre-alojamiento'),
                    'nombre-alojamiento',
                    $errors,
                    true,
                    3,
                    255
                );

                $tipoAlojamiento = $this->verificador->opcionEntera(
                    $this->request->post('tipo-alojamiento'),
                    'tipo-alojamiento',
                    $errors,
                    $tiposPermitidos
                );

                $capacidadMaxima = $this->verificador->entero(
                    $this->request->post('capacidad-maxima'),
                    'capacidad-maxima',
                    $errors,
                    1,
                    100
                );

                $cantBanios = $this->verificador->entero(
                    $this->request->post('cant-banios'),
                    'cant-banios',
                    $errors,
                    1,
                    50
                );

                $cantidadDormitorios = $this->verificador->entero(
                    $this->request->post('cantidad-dormitorios'),
                    'cantidad-dormitorios',
                    $errors,
                    1,
                    100
                );

                $cochera = $this->verificador->checkbox(
                    $this->request->post('cochera')
                );

                $pileta = $this->verificador->checkbox(
                    $this->request->post('pileta')
                );

                $aireAcondicionado = $this->verificador->checkbox(
                    $this->request->post('aire-acondicionado')
                );

                $wifi = $this->verificador->checkbox(
                    $this->request->post('wifi')
                );

                $normasAlojamiento = $this->verificador->texto(
                    $this->request->post('normas-alojamiento'),
                    'normas-alojamiento',
                    $errors,
                    true,
                    3,
                    5000
                );

                $descripcionAlojamiento = $this->verificador->texto(
                    $this->request->post('descripcion-alojamiento'),
                    'descripcion-alojamiento',
                    $errors,
                    true,
                    3,
                    5000
                );

                // Verifica si hay errores
                if (empty($errors)) {
                    // Preparar el array de datos para setear el objecto
                    $this->logger->debug("No hay errores..");

                    $publicacion = [
                        'provincia' => $provincia,
                        'codigo_postal' => $codigo_postal,
                        'direccion' => $direccion,
                        'direccion_completa' => $direccion_completa,
                        'precio' => $precio,
                        'nombre_alojamiento' => $nombreAlojamiento,
                        'tipo_alojamiento_id' => $tipoAlojamiento,
                        'capacidad_maxima' => $capacidadMaxima,
                        'cant_banios' => $cantBanios,
                        'cantidad_dormitorios' => $cantidadDormitorios,
                        'cochera' => $cochera,
                        'pileta' => $pileta,
                        'aire_acondicionado' => $aireAcondicionado,
                        'wifi' => $wifi,
                        'normas_alojamiento' => $normasAlojamiento,
                        'descripcion_alojamiento' => $descripcionAlojamiento,
                        'id_usuario' => $idUser,
                        'estado_id' => 1
                    ];
                    // setear el objeto Publicacion
                    $ObjPublicacion = new Publicacion($publicacion, $this->logger);
                    $publicacionObj = $ObjPublicacion->getEstadoConstructor();

                    if ($publicacionObj['exito']) {

                        if (!$this->solicitudTieneImagenes()) {
                            $this->renderizarAltaConError(
                                [
                                    'errors' => ['imagenes' => 'Debe subir al menos una imagen.']
                                ],
                                $formData,
                                2
                            );

                            return;
                        }

                        $imagenesPublicacion = [];

                        $imagenesCollection = new ImagenCollection($_FILES['imagenes']);

                        $resultadoVerificacion = $imagenesCollection->verificarCollectionImagenes();

                        if ($resultadoVerificacion['exito']) {

                            // Manejar la inserción de datos
                            [$idPublicacionGenerado, $resultado] = $this->model->create($ObjPublicacion);

                            $this->logger->info(
                                'Publicacion creada.',
                                [
                                    'publicacion_id' => $idPublicacionGenerado,
                                    'usuario_id' => $idUser
                                ]
                            );


                            $resultadoSubidaImagenes = $imagenesCollection->guardarImagenes($idPublicacionGenerado, $idUser);

                            if ($resultadoSubidaImagenes['exito']) {

                                // Inserta todas las imágenes en la base de datos en una única operación
                                $this->model->insertMany('imagenes_publicacion', $imagenesCollection->getImagenesCollection());

                                redirect('publicacion/ver?id_pub=' . $idPublicacionGenerado);
                            } else {
                                $this->renderizarAltaConError(
                                    ['imagen_errors' => $imagenesCollection->getErroresCollectionSubida()],
                                    $formData,
                                    2
                                );

                                return;
                            }
                        } else {
                            $this->renderizarAltaConError(
                                ['imagen_errors' => $imagenesCollection->getErroresCollection()],
                                $formData,
                                2
                            );

                            return;
                        }
                    } else {

                        $erroresPublicacion = $publicacionObj['errores'] ?? ['publicacion' => 'No se pudo preparar la publicación.'];

                        $this->logger->error('Publicación no generada.', ['cantidad_errores' => count($erroresPublicacion)]);

                        $this->renderizarAltaConError(
                            ['errors' => $erroresPublicacion],
                            $formData,
                            1
                        );

                        return;
                    }
                } else {
                    $this->logger->warning('La publicación no superó la validación.', ['campos_con_error' => array_keys($errors)]);

                    $this->renderizarAltaConError(
                        ['errors' => $errors],
                        $formData,
                        $this->determinarPasoFormularioAlta($errors)
                    );

                    return;
                }
            } else {
                $datos = [
                    'titulo' => 'PAWPERTIES | NUEVA PUBLICACION',
                ];

                view('publicacion.new.view', array_merge(
                    $this->menuAndSession,
                    $datos,
                    $this->model->traerTipos()
                ));
            }
        } catch (Exception $e) {

            // Manejar la excepción
            $this->logger->error("Error en el proceso: " . $e->getMessage());

            view('errors/internal_error.view', [
                'error_message' => "Error en el proceso: " . $e->getMessage()
            ]);
        }
    }

    private function obtenerDatosFormularioAlta(): array
    {
        /*Se recuperan solamente los campos permitidos. No se copia POST completo*/
        $camposTexto = [
            'nombre-alojamiento',
            'tipo-alojamiento',
            'capacidad-maxima',
            'cant-banios',
            'cantidad-dormitorios',
            'ubicacion',
            'provincia',
            'codigo_postal',
            'direccion',
            'direccion_completa',
            'descripcion-alojamiento',
            'normas-alojamiento',
            'precio'
        ];

        $datos = [];

        foreach ($camposTexto as $campo) {
            $valor = $this->request->post($campo, '');

            $datos[$campo] = is_scalar($valor) ? (string) $valor : '';
        }

        /* Los checkbox se conservan como booleanos */
        $datos['cochera'] = $this->request->post('cochera') !== null;

        $datos['pileta'] = $this->request->post('pileta') !== null;

        $datos['aire-acondicionado'] = $this->request->post('aire-acondicionado') !== null;

        $datos['wifi'] = $this->request->post('wifi') !== null;

        return $datos;
    }

    private function solicitudTieneImagenes(): bool
    {
        if (!isset($_FILES['imagenes']['name']) || !is_array($_FILES['imagenes']['name'])) {
            return false;
        }

        foreach ($_FILES['imagenes']['name'] as $nombreImagen) {
            if (trim((string) $nombreImagen) !== '') {
                return true;
            }
        }

        return false;
    }

    private function determinarPasoFormularioAlta(array $errores): int {

        $camposPaso1 = [
            'nombre-alojamiento',
            'tipo-alojamiento',
            'capacidad-maxima',
            'cant-banios',
            'cantidad-dormitorios',
            'provincia',
            'codigo_postal',
            'direccion',
            'direccion_completa'
        ];

        foreach (array_keys($errores) as $campo) {
            if (in_array($campo, $camposPaso1, true)) {
                return 1;
            }
        }

        $camposPaso3 = [
            'descripcion-alojamiento',
            'normas-alojamiento',
            'precio'
        ];

        foreach (array_keys($errores) as $campo) {
            if (in_array($campo, $camposPaso3, true)) {
                return 3;
            }
        }

        return 1;
    }

    private function renderizarAltaConError(array $datosError, array $formData, int $initialStep): void {
        view(
            'publicacion.new.view',
            array_merge(
                $this->menuAndSession,
                [
                    'titulo' => 'PAWPERTIES | NUEVA PUBLICACION',

                    'form_data' => $formData,

                    'initial_step' => $initialStep,

                    'must_reselect_images' => true
                ],
                $this->model->traerTipos(),
                $datosError
            )
        );
    }

    public function gestionarPublicaciones()
    {
        //Solamente el empleado puede moderar publicaciones
        $this->usuario->chequearTiposPermitidos([2]);

        $listaPublicaciones = $this->model->traerPublicaciones();

        $datos = [
            'titulo' => 'PAWPERTIES | GESTIONAR PUBLICACIONES',
            'exito' => true
        ];

        view(
            'publicaciones.gestionar.view',
            array_merge(
                $listaPublicaciones,
                $datos,
                $this->menuAndSession
            )
        );
    }

    public function actualizarEstadoPublicacion(){

        try {
            
            $this->usuario->chequearTiposPermitidos([2]);

            $this->usuario->chequearCsrf();

            $accion = $this->request->getSegments(2);

            if (!in_array($accion, ['aceptar', 'rechazar'],true)) {

                http_response_code(400);

                view(
                    'errors/bads-request.view',
                    array_merge(
                        ['error_message' => 'La acción solicitada no es válida.'],
                        $this->menuAndSession
                    )
                );

                return;
            }

            $errores = [];

            $idPublicacion =
                $this->verificador->entero(
                    $this->request->post('id_pub'),
                    'id_pub',
                    $errores,
                    1
                );

            if (!empty($errores)) {

                http_response_code(400);

                view(
                    'errors/bads-request.view',
                    array_merge(
                        ['error_message' => implode(' ', $errores)],
                        $this->menuAndSession
                    )
                );

                return;
            }

            $publicacion = $this->model->getOne($idPublicacion);

            if (!$publicacion) {
                http_response_code(404);

                view(
                    'errors/not-found.view',
                    array_merge(
                        ['error_message' => 'La publicación no existe.'],
                        $this->menuAndSession
                    )
                );

                return;
            }

            
            if ((int) $publicacion['estado_id'] !== 1) {

                http_response_code(400);

                view(
                    'errors/bads-request.view',
                    array_merge(
                        ['error_message' => 'La publicación ya fue procesada.'],
                        $this->menuAndSession
                    )
                );

                return;
            }

            $this->model->actualizarEstadoPublicacion($idPublicacion, $accion);

        
            $body = view(
                'correoDeCambioEstadoPublicacion',
                [
                    'fullUrl' => $this->request->host() . '/mis_publicaciones',
                    'accion' => $accion
                ],
                true
            );

            $resultadoSend =
                $this->mailer->send(
                    $publicacion['email'],
                    'Estado de tu publicación: ' . $accion,
                    $body
                );

            if ($resultadoSend) {
                $this->logger->info(
                    'Publicación procesada y correo enviado.',
                    [
                        'publicacion_id' => $idPublicacion,
                        'accion' => $accion,
                        'empleado_id' => $this->usuario->getUserId()
                    ]
                );
            } else {
                $this->logger->warning(
                    'Publicación procesada, pero falló el correo.',
                    [
                        'publicacion_id' => $idPublicacion,
                        'accion' => $accion,
                        'empleado_id' => $this->usuario->getUserId()
                    ]
                );
            }

            redirect('publicaciones/gestionar');

        } catch (Exception $e) {
            $this->logger->error(
                'Error al actualizar una publicación.',
                ['mensaje' => $e->getMessage()]
            );

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message' => 'No se pudo actualizar la publicación.'],
                    $this->menuAndSession
                )
            );
        }

    }

    public function apiPublicaciones()
    {
        $publicaciones = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode(array_values($publicaciones));
    }

    public function guardarComentario(){
        
        $this->usuario->chequearSesion();

        $this->usuario->chequearCsrf();

        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->post('id_pub'),
            'id_pub',
            $errores,
            1
        );

        $rating = $this->verificador->entero(
            $this->request->post('rating'),
            'rating',
            $errores,
            1,
            5
        );

        $textoComentario = $this->verificador->texto(
                $this->request->post('comment'),
                'comment',
                $errores,
                true,
                1,
                2000
            );

        //Si el ID de publicación es válido, se regresa al detalle y se muestran los errores.
        
        if (!empty($errores)) {
            if ($idPublicacion !== null) {
                $this->request->setResultadoEnSesion(
                    'resultadoComentario',
                    [
                        'exito' => false,
                        'mensaje' => implode(' ', $errores)
                    ]
                );

                redirect('publicacion/ver?id_pub=' . $idPublicacion);

                return;
            }

            //No se puede volver al detalle porque el propio ID de la publicación es inválido

            http_response_code(400);

            view(
                'errors/bads-request.view',
                array_merge(
                    ['error_message' => 'El identificador de la publicación no es válido.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $publicacion = $this->model->getOne($idPublicacion);

        if (!$publicacion) {
            http_response_code(404);

            view(
                'errors/not-found.view',
                array_merge(
                    ['error_message' => 'La publicación no existe.'],
                    $this->menuAndSession
                )
            );

            return;
        }

        $comentario = [
            'id_publicacion' => $idPublicacion,

            //El autor sale de la sesión.
            'id_user' => $this->usuario->getUserId(),

            'rating' => $rating,
            'comment' => $textoComentario
        ];

        $resultadoComentario = $this->model->insertarComentario($comentario);


        if (!$resultadoComentario['exito']) {
            $resultadoComentario = [
                'exito' => false,
                'mensaje' =>
                    'No se pudo guardar el comentario. '
                    . 'Si ya calificaste esta publicación, '
                    . 'no podés volver a calificarla.'
            ];
        }

        $this->request->setResultadoEnSesion(
            'resultadoComentario',
            $resultadoComentario
        );

        redirect('publicacion/ver?id_pub=' . $idPublicacion);

    }

}
