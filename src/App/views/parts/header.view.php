<header> <!--block-->

<a href="/"><h1 class="icono-header-pawperties">PawPerties</h1></a>
    <nav class="nav-index">
        <ul class="lista-nav-main">
            <?php 
            global $log;
            $log->info("datos this->menu: ", [$this->menu]);
            foreach ($this->menu as $item) : 
            ?>                 
            <li>
                <a class="anchor-item-main" href="<?= $item['href'] ?>"><?= $item['name']?></a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>
    <nav class="nav-sesion">
        <ul class="lista-opciones-sesion">
            <li class="item-opcion-sesion">
                    <?php if($this->usuario->isUserLoggedIn()): ?>    
                        <a class="anchor-item-sesion" href="/cerrar-sesion">CERRAR SESION</a>
                    <?php else: ?>
                        <a class="anchor-item-sesion" href="/iniciar-sesion">INICIAR SESION</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
</header>
