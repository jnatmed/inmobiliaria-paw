class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
      
      if (['/buscar'].includes(window.location.pathname))
      {

        // PAW.cargarScript('Cookier', '/assets/js/components/cookier.js')
        PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{         

          const mapaLeaf = new MapaLeaflet()

          // const cookier = new Cookier()
          
          const locationDiv = document.querySelector('#location');

          const lastSearch = getCookie('location');

          if (lastSearch) {
              locationDiv.textContent = `Última ubicación: ${lastSearch}`;
              mapaLeaf.buscar(lastSearch);
              console.log(lastSearch)
          }

          document.querySelector('#buscarUbicacion').addEventListener('click', () => {
              const query = document.querySelector('#ubicacion').value;
              fetch(`/buscar?ubicacion=${encodeURIComponent(query)}`) // promesa
                  .then(response => response.json()) // la promesa devuelta por fetch, pasa a este then, que la convierte en json y devuelve otra promesa
                  .then(data => { // la promesa del anterior then, pasa a data, que usa location
                      const location = data.location;
                      locationDiv.textContent = `Última ubicación: ${location}`;
                      setCookie('location', location, 7);
                  })
                  .catch(error => console.error('Error:', error));
          });

          function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }
    
          function getCookie(name) {
              const cname = name + "=";
              const decodedCookie = decodeURIComponent(document.cookie);
              const ca = decodedCookie.split(';');
              for (let i = 0; i < ca.length; i++) {
                  let c = ca[i];
                  while (c.charAt(0) === ' ') {
                      c = c.substring(1);
                  }
                  if (c.indexOf(cname) === 0) {
                      return c.substring(cname.length, c.length);
                  }
              }
              return "";
          }        
  
          // Agregar un event listener al botón de búsqueda
          document.querySelector('#buscarUbicacion').addEventListener('click', () => {
            const address = document.querySelector('#ubicacion').value;
            mapaLeaf.buscar(address);
          });            
            
        })
      }        
          

    })
  }
}

const app = new appPAW()
