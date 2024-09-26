<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;
// use Paw\App\Controllers\UsuarioController;
use Paw\App\Models\Mailer;
use Paw\App\Models\PublicacionCollection;
use Paw\Core\Database\QueryBuilder;

class PageController extends Controller
{

    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;
    public $mailer;
    public $menuAndSession;
    public $publicacionCollection;

    public function __construct()
    {
        global $log, $connection;

        parent::__construct();

        $this->uploader = new Uploader;

        $this->verificador = new Verificador;

        $this->usuario = new UsuarioController();

        $this->publicacionCollection = new PublicacionCollection();
        $this->publicacionCollection->setQueryBuilder(new QueryBuilder($connection, $log));

        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;

        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);
    }

    public function index()
    {
        $datos = [
              'titulo' => "PAWPERTIES | HOME",
              'resultadoContacto' => $this->request->getResultadoGuardardo('resultadoContacto')
            ];
            
        $this->request->setResultadoEnSesion('resultadoContacto', null);

        view('home.view', array_merge(
            $this->menuAndSession,
            $datos,
            $this->publicacionCollection->traerTipos(),
        ));
    }

    // Para la vista del mapa
    public function mostrarMapa()
    {
        $datos = ['titulo' => "PAWPERTIES | MAPA"];
        view("mapa-general.view", array_merge($this->menuAndSession, $datos));
    }

    // Para el form de contacto del home
    public function contacto()
    {
        $nombre = sanitize($this->request->get('nombre'), inputName: 'nombre');
        $apellido = sanitize($this->request->get('apellido'), inputName: 'apellido');
        $telefono = sanitize($this->request->get('telefono'), inputName: 'telefono');
        $consulta = sanitize($this->request->get('descripcion'), inputName: 'descripcion');
        $emailOrigen = sanitize($this->request->get('email'), inputName: 'email');
        $resultado = $this->mailer->enviarFormContacto($nombre, $apellido, $telefono, $emailOrigen, $consulta);

        $this->request->setResultadoEnSesion("resultadoContacto", $resultado);
        $this->logger->debug("info resultadoContacto: ", [$resultado]);

        redirect('');
    }
}
