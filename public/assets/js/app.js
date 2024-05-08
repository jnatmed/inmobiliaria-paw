class appPAW {
    constructor() {

        document.addEventListener("DOMContentLoaded", () => {
            /**
             * cargo la clase Datos, contiene los datos de prueba
             * para la carga del formulario
             *  */
            PAW.cargarScript("Mapper", "/assets/js/components/mapper.js");


        });
    }
}

let app = new appPAW();





