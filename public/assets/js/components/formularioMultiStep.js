class FormularioMultistep {
    constructor() {
        this.nextButtons = document.querySelectorAll('.next-btn');
        this.prevButtons = document.querySelectorAll('.prev-btn');
        this.fieldsets = document.querySelectorAll('fieldset');
        this.currentStep = 0;

        // Seleccionar los elementos de error para cada paso
        this.errorContainers = {
            0: document.querySelector('#cartel-errores-paso-1'),
            1: document.querySelector('#cartel-errores-paso-2'),
            2: document.querySelector('#cartel-errores-paso-3')
        };

        this.nextButtons.forEach((button) => {
            button.addEventListener('click', () => {
                console.log('siguiente paso')
                if (this.validateFields()) {
                    this.nextStep();
                } else {
                    this.showError();
                }
            });

        });

        this.prevButtons.forEach((button) => {
            button.addEventListener('click', () => {
                this.prevStep();
            });
        });

        // Añadir un event listener al formulario para el envío
        const form = document.querySelector('.form-publicacion-new');
        if (form) {
            form.addEventListener('submit', (event) => {
                if (!this.validateFields(true)) {
                    event.preventDefault(); // Evitar el envío del formulario
                    this.showError(); // Mostrar errores si hay alguno
                } else {
                    this.removeFormattingForSubmit(); // Desformatear números antes de enviar
                }
            });
        }

        // Aplicar el formateo de números en el input de precio
        this.precioInput = document.querySelector('#precio');
        if (this.precioInput) {
            this.precioInput.addEventListener('input', (event) => this.formatNumber(event));
            this.precioInput.addEventListener('blur', (event) => this.formatOnBlur(event));
            this.precioInput.addEventListener('focus', (event) => this.removeFormatting(event));
        }
    }

    validateFields(isFinalValidation = false) {
        let valid = true;
        let firstInvalidInput = null;
        const currentErrorContainer = this.errorContainers[this.currentStep];

        // Limpiar errores anteriores
        console.log("limpiando datos")
        currentErrorContainer.innerHTML = '';
        currentErrorContainer.classList.remove('visible'); // Ocultar el contenedor de errores

        // Validar todos los campos requeridos en el paso actual
        this.fieldsets[this.currentStep].querySelectorAll('input, input[required], textarea[required], select[required]').forEach((input) => {
            console.log("para cada input del paso en curso hacer " + this.currentStep)
            if (!input.value.trim() || (input.type === 'number' && parseFloat(input.value) <= 0)) {
                valid = false;
                console.log("input invalida " + input)
                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
                const placeholder = input.placeholder || input.name;
                this.mostrarError(`El campo "${placeholder}" está incompleto o tiene un valor no permitido.`, currentErrorContainer);
            }
        });

        // Validar el campo de precio en el último paso si es una validación final
        if (isFinalValidation && this.currentStep === this.fieldsets.length - 1) {
            const precioInput = document.querySelector('#precio');
            if (precioInput && parseFloat(precioInput.value) <= 0) {
                valid = false;
                this.mostrarError(`El campo "Precio/Noche" debe ser mayor que cero.`, currentErrorContainer);
            }
        }

        if (!valid) {
            this.showError();
            if (firstInvalidInput) {
                firstInvalidInput.focus(); // Enfocar el primer campo inválido
            }
        }

        return valid;
    }

    mostrarError(message, currentErrorContainer) {

        // let errorContainer = document.querySelector("#cartel-errores-paso-1");
        
        // console.log(errorContainer)
        // creo un error item
        let errorItem = document.createElement("p");
        // agrego una clase al errorItem
        errorItem.classList.add("error-message");
        errorItem.classList.add("visible");

        errorItem.innerHTML = message; // Asignamos el mensaje de error

        // agrego el boton de cerrar, para sacar el mensaje una vez leido
        let closeButton = document.createElement("span");
        closeButton.classList.add("close-button");
        closeButton.innerHTML = `X`

        // asigno el evento para eliminar el errorItem
        closeButton.onclick = () => {
            errorItem.remove();
        };

        // agrego el boton al errorItem
        errorItem.appendChild(closeButton);
        // agrego el errorItem al errorContainer
        currentErrorContainer.appendChild(errorItem);

        // errorContainer.style.display = "flex";
    }



    formatNumber(event) {
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

    removeFormattingForSubmit() {
        let value = this.precioInput.value;
        this.precioInput.value = value.replace(/\./g, '').replace(',', '.'); // Eliminar puntos y cambiar la coma por punto
    }

    showError() {
        const currentErrorContainer = this.errorContainers[this.currentStep];
        currentErrorContainer.classList.add('visible'); // Mostrar el contenedor de error
    }

    nextStep() {
        this.fieldsets[this.currentStep].classList.add('hidden');
        this.currentStep++;
        this.fieldsets[this.currentStep].classList.remove('hidden');
    }

    prevStep() {
        this.fieldsets[this.currentStep].classList.add('hidden');
        this.currentStep--;
        this.fieldsets[this.currentStep].classList.remove('hidden');
    }
}
