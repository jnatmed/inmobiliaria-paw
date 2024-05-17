class Cookier {

    // Establecer una cookie con nombre, valor y número de días hasta su expiración
    setCookie(name, value, days) {
        if (typeof name !== 'string' || typeof value !== 'string' || typeof days !== 'number') {
            throw new Error("Parámetros inválidos");
        }
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
    }

    // Obtener el valor de una cookie por su nombre
    getCookie(name) {
        const cname = name + "=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(cname) === 0) {
                return c.substring(cname.length, c.length);
            }
        }
        return "";
    }

    // Eliminar una cookie por su nombre
    deleteCookie(name) {
        this.setCookie(name, "", -1);
    }
}