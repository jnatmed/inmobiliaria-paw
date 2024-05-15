class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
      
      if (['/buscar'].includes(window.location.pathname))
      {
        PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{
            const mapaLeaf = new MapaLeaflet()

          // Agregar un event listener al botón de búsqueda
          document.getElementById('buscarUbicacion').addEventListener('click', () => {
            const address = document.getElementById('ubicacion').value;
            mapaLeaf.buscar(address);
          });            
            
        })
      }        
          

    })
  }
}

const app = new appPAW()
