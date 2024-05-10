<!DOCTYPE html>
<html lang="es">

<head>

    <?php require __DIR__.'/parts/head.view.php' ?>

</head>

<body class="home">

    <?php require __DIR__.'/parts/header.view.php' ?>


    <main>


            <div>
            <!-- Input para la ubicación -->
            <label for="ubicacion">Ubicación:</label>
            <input type="text" id="ubicacion" />
            <button id="buscarUbicacion">Buscar</button>
             </div>

            <div id="mapid"></div>

    </main>

    

</body>

</html>