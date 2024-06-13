class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
      
      if (['/publicacion/new'].includes(window.location.pathname))
        {
            PAW.cargarScript('FormularioMultiStep', '/assets/js/components/formularioMultiStep.js');

            PAW.cargarScript("Drag_Drop", "/assets/js/components/drag-drop.js", () => {
              
              document.querySelectorAll('.input-dad').forEach(dropArea => {
                const inputId = dropArea.dataset.input;
                const inputFile = document.querySelector(`#${inputId}`);
                const output = dropArea.nextElementSibling;
                new Drag_Drop(dropArea, inputFile, output);

              });

              const formulario = new FormularioMultistep();
              
            })                   
        }


      if (['/buscar', '/publicacion/new'].includes(window.location.pathname))
      {

        PAW.cargarScript('GestorInmobiliaria', '/assets/js/components/gestor-inmobiliaria.js')
        PAW.cargarScript('Cookier', '/assets/js/components/cookier.js')
        PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{         

          let gestor = new GestorInmobiliaria()

          const mapaLeaf = new MapaLeaflet()
          
          const locationDiv = document.querySelector('#location');

          // Agregar un event listener al botón de búsqueda
          document.querySelector('#buscarUbicacion').addEventListener('click', (event) => {
            event.preventDefault(); // Evitar comportamiento predeterminado del botón
            const address = document.querySelector('#ubicacion').value;
            // console.log(address);
            mapaLeaf.buscar(address);
        });
            
        })
      }        
      
      if (['/reservas'].includes(window.location.pathname)){


        PAW.cargarScript('Calendario', '/assets/js/components/calendario.js', async () =>{

          const calendario = new Calendario()

          fetch('/reservas/intervalos')
          .then(response => {
              if (!response.ok) {
                  throw new Error('Error al obtener los intervalos de reserva');
              }
              return response.json();
          })
          .then(periodos => {
              
              console.log(periodos);
              const calendar = new Calendario();
              calendario.marcarIntervalos(periodos);              
              // Aquí puedes utilizar los intervalos de reserva como desees
          })
          .catch(error => {
              console.error('Error al cargar los intervalos de reserva:', error);
          });


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
