class MapaGeneral {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => {
            const URL_API = '/api/publicaciones';
            
            // Promesa para cargar el script de Leaflet
            const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');
            
            // Función para obtener publicaciones
            const fetchPublicaciones = async () => {
                try {
                    const response = await fetch(URL_API);
                    if (!response.ok) {
                        throw new Error(`Error en la solicitud: ${response.statusText}`);
                    }
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error al obtener publicaciones:', error);
                    return [];
                }
            };

            // Ejecutar ambas promesas
            Promise.all([
                promiseMapaLeafLet,
                fetchPublicaciones()
            ])
            .then(([_, publicaciones]) => {
        
                    // Inicializar el mapa después de cargar el script y obtener las publicaciones
                    const mapaLeaf = new MapaLeaflet();
        
                    mapaLeaf.agregarPublicacionesAlMapa(publicaciones);

                    const buscarButton = document.querySelector('#buscarUbicacion');
                    const loading = document.querySelector('.loader');
                    const accionesBusqueda = document.querySelector('.busqueda-ubicacion-acciones');
                    const ubicacionInput = document.querySelector('#ubicacion');

                    if (!buscarButton || !loading || !accionesBusqueda || !ubicacionInput) {
                        return;
                    }

                    buscarButton.addEventListener(
                        'click',
                        async event => {

                            event.preventDefault();
                            const address = ubicacionInput.value.trim();

                            if (address === '' || buscarButton.disabled) {
                                return;
                            }

                            loading.classList.add('activo');
                            accionesBusqueda.classList.add('cargando');
                            buscarButton.disabled = true;

                            try {
                                await mapaLeaf.buscar(address, false);
                            } catch (error) {
                                console.error('No se pudo buscar la ubicación:', error);

                            } finally {
                                loading.classList.remove('activo');
                                accionesBusqueda.classList.remove('cargando');
                                buscarButton.disabled = false;
                            }
                        }
                    );             

                })
                .catch(error => {
                    console.error('Error al cargar el mapa o las publicaciones:', error);
                });
        });
    }
}

const mapaGeneral = new MapaGeneral();
