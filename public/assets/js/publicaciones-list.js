document.addEventListener(

    'DOMContentLoaded',
    () => {

        const loader = document.getElementById('loader-publicaciones');

        PAW.cargarScriptPromise('Carrousel', '/assets/js/components/carrousel.js').then(() => {
			Carrousel.inicializarTodos(document);
        }).catch((error) => {
			console.error('No se pudieron cargar los carruseles del listado:', error);
		});

        PAW.cargarScriptPromise('SliderPrecio', '/assets/js/components/sliderPrecio.js').then(() => {
			new SliderPrecio();
		}).catch((error) => {
			console.error('No se pudo cargar el selector de precio:', error);
		});

        PAW.cargarScriptPromise('filtrarPublicaciones', '/assets/js/components/filtrarPublicaciones.js')
            .then(() => {
                new filtrarPublicaciones();
            })
            .catch((error) => {
                console.error('No se pudieron cargar los filtros del listado:', error);
            })
            .finally(() => {
                if (loader) {
                    loader.style.display = 'none';
                }
            });
    }
);