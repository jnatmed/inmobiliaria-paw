class SliderPrecio {
    constructor() {
        this.sliders = document.querySelectorAll('#precio');
        this.valores = document.querySelectorAll('#precio-valor');
        this.forms = document.querySelectorAll('form');

        //Hay mas de un form por ende hay que asignar un listener a cada uno

        this.sliders.forEach((slider, i) => {
            slider.addEventListener('input', () => {
                // Si el valor del slider es 0, mostrar solo "-"
                if (slider.value == 0) {
                    this.valores[i].innerText = "-";
                } else {
                    // Mostrar el valor actualizado del slider
                    this.valores[i].innerText = `$0 - $${this.formatearPrecio(slider.value)}`;
                }
            });
        });

        this.forms.forEach(form => {
            form.addEventListener('reset', () => {
                setTimeout(() => {
                    this.sliders.forEach((slider, i) => {
                        if (slider.value == 0) {
                            this.valores[i].innerText = "-";
                        } else {
                            this.valores[i].innerText = `$0 - $${this.formatearPrecio(slider.value)}`;
                        }
                    });
                }, 0);
            });
        });
    }

    formatearPrecio(valor) {
        return new Intl.NumberFormat('es-AR').format(valor);
    }
}
