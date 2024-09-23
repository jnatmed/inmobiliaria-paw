document.addEventListener('DOMContentLoaded', function () {
    const precioSlider = document.getElementById('precio');
    
    const precioValor = document.getElementById('precio-valor');
    const form = document.querySelector('form');

    // Actualizar el valor del span cuando cambia el valor del slider
    precioSlider.addEventListener('input', function () {
        // Si el valor es 0, mostrar solo "-"
        if (this.value == 0) {
            precioValor.innerText = "-";
        } else {
            // Mostrar desde 0 hasta el valor actual
            precioValor.innerText = `0 - $ ${this.value}`;
        }
    });

    // Restablecer el valor cuando se hace clic en el botón de limpiar
    form.addEventListener('reset', function () {
        setTimeout(() => {
            if (precioSlider.value == 0)
                insertarEnTodos(preciosValor, "-")
            else
                insertarEnTodos(preciosValor, precioSlider.value)
        }, 0);
    });

    function insertarEnTodos(elementos, texto)  {
        elementos.forEach(element => {
            element.innerText = texto;
        });
    };
});