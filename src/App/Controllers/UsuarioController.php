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
    public $menuAndSession;

    public function __construct()
    {
        parent::__construct();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Inicia la sesión si no está iniciada
        }

        $this->verificador = new Verificador;
        $this->menu = $this->adjustMenuForSession($this->menu); 

        $this->menuAndSession = [
            'isUserLoggedIn' => $this->isUserLoggedIn(),
            'menu' => $this->menu,
            'urlPublicacion' => $this->request->fullUrl(),
            'id_usuario' => $this->getUserId()
        ];

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
                return !in_array($item['href'], ['/mis_publicaciones', '/usuario/mi_perfil', '/mis_publicaciones/reservas']);
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

    public function getUserName()
    {
        return $_SESSION['nombre'];
    }

    public function getEmailAddress()
    {
        return $_SESSION['email'];
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
        $titulo = 'PAWPERTIES | SESION';

        if ($this->request->method() == 'POST') {
            $email = strtolower($this->request->get('email'));
            $contrasenia = $this->request->get('contrasenia');
            
            $user = new User($email, $contrasenia);

            $usuarioAutenticado = $this->model->findByEmailAndPassword($user->getEmail(), $user->getContrasenia());
            
            $this->logger->info("usuarioAutenticado: ", [$usuarioAutenticado]);

            if ($usuarioAutenticado) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();  // Inicia la sesión si no está iniciada
                }
                $this->sesion_en_curso = true;
                // Guardar los datos del usuario en la sesión
                $_SESSION['email'] = $usuarioAutenticado['email'];
                $_SESSION['tipo'] = $usuarioAutenticado['tipo_usuario'];
                $_SESSION['nombre'] = $usuarioAutenticado['nombre'];
                $this->tipoUsuario = $_SESSION['tipo'];
                $_SESSION['usuario_id'] = $usuarioAutenticado['id'];
                // Redirigir al usuario a la página principal

                $this->logger->info("sesion: ",[$_SESSION]);

                redirect('');
            } else {
                $this->tipoUsuario = 'anonimo';
                $resultado = [
                    'resultado' => [
                        'error' => 'Usuario o contraseña incorrectos'
                    ],
                    'tipoUsuario' => $this->tipoUsuario
                ];
                view('login.view', $resultado);
            }
        }else{
            view('login.view', ['titulo' => $titulo]);
        }
    
    }


    public function register() {
        $titulo = 'PAWPERTIES | REGISTRO';
    
        global $log;
    
        if ($this->request->method() == 'POST') {
            // Obtener los datos del formulario
            $email = $this->request->get('email');
            $nombre = $this->request->get('nombre');
            $apellido = $this->request->get('apellido');
            $contrasenia = $this->request->get('contrasenia');
            $contrasenia_repetida = $this->request->get('contrasenia_check');
            $telefono = $this->request->get('telefono');
            
            // Verificar si las contraseñas coinciden
            if ($contrasenia !== $contrasenia_repetida) {
                $resultado['error'] = 'Las contraseñas no coinciden';

                redirect('registrarse');
            }
    
            try {
                // Crear un nuevo usuario
                $nuevoUsuario = [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $email,
                    'contrasenia' => password_hash($contrasenia, PASSWORD_DEFAULT), // Hashear la contraseña con salt
                    'telefono' => $telefono,
                    'tipo_usuario' => 'propietario'
                ];   
                
                // Insertar el nuevo usuario en la base de datos
                list($idUsuario, $resultado) = $this->model->insert('usuarios', $nuevoUsuario);
                
                if ($resultado) {
                    $log->info("registro exitoso del usuario {$nombre}");
                    $resultado = [];
                    $resultado['exito'] = "Registro exitoso del usuario: {$nombre} {$apellido}";

                    view('register-exito.view', array_merge(
                        ['exito' => $resultado['exito']],
                        $this->menuAndSession
                    ));

                } else {
                    $error = 'Error al registrar el usuario';
                    $log->error("error: ", [$error]);
                    $resultado['error'] = $error;

                    view('register.view', array_merge(
                        ['error' => $resultado['error']],
                        $this->menuAndSession
                    ));
                }
            } catch (PDOException $e) {

                $log->error("error: ", ["Error al registrar el usuario: " . $e->getMessage()]);
                
                // Mostrar un mensaje de error genérico al usuario
                $error = 'Error al registrar el usuario';
                $resultado['error'] = "Error al registrar el usuario: " . $e->getMessage();

                view('register.view', array_merge(
                    ['error' => $resultado['error']],
                    $this->menuAndSession
                ));
            }
        }else{
            view('register.view', array_merge(
                $this->menuAndSession
            ));
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
        redirect('');
    }

    public function perfil() {

        $titulo = 'PAWPERTIES | PERFIL';

        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Inicia la sesión si no está iniciada
        }

        $userId = $this->getUserId(); // Ajusta esto según cómo manejes la sesión

        if($userId !== null){
            // Obtener los datos del usuario
            $usuario = $this->model->findById($userId);
    
            $this->logger->info("datos de usuario: ", [$usuario]);
            // Pasar los datos del usuario a la vista
            view('mi_perfil.view', array_merge(
                [
                    'usuario' => $usuario,
                ],
                $this->menuAndSession
            ));
        }else{
            // Redirigir a la página de inicio si no está logueado

            redirect('iniciar-sesion');
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
            redirect('iniciar-sesion');
        }          
    }

    public function resetPassword()
    {
        if ($this->request->method() == 'POST'){
            $email = htmlspecialchars($this->request->get('email'));
            /**
             * 1) buscar email en la tabla usuarios
             */
            /**
             * 2) si exite creo un token
             * e inserto en la tabla password_resets
             * (id_user, token_password)
             */
            /**
             * 3) envio un correo con este token al 
             * email enviado
             */
        }else{
            view('password_reset_request.view', array_merge(
                $this->menuAndSession
            ));
        }
    }

}
