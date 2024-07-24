<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
</head>

<body class="login_bg">

    <main class="login_form_container">

        <section class="login_form_header">
            <a href="/">
                <h1 class="icono-header-pawperties">PawPerties</h1>
            </a>
            <h1 class="login_title">REGISTRAR USUARIO</h1>
        </section>

        <section class="login_form">

            <form action="/registrarse" method="post">

                <label for="email">Email (*)</label>
                <input required type="email" name="email" id="email" placeholder="Ingrese su correo electrónico">

                <label for="username">Nombre (*)</label>
                <input required type="text" name="nombre" id="nombre" placeholder="Ingrese su nombre">

                <label for="username">Apellido (*)</label>
                <input required type="text" name="apellido" id="apellido" placeholder="Ingrese su apellido">

                <label for="contrasenia">Contraseña (*)</label>
                <input required type="password" name="contrasenia" id="contrasenia" placeholder="Ingrese su contraseña">

                <label for="contrasenia-check">Ingrese nuevamente su contraseña (*)</label>
                <input required type="password" name="contrasenia-check" id="contrasenia-check" placeholder="Ingrese su contraseña">

                <label for="telefono">Numero de telefono (*)</label>
                <input required type="tel" name="telefono" id="telefono" placeholder="Ingrese su numero de telefono">

                <input type="submit" value="REGISTRARSE">

                <?php if (!empty($resultado['error'])) : ?>
                    <p class="error-registro"><?php echo htmlspecialchars($resultado['error']); ?></p>
                <?php endif; ?>
            </form>
        </section>

    </main>

</body>

</html>