class SliderPrecio {

    constructor() {

        this.sliders = document.querySelectorAll('.filtro-precio');

        this.formularios = document.querySelectorAll('.form-filtros');

        this.sliders.forEach((slider) => {
            slider.addEventListener(
                'input',
                () => {
                    this.actualizarTexto(slider);
                }
            );
        });

        this.formularios.forEach(
            (formulario) => {
                formulario.addEventListener(
                    'reset',
                    () => {
                        setTimeout(() => {
                            const slider = formulario.querySelector('.filtro-precio');

                            if (slider) {
                                this.actualizarTexto(slider);
                            }
                        }, 0);
                    }
                );
            }
        );
    }

    actualizarTexto(slider) {

        const formulario = slider.closest('form');

        const valor = formulario ? formulario.querySelector('.precio-valor') : null;

        if (!valor) {
            return;
        }

        valor.textContent
            = Number(slider.value) === 0
            ? '-'
            : 'USD 0 - USD '
                + this.formatearPrecio(
                    slider.value
                );
    }

    formatearPrecio(valor) {
        return new Intl.NumberFormat('es-AR').format(valor);
    }
}