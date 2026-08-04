class FormularioMultistep {

    constructor() {

        this.form = document.querySelector('.form-publicacion-new');

        if (!this.form) {
            return;
        }

        /*Se conserva required en el HTML, pero se desactiva la interfaz nativa para mostrar todos los errores de manera personalziada*/
        this.form.noValidate = true;

        this.nextButtons = this.form.querySelectorAll('.next-btn');

        this.prevButtons = this.form.querySelectorAll('.prev-btn');

        this.fieldsets = this.form.querySelectorAll('fieldset');

        this.currentStep = 0;

        this.errorContainers = {
            0: document.querySelector('#cartel-errores-paso-1'),
            1: document.querySelector('#cartel-errores-paso-2'),
            2: document.querySelector('#cartel-errores-paso-3')
        };

        this.precioInput = document.querySelector('#precio');

        this.prepararErroresDelServidor();
        this.prepararNavegacion();
        this.prepararEnvio();
        this.prepararLimpiezaDeCampos();
    }

    prepararErroresDelServidor() {

        const mensajes = this.form.querySelectorAll('.error-message');

        mensajes.forEach(
            mensaje => {

                mensaje.classList.add('visible');

                const botonCerrar = mensaje.querySelector('.close-button');

                if (botonCerrar) {
                    botonCerrar.onclick = () => {
                        mensaje.remove();
                    };
                }
            }
        );
    }

    prepararNavegacion() {
        this.nextButtons.forEach(
            boton => {

                boton.addEventListener(
                    'click',
                    () => {

                        if (this.validateFields()) {
                            this.nextStep();
                        }
                    }
                );
            }
        );

        this.prevButtons.forEach(
            boton => {

                boton.addEventListener(
                    'click',
                    () => {this.prevStep();}
                );
            }
        );
    }

    prepararEnvio() {
        this.form.addEventListener(
            'submit',
            event => {

                if (!this.validateFields(true)) {
                    event.preventDefault();
                    return;
                }

                /*Se quitan los puntos de miles antes de enviar el precio al backend*/
                this.removeFormattingForSubmit();
            }
        );
    }

    prepararLimpiezaDeCampos() {

        const campos = this.form.querySelectorAll('input, textarea, select');

        campos.forEach(
            campo => {

                const tipoEvento =
                    campo.type === 'file' ||
                    campo.tagName === 'SELECT'
                        ? 'change'
                        : 'input';

                campo.addEventListener(
                    tipoEvento,
                    () => {
                        this.limpiarMarcaCampo(campo);
                    }
                );
            }
        );

        document.addEventListener(
            'imagenes:actualizadas',
            event => {
                const archivos = event.detail && Array.isArray(event.detail.archivos) ? event.detail.archivos : [];

                if (archivos.length === 0) {
                    return;
                }

                const inputImagenes = this.form.querySelector('#drop-input');

                this.limpiarMarcaCampo(inputImagenes);
            }
        );
    }

    validateFields(isFinalValidation = false) {
        const fieldsetActual = this.fieldsets[this.currentStep];

        const contenedorErrores = this.errorContainers[this.currentStep];

        if (!fieldsetActual || !contenedorErrores) {
            return false;
        }

        this.limpiarErroresPaso(fieldsetActual, contenedorErrores);

        let formularioValido = true;
        let primerCampoInvalido = null;

        const registrarError = (
            campo,
            mensaje
        ) => {
            formularioValido = false;

            if (!primerCampoInvalido) {
                primerCampoInvalido =
                    campo;
            }

            this.marcarCampoInvalido(campo);

            this.mostrarError(mensaje, contenedorErrores, campo);
        };

        /*La ubicación se presenta al usuario como un solo dato*/
        if (this.currentStep === 0) {

            const ubicacionInput = document.querySelector('#ubicacion');

            if (!this.ubicacionSeleccionadaEsValida()) {
                registrarError(ubicacionInput, 'Seleccioná una ubicación válida desde las opciones del buscador.');
            }
        }

        /*Campos de la ubicacion*/
        const camposInternosUbicacion =
            new Set([
                'ubicacion',
                'provincia',
                'direccion',
                'direccion_completa'
            ]);

        const camposRequeridos = fieldsetActual.querySelectorAll('[required]');

        camposRequeridos.forEach(
            campo => {

                if (camposInternosUbicacion.has(campo.id)) {
                    return;
                }

                const mensajeError = this.obtenerErrorCampo(campo);

                if (mensajeError !== null) {
                    registrarError(campo, mensajeError);
                }
            }
        );

        if (!formularioValido) {
            contenedorErrores.classList.add('visible');

            if (primerCampoInvalido) {
                primerCampoInvalido.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                window.setTimeout(
                    () => {
                        primerCampoInvalido.focus({
                            preventScroll: true
                        });
                    },
                    250
                );
            }
        }

        return formularioValido;
    }

    ubicacionSeleccionadaEsValida() {
        const provincia = document.querySelector('#provincia');

        const coordenadas = document.querySelector('#direccion');

        const direccionCompleta = document.querySelector('#direccion_completa');

        if (!provincia || !coordenadas || !direccionCompleta) {
            return false;
        }

        if (provincia.value.trim() === '' || coordenadas.value.trim() === '' || direccionCompleta.value.trim() === '') {
            return false;
        }

        try {
            const datosCoordenadas = JSON.parse(coordenadas.value);

            return (
                datosCoordenadas && Number.isFinite(Number(datosCoordenadas.lat)) && Number.isFinite(Number(datosCoordenadas.lng))
            );

        } catch (error) {
            return false;
        }
    }

    obtenerErrorCampo(campo) {
        const nombreCampo = this.obtenerNombreCampo(campo);

        if (campo.type === 'file') {

            if (!campo.files || campo.files.length === 0) {
                return (
                    `Seleccioná al menos una imagen en ` +
                    `"${nombreCampo}".`
                );
            }

            return null;
        }

        const valor = String(campo.value ?? '').trim();

        if (campo.id === 'precio') {

            if (valor === '') {
                return (
                    'Completá el campo ' +
                    '"Precio por noche en USD".'
                );
            }

            const valorNormalizado = valor.replace(/\./g, '').replace(',', '.');

            const numero = Number(valorNormalizado);

            if (!Number.isFinite(numero) || numero <= 0) {
                return (
                    'El precio por noche en USD ' +
                    'debe ser mayor que cero.'
                );
            }

            return null;
        }

        if (valor === '') {
            return (
                `Completá el campo ` +
                `"${nombreCampo}".`
            );
        }

        if (campo.type === 'number') {
            const numero = Number(valor);

            const minimo =
                campo.min !== ''
                    ? Number(campo.min)
                    : null;

            if (!Number.isFinite(numero) || (minimo !== null && numero < minimo)) {
                return (
                    `El campo "${nombreCampo}" ` +
                    `debe ser mayor o igual a ` +
                    `${minimo ?? 1}.`
                );
            }
        }

        const longitudMinima = Number(campo.getAttribute('minlength')) || 0;

        if (longitudMinima > 0 && valor.length < longitudMinima) {
            return (
                `El campo "${nombreCampo}" ` +
                `debe tener al menos ` +
                `${longitudMinima} caracteres.`
            );
        }

        const longitudMaxima = Number(campo.getAttribute('maxlength')) || 0;

        if (longitudMaxima > 0 && valor.length > longitudMaxima) {
            return (
                `El campo "${nombreCampo}" ` +
                `no puede superar ` +
                `${longitudMaxima} caracteres.`
            );
        }

        return null;
    }

    obtenerNombreCampo(campo) {

        const nombrePersonalizado = campo.dataset.errorLabel;

        if (nombrePersonalizado) {
            return nombrePersonalizado;
        }

        if (campo.id) {
            const etiqueta = this.form.querySelector(`label[for="${campo.id}"]`);

            if (etiqueta) {
                return etiqueta.textContent.replace(/\(\*\)/g, '').replace(/\s+/g, ' ').trim();
            }
        }

        return (campo.placeholder || campo.name || 'Campo');
    }

    marcarCampoInvalido(campo) {
        if (!campo) {
            return;
        }

        campo.classList.add('field-invalid');

        campo.setAttribute('aria-invalid', 'true');

        const grupoPrecio = campo.closest('.precio-input-group');

        if (grupoPrecio) {
            grupoPrecio.classList.add('field-invalid-group');
        }

        if (campo.type === 'file') {
            const areaImagenes = campo.closest('#drop-area');

            if (areaImagenes) {
                areaImagenes.classList.add('field-invalid-group');
            }
        }
    }

    limpiarMarcaCampo(campo) {
        if (!campo) {
            return;
        }

        campo.classList.remove('field-invalid');

        campo.removeAttribute('aria-invalid');

        const grupoPrecio = campo.closest('.precio-input-group');

        if (grupoPrecio) {
            grupoPrecio.classList.remove('field-invalid-group');
        }

        if (campo.type === 'file') {
            const areaImagenes = campo.closest('#drop-area');

            if (areaImagenes) {
                areaImagenes.classList.remove('field-invalid-group');
            }
        }

        this.eliminarErrorDeCampo(campo);
    }

    eliminarErrorDeCampo(campo) {
        if (!campo) {
            return;
        }

        const identificador = campo.id || campo.name;

        if (!identificador) {
            return;
        }

        this.form
            .querySelectorAll(
                '.error-message[data-error-for]'
            )
            .forEach(
                mensaje => {
                    if (mensaje.dataset.errorFor === identificador) {
                        mensaje.remove();
                    }
                }
            );

        Object.values(
            this.errorContainers
        ).forEach(
            contenedor => {
                if (contenedor && !contenedor.querySelector('.error-message')) {
                    contenedor.classList.remove('visible');
                }
            }
        );
    }

    limpiarErroresPaso(fieldset, contenedorErrores) {
        contenedorErrores.innerHTML = '';

        contenedorErrores.classList.remove('visible');

        fieldset
            .querySelectorAll(
                '.field-invalid'
            )
            .forEach(
                campo => {
                    campo.classList.remove('field-invalid');

                    campo.removeAttribute('aria-invalid');
                }
            );

        fieldset
            .querySelectorAll(
                '.field-invalid-group'
            )
            .forEach(
                grupo => {
                    grupo.classList.remove('field-invalid-group');
                }
            );
    }

    mostrarError(mensaje, contenedorErrores, campo = null) {
        const errorItem = document.createElement('p');

        errorItem.classList.add('error-message', 'visible');

        /*Se guarad el campo donde se origino el mensaje */
        if (campo) {
            errorItem.dataset.errorFor = campo.id || campo.name || '';
        }

        const texto = document.createElement('span');

        texto.textContent = mensaje;

        const botonCerrar = document.createElement('button');

        botonCerrar.type = 'button';

        botonCerrar.classList.add('close-button');

        botonCerrar.setAttribute('aria-label', 'Cerrar mensaje de error');

        botonCerrar.textContent = '×';

        botonCerrar.addEventListener(
            'click',
            () => {

                errorItem.remove();

                if (contenedorErrores.querySelectorAll('.error-message').length === 0) {
                    contenedorErrores.classList.remove('visible');
                }
            }
        );

        errorItem.appendChild(texto);
        errorItem.appendChild(botonCerrar);

        contenedorErrores.appendChild(errorItem);

    }

    nextStep() {
        if (this.currentStep >= this.fieldsets.length - 1) {
            return;
        }

        this.fieldsets[this.currentStep].classList.add('hidden');

        this.currentStep++;

        this.fieldsets[this.currentStep].classList.remove('hidden');

        this.notificarCambioPaso();
    }

    prevStep() {
        if (this.currentStep <= 0) {
            return;
        }

        this.fieldsets[this.currentStep].classList.add('hidden');

        this.currentStep--;

        this.fieldsets[this.currentStep].classList.remove('hidden');

        this.notificarCambioPaso();
    }

    notificarCambioPaso() {
        document.dispatchEvent(
            new CustomEvent(
                'formulario:paso-cambiado',
                {
                    detail: {
                        paso:
                            this.currentStep + 1
                    }
                }
            )
        );
    }

    removeFormattingForSubmit() {
        if (!this.precioInput) {
            return;
        }

        this.precioInput.value = this.precioInput.value.replace(/\./g, '').replace(',', '.');
    }
}