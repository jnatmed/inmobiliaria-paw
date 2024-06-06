<header> <!--block-->

<a href="/"><h1 class="icono-header-pawperties">PawPerties</h1></a>
    <nav class="nav-index">
        <ul class="lista-nav-main">
            <?php foreach ($this->menu as $item) : ?>                 
            <li>
                <a class="anchor-item-main" href="<?= $item['href'] ?>"><?= $item['name']?></a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>
    <nav class="nav-sesion">
        <ul class="lista-opciones-sesion">
            <li class="item-opcion-sesion">
                <a class="anchor-item-sesion" href="/iniciar-sesion">INICIAR SESION</a>
            </li>
        </ul>
    </nav>
</header>
