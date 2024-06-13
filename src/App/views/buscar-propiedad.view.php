<!DOCTYPE html>
<html lang="es">

<head>

    <?php require __DIR__.'/parts/head.view.php' ?>

</head>

<body class="home">

    <?php require __DIR__.'/parts/header.view.php' ?>


    <main>

    <section class="seccion-busqueda">
            <form action="" class="form-busqueda-propiedad" method="GET">
                <label for="input-zona" class="label-form" id="input-zona">Zona</label>
                <input type="text" class="input-form-busqueda" placeholder="Elegi tu Zona...">
                <label for="input-tipo" class="label-form">Tipo</label>
                <input type="text" class="input-form-busqueda" id="input-tipo" placeholder="Tipo">
                <label for="input-categoria" class="label-form">Categoria</label>
                <input type="text" class="input-form-busqueda" id="input-categoria" placeholder="Categoria">
                <input type="submit" value="Buscar" class="btn-form-busqueda">
            </form>

        <?php require __DIR__.'/parts/mapa.view.php' ?>
    </section>

    </main>
    
    <?php require __DIR__.'/parts/footer.view.php' ?>
</body>