<?php

namespace Paw\App\Controllers;

use PDOException;

use Paw\App\Models\UserCollection;
use Paw\App\Models\User;
use Paw\App\Utils\Verificador;
use Paw\Core\Controller;

class UsuarioController extends Controller
{

    public Verificador $verificador;
    public ?string $modelName = UserCollection::class;
    public $tipoUsuario;
    public $usuario; 

    public function __construct()
    {
        parent::__construct();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Inicia la sesión si no está iniciada
        }

        $this->verificador = new Verificador;
        $this->menu = $this->adjustMenuForSession($this->menu); 
    }

    public function adjustMenuForSession($menu) {
        global $log;
        if (isset($_SESSION['email'])) {
            // Si el usuario es de tipo "cliente", eliminar el menú de empleado
            $log->info("hay sesion: ", [$_SESSION]);
            if ($_SESSION['tipo'] === 'propietario') {
                // Filtra el menú para eliminar el menú de empleado si es necesario
                $menu = array_filter($menu, function ($item) {
                    return $item['href'] !== '/menu_empleado'; // Ajusta según sea necesario
                });
            }
            $this->tipoUsuario = $_SESSION['tipo'];
            setcookie('tipo_usuario', $this->tipoUsuario, time() + (86400 * 30), "/"); // La cookie expira en 30 días
        } else {
            $log->info("no existe sesion: ", [$_SESSION]);
            $menu = array_filter($menu, function ($item) {
                return !in_array($item['href'], ['/mis_publicaciones', '/usuario/mi_perfil']);
            });
            $log->info("DATOS THIS->MENU: ", [$menu]);
        }
    
        // Reindexar el array para que no tenga índices numéricos adicionales
        $menu = array_values($menu);
    
        return $menu;
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
            
            $user = new User($email, $contrasenia);

            $usuarioAutenticado = $this->model->findByEmailAndPassword($user->getEmail(), $user->getContrasenia());
            
            $log->info("usuarioAutenticado: ", [$usuarioAutenticado]);

            if ($usuarioAutenticado) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();  // Inicia la sesión si no está iniciada
                }
                $this->sesion_en_curso = true;
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

                    header('Location: /mis_publicaciones');
                    exit();                    

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

    public function logout() {
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

        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Inicia la sesión si no está iniciada
        }

        $userId = $this->getUserId(); // Ajusta esto según cómo manejes la sesión

        if($userId !== null){
            // Obtener los datos del usuario
            $usuario = $this->model->findById($userId);
    
            $this->logger->info("datos de usuario: ", [$usuario]);
            // Pasar los datos del usuario a la vista
            require $this->viewsDir . 'mi_perfil.view.php';
        }else{
            // Redirigir a la página de inicio si no está logueado
            header('Location: /');
            exit();
        }
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
