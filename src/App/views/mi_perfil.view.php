<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
    
    <link rel="stylesheet" href="/assets/css/perfil.css">

</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>

    <main class="profile-main">
        <aside class="profile-aside">
            <img src="/assets/imgs/perfil/tipe-<?= ($usuario['tipo_usuario'] == 'propietario') ? "administrator" : "visitor" ?>.png" alt="Imagen de perfil" class="profile-image">
            <h2 class="profile-name"><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
            <p class="profile-type"><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></p>
        </aside>

        <section class="profile-section">
            <h2>Detalles del Usuario</h2>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
            <p><strong>Apellido:</strong> <?php echo htmlspecialchars($usuario['apellido']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?></p>
        </section>
    </main>

    <?php require __DIR__.'/parts/footer.view.php' ?>
</body>

</html>    