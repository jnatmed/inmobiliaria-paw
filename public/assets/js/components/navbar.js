document.addEventListener('DOMContentLoaded', () => {

  const menuToggle = document.querySelector('.menu-toggle');
  const navIndex = document.querySelector('.nav-index');

  if (!menuToggle || !navIndex) {
    return;
  }

  const mediaMenuMovil = window.matchMedia('(max-width: 1200px)');

  const actualizarMenu = (abierto, devolverFoco = false) => {
    navIndex.classList.toggle('active', abierto);

    menuToggle.setAttribute('aria-expanded', String(abierto));
    menuToggle.setAttribute('aria-label', abierto ? 'Cerrar menú principal' : 'Abrir menú principal');

    if (!abierto && devolverFoco) {
      menuToggle.focus();
    }
  };

  menuToggle.addEventListener('click', () => {
    actualizarMenu(!navIndex.classList.contains('active'));
  });

  navIndex.addEventListener('click', (event) => {
    if (mediaMenuMovil.matches && event.target.closest('a')) {
      actualizarMenu(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && navIndex.classList.contains('active')) {
      actualizarMenu(false, true);
    }
  });

  document.addEventListener('click', (event) => {
    if (mediaMenuMovil.matches && navIndex.classList.contains('active') && !event.target.closest('header')) {
      actualizarMenu(false);
    }
  });

  const cerrarAlVolverAEscritorio = (event) => {
    if (!event.matches) {
      actualizarMenu(false);
    }
  };

  if (typeof mediaMenuMovil.addEventListener === 'function') {
    mediaMenuMovil.addEventListener('change', cerrarAlVolverAEscritorio);
  } else {
    mediaMenuMovil.addListener(cerrarAlVolverAEscritorio);
  }

});