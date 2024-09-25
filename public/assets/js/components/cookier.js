class Cookier {
    // Establecer una cookie
    static setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = `${name}=${value};${expires};path=/`;
    }

    // Obtener una cookie por su nombre
    static getCookie(name) {
        const nameEQ = name + "=";
        const cookiesArray = document.cookie.split(';');
        for (let i = 0; i < cookiesArray.length; i++) {
            let cookie = cookiesArray[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length, cookie.length);
            }
        }
        return null;
    }

    // Método para inicializar el almacenamiento de cookies y restaurar los valores en los campos de búsqueda
    static init(formSelector, fields) {
        // Restaurar los valores guardados en las cookies al cargar la página
        window.onload = function() {
            fields.forEach(field => {
                const savedValue = Cookier.getCookie(field);
                if (savedValue) {
                    document.getElementById(field).value = savedValue;
                }
            });
        };

        // Guardar los valores en las cookies cuando se envía el formulario
        document.querySelector(formSelector).addEventListener('submit', function() {
            fields.forEach(field => {
                const fieldValue = document.getElementById(field).value;
                Cookier.setCookie(field, fieldValue, 7);  // Guardar por 7 días
            });
        });
    }
}
