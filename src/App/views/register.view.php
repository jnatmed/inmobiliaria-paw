<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
</head>

<body class="login_bg">

    <main class="login_form_container">

        <section class="login_form_header">
            <h1 class="icono-header-pawperties">PawPerties</h1>
            <h1 class="login_title">REGISTRAR USUARIO</h1>
        </section>

        <section class="login_form">

            <form action="/registrarse" method="post">

                <label for="username">Nombre de usuario (*)</label>
                <input required type="text" name="username" id="username" placeholder="Ingrese su nombre de usuario">

                <label for="email">Email (*)</label>
                <input required type="email" name="email" id="email" placeholder="Ingrese su correo electrónico">

                <label for="contrasenia">Contraseña (*)</label>
                <input required type="password" name="contrasenia" id="contrasenia" placeholder="Ingrese su contraseña">

                <label for="contrasenia-check">Ingrese nuevamente su contraseña (*)</label>
                <input required type="password" name="contrasenia-check" id="contrasenia-check" placeholder="Ingrese su contraseña">

                <label for="telefono">Numero de telefono</label>
                <input required type="tel" name="telefono" id="telefono" placeholder="Ingrese su numero de telefono">

                <input type="submit" value="REGISTRARSE">

            </form>
        </section>

    </main>

</body>

</html>