document.addEventListener('DOMContentLoaded', () => {

    document.querySelector('.menu-toggle').addEventListener('click', () => {
      document.querySelector('.nav-index').classList.toggle('active');
    });

  });