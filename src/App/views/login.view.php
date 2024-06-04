<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
</head>

<body class="login_bg">

    <main class="login_form_container">

        <section class="login_form_header">
            <h1 class="icono-header-pawperties">PawPerties</h1>
            <h1 class="login_title">INICIO SESIÓN</h1>
        </section>

        <section class="login_form">
            <form action="/iniciar-sesion" method="post">

                <label for="email">Email</label>
                <input required type="email" name="email" id="email" placeholder="Ingrese su correo electrónico">
                
                <label for="contrasenia">Contraseña</label>
                <input required type="password" name="contrasenia" id="contrasenia" placeholder="Ingrese su contraseña">
                
                <input type="submit" value="INICIAR SESION">
            

            </form>

            <form action="/registrarse" method="get">
                <input type="submit" value="REGISTRARSE">
            </form>

            <a href="/recuperar-contrasenia">¿Olvidaste tu contraseña?</a>
        </section>

    </main>

</body>

</html>
