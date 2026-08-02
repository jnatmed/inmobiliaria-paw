document.addEventListener('DOMContentLoaded', () => {

  const menuToggle = document.querySelector('.menu-toggle');

  const navIndex = document.querySelector('.nav-index');

  if (!menuToggle || !navIndex) {
    return;
  }

  menuToggle.addEventListener(
    'click',
    () => {navIndex.classList.toggle('active');}
  );

});