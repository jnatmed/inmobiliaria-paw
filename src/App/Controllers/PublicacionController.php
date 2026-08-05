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

    private const ESTADO_PUBLICACION_PENDIENTE = 1;
    private const ESTADO_PUBLICACION_ARCHIVADA = 4;

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

    private function construirUrlListado($rutaListado, $filtros, $pagina) {

        $parametros = [];

        if ($filtros['zona'] !== null) {
            $parametros['zona'] = $filtros['zona'];
        }

        if ($filtros['precio'] !== null) {
            $parametros['precio'] = $filtros['precio'];
        }

        if (!empty($filtros['tipos'])) {
            $parametros['tipo'] = $filtros['tipos'];
        }

        if (!empty($filtros['instalaciones'])) {
            $parametros['instalaciones'] = $filtros['instalaciones'];
        }

        /*En la primera pagina no es necesario agregar pagina=1 a la URL*/
        if ($pagina > 1) {
            $parametros['pagina'] = $pagina;
        }

        $queryString = http_build_query($parametros, '', '&', PHP_QUERY_RFC3986);

        return $rutaListado . ($queryString !== '' ? '?' . $queryString : '');
    }

    private function paginaNecesitaNormalizacion($paginaActual) {

        $paginaRecibida = $this->request->query('pagina');

        if ($paginaRecibida === null) {
            return false;
        }

        /*Descarta casos como pagina[]=2*/
        if (is_array($paginaRecibida)) {
            return true;
        }

        $paginaValidada = filter_var(
            $paginaRecibida,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($paginaValidada === false) {
            return true;
        }

        /*Devuelve true para pagina=999 cuando la última pagina real es, por ejemplo, 2*/
        return (int) $paginaValidada !== (int) $paginaActual;
    }

    public function list(){

        try {

            $filtros = $this->obtenerFiltrosListado();

            $paginaSolicitada = $this->obtenerPaginaSolicitada();

            $porPagina = self::PUBLICACIONES_POR_PAGINA;

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

            $paginaActual = min($paginaSolicitada, $totalPaginas);

            $rutaListado = '/publicaciones/list';

            $urlListadoActual = $this->construirUrlListado($rutaListado, $filtros, $paginaActual);

            /*Corrige páginas inválidas o inexistentes*/
            if ($this->paginaNecesitaNormalizacion($paginaActual)) {
                redirect($urlListadoActual);
            }

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

            $resultadoTipos = $this->model->traerTipos();

            $tiposAlojamiento = $resultadoTipos['tipos_alojamiento'] ?? [];

            $urlPaginaAnterior = $paginaActual > 1 ? $this->construirUrlListado($rutaListado, $filtros, $paginaActual - 1) : null;

            $urlPaginaSiguiente = $paginaActual < $totalPaginas ? $this->construirUrlListado($rutaListado, $filtros, $paginaActual + 1) : null;

            $datos = [
                'zona' => $filtros['zona'],
                'tipos' => $filtros['tipos'],
                'precio' => $filtros['precio'],
                'instalaciones' => $filtros['instalaciones'],
                'tipos_alojamiento' => $tiposAlojamiento,
                'mayorPrecio' => $mayorPrecio,
                'publicaciones' => $publicaciones,
                'cantidadMostrada' => count($publicaciones),
                'cantidadResultadosFiltrados' => $cantidadResultadosFiltrados,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'paginaActual' => $paginaActual,
                'totalPaginas' => $totalPaginas,
                'porPagina' => $porPagina,
                'rutaListado' => $rutaListado,
                'urlListadoActual' => $urlListadoActual,
                'urlPaginaAnterior' => $urlPaginaAnterior,
                'urlPaginaSiguiente' => $urlPaginaSiguiente,
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
                    $this->menuAndSession
                )
            );
        } catch (Throwable $e) {
            $error_message = 'Error al obtener las publicaciones: ' . $e->getMessage();

            $this->logger->error($error_message);

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message'  => 'No se pudo cargar el listado de propiedades.'],
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

            $rutaListado = '/mis_publicaciones';

            $urlListadoActual = $this->construirUrlListado($rutaListado, $filtros, $paginaActual);

            if ($this->paginaNecesitaNormalizacion($paginaActual)) {
                redirect($urlListadoActual);
            }

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

            $cantidadTotalPublicaciones = $this->model->getPublicacionesTotales($idUser);

            $mayorPrecio = $this->model->getPublicacionMayorPrecio($idUser);

            $resultadoTipos = $this->model->traerTipos();

            $tiposAlojamiento = $resultadoTipos['tipos_alojamiento'] ?? [];

            $urlPaginaAnterior = $paginaActual > 1 ? $this->construirUrlListado($rutaListado, $filtros, $paginaActual - 1) : null;

            $urlPaginaSiguiente = $paginaActual < $totalPaginas ? $this->construirUrlListado($rutaListado, $filtros, $paginaActual + 1) : null;

            $datos = [
                'idUser' => $idUser,
                'id_usuario' => $idUser,
                'zona' => $filtros['zona'],
                'tipos' => $filtros['tipos'],
                'precio' => $filtros['precio'],
                'instalaciones' => $filtros['instalaciones'],
                'tipos_alojamiento' => $tiposAlojamiento,
                'mayorPrecio' => $mayorPrecio,
                'publicaciones' => $publicaciones,
                'cantidadMostrada' => count($publicaciones),
                'cantidadResultadosFiltrados' => $cantidadResultadosFiltrados,
                'cantidadTotalPublicaciones' => $cantidadTotalPublicaciones,
                'paginaActual' => $paginaActual,
                'totalPaginas' => $totalPaginas,
                'porPagina' => $porPagina,
                'rutaListado' => $rutaListado,
                'urlListadoActual' => $urlListadoActual,
                'urlPaginaAnterior' => $urlPaginaAnterior,
                'urlPaginaSiguiente' => $urlPaginaSiguiente,
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

            $datos['resultadoGestionPublicacion'] = $this->request->getResultadoGuardado('resultadoGestionPublicacion');

            $this->request->eliminarResultadoEnSesion('resultadoGestionPublicacion');

            view(
                'publicaciones.list.view',
                array_merge(
                    $datos,
                    $this->menuAndSession
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

    private function detenerGestionPublicacion(int $statusCode, string $vista, string $mensaje, ?string $encabezado = null): void {
        
        http_response_code($statusCode);

        $datos = [
            'error_message' => $mensaje,
            'error_status' => $statusCode
        ];

        if ($encabezado !== null) {
            $datos['error_lead'] = $encabezado;
        }

        view(
            $vista,
            array_merge(
                $datos,
                $this->menuAndSession
            )
        );

        exit;
    }

    private function obtenerPublicacionPropiaDesdePost(): array
    {
        $errores = [];

        $idPublicacion = $this->verificador->entero(
            $this->request->post('id_pub'),
            'id_pub',
            $errores,
            1
        );

        if ($idPublicacion === null) {
            $this->detenerGestionPublicacion(400, 'errors/bads-request.view', 'El identificador de la publicación no es válido.');
        }

        $publicacion = $this->model->getOne($idPublicacion);

        if (!$publicacion) {
            $this->detenerGestionPublicacion(404, 'errors/not-found.view', 'La publicación solicitada no existe.');
        }

        $usuarioActual = (int) $this->usuario->getUserId();
        $duenioPublicacion = (int) $publicacion['id_usuario'];

        if ($usuarioActual !== $duenioPublicacion) {
            $this->logger->warning(
                'Intento de administrar una publicación ajena.',
                [
                    'usuario_id' => $usuarioActual,
                    'publicacion_id' => $idPublicacion
                ]
            );

            $this->detenerGestionPublicacion(
                403,
                'errors/forbidden.view',
                'No tenés permiso para administrar esta publicación.',
                'Acceso denegado'
            );
        }

        return $publicacion;
    }

    private function eliminarArchivosDePublicacion(array $imagenes, int $idPublicacion): void {

        $directorioBase = realpath(Imagen::UPLOADDIRECTORY);

        if ($directorioBase === false) {
            $this->logger->error(
                'No se encontró el directorio de imágenes al eliminar una publicación.',
                ['publicacion_id' => $idPublicacion]
            );

            return;
        }

        foreach ($imagenes as $imagen) {
            $pathGuardado = (string) ($imagen['path_imagen'] ?? '');

            if ($pathGuardado === '' || basename($pathGuardado) === 'image-not-found.png') {
                continue;
            }

            $rutaCandidata = $directorioBase . DIRECTORY_SEPARATOR . ltrim($pathGuardado, '/\\');

            $rutaReal = realpath($rutaCandidata);

            if ($rutaReal === false) {
                $this->logger->warning(
                    'La imagen asociada ya no existe en el disco.',
                    [
                        'publicacion_id' => $idPublicacion,
                        'imagen' => basename($pathGuardado)
                    ]
                );
                continue;
            }

            $prefijoPermitido = $directorioBase . DIRECTORY_SEPARATOR;

            if (strpos($rutaReal, $prefijoPermitido) !== 0) {
                $this->logger->warning(
                    'Se rechazó un path de imagen fuera del directorio permitido.',
                    [
                        'publicacion_id' => $idPublicacion,
                        'imagen' => basename($pathGuardado)
                    ]
                );

                continue;
            }

            if (is_file($rutaReal) && !@unlink($rutaReal)) {
                $this->logger->warning(
                    'No se pudo eliminar un archivo de imagen del disco.',
                    [
                        'publicacion_id' => $idPublicacion,
                        'imagen' => basename($pathGuardado)
                    ]
                );
            }
        }
    }

    public function administrarPublicacionPropia(): void
    {
        try {

            //Los usuarios comunes pueden publicar y administrar lo que crearon
            $this->usuario->chequearTiposPermitidos([1, 3]);

            //Todas las operaciones que modifican datos y requieren POST se chequea el CSRF
            $this->usuario->chequearCsrf();

            $accion = $this->request->getSegments(1);

            if (!in_array($accion, ['archivar', 'reactivar', 'eliminar'], true)) {
                $this->detenerGestionPublicacion(400, 'errors/bads-request.view', 'La acción solicitada no es válida.');
            }

            $publicacion = $this->obtenerPublicacionPropiaDesdePost();

            $idPublicacion = (int) $publicacion['id'];
            $idUsuario = (int) $this->usuario->getUserId();
            $estadoActual = (int) $publicacion['estado_id'];

            if ($accion === 'archivar') {
                if ($estadoActual === self::ESTADO_PUBLICACION_ARCHIVADA) {
                    $this->detenerGestionPublicacion(400, 'errors/bads-request.view', 'La publicación ya está archivada.');
                }

                $this->model->cambiarEstadoPublicacionPropia($idPublicacion, $idUsuario, self::ESTADO_PUBLICACION_ARCHIVADA);

                $mensaje = 'La propiedad fue archivada. Ya no aparece públicamente, ' . 'pero sus reservas y su historial se conservaron.';
            } elseif ($accion === 'reactivar') {
                if ($estadoActual !== self::ESTADO_PUBLICACION_ARCHIVADA) {
                    $this->detenerGestionPublicacion(400, 'errors/bads-request.view', 'Solo se puede reactivar una publicación archivada.');
                }

                $this->model->cambiarEstadoPublicacionPropia($idPublicacion, $idUsuario, self::ESTADO_PUBLICACION_PENDIENTE);

                $mensaje = 'La propiedad fue enviada nuevamente a moderación. Permanecerá pendiente hasta que un empleado la apruebe.';
            } else {

                if ($estadoActual !== self::ESTADO_PUBLICACION_ARCHIVADA) {
                    $this->detenerGestionPublicacion(
                        409, 
                        'errors/bads-request.view', 
                        'Antes de eliminar una propiedad, primero debe estar archivada.', 
                        'La operación no puede completarse'
                    );
                }

                $tieneReservas = $this->model->tieneReservas($idPublicacion);
                $tieneCalificaciones = $this->model->tieneCalificaciones($idPublicacion);

                if ($tieneReservas && $tieneCalificaciones) {
                    $this->detenerGestionPublicacion(
                        409,
                        'errors/bads-request.view',
                        'La propiedad tiene reservas y también comentarios o calificaciones asociados. No puede eliminarse, pero podés mantenerla archivada.',
                        'La operación no puede completarse'
                    );
                }

                if ($tieneReservas) {
                    $this->detenerGestionPublicacion(
                        409,
                        'errors/bads-request.view',
                        'La propiedad tiene reservas asociadas y no puede eliminarse. Podés mantenerla archivada.',
                        'La operación no puede completarse'
                    );
                }

                if ($tieneCalificaciones) {
                    $this->detenerGestionPublicacion(
                        409,
                        'errors/bads-request.view',
                        'La propiedad tiene comentarios o calificaciones y no puede eliminarse. Podés mantenerla archivada.',
                        'La operación no puede completarse'
                    );
                }

                $imagenes = $publicacion['imagenes'] ?? [];

                $this->model->eliminarPublicacionPropia($idPublicacion, $idUsuario);

                //Primero se elimina la fila de la base. Después se limpian los archivos asi no quedan referencias de BD a imagenes inexistentes
                $this->eliminarArchivosDePublicacion($imagenes, $idPublicacion);

                $mensaje = 'La propiedad fue eliminada definitivamente.';
            }

            $this->logger->info(
                'Administración de publicación propia completada.',
                [
                    'usuario_id' => $idUsuario,
                    'publicacion_id' => $idPublicacion,
                    'accion' => $accion
                ]
            );

            $this->request->setResultadoEnSesion(
                'resultadoGestionPublicacion',
                [
                    'exito' => true,
                    'mensaje' => $mensaje
                ]
            );

            redirect('mis_publicaciones');

        } catch (Throwable $e) {
            $this->logger->error(
                'Error al administrar una publicación propia.',
                [
                    'usuario_id' => $this->usuario->getUserId(),
                    'mensaje' => $e->getMessage()
                ]
            );

            $this->detenerGestionPublicacion(500, 'errors/internal_error.view', 'No se pudo completar la operación sobre la propiedad.');
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

    private function obtenerPublicacionPropiaDesdeValor($valorId): array{

        $errores = [];

        $idPublicacion = $this->verificador->entero($valorId, 'id_pub', $errores, 1);

        if ($idPublicacion === null) {
            $this->detenerGestionPublicacion(400, 'errors/bads-request.view', 'El identificador de la publicación no es válido.');
        }

        $publicacion = $this->model->getOne($idPublicacion);

        if (!$publicacion) {
            $this->detenerGestionPublicacion(404, 'errors/not-found.view', 'La publicación solicitada no existe.');
        }

        $usuarioActual = (int) $this->usuario->getUserId();

        if ($usuarioActual !== (int) $publicacion['id_usuario']) {
            $this->logger->warning(
                'Intento de editar una publicación ajena.',
                [
                    'usuario_id' => $usuarioActual,
                    'publicacion_id' => $idPublicacion
                ]
            );

            $this->detenerGestionPublicacion(403, 'errors/forbidden.view', 'No tenés permiso para editar esta publicación.', 'Acceso denegado');

        }

        return $publicacion;
    }

    private function transformarPublicacionEnDatosFormulario(array $publicacion): array {

        $coordenadas = '';

        if (is_numeric($publicacion['latitud'] ?? null) && is_numeric($publicacion['longitud'] ?? null)) {
            $coordenadas = json_encode([
                'lat' => (float) $publicacion['latitud'],
                'lng' => (float) $publicacion['longitud']
            ]);
        }

        return [
            'nombre-alojamiento' => (string) ($publicacion['nombre_alojamiento'] ?? ''),
            'tipo-alojamiento' => (string) ($publicacion['tipo_alojamiento_id'] ?? ''),
            'capacidad-maxima' => (string) ($publicacion['capacidad_maxima'] ?? ''),
            'cant-banios' => (string) ($publicacion['cant_banios'] ?? ''),
            'cantidad-dormitorios' => (string) ($publicacion['cantidad_dormitorios'] ?? ''),
            'ubicacion' => (string) ($publicacion['direccion'] ?? ''),
            'provincia' => (string) ($publicacion['provincia'] ?? ''),
            'codigo_postal' => (string) ($publicacion['codigo_postal'] ?? ''),
            'direccion' => $coordenadas,
            'direccion_completa' => (string) ($publicacion['direccion'] ?? ''),
            'descripcion-alojamiento' => (string) ($publicacion['descripcion_alojamiento'] ?? ''),
            'normas-alojamiento' => (string) ($publicacion['normas_alojamiento'] ?? ''),
            'precio' => (string) ($publicacion['precio'] ?? ''),
            'cochera' => (bool) ($publicacion['cochera'] ?? false),
            'pileta' => (bool) ($publicacion['pileta'] ?? false),
            'aire-acondicionado' => (bool) ($publicacion['aire_acondicionado'] ?? false),
            'wifi' => (bool) ($publicacion['wifi'] ?? false)
        ];
    }

    private function obtenerDatosVistaEdicion(array $publicacion): array {

        $imagenes = $publicacion['imagenes'] ?? [];
        $imagenPrincipalUrl = '';

        if (!empty($imagenes)) {
            $imagenPrincipalUrl =
                '/publicacion?id_pub='
                . (int) $publicacion['id']
                . '&id_img='
                . (int) $imagenes[0]['id_imagen'];
        }

        return [
            'titulo' => 'PAWPERTIES | EDITAR PUBLICACIÓN',
            'modo_edicion' => true,
            'form_action' => '/mis_publicaciones/editar',
            'id_publicacion' => (int) $publicacion['id'],
            'imagenes_actuales' => $imagenes,
            'imagen_principal_actual' => $imagenPrincipalUrl,
            'cantidad_imagenes_actuales' => count($imagenes),
            'texto_boton_envio' => 'Guardar cambios'
        ];
    }

    public function editarPublicacionPropia(): void{

        $this->procesarFormularioPublicacion(true);

    }

    public function new(): void{

        $this->procesarFormularioPublicacion(false);

    }

    //Publicacion nueva el modoEdicion es false y para editar una publicacion el modo es true
    private function procesarFormularioPublicacion(bool $modoEdicion): void {

        try {

            $this->usuario->chequearTiposPermitidos([1, 3]);

            $publicacionOriginal = null;

            if ($modoEdicion) {
                $valorId = $this->request->method() === 'POST'
                    ? $this->request->post('id_pub')
                    : $this->request->query('id_pub');

                $publicacionOriginal = $this->obtenerPublicacionPropiaDesdeValor($valorId);
            }

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

                        'estado_id' =>
                            $modoEdicion
                            && (int) $publicacionOriginal['estado_id']
                                === self::ESTADO_PUBLICACION_ARCHIVADA
                                ? self::ESTADO_PUBLICACION_ARCHIVADA
                                : self::ESTADO_PUBLICACION_PENDIENTE
                    ];

                    // setear el objeto Publicacion
                    $ObjPublicacion = new Publicacion($publicacion, $this->logger);
                    $publicacionObj = $ObjPublicacion->getEstadoConstructor();

                    if ($publicacionObj['exito']) {

                        if ($modoEdicion) {

                            $idPublicacion = (int) $publicacionOriginal['id'];

                            $this->model->actualizarPublicacionPropia($idPublicacion, (int) $idUser, $ObjPublicacion->getAll());

                            $permaneceArchivada = (int) $publicacionOriginal['estado_id'] === self::ESTADO_PUBLICACION_ARCHIVADA;

                            $mensaje = $permaneceArchivada
                                ? 'Los cambios fueron guardados. La propiedad continúa archivada y no aparece públicamente.'
                                : 'Los cambios fueron guardados. La propiedad quedó pendiente para que un empleado vuelva a revisarla.';

                            $this->request->setResultadoEnSesion(
                                'resultadoGestionPublicacion',
                                [
                                    'exito' => true,
                                    'mensaje' => $mensaje
                                ]
                            );

                            $this->logger->info(
                                'Publicación propia editada.',
                                [
                                    'usuario_id' => (int) $idUser,
                                    'publicacion_id' => $idPublicacion,
                                    'estado_resultante' => $ObjPublicacion->getEstadoId()
                                ]
                            );

                            redirect('mis_publicaciones');
                            return;
                        }

                        if (!$this->solicitudTieneImagenes()) {
                            $this->renderizarAltaConError(
                                ['errors' => ['imagenes' => 'Debe subir al menos una imagen.']],
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
                            1,
                            $modoEdicion ? $publicacionOriginal : null
                        );

                        return;
                    }
                } else {
                    $this->logger->warning('La publicación no superó la validación.', ['campos_con_error' => array_keys($errors)]);

                    $this->renderizarAltaConError(
                        ['errors' => $errors],
                        $formData,
                        $this->determinarPasoFormularioAlta($errors),
                        $modoEdicion ? $publicacionOriginal : null
                    );

                    return;
                }
            } else {
                $datos = $modoEdicion
                    ? array_merge(
                        $this->obtenerDatosVistaEdicion($publicacionOriginal),
                        ['form_data' => $this->transformarPublicacionEnDatosFormulario($publicacionOriginal)]
                    ) : ['titulo' => 'PAWPERTIES | NUEVA PUBLICACION'];

                view(
                    'publicacion.new.view',
                    array_merge(
                        $this->menuAndSession,
                        $datos,
                        $this->model->traerTipos()
                    )
                );
            }
        } catch (Throwable $e) {

            $this->logger->error(
                'Error al procesar el formulario de publicación.',
                [
                    'usuario_id' => $this->usuario->getUserId(),
                    'mensaje' => $e->getMessage()
                ]
            );

            http_response_code(500);

            view(
                'errors/internal_error.view',
                array_merge(
                    ['error_message' => 'No se pudo completar la operación sobre la publicación.'],
                    $this->menuAndSession
                )
            );
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

    private function renderizarAltaConError(array $datosError, array $formData, int $initialStep, ?array $publicacionOriginal = null): void {

        $datosVista = [
            'titulo' => 'PAWPERTIES | NUEVA PUBLICACION',
            'form_data' => $formData,
            'initial_step' => $initialStep,
            'must_reselect_images' => true
        ];

        if ($publicacionOriginal !== null) {
            $datosVista = array_merge(
                $datosVista,
                $this->obtenerDatosVistaEdicion($publicacionOriginal),
                ['must_reselect_images' => false]
            );
        }

        view(
            'publicacion.new.view',
            array_merge(
                $this->menuAndSession,
                $datosVista,
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
