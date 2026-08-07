<?php

require __DIR__.'/../vendor/autoload.php';

// librerias de terceros
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;

// librerias propias
use Paw\Core\Router;
use Paw\Core\Config;
use Paw\Core\Request;
use Paw\Core\Database\ConnectionBuilder;

/**
 * 1) DOTENV
 * configurando el dotenv - para las variables de entorno 
 */
$dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/../');
$dotenv->load();

/**
 * 2) CONFIG
 * con las variables de entorno levantadas
 * inicializo la clase Config
 */
$config = new Config;

$esDesarrollo = $config->get('APP_ENV') === 'development';

if ($esDesarrollo) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

/**
 * 3) LOG
 * ahora configuro el logger
 */
$log = new Logger('mvc-app');
$handler = new StreamHandler(getenv('LOG_PATH'));
$handler->setLevel($config->get("LOG_LEVEL"));
$log->pushHandler($handler);

/**
 * 4) BASE DE DATOS - ConnectionBuilder
 */
$connectionBuilder = new ConnectionBuilder;
$connectionBuilder->setLogger($log);
$connection = $connectionBuilder->make($config);

/**
 * 5) WHOOPS 
 * configuro el whoops para los errores del servidor
 */
$whoops = new \Whoops\Run;

if ($esDesarrollo) { //En desarrollo se muestra la pagina detallada de whoops
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else { //En produccion el detalle queda solamente en el log
    $whoops->pushHandler(
        function (\Throwable $exception) use ($log) {
            $log->error(
                'Excepción no controlada.',
                [
                    'tipo' => get_class($exception),
                    'mensaje' =>$exception->getMessage()
                ]
            );

            http_response_code(500);

            header('Content-Type: text/html; ' . 'charset=UTF-8');

            echo '<!DOCTYPE html>'
                . '<html lang="es">'
                . '<head>'
                . '<meta charset="UTF-8">'
                . '<title>Error interno</title>'
                . '</head>'
                . '<body>'
                . '<main>'
                . '<h1>Error 500</h1>'
                . '<p>Ocurrió un error interno. '
                . 'Intentá nuevamente más tarde.</p>'
                . '<p><a href="/">'
                . 'Volver al inicio'
                . '</a></p>'
                . '</main>'
                . '</body>'
                . '</html>';

            return \Whoops\Handler\Handler::QUIT;
        }
    );
}

$whoops->register();

/**
 * 6) REQUEST
 * inicializo request 
 */
$request = new Request;

/**
 * 7) TWIG
 * Load template engine
 */
$templateDir = __DIR__ . $config->get('TEMPLATE_DIR');
$cacheDir = __DIR__ . $config->get('TEMPLATE_CACHE_DIR');

$log->debug('Moton de plantillas inicializando.');

try {
    $loader = new \Twig\Loader\FilesystemLoader($templateDir);
} catch (Exception $e) {
    $log->error('Error al cargar el loader: ' . $e->getMessage());
    exit;
}

try {
    $twig = new \Twig\Environment(
        $loader,
        [
            'cache' => $cacheDir,
            'debug' => $esDesarrollo,
            'autoescape' => 'html'
        ]
    );
} catch (Exception $e) {
    $log->error('Error al crear el entorno de Twig: ' . $e->getMessage());
    exit;
}

if ($esDesarrollo) {

    try {

        $twig->addExtension(new \Twig\Extension\DebugExtension());

    } catch (Exception $e) {

        $log->error('Error al agregar la extensión de depuración: ' . $e->getMessage());
        
        exit;

    }
}

/**
 * 7.1 TwigFilter: 
 * Aca agregamos unos filtros de twig
 */

 require __DIR__.'/Core/TwigFilters.php';

 /**
 * 7.2) Datos estructurados globales del sitio
 *Se generan en PHP para que Twig solamente los muestre
 *De esta forma se evita construir JSON manualmente dentro de las plantillas
 */
$baseUrl = rtrim($request->host(), '/');

$jsonLdFlags = JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
    | JSON_INVALID_UTF8_SUBSTITUTE;

$jsonLdOrganizacion = json_encode(
    [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => $baseUrl . '/#organization',
        'name' => 'Pawperties',
        'url' => $baseUrl . '/',
        'logo' => $baseUrl . '/assets/imgs/svg/logo-inmobiliaria.svg'
    ],
    $jsonLdFlags
);

$jsonLdSitioWeb = json_encode(
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => $baseUrl . '/#website',
        'name' => 'Pawperties',
        'url' => $baseUrl . '/',
        'inLanguage' => 'es-AR',
        'publisher' => ['@id' => $baseUrl . '/#organization']
    ],
    $jsonLdFlags
);

