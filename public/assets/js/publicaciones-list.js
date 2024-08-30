
  document.addEventListener('DOMContentLoaded', () => {

    const promiseCarrousel = PAW.cargarScriptPromise("Carrousel", "/assets/js/components/carrousel.js");

    Promise.all([promiseCarrousels])-then(function(){
      
        new Carrousel();
   
    }).catch(function(error) {
      // Manejar cualquier error en la carga de scripts
      console.error('Error loading one or more scripts:', error);
    });

  })
    
  
  