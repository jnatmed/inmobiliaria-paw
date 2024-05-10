class appPAW {
  constructor() {

    document.addEventListener('DOMContentLoaded', () => {
      /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
      
      if (['/'].includes(window.location.pathname))
      {
        PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{
            const mapaLeaf = new MapaLeaflet()
        })
      }        
          

    })
  }
}

const app = new appPAW()
