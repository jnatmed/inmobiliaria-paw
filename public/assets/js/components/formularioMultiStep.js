class FormularioMultistep {
    constructor() {
        this.nextButtons = document.querySelectorAll('.next-btn');
        this.prevButtons = document.querySelectorAll('.prev-btn');
        this.fieldsets = document.querySelectorAll('fieldset');
        this.currentStep = 0;

        this.nextButtons.forEach((button) => {
            button.addEventListener('click', () => {
                this.nextStep();
            });
        });

        this.prevButtons.forEach((button) => {
            button.addEventListener('click', () => {
                this.prevStep();
            });
        });
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