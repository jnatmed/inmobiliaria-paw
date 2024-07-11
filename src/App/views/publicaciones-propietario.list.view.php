<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__.'/parts/head.view.php' ?>
    <script src="/assets/js/mis-publicaciones.js"></script>

</head>

<body class="home">
    <?php require __DIR__.'/parts/header.view.php' ?>

    <main class="main-home">
            <section class="container_publicaciones">
<!--            <aside class="filtro_publicaciones">
                <h3>Filtrar por:</h3>
                <form action="" method="get" class="form_filtros_publicaciones">
                    <label for="ordenar">Ordenar por:</label>
                    <select name="ordenar" id="ordenar">
                        <option value="precio_asc">Precio más bajo</option>
                        <option value="ubicacion">Ubicación</option>
                    </select>
                    <label for="ubicacion">Ubicación:</label>
                    <input type="text" name="ubicacion" id="ubicacion" value="Capital Federal, Argentina">
                    <button type="submit">Aplicar filtros</button>
                </form>
            </aside> -->
            <section class="publicaciones_list">
                <h2 class="h2-titulo-publicaciones">Mis Publicaciones</h2>
                <ul class="publicaciones-list">
                    <?php foreach ($publicaciones as $publicacion): ?>
                        <li class="publicacion-item">
                            <nav class="nav-destacados">
                                <ul class="carousel">
                                    <?php foreach ($publicacion['imagenes'] as $imagen): ?>
                                        <li class="carousel-item">
                                            <img class="carousel-img" src="/publicacion?id_pub=<?= $publicacion['id'] ?>&id_img=<?= $imagen['id_imagen'] ?>" alt="<?= $imagen['nombre_imagen'] ?>">
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <button class="flechas prevButton"></button>
                                <button class="flechas nextButton"></button>
                                <section class="info_publicacion">
                                    <h3 class="publicacion-titulo"><?= htmlspecialchars($publicacion['nombre_alojamiento']) ?></h3>
                                    <p class="publicacion-datos">Dirección: <?= htmlspecialchars($publicacion['direccion']) ?>, <?= htmlspecialchars($publicacion['localidad']) ?>, <?= htmlspecialchars($publicacion['provincia']) ?></p>
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
                                    <a class="whatsapp-link" href="https://web.whatsapp.com/send?phone=<?= htmlspecialchars($publicacion['telefono']) ?>" target="_blank">
                                        <img src="/assets/imgs/svg/whatsapp-icon.png" alt="WhatsApp">
                                    </a>
                                    <a class="contact-link email-link" href="mailto:<?= htmlspecialchars($publicacion['email']) ?>" target="_blank">
                                        <img src="/assets/imgs/svg/email_green.png" alt="Email">
                                    </a>                                   
                                </section>    
                            </nav>            
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </section>
    </main>

    <?php require __DIR__.'/parts/footer.view.php' ?>
</body>

</html>
