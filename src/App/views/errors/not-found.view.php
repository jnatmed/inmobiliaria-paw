<!DOCTYPE html>
<html lang="es">
<head>

<?php require __DIR__.'/../parts/head.view.php' ?>

</head>

<body>
    
    <?php require __DIR__.'/../parts/header.view.php' ?>

   
<main>
        
        <section class="titulo titulo_portada">
            <h2>NOT FOUND</h2>
            <p>No pudimos encontrar la pagina que buscas</p>
        </section>
        
</main>

    <?php if(isset($error)) : ?>
        <h4 class="msj msj_error">
        <?= $error; ?>
        </h4>
    <?php endif ?>
    
    
    
</body>
</html>