<?php

namespace Paw\App\Controllers;

use Exception;
use PDOException;

use Paw\App\Models\UserCollection;
use Paw\App\Models\User;
use Paw\App\Utils\Verificador;
use Paw\Core\Controller;
use Paw\App\Models\Mailer;

class UsuarioController extends Controller
{
    public Verificador $verificador;
    public ?string $modelName = UserCollection::class;
    public $tipoUsuario;
    public $menuAndSession;
    public $mailer;

    public function __construct()
    {
        global $log;

        parent::__construct();

        $this->setLogger($log);

        $this->mailer = new Mailer();

        if (session_status() == PHP_SESSION_NONE) {
            $usaHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            session_set_cookie_params([
                'lifetime' => 0, //dura hasta cerrar el navegador
                'path' => '/',
                'domain' => '',
                'secure' => $usaHttps, //cuando se usa HTTPS, la cookie solamente viaja mediante HTTPS
                'httponly' => true, //JavaScript no puede leer facilmente la cookie de sesion
                'samesite' => 'Lax' //limita el envio de la cookie desde otros sitios
            ]);

            session_start();  // Inicia la sesión si no está iniciada
        }

        $this->verificador = new Verificador;
        $this->menu = $this->adjustMenuForSession($this->menu);

        $this->menuAndSession = [
            'isUserLoggedIn' => $this->isUserLoggedIn(),
            'menu' => $this->menu,
            'urlPublicacion' => $this->request->fullUrl(),
            'id_usuario' => $this->getUserId(),
            'tipo_usuario' => $this->getUserType(),
            'email' => $this->getEmailAddress(),
            'nombre' => $this->getUserName(),
            'apellido' =>$this->getApellido(),
            'telefono' =>$this->getTelefono()
        ];
    }

    public function adjustMenuForSession($menu)
    {
        global $log;
        if (isset($_SESSION['email'])) {
            // Si el usuario es de tipo "propietario"
            
            if ($this->getUserType() === 1) {

                $menu = sacarDelMenu($menu, [
                    '/menu_empleado',
                    '/publicaciones/gestionar',
                ]);
            }
            // Si el usuario es de tipo "empleado"            
            if ($this->getUserType() === 2) {

                $menu = sacarDelMenu($menu, [
                    '/menu_empleado',
                    '/mis_publicaciones',
                    '/mis_publicaciones/reservas'
                ]);
            }
            // Si el usuario es de tipo "inquilino"
            if ($this->getUserType() === 3) {

                $menu = sacarDelMenu($menu, [
                    '/menu_empleado',
                    '/publicaciones/gestionar',
                    '/mis_publicaciones',
                    '/mis_publicaciones/reservas'
                ]);
            }

            $this->tipoUsuario = $_SESSION['tipo'];

        } else {
            $log->info("no existe sesion: ", [$_SESSION]);

            $menu = sacarDelMenu($menu, [
                '/publicacion/new',
                '/mis_publicaciones',
                '/usuario/mi_perfil',
                '/mis_publicaciones/reservas',
                '/publicaciones/gestionar'
            ]);

            $log->info("DATOS THIS->MENU: ", [$menu]);
        }

        // Reindexar el array para que no tenga índices numéricos adicionales
        $menu = array_values($menu);

        return $menu;
    }

    public function chequearSesion()
    {
        if ($this->isUserLoggedIn()){
            return;
        }

        $this->logger->info('Intento de acceder sin sesión iniciada.');

        if ($this->request->method() === 'GET'){
            $redirectTo = ltrim(
                $_SERVER['REQUEST_URI'] ?? '',
                '/'
            );

            $this->setRedirectTo($redirectTo);
        } else {
            //No se vuelve automaticamente a una accion POST.
            $this->setRedirectTo(null, true);
        }

        redirect('iniciar-sesion');        
    }

    public function isUserLoggedIn()
    {
        return isset($_SESSION['email'], $_SESSION['usuario_id']);
    }

    public function getUserName()
    {
        return $_SESSION['nombre'] ?? null;
    }

    public function getApellido()
    {
        return $_SESSION['apellido'] ?? null;
    }

    public function getTelefono()
    {
        return $_SESSION['telefono'] ?? null;
    }

