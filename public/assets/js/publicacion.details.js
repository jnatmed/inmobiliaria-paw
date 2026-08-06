class publicacionDetails {

    constructor() {
        document.addEventListener(
            'DOMContentLoaded',
            () => {

                PAW.cargarScriptPromise('Carrousel', '/assets/js/components/carrousel.js').then(() => {
                  Carrousel.inicializarTodos(document);
                }).catch((error) => {
                  console.error('No se pudo cargar el carrusel del detalle:', error);
                });

                PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js')
                    .then(() => {

                        const latitudElement = document.querySelector('#latitud');

                        const longitudElement = document.querySelector('#longitud');

                        if (!latitudElement || !longitudElement) {
                            return;
                        }

                        const latitud = latitudElement.value;

                        const longitud = longitudElement.value;

                        if (latitud && longitud) {
                            const mapaLeaflet = new MapaLeaflet();

                            mapaLeaflet.buscarPorLatitudyLongitud(latitud, longitud);
                        } else {
                            console.error('Valores de latitud o longitud en nulo');
                        }
                    })
                    .catch((error) => {
                        console.error('No se pudo cargar el mapa del detalle:', error);
                    });

                const botonCerrar = document.querySelector('.close-btn');

                const overlay = document.querySelector('.overlay');

                if (botonCerrar && overlay) {
                    botonCerrar.addEventListener(
                        'click',
                        () => {
                            overlay.style.display = 'none';
                        }
                    );
                }
            }
        );
    }
}

const appPublicacion = new publicacionDetails();