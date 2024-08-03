document.addEventListener('DOMContentLoaded', () => {
    
    
    PAW.cargarScriptPromise("Calendario", "/assets/js/components/calendario.js")
    .then(() => {
      
        const calendario = new Calendario()

        calendario.init()
        })
    .catch((error) => {
      console.error('Error al cargar el script del Calendario:', error);
    });
    
    

})