    public function getEmailAddress()
    {
        return $_SESSION['email'] ?? null;
    }

    public function getUserId()
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public function getUserType()
    {
        return isset($_SESSION['tipo']) ? (int) $_SESSION['tipo'] : null;
    }

    public function login(){

        $titulo = 'PAWPERTIES | LOGIN';
        $referer = $this->request->referer();

        if ($this->request->method() === 'POST') {
            $errores = [];
            $email = $this->verificador->email($this->request->post('email'), 'email', $errores);

            //Se usa minimo 1 en login para no bloquear cuentas antiguas que tengan contraseñas cortas.
            $contrasenia = $this->verificador->password(
            $this->request->post('contrasenia'),
                'contrasenia',
                $errores,
                1
            );

            if (!empty($errores)) {
                view('login.view', array_merge(
                    [
                        'titulo' => $titulo,
                        'resultado' => ['error' => 'Los datos ingresados no son válidos.']
                    ],
                    $this->menuAndSession
                ));

                return;
            }

            $user = new User($email, $contrasenia);

            $usuarioAutenticado = $this->model->findByEmailAndPassword($user->getEmail(), $user->getContrasenia());

            if ($usuarioAutenticado) {
                
                //Se camie el identificador de la sesion despues del login para evitar fijacion de sesion
                session_regenerate_id(true);

                $this->sesion_en_curso = true;

                $_SESSION['email'] = $usuarioAutenticado['email'];

                $_SESSION['tipo'] = (int) $usuarioAutenticado['tipo_usuario_id'];

                $_SESSION['nombre'] = $usuarioAutenticado['nombre'];

                $_SESSION['apellido'] = $usuarioAutenticado['apellido'];

                $_SESSION['telefono'] = $usuarioAutenticado['telefono'];

                $_SESSION['usuario_id'] = (int) $usuarioAutenticado['id'];

                $this->tipoUsuario = $_SESSION['tipo'];

                $this->logger->info(
                    'Inicio de sesión exitoso.',
                    ['usuario_id' => $_SESSION['usuario_id']]
                );

                $redirectTo = $this->getRedirectTo();

                if (!empty($redirectTo)) {
                    $this->setRedirectTo(null, true);
                    redirect($redirectTo);
                    return;
                }

                if ($referer && $this->request->isUrlSafe($referer)){
                    $path = ltrim(
                        (string) parse_url(
                            $referer,
                            PHP_URL_PATH
                        ),
                        '/'
                    );

                    $query = parse_url(
                        $referer,
                        PHP_URL_QUERY
                    );

                    if (!empty($query)){
                        $path .= '?' . $query;
                    }

                    redirect($path);

                    return;
                }

                redirect('');
                return;
            }

            $this->tipoUsuario = 'anonimo';

            view('login.view', array_merge(
                [
                    'titulo' => $titulo,
                    'resultado' => ['error' => 'Usuario o contraseña incorrectos'],
                    'tipoUsuario' => $this->tipoUsuario
                ],
                $this->menuAndSession
            ));

            return;
        }

        view('login.view', array_merge(
            ['titulo' => $titulo],
            $this->menuAndSession
        ));
    }


    
    public function setRedirectTo($redirectUrl = null, $unset = false)
    {
        if (!$unset) {
            $_SESSION['redirect_to'] = $redirectUrl;
        } else {
            unset($_SESSION['redirect_to']);
        }
    }

    public function getRedirectTo()
    {
        return $_SESSION['redirect_to'] ?? null;
    }

