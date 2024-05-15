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

            let address = "Libertad 160, CABA";
            
            mapaLeaf.buscar(address);
            
        })
      }        
          

    })
  }
}

const app = new appPAW()
