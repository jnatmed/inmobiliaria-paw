<!DOCTYPE html>
<html lang="es">

<head>

<?php require __DIR__ . '/parts/head.view.php' ?>
<link rel="stylesheet" href="/assets/css/gestion-publicaciones.css">

</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>

    <main>

    <section class="gestionar-publicaciones-container">
        <h1 class="gestionar-publicaciones-title">Gestionar Publicaciones</h1>
        <?php if (!empty($publicaciones)): ?>
            <table class="gestionar-publicaciones-table">
                <thead>
                    <tr class="gestionar-publicaciones-header-row">
                        <th class="gestionar-publicaciones-header-cell">ID</th>
                        <th class="gestionar-publicaciones-header-cell">Título</th>
                        <th class="gestionar-publicaciones-header-cell">Descripción</th>
                        <th class="gestionar-publicaciones-header-cell">Estado</th>
                        <!-- Otros campos que necesites -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($publicaciones as $publicacion): ?>
                        <tr class="gestionar-publicaciones-row">
                            <td class="gestionar-publicaciones-cell"><?= htmlspecialchars($publicacion['id']) ?></td>
                            <td class="gestionar-publicaciones-cell"><?= htmlspecialchars($publicacion['nombre_alojamiento']) ?></td>
                            <td class="gestionar-publicaciones-cell"><?= htmlspecialchars($publicacion['descripcion_alojamiento']) ?></td>
                            <td class="gestionar-publicaciones-cell"><?= htmlspecialchars($publicacion['estado_descripcion']) ?></td>
                            <!-- Otros campos que necesites -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="gestionar-publicaciones-no-data">No hay publicaciones disponibles.</p>
        <?php endif; ?>
    </section>

    </main>

    <?php require __DIR__.'/parts/footer.view.php' ?>
</body>

</html>    