    public function register(){
    
        $titulo = 'PAWPERTIES | REGISTRO';

        global $log;

        if ($this->request->method() === 'POST') {
            $errores = [];

            $email = $this->verificador->email(
                $this->request->post('email'),
                'email',
                $errores
            );

            $nombre = $this->verificador->texto(
                $this->request->post('nombre'),
                'nombre',
                $errores,
                true,
                2,
                100
            );

            $apellido = $this->verificador->texto(
                $this->request->post('apellido'),
                'apellido',
                $errores,
                true,
                2,
                100
            );

            $telefono = $this->verificador->telefono(
                $this->request->post('telefono'),
                'telefono',
                $errores
            );

            $contrasenia = $this->verificador->password(
                $this->request->post('contrasenia'),
                'contrasenia',
                $errores,
                8
            );

            $contraseniaRepetida = $this->verificador->password(
                $this->request->post('contrasenia-check'),
                'contrasenia-check',
                $errores,
                8
            );

            if ($contrasenia !== null && $contraseniaRepetida !== null && $contrasenia !== $contraseniaRepetida){
                $errores['contrasenia-check'] = 'Las contraseñas no coinciden.';
            }

            if (!empty($errores)) {
                view('register.view', array_merge(
                    [
                        'titulo' => $titulo,
                        'error' => implode(' ', $errores)
                    ],
                    $this->menuAndSession
                ));

                return;
            }

            try {

                $nuevoUsuario = [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $email,
                    'contrasenia' => password_hash($contrasenia, PASSWORD_DEFAULT),
                    'telefono' => $telefono,
                    'tipo_usuario_id' => 1
                ];

                list($idUsuario, $resultado) =$this->model->insert('usuarios', $nuevoUsuario);

                if (!is_null($idUsuario)) {
                    $log->info(
                        'Registro exitoso.',
                        ['usuario_id' => $idUsuario]
                    );

                    redirect('');
                    return;
                }

                view('register.view', array_merge(
                    [
                        'titulo' => $titulo,
                        'error' => 'No se pudo completar el registro.'
                    ],
                    $this->menuAndSession
                ));

                return;

            } catch (PDOException $e) {

                $log->error(
                    'Error al registrar el usuario.',
                    ['exception' => $e]
                );

                view('register.view', array_merge(
                    [
                        'titulo' => $titulo,
                        'error' => 'No se pudo completar el registro.'
                    ],
                    $this->menuAndSession
                ));

                return;

            } catch (Exception $e) {

                $log->error(
                    'Error al registrar el usuario.',
                    ['exception' => $e]
                );

                view('register.view', array_merge(
                    [
                        'titulo' => $titulo,
                        'error' => 'No se pudo completar el registro.'
                    ],
                    $this->menuAndSession
                ));

                return;
            }
        }

        view('register.view', array_merge(
            ['titulo' => $titulo],
            $this->menuAndSession
        ));
    }

    
    public function logout()
    {
        // Iniciar la sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Destruir todas las variables de sesión
        $_SESSION = [];

        // Si se desea destruir la sesión completamente, también se deben eliminar las cookies de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finalmente, destruir la sesión
        session_destroy();

        // Redirigir al usuario a la página principal
        redirect('');
    }

    public function perfil()
    {

        $titulo = 'PAWPERTIES | PERFIL';

        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Inicia la sesión si no está iniciada
        }

        $userId = $this->getUserId(); // Ajusta esto según cómo manejes la sesión

