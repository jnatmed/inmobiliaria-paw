<?php

namespace Paw\App\Controllers;

use PDOException;

use Paw\App\Models\Usuario;
use Paw\App\Utils\Verificador;
use Paw\Core\Controller;

class UsuarioController extends Controller
{

    public Verificador $verificador;
    public ?string $modelName = Usuario::class;
    public $tipoUsuario;

    public function __construct()
    {
        parent::__construct();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Inicia la sesión si no está iniciada
        }
        $this->verificador = new Verificador;
        list($this->menuPerfil, $this->menuEmpleado) = $this->adjustMenuForSession($this->menuPerfil, $this->menuEmpleado); 
    }

    public function adjustMenuForSession($menuPerfil, $menuEmpleado) {
        if (isset($_SESSION['usuario'])) {
            // Si hay una sesión iniciada, quitar las opciones de iniciar y registrar
            $menuPerfil = array_filter($menuPerfil, function ($item) {
                return !in_array($item['href'], ['/iniciar_sesion', '/registrar_usuario']);
            });
            // Si el usuario es de tipo "cliente", eliminar el menú de empleado
            if ($_SESSION['tipo'] === 'cliente') {
                $menuEmpleado = [];
            }
            $this->tipoUsuario = $_SESSION['tipo'];
            setcookie('tipo_usuario', $this->tipoUsuario, time() + (86400 * 30), "/"); // La cookie expira en 30 días

        }else{
            $menuEmpleado = [];
            $menuPerfil = array_filter($menuPerfil, function ($item) {
                return !in_array($item['href'], ['/perfil_usuario', '/cerrar_sesion', '/ver_mi_pedido', '/ver_mi_reserva']);
            });            
        }

        return [$menuPerfil, $menuEmpleado];
    }


    public function isUserLoggedIn()
    {
        return isset($_SESSION['email']);
    }

    public function getUserId()
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public function getUserType()
    {
        return $_SESSION['tipo'] ?? 'anonimo';
    }

    public function login() {
        $titulo = 'PAW PROPERTIES | SESION';
        
        global $log;

        if ($this->request->method() == 'POST') {
            $email = strtolower($this->request->get('email'));
            $contrasenia = $this->request->get('contrasenia');
            
            $usuarioAutenticado = $this->model->findByUsernameAndPassword($email, $contrasenia);
            
            $log->info("usuarioAutenticado: ", [$usuarioAutenticado]);

            if ($usuarioAutenticado) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();  // Inicia la sesión si no está iniciada
                }
                // Guardar los datos del usuario en la sesión
                $_SESSION['email'] = $usuarioAutenticado['email'];
                $_SESSION['tipo'] = $usuarioAutenticado['tipo_usuario'];
                $this->tipoUsuario = $_SESSION['tipo'];
                $_SESSION['usuario_id'] = $usuarioAutenticado['id'];
                // Redirigir al usuario a la página principal

                $log->info("sesion: ",[$_SESSION]);

                header('Location: /');
                exit();
            } else {
                $this->tipoUsuario = 'anonimo';
                $resultado['error'] = 'Usuario o contraseña incorrectos';
                require $this->viewsDir . 'login.view.php';
            }
        }else{
            require $this->viewsDir . 'login.view.php';
        }
    
    }


    public function register() {
        $titulo = 'PAW PROPERTIES | REGISTRO';
    
        global $log;
    
        if ($this->request->method() == 'POST') {
            // Obtener los datos del formulario
            $email = $this->request->get('email');
            $nombre = $this->request->get('nombre');
            $contrasenia = $this->request->get('password');
            $contrasenia_repetida = $this->request->get('password_repetida');
            $telefono = $this->request->get('telefono');
            
            // Verificar si las contraseñas coinciden
            if ($contrasenia !== $contrasenia_repetida) {
                $resultado['error'] = 'Las contraseñas no coinciden';
                require $this->viewsDir . 'register.view.php';
            }
    
            try {
                // Crear un nuevo usuario
                $nuevoUsuario = [
                    'email' => $email,
                    'username' => $nombre,
                    'password' => password_hash($contrasenia, PASSWORD_DEFAULT), // Hashear la contraseña con salt
                    'telefono' => $telefono,
                    'tipo_usuario' => 'propietario'
                ];   
                
                // Insertar el nuevo usuario en la base de datos
                list($idUsuario, $resultado) = $this->model->insert('users', $nuevoUsuario);
                
                if ($resultado) {
                    $log->info("registro exitoso del usuario {$nombre}");
                    $resultado = [];
                    $resultado['exito'] = "registro exitoso del usuario {$nombre}";
                    require $this->viewsDir . 'register.view.php';
                } else {
                    $error = 'Error al registrar el usuario';
                    $log->error("error: ", [$error]);
                    $resultado['error'] = $error;
                    require $this->viewsDir . 'register.view.php';
                }
            } catch (PDOException $e) {

                $log->error("error: ", ["Error al registrar el usuario: " . $e->getMessage()]);
                
                // Mostrar un mensaje de error genérico al usuario
                $error = 'Error al registrar el usuario';
                $resultado['error'] = "Error al registrar el usuario: " . $e->getMessage();
                require $this->viewsDir . 'register.view.php';
            }
        }else{
            require $this->viewsDir . 'register.view.php';
        }
    }

    public function cerrar_sesion() {
        // Iniciar la sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Destruir todas las variables de sesión
        $_SESSION = [];

        // Si se desea destruir la sesión completamente, también se deben eliminar las cookies de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finalmente, destruir la sesión
        session_destroy();

        // Redirigir al usuario a la página principal
        header('Location: /');
        exit();
    }

    public function perfil() {

        $titulo = 'PAW PROPERTIES | PERFIL';

        $userId = $this->getUserId(); // Ajusta esto según cómo manejes la sesión

        // Obtener los datos del usuario
        $usuario = $this->model->findById($userId);

        // Pasar los datos del usuario a la vista
        require $this->viewsDir . 'mi_perfil.view.php';
    }


    public function verificarSesion()
    {
        global $log;
        if (!$this->isUserLoggedIn()) {
            $resultado = [
                "success" => false,
                "message" => "Debe iniciar sesión para realizar una reserva o pedido."
            ];
            $log->info("Intento de reserva sin sesión iniciada.");
            require $this->viewsDir . 'login.view.php';
            return;
        }          
    }

}
