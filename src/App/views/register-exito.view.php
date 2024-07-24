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
            <h1 class="login_title">EXITO!</h1>
        </section>

        <section class="login_form">

            <form action="/iniciar-sesion" method="post">
                <label><?= $resultado["exito"] ?></label>
                <input type="submit" value="INICIAR SESION">

            </form>
        </section>

    </main>

</body>

</html>