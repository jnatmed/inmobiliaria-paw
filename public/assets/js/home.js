document.addEventListener('DOMContentLoaded', () => {
    
    
    PAW.cargarScriptPromise("Cookier", "/assets/js/components/cookier.js")
    .then(() => {
      
        Cookier.init('.form-busqueda-propiedad', ['zona', 'tipo']);

        const btn_close = document.querySelector('.close-btn')
        btn_close.addEventListener('click', function(e) {
          document.querySelector('.overlay').style.display = 'none';
        })

        })
    .catch((error) => {
      console.error('Error al cargar el script del Calendario:', error);
    });
    

})