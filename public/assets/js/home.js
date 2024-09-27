document.addEventListener('DOMContentLoaded', () => {
    
  const promiseCarrouselHome = PAW.cargarScriptPromise('Carrousel', '/assets/js/components/carrouselHome.js');
  const promiseCookier = PAW.cargarScriptPromise("Cookier", "/assets/js/components/cookier.js")

  Promise.all([promiseCookier, promiseCarrouselHome]).then(function() {

    Cookier.init('.form-busqueda-propiedad', ['zona', 'tipo']);

      new CarrouselHome('.home-carousel-container');

      const btn_close = document.querySelector('.close-btn');
      if (btn_close) {
        btn_close.addEventListener('click', function(e) {
          document.querySelector('.overlay').style.display = 'none';
        });
      }

  })

})