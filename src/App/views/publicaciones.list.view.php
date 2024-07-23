<!DOCTYPE html>
<html lang="es">

<head>
    <?php require __DIR__ . '/parts/head.view.php' ?>
    <script src="/assets/js/publicaciones-list.js"></script>
    <script src="/assets/js/components/sliderPrecio.js" defer></script>
</head>

<body class="home">
    <?php require __DIR__ . '/parts/header.view.php' ?>

    <main class="main-home">
        <section class="container_publicaciones">

            <!-- FILTROS -->
            <aside class="filtro-container">
                <h2>Filtros</h2>
                <form method="GET" action="/publicaciones/list" class="form-filtros">
                    <?php require __DIR__ . '/parts/search-filters.view.php' ?>
                </form>
            </aside>

            <!-- PUBLICACIONES -->
            <section class="publicaciones-list">
                <h2 class="h2-titulo-publicaciones">Lista de Publicaciones</h2>
                <?php require __DIR__ . '/parts/lista-publicaciones.view.php' ?>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/parts/footer.view.php' ?>
</body>

</html>