        if ($userId !== null) {
            // Obtener los datos del usuario
            $this->logger->info("UserId $userId");
            $usuario = $this->model->findById($userId);

            $this->logger->info("datos de usuario: ", [$usuario]);
            // Pasar los datos del usuario a la vista
            view('mi_perfil.view', array_merge(
                [
                    'usuario' => $usuario,
                    'titulo' => $titulo
                ],
                $this->menuAndSession
            ));
        } else {
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
        if ($this->request->method() == 'POST') {

            if ($this->request->get('user_id') !== null) {

                if ($this->request->get('password') !== null && $this->request->get('password_repeat') !== null) {

                    $id_user = htmlspecialchars($this->request->get('user_id'));
                    $password = htmlspecialchars($this->request->get('password'));
                    $password_repeat = htmlspecialchars($this->request->get('password_repeat'));

                    if ($password === $password_repeat) {

                        $resultado_insert = $this->model->actualizarContrasenia($id_user, $password);

                        if ($resultado_insert['exito']) {
                            view('login.view', [
                                'exito' => true,
                                "mensaje" => 'Contraseña reseteada. Ya puede iniciar sesion con la nueva contraseña.',
                            ]);
                        } else {
                            view('login.view', [
                                'exito' => false,
                                "mensaje" => 'Error al resetear la Contraseña.',
                            ]);
                        }
                    } else {
                        view('password_reset_request.view', array_merge(
                            ['exito' => false, "mensaje" => 'Error Cliente: las contraseñas no coinciden. Vuelva a intertarlo porfavor'],
                            $this->menuAndSession
                        ));
                    }
                } else {
                    $this->logger->error('Error al Resetar Contraseña. Unos de los parametros [contrasenia] no fue enviado. Porfavor, vuelva a intentarlo');
                    view('login.view', [
                        'exito' => false,
                        "mensaje" => 'Error al Resetar Contraseña. Unos de los parametros [contrasenia] no fue enviado. Porfavor, vuelva a intentarlo',
                    ]);
                }
            } else {
                $email = htmlspecialchars($this->request->get('email'));
                /**
                 * 1) buscar email en la tabla usuarios
                 */
                $busquedaCorreo = $this->model->buscarCorreoEnUsuarios($email);
                /**
                 * 2) si exite, creo un token
                 * e inserto en la tabla password_resets
                 * (id_user, token_password) 
                 */
                if ($busquedaCorreo['exito']) {
                    // Creación de token
                    $token = bin2hex(random_bytes(32));

                    // Insertar el token en la tabla password_resets
                    $insertTokenResult = $this->model->insertResetToken($busquedaCorreo['usuario']['id'], $token);

                    // Verificar si la inserción del token fue exitosa
                    if ($insertTokenResult[1]) {
                        /**
                         * 3) envio un correo con este token al 
                         * email enviado
                         */
                        $body = view('correoDeResetPassword.view', [
                            'url' => $this->request->fullUrl(),
                            'token' => $token
                        ], true);
                        // Aca enviar un correo al usuario 
                        $resultadoSend = $this->mailer->send(
                            $email,
                            "Pawproperties - Recuperar contraseña: ",
                            $body
                        );

                        if ($resultadoSend) {
                            $this->logger->error("Correo enviado con exito: ", [$resultadoSend]);
                            view('password_reset_request.view', array_merge(
                                ['exito' => true, "mensaje" => 'El mensaje de reseteo se envio con exito. Por favor, revise su casilla de correo'],
                                $this->menuAndSession
                            ));
                        } else {
                            $this->logger->error("ERROR al enviar el Correo: ", [$resultadoSend]);
                            view('password_reset_request.view', array_merge(
                                ['exito' => false, "mensaje" => 'Error Interno: no se pudo enviar el correo, debido a un problema del servidor'],
                                $this->menuAndSession
                            ));
                        }
                    } else {
                        $this->logger->error("ERROR al guardar el token: ", [$insertTokenResult]);
                    }
                } else {
                    $this->logger->error("ERROR no se encontro el correo: ", [$busquedaCorreo]);
                    view('password_reset_request.view', array_merge(
                        ['exito' => false, "mensaje" => 'Error Cliente: no se encontro el correo proporcionado'],
                        $this->menuAndSession
                    ));
                }
            }
        } else {
            if ($this->request->get('token') !== null) {

                $token = htmlspecialchars($this->request->get('token'));

                $resultadoBusquedaToken = $this->model->buscarToken($token);

                if ($resultadoBusquedaToken['exito']) {

                    $tokenCreado = strtotime($resultadoBusquedaToken['token']['created_at']);
                    $tiempoActual = time();

                    if ($tokenCreado && ($tiempoActual - $tokenCreado) < 3600) {
                        view('password_reset_request.view', array_merge(
                            ['resetear_de_contrasenia_solicitado' => true, 'user_id' => $resultadoBusquedaToken['token']['user_id']],
                            $this->menuAndSession
                        ));
                    } else {
                        view('password_reset_request.view', array_merge(
                            ['exito' => false, "mensaje" => 'Token expirado. Vuelva a solicitar un nuevo reseteo de contraseña'],
                            $this->menuAndSession
                        ));
                    }
                } else {
                    view('password_reset_request.view', array_merge(
                        ['exito' => false, "mensaje" => 'Token no encontrado'],
                        $this->menuAndSession
                    ));
                }
            } else {
                view('password_reset_request.view', array_merge(
                    $this->menuAndSession
                ));
            }
        }
    }

    public function update()
    {
        $email = $this->request->get('email');

        $resultUpdate = $this->model->updateEmail($this->getUserId(), $email);

        redirect('usuario/mi_perfil');
    }
}
