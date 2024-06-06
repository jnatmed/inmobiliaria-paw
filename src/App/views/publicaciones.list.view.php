<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__.'/parts/head.view.php' ?>
</head>

<body class="home">
    <?php require __DIR__.'/parts/header.view.php' ?>

    <main class="main-home">
        <h2>Lista de Publicaciones</h2>
            <ul class="publicaciones-list">
        <?php foreach ($publicaciones as $publicacion): ?>
            <li class="publicacion-item">
                <h3 class="publicacion-titulo"><?= htmlspecialchars($publicacion['nombre_alojamiento']) ?></h3>
                <p class="publicacion-datos">Propietario: <?= htmlspecialchars($publicacion['nombre'] . ' ' . $publicacion['apellido']) ?></p>
                <p class="publicacion-datos">Dirección: <?= htmlspecialchars($publicacion['direccion']) ?>, <?= htmlspecialchars($publicacion['localidad']) ?>, <?= htmlspecialchars($publicacion['provincia']) ?></p>
                <p class="publicacion-datos">Teléfono: <?= htmlspecialchars($publicacion['telefono']) ?></p>
                <p class="publicacion-datos">Email: <?= htmlspecialchars($publicacion['email']) ?></p>
                <p class="publicacion-datos">Capacidad Máxima: <?= htmlspecialchars($publicacion['capacidad_maxima']) ?></p>
                <p class="publicacion-datos">Tipo de Alojamiento: <?= htmlspecialchars($publicacion['tipo_alojamiento']) ?></p>
                <p class="publicacion-datos">Cantidad de Baños: <?= htmlspecialchars($publicacion['cant_banios']) ?></p>
                <p class="publicacion-datos">Cantidad de Dormitorios: <?= htmlspecialchars($publicacion['cantidad_dormitorios']) ?></p>
                <p class="publicacion-datos">Instalaciones: 
                    <?= $publicacion['cochera'] ? '<span class="instalacion">Cochera</span> ' : '' ?>
                    <?= $publicacion['pileta'] ? '<span class="instalacion">Pileta</span> ' : '' ?>
                    <?= $publicacion['aire_acondicionado'] ? '<span class="instalacion">Aire Acondicionado</span> ' : '' ?>
                    <?= $publicacion['wifi'] ? '<span class="instalacion">Wi-Fi</span> ' : '' ?>
                </p>
                <p class="publicacion-datos">Normas: <?= nl2br(htmlspecialchars($publicacion['normas_alojamiento'])) ?></p>
                <p class="publicacion-datos">Descripción: <?= nl2br(htmlspecialchars($publicacion['descripcion_alojamiento'])) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
    </main>

    <?php require __DIR__.'/parts/footer.view.php' ?>
</body>
</html>
