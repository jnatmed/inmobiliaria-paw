class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
      
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

      if (['/'].includes(window.location.pathname)){
        
        const carousel = document.querySelector('.carousel');
        let currentIndex = 0;
    
        function nextSlide() {
            currentIndex++;
            console.log(`currentIndex: ${currentIndex}`)
            if (currentIndex >= carousel.children.length) {
                currentIndex = 0;
                console.log(`currentIndex(${currentIndex}) >= carousel.children.length(${carousel.children.length}) = ${currentIndex >= carousel.children.length}`)
            }
            showSlide(currentIndex);
        }
    
        function prevSlide() {
            currentIndex--;
            console.log(`currentIndex(${currentIndex}) < 0 ? ${currentIndex < 0}`)
            if (currentIndex < 0) {
                currentIndex = carousel.children.length - 1;
                console.log(`currentIndex: ${currentIndex}`)
            }
            showSlide(currentIndex);
        }
    
        function showSlide(index) {
            console.log(`carousel.clientWidth: ${carousel.clientWidth}`)
            const offset = index * carousel.clientWidth;
            carousel.scrollLeft = offset;
            console.log(`carousel.scrollLeft: ${carousel.scrollLeft} // offset: ${offset}`)
        }
    
        // Agregar listeners para los botones de navegación
        document.getElementById('prevButton').addEventListener('click', prevSlide);
        document.getElementById('nextButton').addEventListener('click', nextSlide);     
      }


    })
  }
}

const app = new appPAW()
