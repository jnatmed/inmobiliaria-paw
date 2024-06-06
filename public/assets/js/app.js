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

          // const cookier = new Cookier()
          
          const locationDiv = document.querySelector('#location');

          // const lastSearch = cookier.getCookie('location');

          // if (lastSearch) {
          //     locationDiv.textContent = `Última ubicación: ${lastSearch}`;
          //     mapaLeaf.buscar(lastSearch);
          //     console.log(lastSearch)
          // }

          // document.querySelector('#buscarUbicacion').addEventListener('click', () => {
          //     const query = document.querySelector('#ubicacion').value;
          //     fetch(`/buscar?ubicacion=${encodeURIComponent(query)}`) // promesa
          //         .then(response => response.json()) // la promesa devuelta por fetch, pasa a este then, que la convierte en json y devuelve otra promesa
          //         .then(data => { // la promesa del anterior then, pasa a data, que usa location
          //             const location = data.location;
          //             locationDiv.textContent = `Última ubicación: ${location}`;
          //             // cookier.setCookie('location', location, 7);
          //         })
          //         .catch(error => console.error('Error:', error));
          // });

          // document.querySelector('.form-publicacion-new').addEventListener('submit', (event) => {
          //     event.preventDefault(); // Prevenir el envío predeterminado del formulario
          // });

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

    })
  }
}

const app = new appPAW()
