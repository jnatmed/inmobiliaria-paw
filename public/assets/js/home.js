document.addEventListener('DOMContentLoaded', () => {

    PAW.cargarScriptPromise('Carrousel', '/assets/js/components/carrousel.js').then(() => {
      Carrousel.inicializarTodos(document);
    }).catch((error) => {
      console.error('No se pudo cargar el carrusel del home:', error);
    });

    PAW.cargarScriptPromise('Cookier', '/assets/js/components/cookier.js')
        .then(() => {

            const formularioBusqueda = document.querySelector('.form-busqueda-propiedad');

            if (formularioBusqueda) {
                Cookier.init(
                    '.form-busqueda-propiedad',
                    ['zona', 'tipo']
                );
            }
        })
        .catch((error) => {
            console.error('No se pudo cargar el manejo de cookies:', error);
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
});