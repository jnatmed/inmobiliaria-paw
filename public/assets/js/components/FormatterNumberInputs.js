class FormatterNumberInputs {
    constructor(selector) {
        this.input = document.querySelector(selector);
        if (this.input) {
            this.initialize();
        }
    }

    initialize() {
        this.input.addEventListener('input', (event) => this.formatInput(event));
        this.input.addEventListener('blur', (event) => this.formatOnBlur(event));
        this.input.addEventListener('focus', (event) => this.removeFormatting(event));
    }

    formatInput(event) {
        let value = event.target.value.replace(/\./g, ''); // Eliminar puntos para formatear correctamente
        value = value.replace(/[^\d,]/g, ''); // Mantener solo números y comas

        // Si hay una coma en la entrada, maneja la parte entera y decimal por separado
        let parts = value.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Añadir puntos como separadores de miles
        event.target.value = parts.join(',');
    }

    formatOnBlur(event) {
        let value = event.target.value.replace(/\./g, '');
        let parts = value.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        event.target.value = parts.join(',');
    }

    removeFormatting(event) {
        let value = event.target.value;
        event.target.value = value.replace(/\./g, ''); // Eliminar el formato para permitir edición
    }
}

// Uso
document.addEventListener('DOMContentLoaded', () => {
    new FormatterNumberInputs('#precio'); // Aplica al input con el ID "precio"
});
