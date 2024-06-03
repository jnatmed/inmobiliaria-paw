<!DOCTYPE html>
<html lang="es">

<head>

    <?php require __DIR__.'/parts/head.view.php' ?>

</head>

<body class="home">

    <?php require __DIR__.'/parts/header.view.php' ?>


    <main class="main-home">

    <form action="/publicacion/new" method="post" class="form-publicacion-new">
        <fieldset class="paso-1">
            <h2 class="titulo-form-publicacion">PASO 1 de 3.</h2>
            <p>Completá los datos del dueño del alojamiento y de la persona que va a administrar la priopiedad.</p>
            <h3 class="titulo-datos-propietario">Datos de Propietario</h3>
            <p>No son visibles para el usuario. Deben ser los mismos que van a usarse para verificar la identidad</p>
            <input type="text" name="nombre" id="nombre" class="input-form-publicacion" placeholder="Nombre">
            <input type="text" name="apellido" id="apellido" class="input-form-publicacion" placeholder="Apellido">
            <input type="number" name="dni" id="dni" class="input-form-publicacion" placeholder="DNI">
            <h3 class="titulo-datos-propietario">Datos del Administrador</h3>            
            <p>
                Son los datos que va a utilizar el huesped para comunicarse y reservar
            </p>          
            <input type="tel" name="telefono" id="telefono" class="input-form-publicacion" placeholder="Numero de Telefono">
            <input type="email" name="email" id="email" class="input-form-publicacion" placeholder="Correo Electronico">
            <h3 class="titulo-datos-propietario">Ubicacion del Alojamiento</h3>
            <p>Indica la direccion de tu propiedad para que podamos determinar la ubicacion en el mapa. La direccion exacta no va a ser visible para los usuario</p>
            <input type="text" name="provincia" id="provincia" class="input-form-publicacion" placeholder="Provincia">
            <input type="text" name="localidad" id="localidad" class="input-form-publicacion" placeholder="Localidad">
            <input type="text" name="direccion" id="direccion" class="input-form-publicacion" placeholder="Direccion">
            <p>Move el mapa hasta posicionar el puntero en el lugar adecuado</p>
            <?php require __DIR__.'/parts/mapa.view.php' ?>
            <h3 class="titulo-datos-propietario">Datos del Alojamiento</h3>
            <input type="text" name="nombre-alojamiento" id="nombre-alojamiento" class="input-form-publicacion" placeholder="Nombre del Alojamiento">
            <input type="text" name="tipo-alojamiento" id="tipo-alojamiento" class="input-form-publicacion" placeholder="Tipo de Alojamiento">
            <input type="number" name="capacidad-maxima" id="capacidad-maxima" class="input-form-publicacion" placeholder="Capacidad Maxima">
            <input type="number" name="cant-banios" id="cant-banios" class="input-form-publicacion" placeholder="Cantidad de Baños">
            <input type="number" name="cantidad-dormitorios" id="cantidad-dormitorios" class="input-form-publicacion" placeholder="Capacidad de Dormitorios">
            <h3 class="titulo-datos-propietario">Instalaciones</h3>
            <label for="cochera" class="lbl-chekbox">Cochera<input type="checkbox" name="cochera" id="cochera" title="Cochera"></label>
            <label for="pileta" class="lbl-chekbox">Pileta<input type="checkbox" name="pileta" id="pileta" title="Pileta"></label>
            <h3 class="titulo-datos-propietario">Equipamiento</h3>
            <label for="aire-acondicionado" class="lbl-chekbox">Aire Acondicionado<input type="checkbox" name="aire-acondicionado" id="aire-acondicionado" title="Aire Acondicionado"></label>
            <label for="wifi" class="lbl-chekbox">Wi-Fi<input type="checkbox" name="wifi" id="wifi" title="Wi-Fi"></label>
        </fieldset>
        

        <input type="submit" value="Enviar Publicacion" class="btn-form-enviar-publicacion">



    </form>

    </main>

    <?php require __DIR__.'/parts/footer.view.php' ?>    
    
</body>