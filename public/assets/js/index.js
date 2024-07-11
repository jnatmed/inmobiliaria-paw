class appPAW {
  constructor() {
    document.addEventListener('DOMContentLoaded', () => {
      PAW.cargarScriptPromise("Carrousel", "/assets/js/components/carrousel.js")
        .then(() => {
          let carrousel = new Carrousel();
        })
        .catch((error) => {
          console.error('Error al cargar el script del Carrousel:', error);
        });
    });
  }
}

const app = new appPAW()
