class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
      
      if (['/publicacion/new'].includes(window.location.pathname))
        {
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
      
        }


      if (['/buscar', '/publicacion/new'].includes(window.location.pathname))
      {

        PAW.cargarScript('GestorInmobiliaria', '/assets/js/components/gestor-inmobiliaria.js')
        PAW.cargarScript('Cookier', '/assets/js/components/cookier.js')
        PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{         

          let gestor = new GestorInmobiliaria()


        

            
        })
      }        
      
      if (['/', '/publicaciones/list', '/mis_publicaciones'].includes(window.location.pathname)) {
        // Seleccionar todos los elementos con la clase .carousel
        const carousels = document.querySelectorAll('.carousel');
        
        // Iterar sobre cada elemento .carousel
        carousels.forEach(carousel => {
            let currentIndex = 0;
    
            function nextSlide() {
                currentIndex++;
                if (currentIndex >= carousel.children.length) {
                    currentIndex = 0;
                }
                showSlide(carousel, currentIndex);
            }
    
            function prevSlide() {
                currentIndex--;
                if (currentIndex < 0) {
                    currentIndex = carousel.children.length - 1;
                }
                showSlide(carousel, currentIndex);
            }
    
            function showSlide(carousel, index) {
                const offset = index * carousel.clientWidth;
                carousel.scrollLeft = offset;
            }
    
            // Agregar listeners para los botones de navegación
            carousel.parentElement.querySelector('.prevButton').addEventListener('click', prevSlide);
            carousel.parentElement.querySelector('.nextButton').addEventListener('click', nextSlide);
        });
    }
    


    })
  }
}

const app = new appPAW()
