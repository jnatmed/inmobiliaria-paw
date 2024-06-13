<!DOCTYPE html>
<html lang="es">

<head>

    <?php require __DIR__.'/parts/head.view.php' ?>

</head>

<body class="home">

    <?php require __DIR__.'/parts/header.view.php' ?>


    <main class="main-home">

       <section class="seccion-portada">

            <iframe class="video-background" src="https://www.youtube.com/embed/PETj3DEbkVs?start=0&end=10&autoplay=1&loop=1&playlist=PETj3DEbkVs&mute=1" title="Tour Mini departamento de 30m2 en Santiago de Chile 🇨🇱" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>    

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
                <p class="descripcion-pawperties">
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
        </section>

        <section class="seccion-contactanos">
            <section class="titulo-contactanos">
                <h2>Contactanos</h2>
                <p>¿Tenes alguna consulta para hacernos?</p>
            </section>

            <form action="/enviar-msj-contactanos" class="form-contactanos" method="post">
            <section class="form_campos">
                <fieldset class="form-group">
                    <label for="input-nombre">Nombre:</label>
                    <label for="input-apellido">Apellido:</label>
                    <input type="text" id="input-nombre" name="nombre">
                    <input type="text" id="input-apellido" name="apellido">
                </fieldset>


                <label for="input-telefono">Teléfono:</label>
                <input type="tel" id="input-telefono" name="telefono">

                <label for="input-email">Email:</label>
                <input type="email" id="input-email" name="email">

                <label for="input-descripcion">Descripción:</label>
                <textarea type="text" id="input-descripcion" name="descripcion" rows="10"></textarea>
            </section>
            <section class="form_info">
                <h3>Ubicacion</h3>
                <p>[Domicilio]</p>
                <p>Lujan - Buenos Aires</p>
                <h3>telefono</h3>
                <p>[Numero de telefono]</p>
                <input type="submit" id="input-submit-enviar" value="Enviar">
            </section>

                                
            </form>
        </section>
    </main>

    

</body>

<?php require __DIR__.'/parts/footer.view.php' ?>

</html>