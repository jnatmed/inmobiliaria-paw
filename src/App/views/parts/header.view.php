<header> <!--block-->
        <h1><a href="/" class="logo logo_chico" id="menuDesktop">Paw Burger</a></h1>
        <input type="checkbox" name="menuHamburguesa" id="menuHamburguesa">
        <label for="menuHamburguesa" class="logo logo_chico" id="menuMobile">Paw Burger</label>
        <h1><a href="/" class="logo logo_grande_mobile">Paw Burger</a></h1>


        <nav class="container_nav"> 
            <ul class="nav_menu"> 

                <?php require __DIR__.'/nav.view.php' ?>            

                <li class="opciones_nav">
                    <input type="checkbox" name="menuGestionEmpleado" id="checkPerfilEmpleado">
                    <label for="checkPerfilEmpleado" class="labelPerfilEmpleado">PERFIL EMPLEADO</label>
                    <ul class="submenu submenuEmpleado">

                        <?php require __DIR__.'/nav_empleado.view.php' ?>            

                    </ul>
                </li>
            </ul>
             <ul class="nav_usuario">
                <li class="opciones_nav">
                    <input type="checkbox" name="menuPerfil" id="menuPerfil">
                    <label for="menuPerfil" class="sesion">INICIAR SESION</label>
                    <ul class="submenu submenu_">

                        <?php require __DIR__.'/nav_perfil.view.php' ?>            

                    </ul>
                </li>
            </ul>
            <!--  -->
        </nav>
</header>
