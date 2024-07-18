<!DOCTYPE html>
<html lang="es">
<head>

<?php require __DIR__.'/../parts/head.view.php' ?>

</head>

<body>
    
    <?php require __DIR__.'/../parts/header.view.php' ?>

   
<main>
        
        <div class="error-container">
            <div class="error-404-bg"></div>
            <p class="h1">404</p>
            <p class="error-lead">Oops!, algo salio mal.</p>
            <p>No pudimos encontrar lo que buscabas.</p>
            <div class="center">
                <a href="/" class="btn btn-primary">Volver</a>
            </div>  
        </div>
        
</main>

    <?php if(isset($error)) : ?>
        <h4 class="msj msj_error">
        <?= $error; ?>
        </h4>
    <?php endif ?>
    
    
    
</body>
</html>