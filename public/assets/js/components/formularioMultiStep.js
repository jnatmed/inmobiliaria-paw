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
    }

    validateFields() {
        let valid = true;
        let firstInvalidInput = null;
        const currentErrorContainer = this.errorContainers[this.currentStep];

        // Limpiar errores anteriores
        currentErrorContainer.innerHTML = '';
        currentErrorContainer.classList.remove('visible'); // Ocultar el contenedor de errores

        // Validar todos los campos requeridos en el paso actual
        this.fieldsets[this.currentStep].querySelectorAll('input[required], textarea[required], select[required]').forEach((input) => {
            if (!input.value.trim()) {
                valid = false;
                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
                const placeholder = input.placeholder || input.name;
                currentErrorContainer.innerHTML += `El campo "${placeholder}" está incompleto.<br>`;
            }
        });

        if (!valid) {
            this.showError();
            if (firstInvalidInput) {
                firstInvalidInput.focus(); // Enfocar el primer campo inválido
            }
        }

        return valid;
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
