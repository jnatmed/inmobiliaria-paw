<?php

require __DIR__.'/../vendor/autoload.php';

// librerias de terceros
use Monolog\Logger;
use Monolog\Level;
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

/**
 * 3) LOG
 * ahora configuro el logger
 */
$log = new Logger('mvc-app');
$handler = new StreamHandler(getenv('LOG_PATH'));
$handler->setLevel($config->get("LOG_LEVEL"));
$handler->setLevel(Level::Debug);
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
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
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

$log->info('Loading template engine...', [$templateDir, $cacheDir]);

try {
    $loader = new \Twig\Loader\FilesystemLoader($templateDir);
} catch (Exception $e) {
    $log->error('Error al cargar el loader: ' . $e->getMessage());
    exit;
}

try {
    $twig = new \Twig\Environment($loader, [
        'cache' => $cacheDir, 
        'debug' => true,
    ]);
} catch (Exception $e) {
    $log->error('Error al crear el entorno de Twig: ' . $e->getMessage());
    exit;
}

try {
    $twig->addExtension(new \Twig\Extension\DebugExtension());
} catch (Exception $e) {
    $log->error('Error al agregar la extensión de depuración: ' . $e->getMessage());
    exit;
}

/**
 * 7.1 TwigFilter: 
 * Aca agregamos unos filtros de twig
 */

 require __DIR__.'/Core/TwigFilters.php';


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

$router->get('/publicaciones/list', 'PublicacionController@list');
$router->get('/publicacion', 'PublicacionController@getImgPublicacion');
$router->post('/publicacion/contactar-al-duenio-form', 'PublicacionController@contactarAlDuenio');
$router->get('/reserva', 'ReservasController@reservas');
$router->post('/publicacion/reservar', 'ReservasController@reservarAlojamiento');
$router->get('/publicaciones/gestionar', 'PublicacionController@gestionarPublicaciones');
$router->get('/publicaciones/gestionar/aceptar', 'PublicacionController@actualizarEstadoPublicacion');
$router->get('/publicaciones/gestionar/cancelar', 'PublicacionController@actualizarEstadoPublicacion');
$router->get('/publicaciones/gestionar/rechazar', 'PublicacionController@actualizarEstadoPublicacion');

$router->get('/mis_publicaciones/reservas', 'PublicacionController@verReservas'); // hecha
$router->get('/mis_publicaciones/reserva/aceptar', 'PublicacionController@actualizarEstadoReserva'); // hecha
$router->get('/mis_publicaciones/reserva/cancelar', 'PublicacionController@actualizarEstadoReserva'); // hecha
$router->get('/mis_publicaciones/reserva/rechazar', 'PublicacionController@actualizarEstadoReserva'); // hecha

$router->get('/reservas/intervalos', 'ReservasController@obtenerIntervalosReserva');

$router->get('/mapa', 'PublicacionController@mostrarMapa');

$router->get('/api/publicaciones', 'PublicacionController@apiPublicaciones');

/**
 * 9.1) Logeo de usuario
 */
$router->get('/iniciar-sesion', 'UsuarioController@login');
$router->post('/iniciar-sesion', 'UsuarioController@login');
$router->get('/cerrar-sesion', 'UsuarioController@logout');
$router->get('/recuperar-contrasenia', 'UsuarioController@resetPassword');
$router->post('/recuperar-contrasenia', 'UsuarioController@resetPassword');

$router->get('/registrarse', 'UsuarioController@register');
$router->post('/registrarse', 'UsuarioController@register');
$router->get('/usuario/mi_perfil', 'UsuarioController@perfil');

