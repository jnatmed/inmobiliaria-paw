document.addEventListener('DOMContentLoaded', () => {
    
    
    PAW.cargarScriptPromise("Cookier", "/assets/js/components/cookier.js")
    .then(() => {
      
        Cookier.init('.form-busqueda-propiedad', ['zona', 'tipo']);

        })
    .catch((error) => {
      console.error('Error al cargar el script del Calendario:', error);
    });
    

})