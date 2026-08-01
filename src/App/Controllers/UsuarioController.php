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
        global $log, $twig;

        parent::__construct();

        $this->setLogger($log);

        $this->mailer = new Mailer();
        $this->mailer->setLogger($log);

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

        $csrfToken = $this->request->csrfToken();
        $twig->addGlobal('csrf_token', $csrfToken);

        $this->menu = $this->adjustMenuForSession($this->menu);

        $this->menuAndSession = [
            'isUserLoggedIn' => $this->isUserLoggedIn(),
            'menu' => $this->menu,
            'urlPublicacion' => $this->request->fullUrl(),
            'id_usuario' => $this->getUserId(),
            'tipo_usuario' => $this->getUserType(),
            'email' => $this->getEmailAddress(),
            'nombre' => $this->getUserName(),
            'apellido' => $this->getApellido(),
            'telefono' => $this->getTelefono(),
            'csrf_token' => $csrfToken
        ];
    }

    public function adjustMenuForSession($menu){

        if (isset($_SESSION['email'], $_SESSION['usuario_id'])) {
            $tipoUsuario = $this->getUserType();

            //Tipo 1 y 3: usuarios comunes: pueden buscar propiedades, publicar, ver sus propiedades, sus reservas y reservar propiedades ajenas. No moderan la pagina
            if (in_array($tipoUsuario, [1, 3], true)) {
                $menu = sacarDelMenu($menu, [
                    '/menu_empleado',
                    '/publicaciones/gestionar'
                ]);
            }

            //Tipo 2: empleado moderador: puede gestionar publicaciones pendientes pero no publica ni administra reservas propias
            elseif ($tipoUsuario === 2) {
                $menu = sacarDelMenu($menu, [
                    '/menu_empleado',
                    '/publicacion/new',
                    '/mis_publicaciones',
                    '/mis_publicaciones/reservas'
                ]);
            }

            //Tipo desconocido
            else {
                $menu = sacarDelMenu($menu, [
                    '/publicacion/new',
                    '/mis_publicaciones',
                    '/mis_publicaciones/reservas',
                    '/publicaciones/gestionar'
                ]);
            }

            $this->tipoUsuario = $tipoUsuario;

        } else {
            $menu = sacarDelMenu($menu, [
                '/publicacion/new',
                '/mis_publicaciones',
                '/usuario/mi_perfil',
                '/mis_publicaciones/reservas',
                '/publicaciones/gestionar'
            ]);
        }

        return array_values($menu);
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

        exit;
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

    public function chequearCsrf(): void{

        if ($this->request->method() !== 'POST') {
            return;
        }

        if ($this->request->csrfTokenEsValido()) {
            return;
        }

        $this->logger->warning(
            'Solicitud POST rechazada por token CSRF inválido.',
            [
                'usuario_id' => $this->getUserId(),

                'ruta' => $this->request->uri()
            ]
        );

        http_response_code(403);

        view(
            'errors/forbidden.view',
            array_merge(
                [
                    'error_lead' => 'La solicitud fue rechazada',
                    'error_message' => 'La solicitud no es válida o expiró. ' . 'Recargá la página e intentá nuevamente.'
                ],
                $this->menuAndSession
            )
        );

        exit;
    }

    public function chequearTiposPermitidos(array $tiposPermitidos): void{

        //Primero se verifica que exista una sesión. Si no existe, chequearSesion() redirige al login.
        $this->chequearSesion();

        $tipoActual = $this->getUserType();

        if (in_array($tipoActual, $tiposPermitidos, true)) {
            return;
        }

        $this->logger->warning(
            'Intento de acceder a una función sin autorización.',
            [
                'usuario_id' => $this->getUserId(),
                'tipo_usuario' => $tipoActual,
                'tipos_permitidos' => $tiposPermitidos,
                'ruta' => $this->request->uri()
            ]
        );

        http_response_code(403);

        view(
            'errors/forbidden.view',
            array_merge(
                [
                    'error_message' => 'No tenés permiso para realizar esta acción.'
                ],
                $this->menuAndSession
            )
        );

        exit;
    }

    public function login(){

        $titulo = 'PAWPERTIES | LOGIN';
        $referer = $this->request->referer();

        if ($this->request->method() === 'POST') {

            $this->chequearCsrf();

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

            $this->chequearCsrf();

            $errores = [];

            $valoresFormulario = [
                'email' =>
                    is_string($this->request->post('email'))
                        ? trim($this->request->post('email'))
                        : '',

                'nombre' =>
                    is_string($this->request->post('nombre'))
                        ? trim($this->request->post('nombre'))
                        : '',

                'apellido' =>
                    is_string($this->request->post('apellido'))
                        ? trim($this->request->post('apellido'))
                        : '',

                'telefono' =>
                    is_string($this->request->post('telefono'))
                        ? trim($this->request->post('telefono'))
                        : ''
            ];

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
                        'error' => implode(' ', $errores),
                        'valores' => $valoresFormulario
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

        $this->chequearCsrf();

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

    public function perfil(){

        $this->chequearSesion();

        $titulo = 'PAWPERTIES | PERFIL';

        $userId = (int) $this->getUserId();

        $usuario = $this->model->findById($userId);

        if (!$usuario) {

            http_response_code(404);

            view(
                'errors/not-found.view',
                array_merge(
                    [
                        'titulo' => $titulo,
                        'error_message' => 'El usuario no existe.'
                    ],
                    $this->menuAndSession
                )
            );

            return;
        }

        $resultadoPerfil = $this->request->getResultadoGuardado('resultadoPerfil');

        $this->request->eliminarResultadoEnSesion('resultadoPerfil');

        view(
            'mi_perfil.view',
            array_merge(
                [
                    'usuario' => $usuario,
                    'titulo' => $titulo,
                    'resultadoPerfil' => $resultadoPerfil
                ],
                $this->menuAndSession
            )
        );
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

    public function resetPassword(){
        
        $titulo = 'PAWPERTIES | RECUPERAR CONTRASEÑA';

        $mensajeGenerico = 'Si el correo está registrado, vas a recibir un mensaje con las instrucciones para restablecer la contraseña.';

        if ($this->request->method() === 'POST') {

            $this->chequearCsrf();

            $resetToken = $this->request->post('reset_token');

            //Segundo paso, cambiar la contraseña
            if (is_string($resetToken) && $resetToken !== '') {
                
                $errores = [];

                if (!preg_match('/\A[a-f0-9]{64}\z/i', $resetToken)) {
                    $errores['reset_token'] = 'El enlace de recuperación no es válido o expiró.';
                }

                $password = $this->verificador->password(
                    $this->request->post('password'),
                    'password',
                    $errores,
                    8,
                    255
                );

                $passwordRepeat = $this->verificador->password(
                    $this->request->post('password_repeat'),
                    'password_repeat',
                    $errores,
                    8,
                    255
                );

                if ($password !== null && $passwordRepeat !== null && $password !== $passwordRepeat) {
                    $errores['password_repeat'] = 'Las contraseñas no coinciden.';
                }

                if (!empty($errores)) {
                    view(
                        'password_reset_request.view',
                        array_merge(
                            [
                                'titulo' => $titulo,
                                'resetear_de_contrasenia_solicitado' => true,
                                'reset_token' => $resetToken,
                                'exito' => false,
                                'mensaje' => implode(' ', $errores)
                            ],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                //Se vuelve a buscar el token, no alcanza con haberl validado cuuando se abrio el enlace
                $resultadoToken = $this->model->buscarToken($resetToken);

                $registroToken = $resultadoToken['exito'] ? ($resultadoToken['token'] ?? null) : null;

                $creadoEn = is_array($registroToken) ? strtotime($registroToken['created_at'] ?? '') : false;

                $segundosTranscurridos = $creadoEn !== false ? time() - $creadoEn : null;

                $tokenVigente = is_array($registroToken) && $segundosTranscurridos !== null && $segundosTranscurridos >= 0 && $segundosTranscurridos < 3600;

                if (!$tokenVigente) {
                    view(
                        'password_reset_request.view',
                        array_merge(
                            [
                                'titulo' => $titulo,
                                'exito' => false,
                                'mensaje' => 'El enlace de recuperación no es válido o expiró.'
                            ],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                //El id se toma del token validado
                $userId = (int) $registroToken['user_id'];

                $resultadoActualizacion = $this->model->actualizarContrasenia($userId, $password);

                if (!$resultadoActualizacion['exito']) {

                    http_response_code(500);

                    view(
                        'password_reset_request.view',
                        array_merge(
                            [
                                'titulo' => $titulo,
                                'exito' => false,
                                'mensaje' => 'No se pudo actualizar la contraseña.'
                            ],
                            $this->menuAndSession
                        )
                    );

                    return;
                }

                //Token de un solo uso
                $this->model->eliminarResetTokensPorUsuario($userId);

                $this->logger->info(
                    'Contraseña restablecida.',
                    ['usuario_id' => $userId]
                );

                view(
                    'login.view',
                    array_merge(
                        [
                            'titulo' => 'PAWPERTIES | LOGIN',
                            'exito' => true,
                            'mensaje' => 'Contraseña actualizada. Ya podés iniciar sesión.'
                        ],
                        $this->menuAndSession
                    )
                );

                return;
            }

            //Primer paso, solicitar recuperacion por mail
            $errores = [];

            $email = $this->verificador->email(
                $this->request->post('email'),
                'email',
                $errores
            );

            if (!empty($errores)) {
                view(
                    'password_reset_request.view',
                    array_merge(
                        [
                            'titulo' => $titulo,
                            'exito' => false,
                            'mensaje' => implode(' ', $errores)
                        ],
                        $this->menuAndSession
                    )
                );

                return;
            }

            try {
                $busquedaCorreo = $this->model->buscarCorreoEnUsuarios($email);

                //Solo se genera un correo si existe la cuenta. La respuesta visual será igual en ambos casos.
                if ($busquedaCorreo['exito']) {
                    $userId = (int) $busquedaCorreo['usuario']['id'];

                    //Invalidar solicitudes anteriores
                    $this->model->eliminarResetTokensPorUsuario($userId);

                    $token = bin2hex(random_bytes(32));

                    [$idToken, $tokenGuardado] = $this->model->insertResetToken($userId, $token);

                    if ($idToken !== null && $tokenGuardado === true) {
                        $body = view(
                            'correoDeResetPassword.view',
                            [
                                'url' =>$this->request->host() . '/recuperar-contrasenia',
                                'token' => $token
                            ],
                            true
                        );

                        $correoEnviado = $this->mailer->send(
                            $email,
                            'Pawproperties - Recuperar contraseña',
                            $body
                        );

                        if ($correoEnviado) {
                            $this->logger->info(
                                'Correo de recuperación procesado.',
                                ['usuario_id' => $userId]
                            );
                        } else {
                            //Si falla el correo, el token no queda activo
                            $this->model->eliminarResetTokensPorUsuario($userId);

                            $this->logger->warning(
                                'No se pudo enviar el correo de recuperación.',
                                ['usuario_id' => $userId]
                            );
                        }

                    } else {
                        $this->logger->error(
                            'No se pudo guardar un token de recuperación.',
                            ['usuario_id' => $userId]
                        );
                    }
                }

            } catch (Exception $e) {
                $this->logger->error(
                    'Error al procesar una recuperación de contraseña.',
                    ['mensaje' => $e->getMessage()]
                );
            }

            //No revelar si el mail exizste
            view(
                'password_reset_request.view',
                array_merge(
                    [
                        'titulo' => $titulo,
                        'exito' => true,
                        'mensaje' => $mensajeGenerico
                    ],
                    $this->menuAndSession
                )
            );

            return;
        }

        //get sin token, mostrar solicitud de correo
        $token = $this->request->query('token');

        if ($token === null) {
            view(
                'password_reset_request.view',
                array_merge(
                    ['titulo' => $titulo],
                    $this->menuAndSession
                )
            );

            return;
        }

        //get con token, validar antes de motrar el formulario de la contra
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
            view(
                'password_reset_request.view',
                array_merge(
                    [
                        'titulo' => $titulo,
                        'exito' => false,
                        'mensaje' =>'El enlace de recuperación no es válido o expiró.'
                    ],
                    $this->menuAndSession
                )
            );

            return;
        }

        $resultadoToken = $this->model->buscarToken($token);

        $registroToken = $resultadoToken['exito'] ? ($resultadoToken['token'] ?? null) : null;

        $creadoEn = is_array($registroToken) ? strtotime($registroToken['created_at'] ?? ''): false;

        $segundosTranscurridos = $creadoEn !== false ? time() - $creadoEn : null;

        $tokenVigente = is_array($registroToken) && $segundosTranscurridos !== null && $segundosTranscurridos >= 0 && $segundosTranscurridos < 3600;

        if (!$tokenVigente) {
            view(
                'password_reset_request.view',
                array_merge(
                    [
                        'titulo' => $titulo,
                        'exito' => false,
                        'mensaje' => 'El enlace de recuperación no es válido o expiró.'
                    ],
                    $this->menuAndSession
                )
            );

            return;
        }

        view(
            'password_reset_request.view',
            array_merge(
                [
                    'titulo' => $titulo,
                    'resetear_de_contrasenia_solicitado' => true,
                    'reset_token' => $token
                ],
                $this->menuAndSession
            )
        );
    }

    public function update(){

        $this->chequearSesion();
        $this->chequearCsrf();

        $errores = [];

        $email = $this->verificador->email(
            $this->request->post('email'),
            'email',
            $errores
        );

        if (!empty($errores)) {
            $this->request->setResultadoEnSesion(
                'resultadoPerfil',
                [
                    'exito' => false,
                    'mensaje' => implode(' ', $errores)
                ]
            );

            redirect('usuario/mi_perfil');
        }

        try {
            $usuarioConEseCorreo = $this->model->buscarCorreoEnUsuarios($email);

            if ($usuarioConEseCorreo['exito'] && ((int) $usuarioConEseCorreo['usuario']['id'] !== (int) $this->getUserId())) {

                $this->request->setResultadoEnSesion(
                    'resultadoPerfil',
                    [
                        'exito' => false,
                        'mensaje' => 'Ese correo ya está utilizado por otro usuario.'
                    ]
                );

                redirect('usuario/mi_perfil');
            }

            $resultado = $this->model->updateEmail(
                (int) $this->getUserId(),
                $email
            );

            if ($resultado['exito']) {
                
                //Sincronizar sesion y base de datos
                $_SESSION['email'] = $email;

                $this->request->setResultadoEnSesion(
                    'resultadoPerfil',
                    [
                        'exito' => true,
                        'mensaje' => 'El correo se actualizó correctamente.'
                    ]
                );

                $this->logger->info(
                    'Correo del perfil actualizado.',
                    ['usuario_id' => $this->getUserId()]
                );

            } else {
                $this->request->setResultadoEnSesion(
                    'resultadoPerfil',
                    [
                        'exito' => false,
                        'mensaje' => 'No se pudo actualizar el correo.'
                    ]
                );
            }

        } catch (Exception $e) {
            $this->logger->error(
                'Error al actualizar el correo del perfil.',
                [
                    'usuario_id' => $this->getUserId(),
                    'mensaje' => $e->getMessage()
                ]
            );

            $this->request->setResultadoEnSesion(
                'resultadoPerfil',
                [
                    'exito' => false,
                    'mensaje' => 'No se pudo actualizar el correo.'
                ]
            );
        }

        redirect('usuario/mi_perfil');
    }
}