$twig->addGlobal('urlActualAbsoluta', $request->fullUrl());

$twig->addGlobal('jsonLdOrganizacion', $jsonLdOrganizacion ?: '{}');

$twig->addGlobal('jsonLdSitioWeb', $jsonLdSitioWeb ?: '{}');


/**
 * 8) ROUTER
 * inicializo router para luego agregarle las rutas
 */
$router = new Router;
$router->setLogger($log);


/**
 * 9) RUTAS
 * Aca van los enrutadores
 */
$router->get('/', 'PageController@index');

$router->get('/publicacion/new', 'PublicacionController@new');
$router->post('/publicacion/new', 'PublicacionController@new');
$router->get('/publicacion/ver', 'PublicacionController@verPublicacion');
$router->get('/mis_publicaciones', 'PublicacionController@listaPublicacionesPropietario');

$router->get('/mis_publicaciones/editar', 'PublicacionController@editarPublicacionPropia');
$router->post('/mis_publicaciones/editar', 'PublicacionController@editarPublicacionPropia');

$router->post('/mis_publicaciones/archivar', 'PublicacionController@administrarPublicacionPropia');
$router->post('/mis_publicaciones/reactivar', 'PublicacionController@administrarPublicacionPropia');
$router->post('/mis_publicaciones/eliminar', 'PublicacionController@administrarPublicacionPropia');

$router->get('/publicaciones/list', 'PublicacionController@list');
$router->get('/publicacion', 'PublicacionController@getImgPublicacion');
$router->get('/publicacion/imagen_destacada', 'PublicacionController@getImgPublicacion');
$router->post('/publicacion/guardarComentario', 'PublicacionController@guardarComentario');
$router->post('/publicacion/contactar-al-duenio-form', 'PublicacionController@contactarAlDuenio');
$router->get('/publicaciones/gestionar', 'PublicacionController@gestionarPublicaciones');
$router->post('/publicaciones/gestionar/aceptar', 'PublicacionController@actualizarEstadoPublicacion');
$router->post('/publicaciones/gestionar/rechazar', 'PublicacionController@actualizarEstadoPublicacion');

$router->post('/publicacion/reservar', 'ReservasController@reservarAlojamiento');
$router->get('/mis_publicaciones/reservas', 'ReservasController@verReservas'); // hecha
$router->post('/mis_publicaciones/reserva/aceptar','ReservasController@actualizarEstadoReserva');
$router->post('/mis_publicaciones/reserva/cancelar','ReservasController@actualizarEstadoReserva');
$router->post('/mis_publicaciones/reserva/rechazar','ReservasController@actualizarEstadoReserva');
$router->get('/api/publicaciones', 'PublicacionController@apiPublicaciones');
$router->get('/reservas/intervalos', 'ReservasController@obtenerIntervalosReserva');

$router->get('/mapa', 'PageController@mostrarMapa');
$router->post('/enviar-msj-contactanos', 'PageController@contacto');


/**
 * 9.1) Logeo de usuario
 */
$router->get('/iniciar-sesion', 'UsuarioController@login');
$router->post('/iniciar-sesion', 'UsuarioController@login');
$router->post('/cerrar-sesion', 'UsuarioController@logout');
$router->get('/recuperar-contrasenia', 'UsuarioController@resetPassword');
$router->post('/recuperar-contrasenia', 'UsuarioController@resetPassword');

$router->get('/registrarse', 'UsuarioController@register');
$router->post('/registrarse', 'UsuarioController@register');
$router->get('/usuario/mi_perfil', 'UsuarioController@perfil');
$router->post('/perfil/update', 'UsuarioController@update');

