class publicacionNew {
    constructor() {
  
      document.addEventListener('DOMContentLoaded', () => {
            /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */

            // Cargar scripts individualmente usando Promesas
            const promiseFormularioMultiStep = PAW.cargarScriptPromise('FormularioMultistep', '/assets/js/components/formularioMultiStep.js');
            const promiseDrag_Drop = PAW.cargarScriptPromise("Drag_Drop", "/assets/js/components/drag-drop.js");
            const promiseMapaLeafLet = PAW.cargarScriptPromise('MapaLeaflet', '/assets/js/components/mapaLeaflet.js');

            // Usar Promise.all para esperar a que todos los scripts se carguen
            Promise.all([promiseFormularioMultiStep, promiseDrag_Drop, promiseMapaLeafLet]).then(function() {
                // Una vez que todos los scripts se han cargado, ejecutar el código que depende de esos scripts

                const mapaLeaf = new MapaLeaflet()

                const locationDiv = document.querySelector('#location');
                                
                // Agregar un event listener al botón de búsqueda
                document.querySelector('#buscarUbicacion').addEventListener('click', (event) => {
                  event.preventDefault(); // Evitar comportamiento predeterminado del botón
                  const address = document.querySelector('#ubicacion').value;
                  // console.log(address);
                  mapaLeaf.buscar(address);
                });

                document.querySelectorAll('.input-dad').forEach(dropArea => {
                    const inputId = dropArea.dataset.input;
                    const inputFile = document.querySelector(`#${inputId}`);
                    const output = dropArea.nextElementSibling;
                    
                    // Inicializar la funcionalidad Drag and Drop
                    new Drag_Drop(dropArea, inputFile, output);

                    // Inicializar el formulario multistep
                    new FormularioMultistep();
                });


            }).catch(function(error) {
                // Manejar cualquier error en la carga de scripts
                console.error('Error loading one or more scripts:', error);
            });
      })

    }
}

const appPublicacion = new publicacionNew()