class publicacionNew {
    constructor() {
  
      document.addEventListener('DOMContentLoaded', () => {
            /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */

            // Cargar scripts individualmente usando Promesas
            const promiseFormularioMultiStep = PAW.cargarScriptPromise('FormularioMultistep', '/assets/js/components/formularioMultiStep.js');
            const promiseDragDrop = PAW.cargarScriptPromise("DragDrop", "/assets/js/components/drag-drop.js");
            const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');
            const promiseFormatterNumberInputs = PAW.cargarScriptPromise('FormatterNumberInputs', '/assets/js/components/FormatterNumberInputs.js');

            // Usar Promise.all para esperar a que todos los scripts se carguen
            Promise.all([promiseFormatterNumberInputs, promiseFormularioMultiStep, promiseDragDrop, promiseMapaLeafLet]).then(function() {
                // Una vez que todos los scripts se han cargado
                new FormularioMultistep();
                
                new FormatterNumberInputs('#precio'); // Selecciona los inputs tipo número que indiquen

                const mapaLeaf = new MapaLeaflet();

                new DragDrop();

                const locationDiv = document.querySelector('#location');

                // Agregar un event listener al botón de búsqueda
                document.querySelector('#buscarUbicacion').addEventListener('click', async (event) => {
                  event.preventDefault(); // Evitar comportamiento predeterminado del botón
                  const address = document.querySelector('#ubicacion').value;
                  const loading = document.querySelector('.loader');
                  loading.classList.add('activo');
                  // console.log(address);
                  await mapaLeaf.buscar(address);
                  loading.classList.remove('activo');
                  
                });

            }).catch(function(error) {
                // Manejar cualquier error en la carga de scripts
                console.error('Error loading one or more scripts:', error);
            });
      })

    }
}

const appPublicacion = new publicacionNew()