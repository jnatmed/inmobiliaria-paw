class publicacionDetails {
    constructor() {
  
      document.addEventListener('DOMContentLoaded', () => {

        const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');
        const promiseCarrousel = PAW.cargarScriptPromise('Carrousel', '/assets/js/components/carrousel.js');

        Promise.all([promiseMapaLeafLet, promiseCarrousel]).then(function() {


            const carousel = new Carrousel('.container-imagenes-publicacion');


            const latitudElement = document.querySelector('#latitud');
            const longitudElement = document.querySelector('#longitud');

            const latitud = latitudElement.value;
            const longitud = longitudElement.value;
        
            if(latitud && longitud) {

                let mapaLeaft = new MapaLeaflet()
                mapaLeaft.buscarPorLatitudyLongitud(latitud, longitud);

            }else{
                console.error('Valores de latitud o longitud en nulo')
            }

        })

        const btn_close = document.querySelector('.close-btn')
        btn_close.addEventListener('click', function(e) {
          document.querySelector('.overlay').style.display = 'none';
        })
        
      })
    }
}

const appPublicacion = new publicacionDetails()