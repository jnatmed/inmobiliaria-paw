
        <?php foreach ($this->menuPerfil as $item) : ?>                 
            <li class="opciones_nav">
                <a href="<?= $item['href'] ?>"><?= $item['name']?></a>
            </li>
        <?php endforeach; ?>


