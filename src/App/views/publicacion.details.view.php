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
                    Dueña directa sin comisiones servicios y expensas incluidos! Hermoso departamento para alquiler frente a la UADE! vistas panoramicas piso 18
                </h3>
                <p class="p-details-texto">
                Hermoso departamento con increíbles vistas panorámicas, ubicado en una de las zonas más exclusivas de la ciudad. Este espacioso hogar cuenta con amplias ventanas que permiten una abundante entrada de luz natural, creando un ambiente cálido y acogedor. Desde el salón principal, podrás disfrutar de impresionantes vistas que abarcan desde el horizonte urbano hasta los majestuosos paisajes naturales que rodean la metrópoli.

                El departamento ofrece tres elegantes habitaciones, cada una con su propio baño en suite y armarios empotrados de alta calidad. La cocina, diseñada con acabados modernos y electrodomésticos de última generación, es ideal tanto para los amantes de la cocina como para aquellos que prefieren la comodidad. Además, cuenta con un balcón privado, perfecto para relajarse al aire libre mientras se contempla la puesta de sol. Entre las comodidades adicionales se incluyen un gimnasio totalmente equipado, piscina en la azotea, y seguridad las 24 horas, asegurando una experiencia de vida lujosa y segura.
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
                 <a class="whatsapp-link" href="https://web.whatsapp.com/send?phone=<?= htmlspecialchars($publicacion['telefono']) ?>" target="_blank">
                    <img src="/assets/imgs/svg/whatsapp-icon.png" alt="WhatsApp">
                </a>
                <p>Al enviar estas aceptando los 
                    <a href="/publicacion/terminos-y-condiciones">términos y condiciones</a>
                </p>
                <hr>
                <article class="publicaciones-duenio">
                    <h5 class="h5-nombre-duenia">Lorena Fuensalida</h5>
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