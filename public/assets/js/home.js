document.addEventListener('DOMContentLoaded', () => {
    
  const promiseCarrousel = PAW.cargarScriptPromise('Carrousel', '/assets/js/components/carrousel.js');
  const promiseCookier = PAW.cargarScriptPromise("Cookier", "/assets/js/components/cookier.js")

  Promise.all([promiseCookier, promiseCarrousel]).then(function() {

    Cookier.init('.form-busqueda-propiedad', ['zona', 'tipo']);
    const carousel = new Carrousel('.container-imagenes-publicacion');

    const btn_close = document.querySelector('.close-btn')
    btn_close.addEventListener('click', function(e) {
      document.querySelector('.overlay').style.display = 'none';
    })

  })

})