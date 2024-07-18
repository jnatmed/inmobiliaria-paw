class buscarInmueble {
    constructor() {
  
      document.addEventListener('DOMContentLoaded', () => {
  
          PAW.cargarScript('GestorInmobiliaria', '/assets/js/components/gestor-inmobiliaria.js')
          PAW.cargarScript('Cookier', '/assets/js/components/cookier.js')
          PAW.cargarScript('MapaLeaflet', '/assets/js/components/mapaLeaflet.js', () =>{         
  
            let gestor = new GestorInmobiliaria()
              
          })
          
        })
    }
}

const buscarInmueble = new buscarInmueble()