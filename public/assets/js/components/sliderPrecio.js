document.addEventListener('DOMContentLoaded', function () {
    const precioSlider = document.getElementById('precio');
    const precioValor = document.getElementById('precio-valor');
    const form = document.querySelector('form');

    // Actualizar el valor del span cuando cambia el valor del slider
    precioSlider.addEventListener('input', function () {
        precioValor.innerText = this.value;
    });

    // Restablecer el valor del span cuando se hace clic en el botón de limpiar
    form.addEventListener('reset', function () {
        setTimeout(() => {
            precioValor.innerText = precioSlider.value;
        }, 0);
    });
});