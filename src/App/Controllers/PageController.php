<?php

namespace Paw\App\Controllers;

use Paw\App\Utils\Verificador;
use Paw\App\Utils\Uploader;

use Paw\Core\Controller;
// use Paw\App\Controllers\UsuarioController;
use Paw\App\Models\Mailer;

class PageController extends Controller
{

    public Uploader $uploader;
    public Verificador $verificador;
    public $usuario;
    public $mailer;
    public $menuAndSession;

    public function __construct()
    {
        global $log;
        parent::__construct();

        $this->uploader = new Uploader;

        $this->verificador = new Verificador;

        $this->usuario = new UsuarioController();

        $this->menu = $this->usuario->adjustMenuForSession($this->menu);

        $this->menuAndSession = $this->usuario->menuAndSession;

        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);
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
