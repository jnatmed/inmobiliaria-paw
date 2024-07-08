<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>


    <main>
        <!-- imagenes del departamento en alquiler-->
        <section class="container-imagenes-publicacion">
            <h2 class="titulo-imagenes-detail">Detalles Publicacion</h2>
            <ul class="ul-imagenes-publicacion">
                <?php foreach($publicacion['imagenes'] as $imagen) : ?>
                <li class="li-imagen-publicacion">
                  <img class="carousel-img" 
                     src="/publicacion?id_pub=<?= $publicacion['id'] ?>&id_img=<?= $imagen['id_imagen'] ?>" 
                     alt="<?= $imagen['nombre_imagen'] ?>"
                   >
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <!-- detalles de la publicacion -->
        <section class="details-depto">
            <h3 class="titulo-details">
                 Bernardo de Irigoyen 
                 <p class="precio-publicacion">
                    USD 1.200
                 </p>
            </h3>
            <article class="article-details-texto">
                <h3 class="titulo-details-texto">
                <?= $publicacion['nombre_alojamiento'] ?>
                </h3>
                <p class="p-details-texto">
                    <?= $publicacion['descripcion_alojamiento'] ?>
                </p>
            </article>
            <!-- formulario de contacto  -->
            <article class="form-contacto-publicaciom">
                 <h4 class="h4-titulo-form">
                    Contactá al Dueño 
                 </h4>
                 <form action="/publicacion/contactar-dueño-form" method="post">
                     <input type="text" class="input-form" placeholder="Email: *">
                     <input type="phone" placeholder="Telefono: *">
                     <textarea name="" id="" placeholder="Mensaje *">"Hola, vi esta propiedad en PawProperties y quiero que me contacten. Gracias."</textarea>    
                     <input type="submit" value="Contactar" class="btn-contactar">
                 </form>
                 <a class="whatsapp-link" href="https://wa.me/<?= htmlspecialchars($publicacion['telefono']) ?>/?text=Hola, vi esta propiedad en Pawproperties y quiero más información por WhatsApp. <?= urlencode($fullUrl) ?>" target="_blank">
    <img src="/assets/imgs/svg/whatsapp-icon.png" alt="WhatsApp">
</a>
                <a class="whatsapp-link" href="https://api.whatsapp.com/send/?phone=<?= htmlspecialchars($publicacion['telefono']) ?>&text=Hola%2C+vi+esta+propiedad+en+Pawproperties+y+quiero+m%C3%A1s+informaci%C3%B3n+por+WhatsApp.+<?= urlencode($fullUrl) ?>&type=phone_number&app_absent=0" target="_blank">
                    <img src="/assets/imgs/svg/whatsapp-icon.png" alt="WhatsApp">
                </a>


                <p>Al enviar estas aceptando los 
                    <a href="/publicacion/terminos-y-condiciones">términos y condiciones</a>
                </p>
                <hr>
                <article class="publicaciones-duenio">
                    <h5 class="h5-nombre-duenia"><?= $publicacion['nombre']." ".$publicacion['apellido'] ?></h5>
                    <a href="/publicaciones/lista?id-duenio" class="link-avisos-duenio"></a>
                    <p>Ver teléfono</p>
                </article>
            </article>
            <!-- mapa donde figura la ubicacion del alquiler -->
            <article class="ubicacion-publicacion">
                <h3 class="titulo-ubicacion">Ubicacion</h3>
                <p>Bernardo de Irigoyen 700, Piso 18
                    San Telmo, Capital Federal
                </p>    
                <article class="mapid"></article>
            </article>
        </section>

    </main>

    <?php require __DIR__ . '/parts/footer.view.php' ?>
</body>

</html>