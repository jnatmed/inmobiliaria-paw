<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>

    <main class="main-home">
        <form action="/publicacion/new" method="post" class="form-publicacion-new">
            <fieldset class="paso-1">
                <h2 class="titulo-form-publicacion">PASO 1 de 3</h2>
                <p>Completá los datos del dueño del alojamiento y de la persona que va a administrar la propiedad.</p>

                <h3 class="titulo-datos-propietario">Datos de Propietario</h3>
                <p>No son visibles para el usuario. Deben ser los mismos que van a usarse para verificar la identidad</p>
                <input type="text" name="nombre" id="nombre" class="input-form-publicacion" placeholder="Nombre" required>
                <input type="text" name="apellido" id="apellido" class="input-form-publicacion" placeholder="Apellido" required>
                <input type="number" name="dni" id="dni" class="input-form-publicacion" placeholder="DNI" required>

                <h3 class="titulo-datos-propietario">Datos del Administrador</h3>
                <p>Son los datos que va a utilizar el huésped para comunicarse y reservar</p>
                <input type="tel" name="telefono" id="telefono" class="input-form-publicacion" placeholder="Número de Teléfono" required>
                <input type="email" name="email" id="email" class="input-form-publicacion" placeholder="Correo Electrónico" required>

                <h3 class="titulo-datos-propietario">Ubicación del Alojamiento</h3>
                <p>Indica la dirección de tu propiedad para que podamos determinar la ubicación en el mapa. La dirección exacta no va a ser visible para los usuarios</p>
                <input type="text" name="provincia" id="provincia" class="input-form-publicacion" placeholder="Provincia" required>
                <input type="text" name="localidad" id="localidad" class="input-form-publicacion" placeholder="Localidad" required>
                <input type="text" name="direccion" id="direccion" class="input-form-publicacion" placeholder="Dirección" required>
                <p>Mueve el mapa hasta posicionar el puntero en el lugar adecuado</p>
                <?php require __DIR__ . '/parts/mapa.view.php' ?>

                <h3 class="titulo-datos-propietario">Datos del Alojamiento</h3>
                <input type="text" name="nombre-alojamiento" id="nombre-alojamiento" class="input-form-publicacion" placeholder="Nombre del Alojamiento" required>
                <input type="text" name="tipo-alojamiento" id="tipo-alojamiento" class="input-form-publicacion" placeholder="Tipo de Alojamiento" required>
                <input type="number" name="capacidad-maxima" id="capacidad-maxima" class="input-form-publicacion" placeholder="Capacidad Máxima" required>
                <input type="number" name="cant-banios" id="cant-banios" class="input-form-publicacion" placeholder="Cantidad de Baños" required>
                <input type="number" name="cantidad-dormitorios" id="cantidad-dormitorios" class="input-form-publicacion" placeholder="Cantidad de Dormitorios" required>

                <h3 class="titulo-datos-propietario">Instalaciones</h3>
                <label for="cochera" class="lbl-chekbox">Cochera
                    <input type="checkbox" name="cochera" id="cochera" title="Cochera">
                </label>
                <label for="pileta" class="lbl-chekbox">Pileta
                    <input type="checkbox" name="pileta" id="pileta" title="Pileta">
                </label>

                <h3 class="titulo-datos-propietario">Equipamiento</h3>
                <label for="aire-acondicionado" class="lbl-chekbox">Aire Acondicionado
                    <input type="checkbox" name="aire-acondicionado" id="aire-acondicionado" title="Aire Acondicionado">
                </label>
                <label for="wifi" class="lbl-chekbox">Wi-Fi
                    <input type="checkbox" name="wifi" id="wifi" title="Wi-Fi">
                </label>
            </fieldset>

            <fieldset class="paso-2">
                <h2 class="titulo-form-publicacion">PASO 2 de 3</h2>
                <p>Agrega fotos de tu alojamiento</p>
                <label for="imagen_plato"> </label>
                <input type="file" id="imagen_plato" name="imagen_plato" accept=".jpeg, .png" hidden>
                <div class="container-dad">
                    <div class="input-dad">
                        <p>Cargue una imagen de ilustracion aqui</p>
                    </div>
                    <div class="output-dad"></div>
                </div>
            </fieldset>

            <fieldset class="paso-3">
                <h2 class="titulo-form-publicacion">PASO 3 de 3</h2>
                <p>Especifica tus condiciones para el alquiler, recuerda que mientras más flexible seas más oportunidades tendrás</p>

                <h3 class="titulo-datos-propietario">Normas del Alojamiento</h3>
                <textarea name="normas-alojamiento" class="descripcion-alojamiento" required></textarea>

                <h3 class="titulo-datos-propietario">Descripción</h3>
                <textarea name="descripcion-alojamiento" class="descripcion-alojamiento" required></textarea>
            </fieldset>

            <input type="submit" value="Enviar Publicación" class="btn-form-enviar-publicacion">
        </form>
    </main>

    <?php require __DIR__ . '/parts/footer.view.php' ?>
</body>
</html>
