class publicacionDetails {
    constructor() {
  
      document.addEventListener('DOMContentLoaded', () => {

        const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');

        Promise.all([promiseMapaLeafLet]).then(function() {

            const latitud = document.querySelector('#latitud');
            const longitud = document.querySelector('#longitud');
            

            if(latitud && longitud) {
                promiseMapaLeafLet.buscarPorLatitudyLongitud(latitud, longitud);
            }else{
                console.error('Valores de latitud o longitud en nulo')
            }

        })

      })
    }
}

const appPublicacion = new publicacionDetails()