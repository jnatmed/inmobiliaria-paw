<!-- Muestra los filtros que se aplican en la busqueda -->
<?php if (!empty($zona) || !empty($tipo) || !empty($precio) || !empty($instalaciones)) : ?>
    <p class="applied-filters">Filtros: <?= $zona ?> <?= $tipo ?> <?= $precio ?> <?= implode(' ', $instalaciones) ?> </p>
<?php endif; ?>

<!-- Muestra la lista de publicaciones -->
<ul>
    <?php foreach ($publicaciones as $publicacion) : ?>
        <?php if ($publicacion !== null) : ?>
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
        <?php endif; ?>
    <?php endforeach; ?>
</ul>