document.addEventListener('DOMContentLoaded', function () {
    const precioSlider = document.getElementById('precio');
    let preciosValor = document.querySelectorAll('.precio-valor');
    const form = document.querySelector('form');

    // Actualizar el valor del span cuando cambia el valor del slider
    precioSlider.addEventListener('input', function () {
        if (this.value == 0)
            insertarEnTodos(preciosValor, "-")
        else
            insertarEnTodos(preciosValor, this.value)
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