<?php

require __DIR__.'/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

use Paw\Core\Router;
use Paw\Core\Config;
use Paw\Core\Request;
use Paw\Core\Database\ConnectionBuilder;

// echo phpinfo();

$dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/../');

$dotenv->load();

$config = new Config;

$whoops = new \Whoops\Run;

$log = new Logger('mvc-app');
$handler = new StreamHandler(getenv('LOG_PATH'));
$handler->setLevel($config->get("LOG_LEVEL"));
$handler->setLevel(Level::Debug);

$log->pushHandler($handler);

$log->info('Datos de Config', [
    "adapter" => $config->get('DB_ADAPTER'),
    "hostname" => $config->get('DB_HOSTNAME'),
    "dbname" => $config->get('DB_DBNAME'),
    "port" => $config->get('DB_PORT'),
    "charset" => $config->get('DB_CHARSET'),
]);


$connectionBuilder = new ConnectionBuilder;
$connectionBuilder->setLogger($log);
$connection = $connectionBuilder->make($config);



$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);

$whoops->register();

$request = new Request;

$router = new Router;
$router->setLogger($log);


// PageController
$router->get('/', 'PageController@index');

$router->get('/iniciar-sesion', 'UsuarioController@login');
$router->post('/iniciar-sesion', 'UsuarioController@login');
$router->get('/cerrar-sesion', 'UsuarioController@logout');

$router->get('/registrarse', 'UsuarioController@register');
$router->post('/registrarse', 'UsuarioController@register');
$router->get('/usuario/mi_perfil', 'UsuarioController@perfil');


//usuario/mi_perfil $router->get('/publicacion/buscar', 'PublicacionController@perfil');
$router->get('/publicacion/new', 'PublicacionController@new');
$router->post('/publicacion/new', 'PublicacionController@new');
$router->get('/publicacion/ver', 'PublicacionController@verPublicacion');
$router->get('/mis_publicaciones', 'PublicacionController@listaPublicacionesPropietario');

$router->get('/publicaciones/list', 'PublicacionController@list');
$router->get('/publicacion', 'PublicacionController@getImgPublicacion');
$router->post('/publicacion/contactar-al-duenio-form', 'PublicacionController@contactarAlDuenio');

$router->get('/reserva', 'ReservasController@reservas');
$router->post('/publicacion/reservar', 'ReservasController@reservarAlojamiento');

$router->get('/mis_publicaciones/reservas', 'PublicacionController@verReservas'); // hecha
$router->get('/mis_publicaciones/reserva/aceptar', 'PublicacionController@actualizarEstadoReserva'); // hecha
$router->get('/mis_publicaciones/reserva/cancelar', 'PublicacionController@actualizarEstadoReserva'); // hecha
$router->get('/mis_publicaciones/reserva/rechazar', 'PublicacionController@actualizarEstadoReserva'); // hecha



$router->get('/reservas/intervalos', 'ReservasController@obtenerIntervalosReserva');
