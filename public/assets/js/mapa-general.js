class MapaGeneral {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => {
            const URL_API = '/api/publicaciones';
            
            // Promesa para cargar el script de Leaflet
            const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');
            
            const promiseCookier = PAW.cargarScriptPromise("Cookier", "/assets/js/components/cookier.js")

            
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
            Promise.all([promiseCookier, promiseMapaLeafLet, fetchPublicaciones()])
                .then(([_, __, publicaciones]) => {
        
                    // Inicializar el mapa después de cargar el script y obtener las publicaciones
                    const mapaLeaf = new MapaLeaflet();
        
                    mapaLeaf.agregarPublicacionesAlMapa(publicaciones);

                    document.querySelector('#buscarUbicacion').addEventListener('click', async (e) => {
                        e.preventDefault();
                        const address = document.querySelector('#ubicacion').value;

                        // Guardar la dirección en la cookie
                        Cookier.setCookie('ubicacion', address, 7); // Guardar por 7 días                        

                        const loading = document.querySelector('.loader');
                        loading.classList.add('activo');
                        await mapaLeaf.buscar(address, false);
                        loading.classList.remove('activo');
                    });

                    // Restaurar el valor de la cookie al cargar la página
                    window.onload = function() {
                        const savedUbicacion = Cookier.getCookie('ubicacion');
                        if (savedUbicacion) {
                            document.querySelector('#ubicacion').value = savedUbicacion;
                        }
                    };                    

                })
                .catch(error => {
                    console.error('Error al cargar el mapa o las publicaciones:', error);
                });
        });
    }
}

const mapaGeneral = new MapaGeneral();
