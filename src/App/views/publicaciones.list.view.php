<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
    <script src="/assets/js/publicaciones-list.js"></script>

    <script src="/assets/js/components/sliderPrecio.js" defer></script>
</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>

    <main class="main-home">
        <section class="container_publicaciones">

            <!-- FILTROS -->
            <aside class="filtro-container">
                <h2>Filtros</h2>
                <form method="GET" action="#" class="form-filtros">
                    <section class="filtro-group">
                        <p for="precio">Precio</p>
                        <input type="range" id="precio" name="precio" min="0" max="1000000" step="10000" value="1000000" oninput="actualizarPrecio(this.value)">
                        <h3 id="precio-valor">1000000</h3>
                    </section>

                    <section class="filtro-group">
                        <p>Tipo</p>
                        <label><input type="radio" name="tipo" value="casa"> Casa</label>
                        <label><input type="radio" name="tipo" value="departamento"> Departamento</label>
                        <label><input type="radio" name="tipo" value="quinta"> Quinta</label>
                    </section>


                    <section class="filtro-group">
                        <p>Instalaciones</p>
                        <label><input type="checkbox" name="instalaciones[]" value="cochera"> Cochera</label>
                        <label><input type="checkbox" name="instalaciones[]" value="pileta"> Pileta</label>
                        <label><input type="checkbox" name="instalaciones[]" value="aire_acondicionado"> Aire Acondicionado</label>
                        <label><input type="checkbox" name="instalaciones[]" value="wifi"> Wi-Fi</label>
                    </section>

                    <section class="botones-conteiner">
                        <button type="submit">Aplicar</button>
                        <button type="reset">Limpiar</button>
                    </section>
                </form>
            </aside>

            <!-- PUBLICACIONES -->
            <section class="publicaciones-list">
                <h2 class="h2-titulo-publicaciones">Lista de Publicaciones</h2>
                <ul>
                    <?php foreach ($publicaciones as $publicacion) : ?>
                        <li class="publicacion-item">
                            <nav class="nav-destacados">
                                <ul class="destacados">
                                    <?php foreach ($publicacion['imagenes'] as $imagen) : ?>
                                        <li class="destacado-item">
                                            <img class="destacado-img" src="/publicacion?id_pub=<?= $publicacion['id'] ?>&id_img=<?= $imagen['id_imagen'] ?>" alt="<?= $imagen['nombre_imagen'] ?>">
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <section class="info_publicacion">
                                    <h3 class="publicacion-precio">$<?= htmlspecialchars($publicacion['precio']) ?></h3>
                                    <h3 class="publicacion-titulo"><?= htmlspecialchars($publicacion['nombre_alojamiento']) ?></h3>
                                    <p class="publicacion-datos"> <?= nl2br(htmlspecialchars($publicacion['descripcion_alojamiento'])) ?></p>
                                    <p class="publicacion-datos"><?= htmlspecialchars($publicacion['localidad']) ?>, <?= htmlspecialchars($publicacion['provincia']) ?></p>
                                    <p class="publicacion-datos">Capacidad: <?= htmlspecialchars($publicacion['capacidad_maxima']) ?></p>
                                    <p class="publicacion-datos">Tipo: <?= htmlspecialchars($publicacion['tipo_alojamiento']) ?></p>
                                    <p class="publicacion-datos">Baños: <?= htmlspecialchars($publicacion['cant_banios']) ?></p>
                                    <p class="publicacion-datos">Dormitorios: <?= htmlspecialchars($publicacion['cantidad_dormitorios']) ?></p>
                                    <p class="publicacion-datos">Normas: <?= nl2br(htmlspecialchars($publicacion['normas_alojamiento'])) ?></p>
                                    <p class="publicacion-datos">
                                        <?= $publicacion['cochera'] ? '<span class="instalacion">Cochera</span> ' : '' ?>
                                        <?= $publicacion['pileta'] ? '<span class="instalacion">Pileta</span> ' : '' ?>
                                        <?= $publicacion['aire_acondicionado'] ? '<span class="instalacion">Aire Acondicionado</span> ' : '' ?>
                                        <?= $publicacion['wifi'] ? '<span class="instalacion">Wi-Fi</span> ' : '' ?>
                                        <?= $publicacion['estado_id'] ? "<span class='estado_publicacion estado_publicacion_{$publicacion['estado_id']}'>{$publicacion['estado_id']}</span>"  : '' ?>
                                    </p>
                                    <a href="/publicacion/ver?id_pub=<?= $publicacion['id'] ?>" class="ver-mas">Ver más ></a>
                                    <a class="whatsapp-link" href="https://web.whatsapp.com/send?phone=<?= htmlspecialchars($publicacion['telefono']) ?>" target="_blank">
                                        <img src="/assets/imgs/svg/whatsapp-icon.png" alt="WhatsApp">
                                    </a>
                                    <a class="contact-link email-link" href="mailto:<?= htmlspecialchars($publicacion['email']) ?>" target="_blank">
                                        <img src="/assets/imgs/svg/email_green.png" alt="Email">
                                    </a>
                                </section>
                            </nav>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/parts/footer.view.php' ?>
</body>

</html>