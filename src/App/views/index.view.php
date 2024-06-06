<!DOCTYPE html>
<html lang="es">

<head>

    <?php require __DIR__.'/parts/head.view.php' ?>

</head>

<body class="home">

    <?php require __DIR__.'/parts/header.view.php' ?>


    <main class="main-home">

        <section class="seccion-portada">
            <h1 class="h1-titulo-empresa">PAWPERTIES</h1>
            <h2 class="h2-titulo">
                Encontra el lugar perfecto para tu proximo destino
            </h2>
            <a href="/publicacion/new" class="enlace-publicar">Quiero Publicar Lugar</a>
            <form action="" class="form-busqueda-propiedad" method="GET">
                <label for="input-zona" class="label-form" id="input-zona">Zona</label>
                <input type="text" class="input-form-busqueda" placeholder="Elegi tu Zona...">
                <label for="input-tipo" class="label-form">Tipo</label>
                <input type="text" class="input-form-busqueda" id="input-tipo" placeholder="Tipo">
                <label for="input-categoria" class="label-form">Categoria</label>
                <input type="text" class="input-form-busqueda" id="input-categoria" placeholder="Categoria">
                <input type="submit" value="Buscar" class="btn-form-busqueda">
            </form>
        </section>
    
        <section class="seccion-que-es-pawperties">
            <h2 class="h2-que-es-pawperties">
                ¿Que es Pawperties?
            </h2>
            <article class="article-descripcion">
                <h3 class="title-descripcion">
                    Bievenido/a
                </h3>
                <p class="descripcion-pawperties">
                    Descubre destinos inspiradores, reserva tu refugio ideal y prepárate  para desconectar del mundo y recargar energías en lugares que exudan  frescura y buena vibra. ¡Tu escapada perfecta comienza aquí!.
                </p>
                <p>
                Si eres propietario de un alojamiento único y deseas compartirlo con  viajeros en busca de experiencias auténticas, ¡estás en el lugar  adecuado! En nuestra plataforma, ofrecemos la oportunidad de dar a  conocer tu espacio y conectar con una audiencia global de personas que  valoran la originalidad y la hospitalidad 
                </p>
            </article>
        </section>

        <section class="seccion-alojamientos-destacados">
            <h2 class="h2-alojamientos-destacados">
                Alojamientos Destacados
            </h2>
            <p class="p-descripcion-alojamientos">
            Descubre los alojamientos mejor calificados y altamente recomendados por  nuestro sitio por su excepcional servicio. Estas opciones han sido  elogiadas por su calidad, atención al detalle y la satisfacción de sus  huéspedes. Sumérgete en una experiencia inolvidable con nuestros  alojamientos destacados, donde la excelencia es una garantía.
            </p>
            <nav class="nav-destacados">
                <ul>
                    <li>
                        <img src="/imagen-1" alt="propiedad-1">
                    </li>
                    <li>
                    <img src="/imagen-2" alt="propiedad-2">
                    </li>
                    <li>
                    <img src="/imagen-3" alt="propiedad-3">
                    </li>
                </ul>
            </nav>
        </section>

        <section class="seccion-contactanos">
            <form action="/enviar-msj-contactanos" class="form-contactanos" method="post">
                <label for="input-nombre">Nombre:</label>
                <input type="text" id="input-nombre" name="nombre">

                <label for="input-apellido">Apellido:</label>
                <input type="text" id="input-apellido" name="apellido">

                <label for="input-telefono">Teléfono:</label>
                <input type="tel" id="input-telefono" name="telefono">

                <label for="input-email">Email:</label>
                <input type="email" id="input-email" name="email">

                <label for="input-descripcion">Descripción:</label>
                <input type="text" id="input-descripcion" name="descripcion">

                <label for="input-ubicacion">Ubicación:</label>
                <input type="text" id="input-ubicacion" name="ubicacion">

                <label for="input-telefono2">Teléfono 2:</label>
                <input type="tel" id="input-telefono2" name="telefono2">

                <input type="submit" id="input-submit-enviar" value="Enviar">                
            </form>
        </section>
    </main>

    

</body>

<?php require __DIR__.'/parts/footer.view.php' ?>

</html>