class publicacionDetails {
    constructor() {
  
      document.addEventListener('DOMContentLoaded', () => {

        const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');

        Promise.all([promiseMapaLeafLet]).then(function() {

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

      })
    }
}

const appPublicacion = new publicacionDetails()